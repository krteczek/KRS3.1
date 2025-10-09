<?php

namespace App\Logger;

use App\Core\Config;

/**
 * Třída pro logování zpráv na obrazovku a do souboru s podporou úrovní logování a rotace
 *	$logger = Logger::getInstance();
 * 	1. Denní rotace:
 *		$logger->setRotation('daily', 0, 7); // Denní rotace, uchovávat 7 souborů
 *		// Soubory: app-2025-10-09.log, app-2025-10-10.log, atd.
 *
 *	2. Hodinová rotace:
 *		$logger->setRotation('hourly', 0, 24); // Hodinová rotace, uchovávat 24 souborů
 *		// Soubory: app-2025-10-09-14.log, app-2025-10-09-15.log, atd.
 *
 *	3. Rotace podle velikosti:
 * 		$logger->setRotation('size', 5242880, 10); // Rotace při 5MB, uchovávat 10 souborů
 * 		//Soubory: app-2025-10-09-143025.log, app-2025-10-09-143126.log, atd.
 *
 *	4. Bez rotace:
 *		$logger->setRotation('none'); // Žádná rotace - všechno v jednom souboru
 *
 *	5. $logger->setLogLevel('ERROR'); // Pouze chyby a výjimky
 *
 *	6. $logger->setLogLevel('DEBUG'); // Všechny zprávy
 *
 *	7. $logger->setLogLevel('INFO'); // Info, warningy, chyby a výjimky
 *
 * @package App\Logger
 * @author KRS3
 * @version 1.0
 */
class Logger
{
    /** @var self|null Singleton instance třídy Logger */
    private static ?self $instance = null;

    /** @var string Cesta k souboru pro logování */
    private string $currentLogFile;

    /** @var string Základní název log souboru bez přípony */
    private string $baseLogFile;

    /** @var bool Určuje, zda se logy vypisují na obrazovku */
    private bool $echoOutput;

    /** @var bool Určuje, zda se logy zapisují do souboru */
    private bool $fileOutput;

    /** @var int Aktuální úroveň logování */
    private int $logLevel;

    /** @var string Typ rotace logů */
    private string $rotation;

    /** @var int Maximální velikost souboru v bytech pro rotaci podle velikosti */
    private int $maxSize;

    /** @var int Maximální počet souborů pro rotaci */
    private int $maxFiles;

    /** @var array Mapování úrovní logování na číselné hodnoty */
    private const LEVELS = [
        'DEBUG' => 0,
        'INFO' => 1,
        'WARNING' => 2,
        'ERROR' => 3,
        'EXCEPTION' => 4,
        'NONE' => 999
    ];

    /** @var array Mapování rotací na číselné hodnoty */
    private const ROTATIONS = [
        'none' => 0,
        'daily' => 1,
        'hourly' => 2,
        'size' => 3
    ];

    /** @var array Výchozí konfigurace */
    private const DEFAULT_CONFIG = [
        'level' => 'INFO',
        'echo' => true,
        'file' => true,
        'rotation' => 'none',
        'max_size' => 10485760, // 10MB
        'max_files' => 30,
        'file_path' => null
    ];

    /**
     * Konstruktor třídy Logger
     *
     * @param array $config Konfigurace loggeru
     * - 'level': úroveň logování (DEBUG, INFO, WARNING, ERROR, EXCEPTION, NONE)
     * - 'echo': zda logovat na obrazovku
     * - 'file': zda logovat do souboru
     * - 'rotation': typ rotace (none, daily, hourly, size)
     * - 'max_size': maximální velikost souboru v bytech
     * - 'max_files': maximální počet souborů
     * - 'file_path': cesta k log souboru (volitelné)
     */
    public function __construct(array $config = [])
    {
        $config = array_merge(self::DEFAULT_CONFIG, $config);

        // Nastavení úrovně logování
        $this->logLevel = self::LEVELS[strtoupper($config['level'])] ?? self::LEVELS['INFO'];

        // Nastavení výstupů
        $this->echoOutput = (bool) $config['echo'];
        $this->fileOutput = (bool) $config['file'];

        // Nastavení rotace
        $this->rotation = $config['rotation'] ?? 'none';
        $this->maxSize = (int) $config['max_size'];
        $this->maxFiles = (int) $config['max_files'];

        // Nastavení cesty k souboru
        if (!empty($config['file_path'])) {
            $this->baseLogFile = $config['file_path'];
        } else {
            $this->baseLogFile = Config::logs('dir', '') . (Config::logs('file', '') ?: 'app.log');
        }

        // Inicializace aktuálního log souboru
        $this->currentLogFile = $this->generateLogFileName();

        // Zajistíme, že adresář pro logy existuje
        if ($this->fileOutput) {
            $logDir = dirname($this->currentLogFile);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
        }
    }

