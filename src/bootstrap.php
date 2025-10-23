<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
use App\Models\DroneModel;
use App\Models\UserModel;
//use App\Models\CartModel;

// 1. Sesión al inicio
session_start();

// 2. Generar CSRF token solo si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verificar la expiración del carrito
//(new CartModel())->expireCarts();

// 3. Cerrar sesión tras setearlo
// session_write_close();

// 4. Conexión a BD
require_once __DIR__ . '/../vendor/autoload.php';

try {
    $database    = new Database();
    $dbConnection = $database->getConnection();

    $droneModel   = new DroneModel($dbConnection);
    $userModel      = new UserModel($dbConnection);

} catch (Exception $e) {
    error_log("Error crítico: " . $e->getMessage());
    http_response_code(500);
    echo "Error crítico: no se pudo iniciar la aplicación." . $e->getMessage();
    exit;
}