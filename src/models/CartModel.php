<?php
namespace App\Models;

use PDO;
use PDOException;

class CartModel
{
    private int $userId;
    private PDO $db;

    public function __construct(int $userId, PDO $db)
    {
        $this->userId = $userId;
        $this->db     = $db;
    }

    /**
     * Devuelve todos los ítems del carrito del usuario.
     */
    public function getCartItems(): array
    {
        $sql = "
            SELECT
                ci.cart_id,
                ci.drone_id,
                ci.quantity,
                ci.start_date,
                ci.end_date,
                ci.rental_price,
                d.description,
                d.hourly_rate,
                d.daily_rate,
                c.name AS categoria,
                p.name AS proveedor
            FROM cart_items ci
            JOIN carts ct ON ci.cart_id = ct.id_cart
            JOIN drones d ON ci.drone_id = d.id_drone
            JOIN categories c ON d.category_id = c.id_category
            JOIN provider_drones pd ON pd.drone_id = d.id_drone
            JOIN providers p ON pd.provider_id = p.id_provider
            WHERE ct.user_id = :uid
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $this->userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve un ítem concreto del carrito, o null si no existe.
     */
    public function getCartItem(int $droneId, int $cartId): ?array
    {
        $sql = "
            SELECT ci.*
            FROM cart_items ci
            WHERE ci.cart_id = :cid
              AND ci.drone_id = :did
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':cid' => $cartId,
            ':did' => $droneId,
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Cuenta el número total de drones en el carrito.
     */
    public function getCartItemCount(): int
    {
        $sql = "
        SELECT COALESCE(SUM(ci.quantity), 0) AS cnt
        FROM cart_items ci
        JOIN carts ct ON ci.cart_id = ct.id_cart
        WHERE ct.user_id = :uid
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $this->userId]);
        return (int)$stmt->fetchColumn();
    }

