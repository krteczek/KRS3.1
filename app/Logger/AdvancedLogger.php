<?php
// AdvancedLogger.php (Není třeba do ní nic kopírovat, je kompletní!)

declare(strict_types=1);

namespace App\Logger;

/**
 * Rozšířený logger s pokročilým filtrováním a vlastním formátováním.
 * Využívá rotaci a správu souborů z třídy Logger (rodičovská třída).
 *
 * @package App\Logger
 * @author KRS3
 * @version 2.0 (Refaktorovaná verze, využívající protected vlastnosti Loggeru)
 */
class AdvancedLogger extends Logger
{
    // Custom mapování úrovní, které se liší od rodiče.
    private const ADVANCED_LEVELS = [
        'DEBUG' => 0,
        'INFO' => 1,
        'WARNING' => 2,
        'ERROR' => 3,
        'EXCEPTION' => 4 // Všimněte si, že zde chybí CRITICAL a má jinou hodnotu pro EXCEPTION
    ];

    private int $minLevel;

    public function __construct(
        string $logFile = "app.log",
        bool $echoOutput = true,
        string $minLevel = 'DEBUG'
    ) {
        // Voláme konstruktor rodiče. Rodiči předáme level 'DEBUG' (0), 
        // aby on sám neprováděl žádné filtrování, jelikož filtrování bude dělat potomek.
        parent::__construct([
            'file_path' => $logFile,
            'echo' => $echoOutput,
            'level' => 'DEBUG' 
        ]);
        
        // Nastavíme vlastní minimální úroveň pro filtrování
        $minLevelUpper = strtoupper($minLevel);
        $this->minLevel = self::ADVANCED_LEVELS[$minLevelUpper] ?? self::ADVANCED_LEVELS['INFO'];
    }

    /**
     * PŘEPSANÁ metoda log, která provede filtrování a vlastní formátování.
     * Logování do souboru nechá na rodičovské metodě (přes parent::log).
     */
    public function log(string $message, string $level = "INFO"): void
    {
        $levelUpper = strtoupper($level);
        $levelValue = self::ADVANCED_LEVELS[$levelUpper] ?? self::ADVANCED_LEVELS['INFO'];

        // 1. FILTROVÁNÍ: Pokud je úroveň zprávy nižší než povolená, ukončíme (podle ADVANCED_LEVELS).
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
        
        // 3. VÝPIS NA OBRAZOVKU: Využijeme děděnou (protected) vlastnost $echoOutput.
        // Rodičovská metoda log() to již neudělá, protože jí předáme 'NONE'.
        if ($this->echoOutput) {
            echo $formattedMessage . PHP_EOL;
        }
        
        // 4. ZÁPIS DO SOUBORU: Zavoláme metodu rodiče, ale s naformátovanou zprávou.
        // Rodič zajistí: checkRotation, ensureLogDirectory, LOCK_EX a sanitizaci zprávy.
        // Použijeme level 'NONE', abychom obešli jakékoliv interní filtrování rodiče.
        // Použijeme zde $formattedMessage, aby rodič sanitizoval náš výstup.
        parent::log($formattedMessage, 'NONE');
    }

    // --- Pomocné metody pro snadné volání ---

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
    
    // Tato třída nezná úroveň CRITICAL, takže se spoléháme jen na známé úrovně.
    // Metoda exception() z původního kódu:
    public function exception(string $message): void
    {
        $this->log($message, "EXCEPTION");
    }
}
