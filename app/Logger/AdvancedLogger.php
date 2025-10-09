<?php
// app/Core/Logger.php
declare(strict_types=1);

namespace App\Core;

class AdvancedLogger extends Logger
{
    private array $levels = [
        'DEBUG' => 0,
        'INFO' => 1,
        'WARNING' => 2,
        'ERROR' => 3,
        'EXCEPTION' => 4
    ];

    private int $minLevel;

    public function __construct(
        string $logFile = "app.log",
        bool $echoOutput = true,
        string $minLevel = 'DEBUG'
    ) {
        parent::__construct($logFile, $echoOutput);
        $this->minLevel = $this->levels[$minLevel] ?? $this->levels['INFO'];
    }

    public function log(string $message, string $level = "INFO"): void
    {
        $levelValue = $this->levels[$level] ?? $this->levels['INFO'];

        if ($levelValue < $this->minLevel) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s.v');
        $logEntry = sprintf(
            "[%s] %-8s %s%s",
            $timestamp,
            $level,
            $message,
            PHP_EOL
        );

        if ($this->echoOutput) {
            echo $logEntry;
        }

        try {
            file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            if ($this->echoOutput) {
                echo "LOG ERROR: " . $e->getMessage() . PHP_EOL;
            }
        }
    }

    public function debug(string $message): void
    {
        $this->log($message, "DEBUG");
    }
}