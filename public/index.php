<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

// Sanitizar input
$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING) ?: 'home';

// Mapa de rutas a controlador y sus args
$routes = [
    'home'    => [
        App\Controllers\HomeController::class,
        [
            'model' => $droneModel,
            'db'    => $dbConnection,
        ],
    ],

    'catalog' => [
        App\Controllers\CatalogController::class,
        [
            'droneModel' => $droneModel,
            'db'           => $dbConnection,
        ],
    ],

    'product' => [
        App\Controllers\DroneController::class,
        [
            'droneModel' => $droneModel,
        ],
    ],

    'cart'    => [
        App\Controllers\CartController::class,
        [
            'cartModel'    => new \App\Models\CartModel((int)($_SESSION['user_id'] ?? ''), $dbConnection),
            'droneModel' => $droneModel,
        ],
    ],

    'login'   => [
        App\Controllers\LoginController::class,
        [
            'userModel' => $userModel,
        ],
    ],

    'register'  => [
        App\Controllers\RegisterController::class,
        [
            'userModel' => $userModel,
        ],
    ],

    'logout'  => [
        App\Controllers\LogoutController::class, [],
    ],

    'profile' => [
        App\Controllers\ProfileController::class, [
            'userModel' => $userModel,
            'droneModel' => $droneModel,
            'db'          => $dbConnection
        ],
    ],

];

if (!isset($routes[$page])) {
    http_response_code(404);
    include __DIR__ . '/../src/Views/404.php';
    exit;
}

// Inyección automática de dependencias por Reflection
[$class, $deps] = $routes[$page];
$ref            = new \ReflectionClass($class);
$args           = [];

$ctor  = $ref->getConstructor();
if ($ctor) {
    foreach ($ctor->getParameters() as $param) {
        $name        = $param->getName();
        $args[]      = $deps[$name] ?? null;
    }
}

$controller = $ref->newInstanceArgs($args);

try {
    $controller->handleRequest();
} catch (\Exception $e) {
    error_log("Error en controlador {$class}: " . $e->getMessage());
    http_response_code(500);
    include __DIR__ . '/../src/Views/500.php';
    exit;
}