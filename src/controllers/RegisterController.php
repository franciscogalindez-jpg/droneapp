<?php
namespace App\Controllers;

use App\Models\UserModel;

class RegisterController {
    private UserModel $userModel;

    public function __construct(UserModel $userModel) {
        $this->userModel = $userModel;
    }

    public function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleRegistration();
        } else {
            $this->showRegistrationForm();
        }
    }

    private function handleRegistration(): void {
        // CSRF
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            http_response_code(400);
            echo "Solicitud inválida.";
            exit;
        }

        // Sanitizar entrada
        $data = array_map('trim', [
            'card_id'          => $_POST['card_id'] ?? '',
            'username'         => $_POST['username'] ?? '',
            'full_name'        => $_POST['full_name'] ?? '',
            'email'            => $_POST['email'] ?? '',
            'password'         => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'phone'            => $_POST['phone'] ?? '',
            'address'          => $_POST['address'] ?? '',
            'gender_id'        => (int)($_POST['gender_id'] ?? 1),
        ]);

        // Validar contraseñas
        if ($data['password'] !== $data['confirm_password']) {
            $_SESSION['error'] = "Las contraseñas no coinciden.";
            $this->showRegistrationForm($data);
            return;
        }

        try {
            $this->userModel->createUser($data);
            $_SESSION['success'] = "Registro exitoso. Por favor inicia sesión.";
            header('Location: ?page=login');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->showRegistrationForm($data);
        }
    }

    private function showRegistrationForm(array $data = []): void {
        $view = __DIR__ . '/../Views/register.php';
        extract($data, EXTR_SKIP);
        if (!file_exists($view)) {
            throw new \Exception('Vista register.php no encontrada');
        }
        include $view;
    }
}