    public function addToCart(int $droneId, int $quantity, ?string $startDate = null, ?string $endDate = null, float $rentalPrice = 0.00): bool
     {
        try {
            $this->db->beginTransaction();

            $cartId = $this->getPendingCartId() ?: $this->createCart();

            // 1. Verificar stock disponible 
            $stock = $this->getDroneStock($droneId);
            if ($stock === null || $stock < $quantity) {
                $this->db->rollBack();
                return false; // No hay suficiente stock
            }

            // 2. Si el dron ya está en el carrito para este usuario, actualizamos cantidad
            $existing = $this->getCartItem($droneId, $cartId);
            if ($existing) {
                $newQuantity = $existing['quantity'] + $quantity;
                if ($stock < $newQuantity) {
                    $this->db->rollBack();
                    return false; // No hay suficiente stock incluso sumando
                }
                $sql = "
                    UPDATE cart_items
                    SET quantity = :qty,
                        start_date = :start_date,
                        end_date = :end_date,
                        rental_price = :rental_price
                    WHERE cart_id = :cid
                      AND drone_id = :did
                ";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':qty' => $newQuantity,
                    ':cid' => $cartId,
                    ':did' => $droneId,
                    ':start_date' => $startDate,
                    ':end_date' => $endDate,
                    ':rental_price' => $rentalPrice,
                ]);        
            } else {
                $sql = "
                    INSERT INTO cart_items (cart_id, drone_id, quantity, start_date, end_date, rental_price)
                    VALUES (:cid, :did, :qty, :start_date, :end_date, :rental_price)
                ";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':cid' => $cartId,
                    ':did' => $droneId,
                    ':qty' => $quantity,
                    ':start_date' => $startDate,
                    ':end_date' => $endDate,
                    ':rental_price' => $rentalPrice,
                ]);
            }

            // 3. Reducir el stock en provider_drones
            $sqlStock = "
                UPDATE provider_drones
                SET stock = stock - :qty
                WHERE drone_id = :did
            ";
            $stmtStock = $this->db->prepare($sqlStock);
            $stmtStock->execute([
                ':qty' => $quantity,
                ':did' => $droneId,
            ]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error en addToCart (con fechas y precio): " . $e->getMessage());
            return false;
        }
    }

    public function updateRentalPrice(int $droneId, float $rentalPrice): bool
    {
        $sql = "
            UPDATE cart_items ci
            JOIN carts ct ON ci.cart_id = ct.id_cart
            SET ci.rental_price = :price
            WHERE ct.user_id = :uid
            AND ci.drone_id = :did
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':price' => $rentalPrice,
            ':uid' => $this->userId,
            ':did' => $droneId,
        ]);
    }

    private function getDroneStock(int $droneId): ?int
    {
        $stmt = $this->db->prepare("
            SELECT stock
            FROM provider_drones
            WHERE drone_id = :did
        ");
        $stmt->execute([':did' => $droneId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['stock'] : null;
    }

    /**
     * Actualiza la cantidad de un ítem en el carrito.
     */
    public function updateQuantity(int $droneId, int $newQuantity): bool
    {
        $sql = "
            UPDATE cart_items ci
            JOIN carts ct ON ci.cart_id = ct.id_cart
            SET ci.quantity = :qty
            WHERE ct.user_id = :uid
            AND ci.drone_id = :did
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':qty' => $newQuantity,
            ':uid' => $this->userId,
            ':did' => $droneId,
        ]);
    }

    /**
     * Elimina un ítem del carrito.
     */
    public function removeFromCart(int $droneId): bool
    {
        try {
            $this->db->beginTransaction();

            // 1) Obtener cart_id
            $cartId = $this->getPendingCartId();
            if (!$cartId) throw new \Exception("Carrito no existe.");

            // 2) Leer cantidad del item
            $stmt = $this->db->prepare("
                SELECT quantity
                FROM cart_items
                WHERE cart_id = :cid AND drone_id = :did
            ");

            $stmt->execute([':cid'=>$cartId, ':did'=>$droneId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) throw new \Exception("Item no encontrado.");
            $qty = (int)$row['quantity'];

            // 3) Restaurar stock
            $stmtUp = $this->db->prepare("
                UPDATE provider_drones
                SET stock = stock + :qty
                WHERE drone_id = :did
            ");
            $stmtUp->execute([':qty'=>$qty, ':did'=>$droneId]);

            // 4) Borrar el item
            $stmtDel = $this->db->prepare("
                DELETE FROM cart_items
                WHERE cart_id = :cid AND drone_id = :did
            ");
            $stmtDel->execute([':cid'=>$cartId, ':did'=>$droneId]);

            // 5) Si ya no hay más items, borrar el carrito
            $stmtCount = $this->db->prepare("
                SELECT COUNT(*) AS cnt
                FROM cart_items
                WHERE cart_id = :cid
            ");
            $stmtCount->execute([':cid'=>$cartId]);
            $count = (int)$stmtCount->fetchColumn();
            if ($count === 0) {
                $this->db->prepare("
                DELETE FROM carts WHERE id_cart = :cid
                ")->execute([':cid'=>$cartId]);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Error en removeFromCart: ".$e->getMessage());
            return false;
        }
    }

    /**
     * Vacía todo el carrito del usuario.
     */
    public function clearCart(): bool
    {
        try {
            $this->db->beginTransaction();

            // 1) Obtengo todos los items del carrito del usuario
            $sql = "
                SELECT ci.drone_id, ci.quantity
                FROM cart_items ci
                JOIN carts ct ON ci.cart_id = ct.id_cart
                WHERE ct.user_id = :uid
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':uid' => $this->userId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 2) Para cada item, repongo stock
            $stmtUp = $this->db->prepare("
                UPDATE provider_drones
                SET stock = stock + :qty
                WHERE drone_id = :did
            ");
            foreach ($items as $it) {
                $stmtUp->execute([
                    ':qty' => $it['quantity'],
                    ':did' => $it['drone_id']
                ]);
            }

            // 3) Borro los items
            $this->db->exec("
                DELETE ci
                FROM cart_items ci
                JOIN carts ct ON ci.cart_id = ct.id_cart
                WHERE ct.user_id = {$this->userId}
            ");

            // 4) Borro el carrito
            $this->db->exec("
                DELETE
                FROM carts
                WHERE user_id = {$this->userId}
            ");

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error en clearCart: " . $e->getMessage());
            return false;
        }
    }

    // Obtiene el ID del carrito pendiente (si existe).
    public function getPendingCartId(): ?int
    {
        $stmt = $this->db->prepare("
            SELECT id_cart
            FROM carts
            WHERE user_id = :uid
            LIMIT 1
        ");
        $stmt->execute([':uid' => $this->userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id_cart'] : null;
    }

    
    // Crea un nuevo carrito para el usuario.
    private function createCart(): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO carts (user_id, created_at, expires_at)
            VALUES (:uid, NOW(), DATE_ADD(NOW(), INTERVAL 30 MINUTE))
        ");
        $stmt->execute([':uid' => $this->userId]);
        return (int)$this->db->lastInsertId();
    }

    // Elimina el carro cuando expira y devuelve los drones al stock
    public function expireCarts(): void
    {
        try {
            $this->db->beginTransaction();

            // 1) obtengo los carts expirados
            $sql = "
                SELECT id_cart
                FROM carts
                WHERE expires_at IS NOT NULL
                AND expires_at < NOW()
            ";
            $expired = $this->db->query($sql)->fetchAll(PDO::FETCH_COLUMN);

            if (empty($expired)) {
                $this->db->commit();
                return;
            }

            // 2) por cada cart expirado repongo stock
            $sqlItems = "
                SELECT drone_id, quantity
                FROM cart_items
                WHERE cart_id = :cid
            ";
            $stmtItems = $this->db->prepare($sqlItems);
            $stmtUp    = $this->db->prepare("
                UPDATE provider_drones
                SET stock = stock + :qty
                WHERE drone_id = :did
            ");

            foreach ($expired as $cid) {
                $stmtItems->execute([':cid' => $cid]);
                $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
                foreach ($items as $it) {
                    $stmtUp->execute([
                        ':qty' => $it['quantity'],
                        ':did' => $it['drone_id']
                    ]);
                }
            }

            // 3) borro todos los items de esos carts expirados
            $in = implode(',', array_map('intval', $expired));
            $this->db->exec("
                DELETE FROM cart_items
                WHERE cart_id IN ({$in})
            ");
            // 4) borro los carts
            $this->db->exec("
                DELETE FROM carts
                WHERE id_cart IN ({$in})
            ");

            $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error en expireCarts: " . $e->getMessage());
        }
    }

}