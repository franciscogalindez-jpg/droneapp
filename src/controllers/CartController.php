<?php
namespace App\Controllers;

use App\Models\CartModel;
use App\Models\DroneModel; 
use App\Config\Database;

$database    = new Database();
$dbConnection = $database->getConnection();
 
if (!empty($_SESSION['user_id'])) {
    require_once __DIR__ . '/../models/CartModel.php'; // Ruta relativa correcta
    $cartModel = new CartModel((int)$_SESSION['user_id'], $dbConnection);
    $_SESSION['cart_count'] = $cartModel->getCartItemCount();
} else {
    $_SESSION['cart_count'] = 0;
}

class CartController
{
    private CartModel $cartModel;
    private DroneModel $droneModel;

    public function __construct(CartModel $cartModel, DroneModel $droneModel)
    {
        $this->cartModel  = $cartModel;
        $this->droneModel = $droneModel;
    }

    public function handleRequest(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->handleUnauthenticated();
        }

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->routePost();
            } elseif (isset($_GET['remove'])) {
                $this->removeFromCart();
            }

            $this->showCart();
        } catch (\Exception $e) {
            error_log("Error en CartController: " . $e->getMessage());
            $this->showErrorPage();
        }
    }

    private function routePost(): void
    {
        if (isset($_POST['add_to_cart'])) {
            $this->addToCart();
        } elseif (isset($_POST['update_quantity'])) {
            $this->updateQuantity();
        } elseif (isset($_POST['clear_cart'])) {
            $this->clearCart();
        }
    }

    private function handleUnauthenticated(): void
    {
        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success'  => false,
                'message'  => 'Debes iniciar sesión',
                'loginUrl' => '?page=login'
            ]);
            exit;
        }
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        $_SESSION['error']        = "Debes iniciar sesión para acceder al carrito";
        header("Location: ?page=login");
        exit;
    }

    private function addToCart(): void
    {
        // CSRF
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            http_response_code(400);
            echo "Solicitud inválida";
            exit;
        }

        // Validar y sanitizar
        $droneId = filter_input(INPUT_POST, 'drone_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
        $startDate = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_STRING);
        $endDate = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_STRING);

        if ($startDate) {
            $now = new \DateTime();
            $start = new \DateTime($startDate);
            $end   = new \DateTime($endDate);

            if ($start < $now) {
                $this->sendError("La fecha de inicio no puede ser anterior a hoy.");
                return;
            }
            if ($end <= $start) {
                $this->sendError("La fecha de fin debe ser posterior a la de inicio.");
                return;
            }
        }


        if (!$droneId) {
            $this->sendError("ID de dron inválido");
        }

        // Validar fechas si es un alquiler
        if (isset($_POST['is_rental']) && $_POST['is_rental'] == 1) {
            if (!$startDate || !$endDate || new \DateTime($startDate) >= new \DateTime($endDate)) {
                $this->sendError("Fechas de alquiler inválidas");
                return;
            }
        } else {
            // Si no es alquiler, las fechas pueden ser null o ignoradas
            $startDate = null;
            $endDate = null;
        }

        // Obtener el dron para calcular el precio del alquiler
        $drone = $this->droneModel->getDroneById($droneId);
        if (!$drone) {
            $this->sendError("Dron no encontrado");
            return;
        }

        $rentalPrice = 0;
        if ($startDate && $endDate) {
            $rentalPrice = $this->calculateRentalCost($drone['hourly_rate'], $drone['daily_rate'], $startDate, $endDate) * $quantity;
        }

        $success = $this->cartModel->addToCart(
            $droneId,
            $quantity,
            $startDate,
            $endDate,
            $rentalPrice // Pasar el precio del alquiler al modelo
        );

        $this->respondOrRedirect($success, 'Añadido al carrito');
    }

    private function calculateRentalCost(float $hourlyRate, float $dailyRate, string $startDate, string $endDate): float
    {
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $interval = $start->diff($end);
        $days = $interval->d;
        $hours = $interval->h;

        $totalCost = ($days * $dailyRate) + ($hours * $hourlyRate);
        return round($totalCost, 2);
    }

    private function removeFromCart(): void
    {
        $droneId = filter_input(INPUT_GET, 'remove', FILTER_VALIDATE_INT) ?: 0;
        if (!$droneId) {
            $this->sendError("ID inválido");
        }
        $success = $this->cartModel->removeFromCart($droneId);
        $this->respondOrRedirect($success, 'Dron eliminado del carrito');
    }

    private function updateQuantity(): void
    {
        // CSRF validation (similar a addToCart)
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            http_response_code(400);
            echo "Solicitud inválida";
            exit;
        }

        $droneId = filter_input(INPUT_POST, 'drone_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if (!$droneId || $quantity === false) {
            $this->sendError("Datos de cantidad inválidos");
            return;
        }

        // Verificar stock disponible
        $drone = $this->droneModel->getDroneById($droneId);
        if (!$drone || (int)$drone['drone_stock'] < $quantity) {
            $this->sendError("Cantidad no disponible en stock");
            return;
        }

        $success = $this->cartModel->updateQuantity($droneId, $quantity);

        if ($success) {
            // Recalcular el precio del alquiler si es aplicable
            $cartItem = $this->cartModel->getCartItem($droneId, $this->cartModel->getPendingCartId());
            if ($cartItem['start_date'] && $cartItem['end_date']) {
                $rentalPrice = $this->calculateRentalCost(
                    $drone['hourly_rate'],
                    $drone['daily_rate'],
                    $cartItem['start_date'],
                    $cartItem['end_date']
                ) * $quantity;
                $this->cartModel->updateRentalPrice($droneId, $rentalPrice);
            }
            $this->respondOrRedirect(true, 'Cantidad actualizada');
        } else {
            $this->respondOrRedirect(false, 'Error al actualizar la cantidad');
        }
    }

    private function clearCart(): void
    {
        $success = $this->cartModel->clearCart();

        // Si detectas que viene de fetch() (AJAX), devuelves JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => $success]);
            exit;
        }

        // Si es un POST normal, haces redirect o render
        $this->respondOrRedirect($success, 'Carrito vaciado correctamente');
    }


    private function showCart(): void
    {
        $cartItems = $this->cartModel->getCartItems();
        $total = 0;
        $subtotalGeneral = 0; // Variable para almacenar el subtotal general

        foreach ($cartItems as &$item) {
            // Obtener datos actualizados del dron
            $drone = $this->droneModel->getDroneById((int)$item['drone_id']);
            if (!$drone) continue;

            $subtotalItem = 0;
            if ($item['start_date'] && $item['end_date']) {
                $startDate = new \DateTime($item['start_date']);
                $endDate = new \DateTime($item['end_date']);
                $interval = $startDate->diff($endDate);
                $rentalDays = $interval->d;
                $item['rental_days'] = $rentalDays;
                $subtotalItem = $item['rental_price'];
            } else {
                $subtotalItem = $drone['daily_rate'] * $item['quantity'];
            }

            $item['drone']    = $drone;
            $item['subtotal'] = $subtotalItem;
            $subtotalGeneral += $subtotalItem; // Acumular el subtotal general
            $total += $subtotalItem; // Inicialmente, el total es el subtotal
        }
        unset($item);

        // Calcular el costo de envío (5.5% del total general)
        $shippingCost = $total * 0.055;

        // Sumar el costo de envío al subtotal y total
        $total += $shippingCost;

        // Pasar el costo de envío a la vista
        $data['cartItems'] = $cartItems;
        $data['total'] = $total;
        $data['shippingCost'] = $shippingCost; // Pasar el costo de envío
        include __DIR__ . '/../Views/cart.php';
    }

    private function respondOrRedirect(bool $ok, string $msg): void
    {
        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success'    => $ok,
                'message'    => $ok ? $msg : 'Error: ' . $msg,
                'cart_count' => $this->cartModel->getCartItemsCount()
            ]);
            exit;
        }
        $_SESSION[$ok ? 'success' : 'error'] = $msg;
        header("Location: ?page=cart");
        exit;
    }

    private function sendError(string $msg): void
    {
        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode(['success'=>false,'message'=>$msg]);
            exit;
        }
        $_SESSION['error'] = $msg;
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '?page=home'));
        exit;
    }

    private function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    private function showErrorPage(): void
    {
        http_response_code(500);
        include __DIR__ . '/../Views/500.php';
        exit;
    }
}