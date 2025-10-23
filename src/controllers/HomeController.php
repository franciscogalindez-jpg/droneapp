<?php
namespace App\Controllers;

use App\Models\DroneModel;
use App\Models\CartModel; 
use PDO;

class HomeController {
    private DroneModel $model;
    private PDO $db;

    public function __construct(DroneModel $model, PDO $db) {
        $this->model = $model;
        $this->db    = $db;        
    }

    public function handleRequest(): void {
        $featured = $this->model->getFeaturedDrones(12);
        $featuredProducts = $featured;

        $availableStateId = (int)$this->db
            ->query("SELECT id_state FROM states_drone WHERE name = 'Available'")
            ->fetchColumn();
        $completedStateId = (int)$this->db
            ->query("SELECT id_state FROM states_transaction WHERE name = 'Completed'")
            ->fetchColumn();

        $sql = <<<SQL
            SELECT
            (SELECT COUNT(*) FROM drones WHERE state_id = :avail)      AS total_drones,
            (SELECT COUNT(*) FROM categories)                          AS total_categories,
            (SELECT COUNT(DISTINCT user_id)
                FROM transactions
                WHERE state_id = :completed) + 150                       AS happy_customers
            SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':avail'     => $availableStateId,
            ':completed' => $completedStateId,
        ]);
        $rawStats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $stats = [
            'total_products'  => $rawStats['total_drones']    ?? 0,
            'happy_customers' => $rawStats['happy_customers'] ?? 0,
            // 'total_categories' => $rawStats['total_categories'] ?? 0,
        ];

        include __DIR__ . '/../Views/home.php';
    }
}