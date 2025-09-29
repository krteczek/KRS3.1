<?php
// app/Core/Config.php
declare(strict_types=1);

namespace App\Core;

class Config
{
    private static array $config = [];

    public static function load(string $configPath): void
    {
        $loadedConfig = require $configPath;
        self::$config = array_merge(self::$config, $loadedConfig); // ‹ MERGE místo pøepsání
    }


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

    public static function site(string $key, $default = null)
    {
        return self::get("site.{$key}", $default);
    }

	public static function text(string $key, array $replace = [], string $default = ''): string
	{
	    $currentLang = $_SESSION['language'] ?? 'cs'; // nebo z URL/cookie

	    $value = self::get("texts.{$currentLang}.{$key}", $default);

	    foreach ($replace as $search => $replacement) {
	        $value = str_replace('{' . $search . '}', (string)$replacement, $value);
	    }

	    return $value;
	}


    public static function hasText(string $key): bool
    {
        return self::has('texts.' . $key);
    }

    // ODSTRAN loadTexts() - je zbyteèná když máme load()
}