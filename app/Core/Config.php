<?php
// app/Core/Config.php
declare(strict_types=1);

namespace App\Core;

/**
 * Tøída pro správu konfigurace aplikace
 *
 * Poskytuje statické metody pro naèítání a pøístup ke konfiguraèním hodnotám,
 * lokalizovaným textùm a nastavení webu. Používá merge pro kombinování více konfigù.
 *
 * @package App\Core
 * @author KRS3
 * @version 3.0
 */
class Config
{
    /**
     * @var array Hlavní konfiguraèní pole
     */
    private static array $config = [];

    /**
     * Naète konfiguraèní soubor a slouèí ho s existující konfigurací
     *
     * @param string $configPath Cesta ke konfiguraènímu souboru
     * @return void
     *
     * @example
     * Config::load(__DIR__ . '/../../config/config.php');
     */
    public static function load(string $configPath): void
    {
        $loadedConfig = require $configPath;
        self::$config = array_merge(self::$config, $loadedConfig); // ‹ MERGE místo pøepsání
    }

    /**
     * Získá konfiguraèní hodnotu podle klíèe s teèkovou notací
     *
     * @param string $key Klíè hodnoty (napø. 'database.host')
     * @param mixed $default Výchozí hodnota pokud klíè neexistuje
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
     * Získá nastavení webu podle klíèe
     *
     * @param string $key Klíè nastavení webu (napø. 'name', 'url')
     * @param mixed $default Výchozí hodnota pokud klíè neexistuje
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
     * Získá lokalizovaný text podle klíèe
     *
     * Texty se naèítají podle aktuálního jazyka z session.
     * Podporuje nahrazování parametrù v textu pomocí {param}.
     *
     * @param string $key Klíè textu (napø. 'pages.home', 'messages.welcome')
     * @param array $replace Parametry pro nahrazení v textu
     * @param string $default Výchozí hodnota pokud klíè neexistuje
     * @return string Pøeložený text s nahrazenými parametry
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
		if (strpos($key, 'pages.login') !== false || strpos($key, 'ui.login') !== false) {
		        $language = $_SESSION['language'] ?? $_GET['lang'] ?? 'cs';
		    } else {
		        $language = $_SESSION['language'] ?? 'cs';
		    }

		    $value = self::get("texts.{$language}.{$key}", $default);
        $currentLang = $_SESSION['language'] ?? 'cs'; // nebo z URL/cookie

        $value = self::get("texts.{$currentLang}.{$key}", $default);

        foreach ($replace as $search => $replacement) {
            $value = str_replace('{' . $search . '}', (string)$replacement, $value);
        }

        return $value;
    }

    /**
     * Zkontroluje, zda existuje lokalizaèní text pro daný klíè
     *
     * @param string $key Klíè textu
     * @return bool True pokud text existuje
     */
    public static function hasText(string $key): bool
    {
        return self::has('texts.' . $key);
    }

    // ODSTRAN loadTexts() - je zbyteèná když máme load()
}