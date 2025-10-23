<?php
namespace App\Controllers;

use App\Models\AdminModel;

class AdminController
{
    private AdminModel $adminModel;

    public function __construct(AdminModel $adminModel)
    {
        $this->adminModel = $adminModel;
    }

    public function handleRequest(): void
    {
        // Autorización
        if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = "Acceso denegado. Se requieren privilegios de administrador.";
            header("Location: ?page=login");
            exit;
        }

        try {
            $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING) ?: 'dashboard';
            match ($action) {
                'manage_users'     => $this->manageUsers(),
                'manage_drones'    => $this->manageDrones(),
                'manage_providers' => $this->manageProviders(),
                'reports'          => $this->generateReports(),
                default            => $this->showDashboard(),
            };
        } catch (\Exception $e) {
            error_log("Error en AdminController: " . $e->getMessage());
            http_response_code(500);
            include __DIR__ . '/../Views/500.php';
        }
    }

    private function showDashboard(): void
    {
        $stats = $this->adminModel->getDashboardStats();
        include __DIR__ . '/../Views/admin/dashboard.php';
    }

    private function manageUsers(): void
    {
        $users = $this->adminModel->getAllUsers();
        include __DIR__ . '/../Views/admin/manage_users.php';
    }

    private function manageDrones(): void
    {
        $drones = $this->adminModel->getAllDrones();
        include __DIR__ . '/../Views/admin/manage_drones.php';
    }

    private function manageProviders(): void
    {
        $providers = $this->adminModel->getAllProviders();
        include __DIR__ . '/../Views/admin/manage_providers.php';
    }

    private function generateReports(): void
    {
        $reports = $this->adminModel->generateSystemReports();
        include __DIR__ . '/../Views/admin/reports.php';
    }
}