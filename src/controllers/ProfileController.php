<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Models\DroneModel;
use PDO;

class ProfileController
{
    private UserModel  $userModel;
    private DroneModel $droneModel;
    private PDO        $db;

    public function __construct(UserModel $userModel, DroneModel $droneModel, PDO $db)
    {
        $this->userModel  = $userModel;
        $this->droneModel = $droneModel;
        $this->db         = $db;
    }

    public function handleRequest(): void
    {
        if (empty($_SESSION['user_id'])) {
            $_SESSION['error'] = "Debes iniciar sesión para acceder a tu perfil";
            header('Location: ?page=login');
            exit;
        }

        try {
            $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING) ?: 'view';
            match ($action) {
                'edit'    => $this->editProfile(),
                'update'  => $this->updateProfile(),
                'history' => $this->viewHistory(),
                default   => $this->viewProfile(),
            };
        } catch (\Exception $e) {
            error_log("Error en ProfileController: " . $e->getMessage());
            http_response_code(500);
            include __DIR__ . '/../Views/500.php';
            exit;
        }
    }

    private function viewProfile(): void
    {
        $user         = $this->userModel->getUserById((int)$_SESSION['user_id']);
        $transactions = $this->userModel->getTransactions((int)$_SESSION['user_id']);
        include __DIR__ . '/../Views/profile/profile.php';
    }

    private function editProfile(): void
    {
        $user    = $this->userModel->getUserById((int)$_SESSION['user_id']);
        $genders = $this->getGenders();
        include __DIR__ . '/../Views/profile/edit.php';
    }

    private function updateProfile(): void
    {
        // CSRF
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')
        ) {
            http_response_code(400);
            echo "Solicitud inválida";
            exit;
        }

        // Mapeo de campos del formulario a columnas reales
        $data = [
            'id'          => (int)$_SESSION['user_id'],
            'full_name'   => filter_input(INPUT_POST, 'nombre',     FILTER_SANITIZE_STRING) ?: '',
            'email'       => filter_input(INPUT_POST, 'email',      FILTER_SANITIZE_EMAIL)  ?: '',
            'phone'       => filter_input(INPUT_POST, 'telefono',   FILTER_SANITIZE_STRING) ?: '',
            'address'     => filter_input(INPUT_POST, 'direccion',  FILTER_SANITIZE_STRING) ?: '',
            'gender_id'   => filter_input(INPUT_POST, 'genero',     FILTER_VALIDATE_INT),
        ];

        try {
            if (empty($data['full_name']) || empty($data['email']) || $data['gender_id'] === false) {
                throw new \Exception("Datos del formulario inválidos");
            }

            $success = $this->userModel->updateUser($data);
            if ($success) {
                $_SESSION['success']   = "Perfil actualizado correctamente";
                $_SESSION['user_name'] = $data['full_name'];
                header('Location: ?page=profile');
                exit;
            }
            throw new \Exception("No se pudo actualizar el perfil");
        } catch (\Exception $e) {
            error_log("Error en updateProfile: " . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            header('Location: ?page=profile&action=edit');
            exit;
        }
    }

    private function viewHistory(): void
    {
        $transactions = $this->userModel->getTransactions((int)$_SESSION['user_id']);
        include __DIR__ . '/../Views/profile/history.php';
    }

    private function getGenders(): array
    {
        try {
            $stmt = $this->db->query("SELECT id_gender, name FROM genders");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en getGenders: " . $e->getMessage());
            return [];
        }
    }
}