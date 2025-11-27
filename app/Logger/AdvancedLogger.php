<?php
// AdvancedLogger.php (Finální, kompatibilní verze)

declare(strict_types=1);

namespace App\Logger;

/**
 * Rozšířený logger s pokročilým filtrováním a vlastním formátováním.
 * Využívá rotaci a správu souborů z třídy Logger (rodičovská třída).
 *
 * @package App\Logger
 * @author KRS3
 * @version 2.1 (Finální kompatibilní verze)
 */
class AdvancedLogger extends Logger
{
    // Custom mapování úrovní, nyní kompatibilní se všemi úrovněmi z rodiče.
    private const ADVANCED_LEVELS = [
        'DEBUG' => 0,
        'INFO' => 1,
        'WARNING' => 2,
        'ERROR' => 3,
        'CRITICAL' => 4,
        'EXCEPTION' => 5
    ];

    private int $minLevel;

    public function __construct(
        string $logFile = "app.log",
        bool $echoOutput = true,
        string $minLevel = 'DEBUG'
    ) {
        // Rodiči předáme level 'DEBUG' (0), aby on sám neprováděl žádné filtrování.
        parent::__construct([
            'file_path' => $logFile,
            'echo' => $echoOutput,
            'level' => 'DEBUG' 
        ]);
        
        $minLevelUpper = strtoupper($minLevel);
        $this->minLevel = self::ADVANCED_LEVELS[$minLevelUpper] ?? self::ADVANCED_LEVELS['INFO'];
    }

    /**
     * PŘEPSANÁ metoda log s vlastním filtrováním a formátováním (vč. milisekund).
     * Pro zápis do souboru využívá parent::log().
     */
    public function log(string $message, string $level = "INFO"): void
    {
        $levelUpper = strtoupper($level);
        $levelValue = self::ADVANCED_LEVELS[$levelUpper] ?? self::ADVANCED_LEVELS['INFO'];

        // 1. FILTROVÁNÍ: Pokud je úroveň zprávy nižší než povolená, ukončíme.
        if ($levelValue < $this->minLevel) {
            return;
        }

        // 2. FORMÁTOVÁNÍ: Vytvoření zprávy s vlastním formátem (vč. milisekund a zarovnání).
        $timestamp = date('Y-m-d H:i:s.v');
        $formattedMessage = sprintf(
            "[%s] %-8s %s",
            $timestamp,
            $levelUpper,
            $message
        );
        
        // 3. VÝPIS NA OBRAZOVKU: Využijeme děděnou (protected) vlastnost.
        if ($this->echoOutput) {
            echo $formattedMessage . PHP_EOL;
        }
        
        // 4. ZÁPIS DO SOUBORU: Zavoláme rodiče s úrovní 'NONE', aby zprávu zapsal 
        // bez dalšího filtrování a zajistil rotaci, uzamykání a sanitizaci.
        parent::log($formattedMessage, 'NONE');
    }

    // --- Pomocné metody pro snadné volání VŠECH úrovní (Kompatibilita) ---

    public function debug(string $message): void
    {
        $this->log($message, "DEBUG");
    }

    public function info(string $message): void
    {
        $this->log($message, "INFO");
    }

    public function warning(string $message): void
    {
        $this->log($message, "WARNING");
    }

    public function error(string $message): void
    {
        $this->log($message, "ERROR");
    }
    
    public function critical(string $message): void // NOVÁ METODA
    {
        $this->log($message, "CRITICAL");
    }

    /**
     * Zapíše výjimku do logu, kompatibilní s rodičovskou signaturou.
     *
     * @param \Throwable $e Výjimka k zalogování
     * @param string $message Volitelná zpráva k výjimce
     * @return void
     */
    public function exception(\Throwable $e, string $message = ""): void // UPRAVENÁ SIGNATURA
    {
        $fullMessage = $message ? "{$message} - " : "";
        $fullMessage .= "Exception: " . $e->getMessage() . PHP_EOL;
        $fullMessage .= "File: " . $e->getFile() . " (Line: " . $e->getLine() . ")" . PHP_EOL;
        $fullMessage .= "Stack trace:" . PHP_EOL . $e->getTraceAsString();

        $this->log($fullMessage, "EXCEPTION");
    }
}
