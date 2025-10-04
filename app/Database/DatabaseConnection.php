<?php
// app/Database/DatabaseConnection.php
declare(strict_types=1);

namespace App\Database;

use App\Core\Config;
use PDO;
use PDOException;
use PDOStatement;

/**
 * Třída pro správu připojení k databázi
 *
 * Poskytuje jednoduché rozhraní pro práci s PDO a automaticky
 * načítá konfiguraci z Config třídy. Zajišťuje bezpečné připojení
 * a základní operace s databází.
 *
 * @package App\Database
 * @author KRS3
 * @version 1.1
 */
class DatabaseConnection
{
    /**
     * @var PDO Instance PDO připojení
     */
    private PDO $pdo;

    /**
     * Konstruktor - vytvoří připojení k databázi
     *
     * @throws \RuntimeException Pokud se připojení k databázi nezdaří
     */
    public function __construct()
    {
        // Získej konfiguraci z Config třídy
        $config = Config::get('database');

        $dsn = "mysql:host={$config['host']};dbname={$config['name']};charset={$config['charset']}";

        try {
            $this->pdo = new PDO(
                $dsn,
                $config['user'],
                $config['pass'],
                $config['options'] ?? []
            );
        } catch (PDOException $e) {
            throw new \RuntimeException(
                "Chyba připojení k databázi: " . $e->getMessage(),
                (int)$e->getCode()
            );
        }
    }

    /**
     * Vytvoří připravený příkaz (PDOStatement) z SQL dotazu
     *
     * @param string $sql SQL dotaz
     * @return PDOStatement Připravený příkaz
     */
    public function prepare(string $sql): PDOStatement
    {
        return $this->pdo->prepare($sql);
    }

    /**
     * Provede SQL dotaz s parametry a vrátí PDOStatement
     *
     * @param string $sql SQL dotaz
     * @param array $params Parametry pro dotaz
     * @return PDOStatement Výsledek dotazu
     * @throws \RuntimeException Pokud dotaz selže
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new \RuntimeException(
                "Chyba při provádění dotazu: " . $e->getMessage(),
                (int)$e->getCode()
            );
        }
    }

    /**
     * Vrátí ID posledního vloženého záznamu
     *
     * @return int ID posledního vloženého záznamu
     */
    public function getLastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Začne transakci
     *
     * @return bool True pokud se transakce začala úspěšně
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Potvrdí transakci
     *
     * @return bool True pokud se transakce potvrdila úspěšně
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Vrátí transakci zpět
     *
     * @return bool True pokud se transakce vrátila úspěšně
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Zkontroluje připojení k databázi
     *
     * @return bool True pokud je připojení aktivní
     */
    public function isConnected(): bool
    {
        try {
            $this->pdo->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}