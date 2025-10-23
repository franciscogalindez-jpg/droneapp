<?php
namespace App\Controllers;

use App\Models\UserModel;

class LoginController
{
    private UserModel $userModel;

    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    public function handleRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLogin();
        } else {
            $this->showLoginForm();
        }
    }

    private function handleLogin(): void
    {
        // CSRF
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            http_response_code(400);
            echo "Solicitud inválida";
            exit;
        }

        // Sanitizar entrada
        $email    = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '';
        $password = $_POST['password'] ?? '';

        // Validación de campos
        if (empty($email) || empty($password)) {
            $_SESSION['error'] = "Todos los campos son obligatorios";
            $this->showLoginForm();
            return;
        }

        // Verificar credenciales
        $user = $this->userModel->verifyCredentials($email, $password);

        if ($user) {
            session_regenerate_id(true);
            $_SESSION['user_id']    = $user['id_user'];
            $_SESSION['username']   = $user['username'];
            $_SESSION['user_role']  = strtolower($user['role_name']);
            $_SESSION['last_login'] = time();

            // Redirigir al destino guardado o al home
            $redirect = $_SESSION['redirect_url'] ?? '?page=home';
            unset($_SESSION['redirect_url']);
            $_SESSION['success'] = "Bienvenido, " . $user['username'];
            header("Location: $redirect");
            exit;
        }

        // Falla login: retraso para mitigar fuerza bruta
        sleep(2);
        $_SESSION['error'] = "Credenciales incorrectas";
        $this->showLoginForm();
    }

    private function showLoginForm(array $data = []): void
    {
        $view = __DIR__ . '/../Views/login.php';
        extract($data, EXTR_SKIP);
        if (!file_exists($view)) {
            throw new \Exception('Vista login.php no encontrada');
        }
        include $view;
    }
}