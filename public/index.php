<?php
// public/index.php
declare(strict_types=1);

/**
 * Hlavní vstupní bod aplikace KRS3
 *
 * Tento soubor inicializuje všechny potřebné služby, načte konfiguraci
 * a zpracuje příchozí HTTP požadavek pomocí routeru.
 * Slouží jako front controller celé aplikace.
 *
 * @package public
 * @author KRS3
 * @version 3.0
 */

require_once __DIR__ . '/../autoload.php';

use App\Logger\Logger;
use App\Database\DatabaseConnection;
use App\Session\SessionManager;
use App\Auth\LoginService;
use App\Security\CsrfProtection;
use App\Core\AdminMenu;
use App\Core\AdminLayout;
use App\Core\Router;
use App\Core\Config;
use App\Core\Template;

// ✅ NAČTENÍ KONFIGURAČNÍCH SOUBORŮ
App\Core\Config::load(__DIR__ . '/../config/config.php');
App\Core\Config::load(__DIR__ . '/../config/texts.php');

//$logger = new AdvancedLogger("app.log", true, 'DEBUG');
// Registrace error handleru
$logger = Logger::getInstance();
set_exception_handler(function(\Throwable $e) use ($logger) {
    $logger->exception($e, "Uncaught exception");
});


set_error_handler(function($errno, $errstr, $errfile, $errline) use ($logger) {
    $logger->error("Error {$errno}: {$errstr} in {$errfile} on line {$errline}");
    return false;
});

register_shutdown_function(function() use ($logger) {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $logger->error("Fatal error: {$error['message']} in {$error['file']} on line {$error['line']}");
    }
});



// ✅ ZPRACOVÁNÍ URL
$url = $_GET['url'] ?? '';

if (empty($url)) {
    $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $basePath = Config::site('base_path') ?? '';
    $url = str_replace($basePath, '', $url);
    $url = ltrim($url, '/');
}

// Base URL pro odkazy v aplikaci
$baseUrl = Config::site('base_path') ?? '';


// Create services
$database = new App\Database\DatabaseConnection();
$session = new App\Session\SessionManager();
$csrf = new App\Security\CsrfProtection($session);
$authService = new App\Auth\LoginService($database, $session,$csrf);
$adminLayout = new App\Core\AdminLayout($authService, $baseUrl);

// Create router (už nemusíme vytvářet ArticleService a CategoryService explicitně)
$router = new App\Core\Router(
    $authService,
    $database,
    $csrf,
    $adminLayout
);

$urlParts = explode('/', $url);

// ✅ SPRÁVA JAZYKŮ
if (isset($_GET['lang']) && in_array($_GET['lang'], ['cs', 'en', 'de'])) {
    $_SESSION['language'] = $_GET['lang'];
}

// ✅ ZPRACOVÁNÍ POŽADAVKU A VÝPIS VÝSLEDKU
echo $router->handleRequest($url, $urlParts);