<?php
namespace App\Config;

use PDO;
use PDOException;
use Exception;
use Dotenv\Dotenv;

class Database
{
    private string $host;
    private string $port;
    private string $dbName;
    private string $username;
    private string $password;
    private ?PDO   $conn = null;

    public function __construct(string $projectRoot = null)    
    {
        if ($projectRoot === null) {
            $projectRoot = realpath(__DIR__ . '/../../');
        }

        $this->host     = getenv('DB_HOST')     ?: 'localhost';
        $this->port     = getenv('DB_PORT')     ?: '3306';
        $this->dbName   = getenv('DB_NAME')     ?: 'droneapp';
        $this->username = getenv('DB_USER')     ?: 'root';
        $this->password = getenv('DB_PASS')     ?: '';
    }

    /**
     * Retorna una conexión PDO configurada.
     *
     * @return PDO
     * @throws Exception Si no puede conectar.
     */
    public function getConnection(): PDO
    {
        if ($this->conn instanceof PDO) {
            return $this->conn;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $this->host,
            $this->port,
            $this->dbName
        );

        try {
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->exec("SET time_zone = '+00:00'");
            $this->conn->exec("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION'");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            throw new Exception('No se pudo conectar a la base de datos.' . $e->getMessage());
        }
    }

    public function beginTransaction(): bool
    {
        return $this->getConnection()->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->getConnection()->commit();
    }

    public function rollBack(): bool
    {
        return $this->getConnection()->rollBack();
    }

    public function lastInsertId(): string
    {
        return $this->getConnection()->lastInsertId();
    }
}
