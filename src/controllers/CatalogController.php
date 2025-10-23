<?php
namespace App\Controllers;

use App\Models\DroneModel;

class CatalogController
{
    private DroneModel $droneModel;
    private int $perPage = 14;

    public function __construct(DroneModel $droneModel)
    {
        $this->droneModel = $droneModel;
    }

    public function handleRequest(): void
    {
        try {
            // Sanitizar entradas
            $categoryId  = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT) ?: null;
            $searchQuery = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) ?: null;
            $sortOption  = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_STRING) ?: 'name_asc';
            $page        = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, [
                'options' => ['default' => 1, 'min_range' => 1]
            ]);

            // Obtener categorías (con conteo)
            $categories = $this->droneModel->getAllCategoriesWithCount();

            // Obtener productos paginados
            $products = $this->getPaginatedProducts($categoryId, $searchQuery, $sortOption, $page);

            // Preparar datos para la vista
            $pageTitle = 'Catálogo de Productos';
            if ($categoryId) {
                foreach ($categories as $cat) {
                    if ((int)$cat['id_category'] === $categoryId) {
                        $pageTitle = $cat['name'];
                        break;
                    }
                }
            }

            include __DIR__ . '/../Views/catalog.php';
        } catch (\Exception $e) {
            error_log("Error en CatalogController: " . $e->getMessage());
            http_response_code(500);
            include __DIR__ . '/../Views/500.php';
        }
    }

    private function getPaginatedProducts(
        ?int $categoryId,
        ?string $searchQuery,
        string $sortOption,
        int $page
    ): array {
        // Traer todos y luego paginar en memoria 
        if ($searchQuery) {
            $all = $this->droneModel->searchDrones($searchQuery, $sortOption);
        } elseif ($categoryId) {
            $all = $this->droneModel->getDronesByCategory($categoryId, $sortOption);
        } else {
            $all = $this->droneModel->getAllAvailableDrones($sortOption);
        }

        $totalItems = count($all);
        $totalPages = (int)ceil($totalItems / $this->perPage);
        $offset     = ($page - 1) * $this->perPage;
        $items      = array_slice($all, $offset, $this->perPage);

        return [
            'items'       => $items,
            'totalItems'  => $totalItems,
            'totalPages'  => $totalPages,
            'currentPage' => $page,
        ];
    }

    private function getCategoryIcon(string $name): string
    {
        $icons = [
            'Profesionales' => 'camera',
            'Carrera'       => 'speedometer2',
            'Fotografía'    => 'camera2',
            'Recreativos'   => 'controller',
        ];
        return $icons[$name] ?? 'grid';
    }
}
