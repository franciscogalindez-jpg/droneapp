<?php
namespace App\Controllers;

use App\Models\DroneModel;

class DroneController {
    private $droneModel;

    public function __construct(DroneModel $droneModel) {
        $this->droneModel = $droneModel;
    }

    public function handleRequest() {
        // Verificar que el ID existe y es válido
        if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
            header("Location: ?page=catalog");
            exit();
        }

        $droneId = (int)$_GET['id'];
        
        try {
            $drone = $this->droneModel->getDroneById($droneId);

            // Si no se encuentra el dron
            if (!$drone) {
                $_SESSION['error'] = "El dron solicitado no existe";
                header("Location: ?page=catalog");
                exit();
            }

            // Preparar datos para la vista
            $viewData = [
                'drone' => $drone,
                'pageTitle' => $drone['description'] . ' - Detalles'
            ];

            // Cargar vista
            $this->renderView('drone_detail', $viewData);
            
        } catch (Exception $e) {
            error_log("Error en DroneController: " . $e->getMessage());
            $this->showErrorPage();
        }
    }
    
    private function renderView($viewName, $data = []) {
        extract($data);
        $viewPath = __DIR__ . '/../views/' . $viewName . '.php';
        
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            throw new Exception("Vista no encontrada: " . $viewName);
        }
    }
    
    private function showErrorPage() {
        header("HTTP/1.0 500 Internal Server Error");
        include __DIR__ . '/../views/500.php';
        exit();
    }
}
?>