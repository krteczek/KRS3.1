<?php
// app/Core/Config.php
declare(strict_types=1);

namespace App\Core;

class Config
{
    private static array $config = [];

    public static function load(string $configPath): void
    {
        self::$config = require $configPath;
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
}