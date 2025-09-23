<?php
//app/Database/DatabaseConnection.php
declare(strict_types=1);

namespace App\Database;

use App\Core\Config;

class DatabaseConnection {
    private \PDO $pdo;


    public function __construct() {
        // Získej konfiguraci z Config tøídy
        $config = Config::get('database');

        $dsn = "mysql:host={$config['host']};dbname={$config['name']};charset={$config['charset']}";

        $this->pdo = new \PDO(
            $dsn,
            $config['user'],
            $config['pass'],
            $config['options'] ?? []
        );
    }


    public function prepare(string $sql): \PDOStatement
    {
        return $this->pdo->prepare($sql);
    }

    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function getLastInsertId(): int {
        return (int) $this->pdo->lastInsertId();
    }
}