<?php
// autoload.php - ZJEDNODUŠENÁ VERZE
declare(strict_types=1);

// Načti Config třídu
require_once __DIR__ . '/app/Core/Config.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/texts.php'; // ← PŘIDEJ TUTO ŘÁDKU!


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
require_once __DIR__ . '/app/Controllers/BaseController.php';
require_once __DIR__ . '/app/Controllers/HomeController.php';

require_once __DIR__ . '/app/Services/ArticleService.php';
require_once __DIR__ . '/app/Core/AdminMenu.php';
require_once __DIR__ . '/app/Core/AdminLayout.php';
require_once __DIR__ . '/app/Core/Router.php';
require_once __DIR__ . '/app/Core/Template.php';


// Debug mód (vypni na production!)
define('DEBUG', true);
if (DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}