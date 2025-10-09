<?php

use App\Logger\Logger;

if (!function_exists('log_info')) {
    function log_info(string $message): void
    {
        Logger::getInstance()->info($message);
    }
}

if (!function_exists('log_error')) {
    function log_error(string $message): void
    {
        Logger::getInstance()->error($message);
    }
}

if (!function_exists('log_exception')) {
    function log_exception(\Throwable $e, string $message = ""): void
    {
        Logger::getInstance()->exception($e, $message);
    }
}