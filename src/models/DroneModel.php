<?php
namespace App\Models;

use PDO;
use PDOException;

class DroneModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Obtiene drones destacados y disponibles
    public function getFeaturedDrones(int $limit = 5): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT DISTINCT d.*,
                    c.name          AS category_name,
                    c.speed         AS category_speed,
                    c.battery       AS category_battery,
                    c.stability     AS category_stability,
                    c.load_capacity AS category_load,
                    p.name          AS provider_name,
                    di.filename     AS drone_image
                FROM drones d
                JOIN categories c ON d.category_id = c.id_category
                JOIN provider_drones pd ON pd.drone_id   = d.id_drone
                JOIN providers p ON pd.provider_id = p.id_provider
                JOIN drone_images di ON d.id_drone = di.drone_id
                WHERE d.state_id = (
                    SELECT id_state
                    FROM states_drone
                    WHERE name = 'Available'
                )
                ORDER BY RAND()
                LIMIT :limit
            ");
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getFeaturedDrones: " . $e->getMessage());
            return [];
        }
    }

    // Detalle de un dron
    public function getDroneById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT d.*,
                    c.name          AS category_name,
                    c.speed         AS category_speed,
                    c.battery       AS category_battery,
                    c.stability     AS category_stability,
                    c.load_capacity AS category_load,
                    p.name          AS provider_name,
                    di.filename     AS drone_image,
                    pd.stock        AS drone_stock
                FROM drones d
                JOIN categories c ON d.category_id = c.id_category
                LEFT JOIN provider_drones pd ON pd.drone_id = d.id_drone
                JOIN providers p ON pd.provider_id = p.id_provider
                LEFT JOIN drone_images di ON di.drone_id = d.id_drone
                WHERE d.id_drone = :id;
            ");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error en getDroneById: " . $e->getMessage());
            return null;
        }
    }

    // Todas las categorías con conteo de drones disponibles
    public function getAllCategoriesWithCount(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT c.*,
                    COUNT(d.id_drone) AS drone_count
                FROM categories c
                LEFT JOIN drones d
                ON d.category_id = c.id_category
                AND d.state_id = (
                    SELECT id_state
                    FROM states_drone
                    WHERE name = 'Available'
                )
                GROUP BY c.id_category
                ORDER BY c.name
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getAllCategoriesWithCount: " . $e->getMessage());
            return [];
        }
    }

    // Lista genérica de drones con filtros y orden
    public function getDronesByCategory(int $categoryId, string $sortOption = 'name_asc'): array
    {
        $filter = 'd.category_id = :catId';
        return $this->fetchDroneList(
            $filter,
            [':catId' => $categoryId],
            $sortOption
        );
    }

    // Los drones disponibles
    public function getAllAvailableDrones(string $sortOption = 'name_asc'): array
    {
        return $this->fetchDroneList(
        '',
        [],
        $sortOption
    );
    }

    // Buscar drones por descripción o nombre
    public function searchDrones(string $searchQuery, string $sortOption = 'name_asc'): array
    {
        $term = "%{$searchQuery}%";

        $filter = "
            (
                d.name        LIKE :term
                OR d.description LIKE :term
                OR c.name        LIKE :term
            )
        ";
        return $this->fetchDroneList(
            $filter,
            [':term' => $term],
            $sortOption
        );
    }

    // Comprueba que un dron no esté ocupado en el rango dado
    public function checkAvailability(int $droneId, string $startDate, string $endDate): bool
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) AS cnt
                FROM drones_transactions dt
                JOIN transactions t
                ON dt.transaction_id = t.id_transaction
                JOIN states_transaction st
                ON t.state_id = st.id_state
                WHERE dt.drone_id = :drone
                AND st.name IN ('Reserved','Rented')
                AND (
                    (:start BETWEEN dt.start_datetime AND dt.end_datetime)
                OR (:end   BETWEEN dt.start_datetime AND dt.end_datetime)
                OR (dt.start_datetime BETWEEN :start AND :end)
                )
            ");
            $stmt->execute([
                ':drone' => $droneId,
                ':start' => $startDate,
                ':end'   => $endDate,
            ]);
            return ((int)$stmt->fetch(PDO::FETCH_ASSOC)['cnt']) === 0;
        } catch (PDOException $e) {
            error_log("Error en checkAvailability: " . $e->getMessage());
            return false;
        }
    }

    // Helper genérico para listados de drones
    private function fetchDroneList(string $filterClause, array $params, string $sortOption): array
    {
        $orderBy = $this->getOrderByClause($sortOption);

        // Construyo la consulta con un único WHERE
        $sql = "
            SELECT
                d.*,
                c.name          AS category_name,
                c.speed         AS category_speed,
                c.battery       AS category_battery,
                c.stability     AS category_stability,
                c.load_capacity AS category_load,
                p.name          AS provider_name,
                di.filename     AS drone_image,
                SUM(pd.stock)   AS total_stock
            FROM drones d
            JOIN categories c ON d.category_id   = c.id_category
            JOIN provider_drones pd ON pd.drone_id   = d.id_drone
            JOIN providers p       ON pd.provider_id = p.id_provider
            LEFT JOIN drone_images di ON di.drone_id = d.id_drone

            WHERE pd.stock > 0                          -- sólo drones con stock
            AND d.state_id = 1                        -- sólo Available
        ";

        // si me pasan filtro adicional, lo encadeno con AND (sin la palabra WHERE)
        if ($filterClause) {
            $sql .= " AND ({$filterClause})";
        }

        $sql .= "
            GROUP BY d.id_drone
            {$orderBy}
        ";

        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en fetchDroneList: " . $e->getMessage());
            return [];
        }
    }


    // Orden según opción elegida
    private function getOrderByClause(string $sortOption): string
    {
        return match ($sortOption) {
            'name_desc'  => 'ORDER BY d.description DESC',
            'price_asc'  => 'ORDER BY d.daily_rate ASC',
            'price_desc' => 'ORDER BY d.daily_rate DESC',
            'featured'   => 'ORDER BY RAND()', 
            default      => 'ORDER BY d.description ASC',
        };
    }

}