    /**
     * Vrátí singleton instanci třídy Logger
     *
     * @return self Instance třídy Logger
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            // Načtení konfigurace z configu, pokud je dostupná
            $config = [
                'level' => Config::logs('level', 'INFO'),
                'echo' => Config::logs('echo', true),
                'file' => Config::logs('file', true),
                'rotation' => Config::logs('rotation', 'none'),
                'max_size' => Config::logs('max_size', 10485760),
                'max_files' => Config::logs('max_files', 30),
                'file_path' => Config::logs('dir', '') . (Config::logs('file', '') ?: 'app.log')
            ];

            self::$instance = new self($config);
        }
        return self::$instance;
    }

    /**
     * Vygeneruje název log souboru podle nastavené rotace
     *
     * @return string Název souboru
     */
    private function generateLogFileName(): string
    {
        $pathInfo = pathinfo($this->baseLogFile);
        $dirname = $pathInfo['dirname'] ?? '';
        $filename = $pathInfo['filename'] ?? 'app';
        $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '.log';

        $suffix = '';
        switch ($this->rotation) {
            case 'daily':
                $suffix = '-' . date('Y-m-d');
                break;
            case 'hourly':
                $suffix = '-' . date('Y-m-d-H');
                break;
            case 'size':
                // Pro rotaci podle velikosti používáme základní název
                break;
            case 'none':
            default:
                // Bez rotace - základní název
                break;
        }

        return $dirname . DIRECTORY_SEPARATOR . $filename . $suffix . $extension;
    }

    /**
     * Zkontroluje a provede rotaci log souborů pokud je potřeba
     *
     * @return void
     */
    private function checkRotation(): void
    {
        if (!$this->fileOutput || $this->rotation === 'none') {
            return;
        }

        // Kontrola časové rotace (daily, hourly)
        if (in_array($this->rotation, ['daily', 'hourly'])) {
            $newLogFile = $this->generateLogFileName();
            if ($newLogFile !== $this->currentLogFile) {
                // Změna časového intervalu - soubor se změní automaticky při příštím zápisu
                $this->currentLogFile = $newLogFile;
                $this->cleanOldFiles();
            }
        }

        // Kontrola rotace podle velikosti
        if ($this->rotation === 'size' && file_exists($this->currentLogFile)) {
            if (filesize($this->currentLogFile) >= $this->maxSize) {
                $this->rotateBySize();
            }
        }
    }

    /**
     * Provede rotaci logů podle velikosti
     *
     * @return void
     */
    private function rotateBySize(): void
    {
        $pathInfo = pathinfo($this->baseLogFile);
        $dirname = $pathInfo['dirname'] ?? '';
        $filename = $pathInfo['filename'] ?? 'app';
        $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '.log';

        // Vytvoříme název archivního souboru s časovým razítkem
        $timestamp = date('Y-m-d-His');
        $archivedFile = $dirname . DIRECTORY_SEPARATOR . $filename . '-' . $timestamp . $extension;

        // Přesuneme aktuální soubor do archivu
        if (file_exists($this->currentLogFile)) {
            rename($this->currentLogFile, $archivedFile);
        }

        // Vyčistíme staré soubory
        $this->cleanOldFiles();
    }

    /**
     * Smaže staré log soubory podle nastavení max_files
     *
     * @return void
     */
    private function cleanOldFiles(): void
    {
        if ($this->maxFiles <= 0) {
            return;
        }

        $pathInfo = pathinfo($this->baseLogFile);
        $dirname = $pathInfo['dirname'] ?? '';
        $filename = $pathInfo['filename'] ?? 'app';
        $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '.log';

        // Najdeme všechny soubory, které odpovídají patternu
        $pattern = $dirname . DIRECTORY_SEPARATOR . $filename . '-*' . $extension;
        $files = glob($pattern);

        if (!$files) {
            return;
        }

        // Seřadíme soubory podle data modifikace (nejnovější první)
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        // Smažeme přebytečné soubory
        if (count($files) > $this->maxFiles) {
            $filesToDelete = array_slice($files, $this->maxFiles);
            foreach ($filesToDelete as $fileToDelete) {
                if (is_file($fileToDelete)) {
                    unlink($fileToDelete);
                }
            }
        }
    }

    /**
     * Zapíše zprávu do logu, pokud úroveň logování je dostatečně vysoká
     *
     * @param string $message Zpráva k zalogování
     * @param string $level Úroveň logování (DEBUG, INFO, WARNING, ERROR, EXCEPTION)
     * @return void
     */
    public function log(string $message, string $level = "INFO"): void
    {
        $levelValue = self::LEVELS[strtoupper($level)] ?? self::LEVELS['INFO'];

        // Pokud je úroveň zprávy nižší než aktuální úroveň logování, ignorovat
        if ($levelValue < $this->logLevel) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] {$level}: {$message}" . PHP_EOL;

        // Zápis na obrazovku
        if ($this->echoOutput) {
            echo $logEntry;
        }

