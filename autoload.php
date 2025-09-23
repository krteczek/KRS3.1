<?php
// autoload.php - ZJEDNODUŠENÁ VERZE
declare(strict_types=1);

// Načti Config třídu
require_once __DIR__ . '/app/Core/Config.php';

// Inicializuj konfiguraci
App\Core\Config::load(__DIR__ . '/config/config.php');

// Načti základní třídy ručně
require_once __DIR__ . '/app/Session/SessionManager.php';
require_once __DIR__ . '/app/Database/DatabaseConnection.php';
require_once __DIR__ . '/app/Security/CsrfProtection.php';
require_once __DIR__ . '/app/Auth/LoginService.php';
require_once __DIR__ . '/app/Controllers/AuthController.php';
require_once __DIR__ . '/app/Controllers/AdminController.php';
require_once __DIR__ . '/app/Controllers/ArticleController.php';
require_once __DIR__ . '/app/Services/ArticleService.php';
require_once __DIR__ . '/app/Core/AdminMenu.php';
require_once __DIR__ . '/app/Core/AdminLayout.php';

// Debug mód (vypni na production!)
define('DEBUG', true);
if (DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}