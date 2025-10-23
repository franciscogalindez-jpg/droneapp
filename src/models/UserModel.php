<?php
namespace App\Models;

use PDO;
use PDOException;
use RuntimeException;
use InvalidArgumentException;

class UserModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Trae el usuario por su ID, incluyendo rol y género.
     */
    public function getUserById(int $userId): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    u.*,
                    r.name   AS role_name,
                    g.name   AS gender_name
                FROM users u
                JOIN roles  r ON u.role_id   = r.id_role
                JOIN genders g ON u.gender_id = g.id_gender
                WHERE u.id_user = :uid
            ");
            $stmt->execute([':uid' => $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error en getUserById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualiza los datos básicos del usuario.
     */
    public function updateUser(array $data): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE users SET
                    username     = :username,
                    email        = :email,
                    phone        = :phone,
                    address      = :address,
                    gender_id    = :gender_id
                WHERE id_user   = :id_user
            ");
            return $stmt->execute([
                ':username' => $data['username'],
                ':email'     => $data['email'],
                ':phone'     => $data['phone'],
                ':address'   => $data['address'],
                ':gender_id' => $data['gender_id'],
                ':id_user'   => $data['id_user'],
            ]);
        } catch (PDOException $e) {
            error_log("Error en updateUser: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo usuario. Lanza excepción si hay validación o duplicado.
     */
    public function createUser(array $data): bool
    {
        $errors = $this->validateUserData($data);
        if ($errors) {
            throw new InvalidArgumentException(implode("\n", $errors));
        }

        try {
            $this->db->beginTransaction();

            // Duplicados: card_id o email
            $stmt = $this->db->prepare("
                SELECT 1 FROM users
                WHERE card_id = :card_id OR email = :email
                LIMIT 1
            ");
            $stmt->execute([
                ':card_id' => $data['card_id'],
                ':email'   => $data['email'],
            ]);
            if ($stmt->fetch()) {
                throw new RuntimeException("La cédula o el correo ya están registrados");
            }

            // Insertar usuario
            $hashed = password_hash($data['password'], PASSWORD_BCRYPT);
            $stmt   = $this->db->prepare("
                INSERT INTO users
                  (card_id, username, password, email, phone, address, gender_id, role_id)
                VALUES
                  (:card_id, :username, :pass, :email, :phone, :address, :gender_id, 2)
            ");
            $stmt->execute([
                ':card_id'   => $data['card_id'],
                ':username' => $data['username'],
                ':pass'      => $hashed,
                ':email'     => $data['email'],
                ':phone'     => $data['phone'],
                ':address'   => $data['address'],
                ':gender_id' => $data['gender_id'],
            ]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Error en createUser: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene un usuario por email (para login).
     */
    public function getUserByEmail(string $email): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    u.*,
                    r.name AS role_name
                FROM users u
                JOIN roles r ON u.role_id = r.id_role
                WHERE u.email = :email
            ");
            $stmt->execute([':email' => $email]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error en getUserByEmail: " . $e->getMessage());
            throw new RuntimeException("Error al buscar usuario");
        }
    }

    /**
     * Verifica credenciales (login). Rehashea si es necesario.
     */
    public function verifyCredentials(string $email, string $password): ?array
    {
        $user = $this->getUserByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            if (password_needs_rehash($user['password'], PASSWORD_BCRYPT)) {
                $this->updatePassword($user['id_user'], $password);
            }
            return $user;
        }
        return null;
    }

    private function updatePassword(int $userId, string $password): bool
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET password = :pass
            WHERE id_user = :uid
        ");
        return $stmt->execute([
            ':pass' => password_hash($password, PASSWORD_BCRYPT),
            ':uid'  => $userId,
        ]);
    }

    /**
     * Recupera las últimas transacciones del usuario.
     */
    public function getTransactions(int $userId, int $limit = 10): array
    {
        $sql = "
            SELECT
                t.id_transaction,
                t.total_cost,
                t.created_at,
                st.name           AS state_name,
                COUNT(dt.drone_id) AS items_count,
                SUM(dt.subtotal)   AS total_amount
            FROM transactions t
            JOIN states_transaction st
            ON t.state_id = st.id_state
            JOIN drone_transactions dt
            ON dt.transaction_id = t.id_transaction
            WHERE t.user_id = :uid
            GROUP BY t.id_transaction, st.name, t.created_at
            ORDER BY t.created_at DESC
            LIMIT :lim
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit,   PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Valida datos antes de crear un usuario.
     */
    private function validateUserData(array $data): array
    {
        $errors = [];

        // Cédula: 12 dígitos
        if (empty($data['card_id']) || !preg_match('/^\d{12}$/', $data['card_id'])) {
            $errors[] = "La cédula debe tener 12 dígitos numéricos";
        }

        // Nombre completo: al menos 3 letras
        if (empty($data['username']) || mb_strlen(trim($data['username'])) < 3) {
            $errors[] = "El nombre completo debe tener al menos 3 caracteres";
        }

        // Email
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El correo electrónico no es válido";
        }

        // Password: 8–10 chars, 1 mayúscula, 1 número, 1 símbolo
        if (empty($data['password']) ||
            !preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*\W).{8,10}$/', $data['password'])
        ) {
            $errors[] = "La contraseña debe tener 8–10 caracteres, al menos 1 mayúscula, 1 número y 1 símbolo";
        }

        // Phone: 7–15 dígitos (+ opcional)
        if (empty($data['phone']) ||
            !preg_match('/^\+?\d{7,15}$/', $data['phone'])
        ) {
            $errors[] = "El teléfono solo puede contener números y un prefijo '+' opcional (7–15 dígitos)";
        }

        // Gender
        if (!isset($data['gender_id']) || !filter_var($data['gender_id'], FILTER_VALIDATE_INT)) {
            $errors[] = "Debe seleccionar un género válido";
        }

        return $errors;
    }
}