        // Zápis do souboru
        if ($this->fileOutput) {
            $this->checkRotation();
            try {
                file_put_contents($this->currentLogFile, $logEntry, FILE_APPEND | LOCK_EX);
            } catch (\Exception $e) {
                if ($this->echoOutput) {
                    echo "LOG ERROR: Cannot write to file {$this->currentLogFile}: " . $e->getMessage() . PHP_EOL;
                }
            }
        }
    }

    /**
     * Zapíše ladící zprávu do logu
     *
     * @param string $message Ladící zpráva k zalogování
     * @return void
     */
    public function debug(string $message): void
    {
        $this->log($message, "DEBUG");
    }

    /**
     * Zapíše informační zprávu do logu
     *
     * @param string $message Informační zpráva k zalogování
     * @return void
     */
    public function info(string $message): void
    {
        $this->log($message, "INFO");
    }

    /**
     * Zapíše varovnou zprávu do logu
     *
     * @param string $message Varovná zpráva k zalogování
     * @return void
     */
    public function warning(string $message): void
    {
        $this->log($message, "WARNING");
    }

    /**
     * Zapíše chybovou zprávu do logu
     *
     * @param string $message Chybová zpráva k zalogování
     * @return void
     */
    public function error(string $message): void
    {
        $this->log($message, "ERROR");
    }

    /**
     * Zapíše výjimku do logu včetně stack trace
     *
     * @param \Throwable $e Výjimka k zalogování
     * @param string $message Volitelná zpráva k výjimce
     * @return void
     */
    public function exception(\Throwable $e, string $message = ""): void
    {
        $fullMessage = $message ? "{$message} - " : "";
        $fullMessage .= "Exception: " . $this->escapeMessage($e->getMessage()) . PHP_EOL . $e->getTraceAsString();
        $this->log($fullMessage, "EXCEPTION");
    }

    /**
     * Nastaví úroveň logování
     *
     * @param string $level Úroveň logování (DEBUG, INFO, WARNING, ERROR, EXCEPTION, NONE)
     * @return void
     */
    public function setLogLevel(string $level): void
    {
        $this->logLevel = self::LEVELS[strtoupper($level)] ?? self::LEVELS['INFO'];
    }

    /**
     * Nastaví, zda se logy vypisují na obrazovku
     *
     * @param bool $echoOutput True pro zapnutí výpisu na obrazovku
     * @return void
     */
    public function setEchoOutput(bool $echoOutput): void
    {
        $this->echoOutput = $echoOutput;
    }

    /**
     * Nastaví, zda se logy zapisují do souboru
     *
     * @param bool $fileOutput True pro zapnutí zápisu do souboru
     * @return void
     */
    public function setFileOutput(bool $fileOutput): void
    {
        $this->fileOutput = $fileOutput;
    }

    /**
     * Nastaví rotaci logů
     *
     * @param string $rotation Typ rotace (none, daily, hourly, size)
     * @param int $maxSize Maximální velikost souboru v bytech (pouze pro size)
     * @param int $maxFiles Maximální počet souborů
     * @return void
     */
    public function setRotation(string $rotation, int $maxSize = 10485760, int $maxFiles = 30): void
    {
        $this->rotation = $rotation;
        $this->maxSize = $maxSize;
        $this->maxFiles = $maxFiles;
        $this->currentLogFile = $this->generateLogFileName();
    }

    /**
     * Nastaví cestu k log souboru
     *
     * @param string $logFile Cesta k souboru
     * @return void
     */
    public function setLogFile(string $logFile): void
    {
        $this->baseLogFile = $logFile;
        $this->currentLogFile = $this->generateLogFileName();

        // Zajistíme, že adresář pro logy existuje
        $logDir = dirname($this->currentLogFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * Vrátí aktuální úroveň logování
     *
     * @return string Aktuální úroveň logování
     */
    public function getLogLevel(): string
    {
        return array_search($this->logLevel, self::LEVELS) ?: 'INFO';
    }

    /**
     * Vrátí aktuální cestu k log souboru
     *
     * @return string Cesta k souboru
     */
    public function getCurrentLogFile(): string
    {
        return $this->currentLogFile;
    }

    /**
     * Zkontroluje, zda je daná úroveň povolena
     *
     * @param string $level Úroveň k ověření
     * @return bool True pokud je úroveň povolena
     */
    public function isLevelEnabled(string $level): bool
    {
        $levelValue = self::LEVELS[strtoupper($level)] ?? self::LEVELS['INFO'];
        return $levelValue >= $this->logLevel;
    }

    /**
     * Ošetří speciální znaky v log zprávě
     *
     * @param string $message Původní zpráva
     * @return string Ošetřená zpráva
     */
    private function escapeMessage(string $message): string
    {
        return str_replace(["\0", "\r", "\n"], ['\\0', '\\r', '\\n'], $message);
    }
}