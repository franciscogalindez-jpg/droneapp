<?php
namespace App\Models;

use PDO;

class AdminModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getDashboardStats(): array
    {
        $sql = <<<SQL
SELECT
  (SELECT COUNT(*) FROM usuarios)                                              AS total_users,
  (SELECT COUNT(*) FROM drones)                                                AS total_drones,
  (SELECT COUNT(*) FROM transacciones)                                         AS total_transactions,
  (
    SELECT SUM(
      a.cantidad * CASE
        WHEN a.es_alquiler = 1 THEN d.precio_hora * TIMESTAMPDIFF(HOUR, t.fecha_inicio, t.fecha_fin)
        ELSE d.precio_alquiler_dia
      END
    )
    FROM alquileres a
    JOIN drones d       ON a.id_dron = d.id_dron
    JOIN transacciones t ON a.id_transaccion = t.id_transaccion
    WHERE t.estado_transaccion = 'completada'
  ) AS total_income
SQL;
        $stmt = $this->db->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function getAllUsers(): array
    {
        $stmt = $this->db->query("
            SELECT u.*, r.nombre_rol
            FROM usuarios u
            JOIN roles r ON u.id_rol = r.id_rol
            ORDER BY u.nombre_usuario
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateUserRole(string $userId, int $newRoleId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE usuarios
            SET id_rol = :role
            WHERE id_usuario = :uid
        ");
        $stmt->bindParam(':role', $newRoleId, PDO::PARAM_INT);
        $stmt->bindParam(':uid',  $userId,    PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function getAllDrones(): array
    {
        $stmt = $this->db->query("
            SELECT d.*, p.nombre_proveedor
            FROM drones d
            LEFT JOIN proveedores p ON d.id_proveedor = p.id_proveedor
            ORDER BY d.nombre_dron
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllProviders(): array
    {
        $stmt = $this->db->query("
            SELECT *
            FROM proveedores
            ORDER BY nombre_proveedor
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function generateSystemReports(): array
    {
        $reports = [];

        // Usuarios por rol
        $reports['users_by_role'] = $this->db
            ->query("
              SELECT r.nombre_rol, COUNT(u.id_usuario) AS cantidad
              FROM roles r
              LEFT JOIN usuarios u ON r.id_rol = u.id_rol
              GROUP BY r.id_rol
            ")
            ->fetchAll(PDO::FETCH_ASSOC);

        // Transacciones por estado
        $reports['transactions_by_status'] = $this->db
            ->query("
              SELECT estado_transaccion, COUNT(*) AS cantidad
              FROM transacciones
              GROUP BY estado_transaccion
            ")
            ->fetchAll(PDO::FETCH_ASSOC);

        // Ingresos por mes
        $reports['income_by_month'] = $this->db
            ->query("
              SELECT
                YEAR(t.fecha_inicio) AS año,
                MONTH(t.fecha_inicio) AS mes,
                SUM(
                  a.cantidad * CASE
                    WHEN a.es_alquiler = 1 THEN d.precio_hora * TIMESTAMPDIFF(HOUR, t.fecha_inicio, t.fecha_fin)
                    ELSE d.precio_alquiler_dia
                  END
                ) AS ingresos
              FROM alquileres a
              JOIN drones d       ON a.id_dron = d.id_dron
              JOIN transacciones t ON a.id_transaccion = t.id_transaccion
              WHERE t.estado_transaccion = 'completada'
              GROUP BY YEAR(t.fecha_inicio), MONTH(t.fecha_inicio)
              ORDER BY año, mes
            ")
            ->fetchAll(PDO::FETCH_ASSOC);

        return $reports;
    }
}