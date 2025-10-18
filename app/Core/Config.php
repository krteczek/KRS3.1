<?php
// app/Core/Config.php
declare(strict_types=1);

namespace App\Core;

/**
 * Třída pro správu konfigurace aplikace
 *
 * Poskytuje statické metody pro načítání a přístup ke konfiguračním hodnotám,
 * lokalizovaným textům a nastavení webu. Používá merge pro kombinování více konfigů.
 *
 * @package App\Core
 * @author KRS3
 * @version 3.0
 */
class Config
{
    /**
     * @var array Hlavní konfigurační pole
     */
    private static array $config = [];

    /**
     * Načte konfigurační soubor a sloučí ho s existující konfigurací
     *
     * @param string $configPath Cesta ke konfiguračnímu souboru
     * @return void
     *
     * @example
     * Config::load(__DIR__ . '/../../config/config.php');
     */
    public static function load(string $configPath): void
    {
        if (!file_exists($configPath)) {
            throw new \RuntimeException("Konfigurační soubor neexistuje: {$configPath}");
        }

        $loadedConfig = require $configPath;
        self::$config = array_merge(self::$config, $loadedConfig);
    }

    /**
     * Získá konfigurační hodnotu podle klíče s tečkovou notací
     *
     * @param string $key Klíč hodnoty (např. 'database.host')
     * @param mixed $default Výchozí hodnota pokud klíč neexistuje
     * @return mixed Nalezená hodnota nebo výchozí hodnota
     *
     * @example
     * $host = Config::get('database.host');
     * $debug = Config::get('app.debug', false);
     */
    public static function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Získá nastavení webu podle klíče
     *
     * @param string $key Klíč nastavení webu (např. 'name', 'url')
     * @param mixed $default Výchozí hodnota pokud klíč neexistuje
     * @return mixed Nalezená hodnota nebo výchozí hodnota
     *
     * @example
     * $siteName = Config::site('name');
     * $siteUrl = Config::site('url');
     */
    public static function site(string $key, $default = null)
    {
        return self::get("site.{$key}", $default);
    }


    /**
     * Získá nastavení logování webu podle klíče
     *
     * @param string $key Klíč nastavení webu (např. 'name', 'url')
     * @param mixed $default Výchozí hodnota pokud klíč neexistuje
     * @return mixed Nalezená hodnota nebo výchozí hodnota
     *
     * @example
     * $logsDir = Config::logs('dir');
     * $logsFile = Config::logs('file');
     */

    public static function logs(string $key, $default = null)
    {
        return self::get("logs.{$key}", $default);
    }


    /**
     * Získá nastavení logování webu podle klíče
     *
     * @param string $key Klíč nastavení webu (např. 'name', 'url')
     * @param mixed $default Výchozí hodnota pokud klíč neexistuje
     * @return mixed Nalezená hodnota nebo výchozí hodnota
     *
     * @example
     * $csfrTokenExpire = Config::csrf('token_expire');
     * $csfrValidateOrigin = Config::csrf('validate_origin');
     */

    public static function csrf(string $key, $default = null)
    {
        return self::get("csrf.{$key}", $default);
    }


    /**
     * Získá lokalizovaný text podle klíče
     *
     * Texty se načítají podle aktuálního jazyka z session.
     * Podporuje nahrazování parametrů v textu pomocí {param}.
     *
     * @param string $key Klíč textu (např. 'pages.home', 'messages.welcome')
     * @param array $replace Parametry pro nahrazení v textu
     * @param string $default Výchozí hodnota pokud klíč neexistuje
     * @return string Přeložený text s nahrazenými parametry
     *
     * @example
     * // Základní použití
     * $text = Config::text('pages.home');
     *
     * // S parametry
     * $welcome = Config::text('navigation.welcome', ['username' => 'John']);
     *
     * // S výchozí hodnotou
     * $text = Config::text('nonexistent.key', [], 'Default text');
     */
    public static function text(string $key, array $replace = [], string $default = ''): string
    {
        // Získání jazyka z session nebo z URL parametru pro přihlášení
        if (strpos($key, 'pages.login') !== false || strpos($key, 'ui.login') !== false) {
            $language = $_SESSION['language'] ?? $_GET['lang'] ?? 'cs';
        } else {
            $language = $_SESSION['language'] ?? 'cs';
        }

        $value = self::get("texts.{$language}.{$key}", $default);

        // Pokud není nalezeno, zkusíme výchozí jazyk 'cs'
        if ($value === $default && $language !== 'cs') {
            $value = self::get("texts.cs.{$key}", $default);
        }

        foreach ($replace as $search => $replacement) {
            $value = str_replace('{' . $search . '}', (string)$replacement, $value);
        }

        return $value;
    }

    /**
     * Zkontroluje, zda existuje konfigurační hodnota pro daný klíč
     *
     * @param string $key Klíč
     * @return bool True pokud hodnota existuje
     */
    public static function has(string $key): bool
    {
        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return false;
            }
            $value = $value[$k];
        }

        return true;
    }

    /**
     * Zkontroluje, zda existuje lokalizační text pro daný klíč
     *
     * @param string $key Klíč textu
     * @return bool True pokud text existuje
     */
    public static function hasText(string $key): bool
    {
        $language = $_SESSION['language'] ?? 'cs';
        return self::has("texts.{$language}.{$key}");
    }
}