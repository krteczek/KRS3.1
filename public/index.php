<?php
// public/index.php - KONEČNÁ VERZE
declare(strict_types=1);

require_once __DIR__ . '/../autoload.php';

use App\Core\Config;
use App\Core\Template;

// ✅ NAČTENÍ KONFIGURACE
Config::load(__DIR__ . '/../config/config.php');
//Config::load(__DIR__ . '/../config/texts.php');

// ✅ ERROR HANDLING (váš existující kód)
$logger = App\Logger\Logger::getInstance();
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

// ✅ BASE URL
$baseUrl = Config::site('base_path') ?? '';
$csrfConfig = Config::get('csrf');
// ✅ VYTVOŘENÍ SLUŽEB
$database = new App\Database\DatabaseConnection();
$session = new App\Session\SessionManager();
$csrf = new App\Security\CsrfProtection($session, $csrfConfig);
$authService = new App\Auth\LoginService($database, $session, $csrf);

// ✅ VYTVOŘENÍ CATEGORY A MENU SERVICE
$categoryService = new App\Services\CategoryService($database);
$menuService = new App\Services\MenuService($categoryService, $baseUrl);

// ✅ VYTVOŘENÍ TEMPLATE S GLOBÁLNÍMI PROMĚNNÝMI
$template = new Template();
$template->assignMultiple([
    'baseUrl' => $baseUrl,
    'siteName' => Config::site('name'),
    'menuService' => $menuService,
    'authService' => $authService
]);

$adminLayout = new App\Core\AdminLayout($authService, $baseUrl);

// ✅ VYTVOŘENÍ ROUTERU
$router = new App\Core\Router(
    $authService,
    $database,
    $csrf,
    $adminLayout,
    $template,
    $menuService
);

// ✅ SPRÁVA JAZYKŮ
if (isset($_GET['lang']) && in_array($_GET['lang'], ['cs', 'en', 'de'])) {
    $_SESSION['language'] = $_GET['lang'];
}

// ✅ ZPRACOVÁNÍ POŽADAVKU
echo $router->handleRequest($url, explode('/', $url));