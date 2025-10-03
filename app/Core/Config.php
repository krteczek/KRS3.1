<?php
// app/Core/Config.php
declare(strict_types=1);

namespace App\Core;

/**
 * T��da pro spr�vu konfigurace aplikace
 *
 * Poskytuje statick� metody pro na��t�n� a p��stup ke konfigura�n�m hodnot�m,
 * lokalizovan�m text�m a nastaven� webu. Pou��v� merge pro kombinov�n� v�ce konfig�.
 *
 * @package App\Core
 * @author KRS3
 * @version 3.0
 */
class Config
{
    /**
     * @var array Hlavn� konfigura�n� pole
     */
    private static array $config = [];

    /**
     * Na�te konfigura�n� soubor a slou�� ho s existuj�c� konfigurac�
     *
     * @param string $configPath Cesta ke konfigura�n�mu souboru
     * @return void
     *
     * @example
     * Config::load(__DIR__ . '/../../config/config.php');
     */
    public static function load(string $configPath): void
    {
        $loadedConfig = require $configPath;
        self::$config = array_merge(self::$config, $loadedConfig); // � MERGE m�sto p�eps�n�
    }

    /**
     * Z�sk� konfigura�n� hodnotu podle kl��e s te�kovou notac�
     *
     * @param string $key Kl�� hodnoty (nap�. 'database.host')
     * @param mixed $default V�choz� hodnota pokud kl�� neexistuje
     * @return mixed Nalezen� hodnota nebo v�choz� hodnota
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
     * Z�sk� nastaven� webu podle kl��e
     *
     * @param string $key Kl�� nastaven� webu (nap�. 'name', 'url')
     * @param mixed $default V�choz� hodnota pokud kl�� neexistuje
     * @return mixed Nalezen� hodnota nebo v�choz� hodnota
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
     * Z�sk� lokalizovan� text podle kl��e
     *
     * Texty se na��taj� podle aktu�ln�ho jazyka z session.
     * Podporuje nahrazov�n� parametr� v textu pomoc� {param}.
     *
     * @param string $key Kl�� textu (nap�. 'pages.home', 'messages.welcome')
     * @param array $replace Parametry pro nahrazen� v textu
     * @param string $default V�choz� hodnota pokud kl�� neexistuje
     * @return string P�elo�en� text s nahrazen�mi parametry
     *
     * @example
     * // Z�kladn� pou�it�
     * $text = Config::text('pages.home');
     *
     * // S parametry
     * $welcome = Config::text('navigation.welcome', ['username' => 'John']);
     *
     * // S v�choz� hodnotou
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
     * Zkontroluje, zda existuje lokaliza�n� text pro dan� kl��
     *
     * @param string $key Kl�� textu
     * @return bool True pokud text existuje
     */
    public static function hasText(string $key): bool
    {
        return self::has('texts.' . $key);
    }

    // ODSTRAN loadTexts() - je zbyte�n� kdy� m�me load()
}