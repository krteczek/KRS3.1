<?php
// app/Database/DatabaseConnection.php
declare(strict_types=1);

namespace App\Database;

use App\Core\Config;
use PDO;
use PDOException;
use PDOStatement;
use App\Logger\Logger;

/**
 * Třída pro správu připojení k databázi
 *
 * Poskytuje jednoduché rozhraní pro práci s PDO a automaticky
 * načítá konfiguraci z Config třídy. Zajišťuje bezpečné připojení
 * a základní operace s databází.
 *
 * @package App\Database
 * @author KRS3
 * @version 1.2
 */
class DatabaseConnection
{
    /**
     * @var PDO Instance PDO připojení
     */
    private PDO $pdo;

    /**
     * @var Logger Instance loggeru
     */
    private Logger $logger;

    /**
     * Konstruktor - vytvoří připojení k databázi
     *
     * @throws \RuntimeException Pokud se připojení k databázi nezdaří
     */
    public function __construct()
    {
        // Inicializace loggeru
        $this->logger = Logger::getInstance();

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

            $this->logger->info('Database connection established', [
                'host' => $config['host'],
                'database' => $config['name'],
                'charset' => $config['charset']
            ]);
        } catch (PDOException $e) {
            $this->logger->exception($e, "Database connection failed");

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
        $this->logger->debug('SQL statement prepared', [
            'sql' => $this->sanitizeSqlForLog($sql)
        ]);

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
        $startTime = microtime(true);

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            $this->logger->debug('SQL query executed', [
                'sql' => $this->sanitizeSqlForLog($sql),
                'params_count' => count($params),
                'execution_time_ms' => $executionTime,
                'rows_affected' => $stmt->rowCount()
            ]);

            return $stmt;
        } catch (PDOException $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            $this->logger->error('SQL query failed', [
                'sql' => $this->sanitizeSqlForLog($sql),
                'params_count' => count($params),
                'execution_time_ms' => $executionTime,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode()
            ]);

            throw new \RuntimeException(
                "Chyba při provádění dotazu: " . $e->getMessage(),
                (int)$e->getCode(),
                $e
            );
        }
    }

    /**
     * Provede SQL příkaz (INSERT, UPDATE, DELETE) bez návratové hodnoty
     *
     * @param string $sql SQL příkaz
     * @param array $params Parametry pro příkaz
     * @return int Počet ovlivněných řádků
     * @throws \RuntimeException Pokud příkaz selže
     */
    public function execute(string $sql, array $params = []): int
    {
        $startTime = microtime(true);

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $rowCount = $stmt->rowCount();

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            $this->logger->debug('SQL command executed', [
                'sql' => $this->sanitizeSqlForLog($sql),
                'params_count' => count($params),
                'execution_time_ms' => $executionTime,
                'rows_affected' => $rowCount
            ]);

            return $rowCount;
        } catch (PDOException $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            $this->logger->error('SQL command failed', [
                'sql' => $this->sanitizeSqlForLog($sql),
                'params_count' => count($params),
                'execution_time_ms' => $executionTime,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode()
            ]);

            throw new \RuntimeException(
                "Chyba při provádění příkazu: " . $e->getMessage(),
                (int)$e->getCode(),
                $e
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
        $id = (int) $this->pdo->lastInsertId();

        $this->logger->debug('Last insert ID retrieved', [
            'id' => $id
        ]);

        return $id;
    }

    /**
     * Začne transakci
     *
     * @return bool True pokud se transakce začala úspěšně
     */
    public function beginTransaction(): bool
    {
        $result = $this->pdo->beginTransaction();

        if ($result) {
            $this->logger->info('Database transaction started');
        } else {
            $this->logger->warning('Failed to start database transaction');
        }

        return $result;
    }

    /**
     * Potvrdí transakci
     *
     * @return bool True pokud se transakce potvrdila úspěšně
     */
    public function commit(): bool
    {
        $result = $this->pdo->commit();

        if ($result) {
            $this->logger->info('Database transaction committed');
        } else {
            $this->logger->warning('Failed to commit database transaction');
        }

        return $result;
    }

    /**
     * Vrátí transakci zpět
     *
     * @return bool True pokud se transakce vrátila úspěšně
     */
    public function rollBack(): bool
    {
        $result = $this->pdo->rollBack();

        if ($result) {
            $this->logger->warning('Database transaction rolled back');
        } else {
            $this->logger->error('Failed to rollback database transaction');
        }

        return $result;
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
            $this->logger->error('Database connection check failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Vrátí PDO instanci (pro pokročilé operace)
     *
     * @return PDO PDO instance
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Sanitizuje SQL pro logování (odstraní citlivá data)
     *
     * @param string $sql SQL dotaz
     * @return string Sanitizovaný SQL
     */
    private function sanitizeSqlForLog(string $sql): string
    {
        // Zkrátit dlouhé SQL dotazy
        if (strlen($sql) > 200) {
            $sql = substr($sql, 0, 200) . '... (truncated)';
        }

        // Nahradit více mezer/nových řádků jednou mezerou
        $sql = preg_replace('/\s+/', ' ', $sql);

        return trim($sql);
    }
}