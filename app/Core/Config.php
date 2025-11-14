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
 * @version 3.1
 */
class Config
{
    /**
     * @var array Hlavní konfigurační pole
     */
    private static array $config = [];

    /**
     * @var array Načtené překlady
     */
    private static array $texts = [];

    /**
     * @var bool Příznak, zda byly překlady načteny
     */
    private static bool $textsLoaded = false;

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
     * Načte překlady pro aktuální jazyk
     *
     * @return void
     */
    private static function loadTexts(): void
    {
        if (self::$textsLoaded) {
            return;
        }

        $language = self::getCurrentLanguage();

        try {
            require_once __DIR__ . '/../../config/texts/loader.php';
            self::$texts = \TextLoader::loadWithFallback($language);
            self::$textsLoaded = true;
        } catch (\Exception $e) {
            // Fallback na prázdné pole
            self::$texts = [];
            self::$textsLoaded = true;
            // Můžeme zalogovat chybu, ale nesmíme aplikaci zastavit
            error_log("Chyba při načítání překladů: " . $e->getMessage());
        }
    }

    /**
     * Získá aktuální jazyk
     *
     * @return string Kód jazyka
     */
    private static function getCurrentLanguage(): string
    {
        // Pro přihlašovací stránku bere v úvahu URL parametr
        if (isset($_GET['lang']) && in_array($_GET['lang'], ['cs', 'en', 'de'])) {
            return $_GET['lang'];
        }

        return $_SESSION['language'] ?? 'cs';
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
     * Získá nastavení logování podle klíče
     *
     * @param string $key Klíč nastavení logování
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
     * Získá nastavení CSRF podle klíče
     *
     * @param string $key Klíč nastavení CSRF
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
     * Získá nastavení zabezpečení podle klíče
     *
     * @param string $key Klíč nastavení zabezpečení
     * @param mixed $default Výchozí hodnota pokud klíč neexistuje
     * @return mixed Nalezená hodnota nebo výchozí hodnota
     *
     * @example
     * $passwordAlgo = Config::security('password_algo');
     */
    public static function security(string $key, $default = null)
    {
        return self::get("security.{$key}", $default);
    }

    /**
     * Získá nastavení šablon podle klíče
     *
     * @param string $key Klíč nastavení šablon
     * @param mixed $default Výchozí hodnota pokud klíč neexistuje
     * @return mixed Nalezená hodnota nebo výchozí hodnota
     *
     * @example
     * $templatesDir = Config::templates('dir');
     */
    public static function templates(string $key, $default = null)
    {
        return self::get("templates.{$key}", $default);
    }

    /**
     * Získá lokalizovaný text podle klíče
     *
     * Texty se načítají podle aktuálního jazyka s fallback na češtinu.
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
        self::loadTexts();

        $keys = explode('.', $key);
        $value = self::$texts;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        // Pokud je hodnota pole, vrátíme výchozí hodnotu
        if (is_array($value)) {
            return $default;
        }

        $text = (string)$value;

        foreach ($replace as $search => $replacement) {
            $text = str_replace('{' . $search . '}', (string)$replacement, $text);
        }

        return $text;
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
        self::loadTexts();

        $keys = explode('.', $key);
        $value = self::$texts;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return false;
            }
            $value = $value[$k];
        }

        return !is_array($value);
    }

    /**
     * Vynutí znovunačtení překladů (užitečné pro testování)
     *
     * @return void
     */
    public static function reloadTexts(): void
    {
        self::$textsLoaded = false;
        self::$texts = [];
    }

    /**
     * Získá základní URL aplikace
     *
     * @return string Základní URL
     */
    public static function getBaseUrl(): string
    {
        return self::site('base_path', '/');
    }

    /**
     * Získá cestu k adresáři šablon
     *
     * @return string Cesta k šablonám
     */
    public static function getTemplatesDir(): string
    {
        return self::templates('dir', __DIR__ . '/../../templates');
    }

    /**
     * Získá cestu k adresáři logů
     *
     * @return string Cesta k logům
     */
    public static function getLogsDir(): string
    {
        return self::logs('dir', __DIR__ . '/../../logs');
    }
}