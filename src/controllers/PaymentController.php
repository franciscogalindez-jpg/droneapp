<?php
namespace App\Controllers;

use App\Models\CartModel;
use App\Models\ProductModel;
use PDO;

class PaymentController
{
    private CartModel    $cartModel;
    private ProductModel $productModel;
    private PDO          $db;

    public function __construct(CartModel $cartModel, ProductModel $productModel, PDO $db)
    {
        $this->cartModel    = $cartModel;
        $this->productModel = $productModel;
        $this->db           = $db;
    }

    public function handleRequest(): void
    {
        if (empty($_SESSION['user_id'])) {
            $_SESSION['error'] = "Debes iniciar sesión para realizar un pago";
            header("Location: ?page=login");
            exit;
        }

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->processPayment();
            } else {
                $this->showCheckoutForm();
            }
        } catch (\Exception $e) {
            error_log("Error en PaymentController: " . $e->getMessage());
            http_response_code(500);
            include __DIR__ . '/../Views/500.php';
            exit;
        }
    }

    private function showCheckoutForm(): void
    {
        $items = $this->cartModel->getCartItems();
        if (empty($items)) {
            $_SESSION['error'] = "Tu carrito está vacío";
            header("Location: ?page=cart");
            exit;
        }
        include __DIR__ . '/../Views/checkout.php';
    }

    private function processPayment(): void
    {
        // CSRF
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
            http_response_code(400);
            echo "Solicitud inválida";
            exit;
        }

        $items = $this->cartModel->getCartItems();
        if (empty($items)) {
            $_SESSION['error'] = "Tu carrito está vacío";
            header("Location: ?page=cart");
            exit;
        }

        $this->db->beginTransaction();
        try {
            // Insertar transacción
            $stmt = $this->db->prepare("
                INSERT INTO transacciones
                  (id_usuario, fecha_inicio, fecha_fin, estado_transaccion)
                VALUES
                  (:uid, NOW(), DATE_ADD(NOW(), INTERVAL 1 DAY), 'completada')
            ");
            $stmt->bindParam(':uid', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->execute();
            $txId = (int)$this->db->lastInsertId();

            // Insertar ítems de alquiler
            $ins = $this->db->prepare("
                INSERT INTO alquileres
                  (id_transaccion, id_dron, cantidad, es_alquiler)
                VALUES
                  (:tx, :dron, :qty, :rental)
            ");
            foreach ($items as $item) {
                $ins->bindParam(':tx',     $txId,                  PDO::PARAM_INT);
                $ins->bindParam(':dron',   $item['id_dron'],       PDO::PARAM_INT);
                $ins->bindParam(':qty',    $item['cantidad'],      PDO::PARAM_INT);
                $ins->bindParam(':rental', $item['es_alquiler'],   PDO::PARAM_BOOL);
                $ins->execute();
            }

            $this->db->commit();
            $this->cartModel->clearCart();

            $_SESSION['success'] = "¡Pago exitoso! Tu número de transacción es #{$txId}";
            header("Location: ?page=profile");
            exit;

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Error en processPayment: " . $e->getMessage());
            $_SESSION['error'] = "Error al procesar el pago. Por favor intenta nuevamente.";
            $this->showCheckoutForm();
        }
    }
}