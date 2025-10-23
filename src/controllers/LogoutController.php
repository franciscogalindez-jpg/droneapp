<?php
namespace App\Controllers;

class LogoutController
{
    public function handleRequest(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //     if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        //         http_response_code(400);
        //         echo "Solicitud inválida";
        //         exit;
        //     }
        // } else {
        //     // Si sólo admites POST, rechaza GET
        //     http_response_code(405);
        //     echo "Método no permitido";
        //     exit;
        // }

        // Limpia las variables de sesión
        $_SESSION = [];

        // Borra la cookie de sesión si existe
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Destruye la sesión
        session_destroy();

        // Redirige al home (página principal)
        header('Location: ?page=home');
        exit;
    }
}
