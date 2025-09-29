<?php
// public/index.php
declare(strict_types=1);

require_once __DIR__ . '/../autoload.php';

use App\Database\DatabaseConnection;
use App\Session\SessionManager;
use App\Auth\LoginService;
use App\Security\CsrfProtection;
use App\Core\AdminMenu;
use App\Core\AdminLayout;
use App\Core\Router;
use App\Core\Config;

use App\Core\Template;


// ✅ NAČTI OBA CONFIG SOUBORY
Config::load(__DIR__ . '/../config/config.php');
Config::load(__DIR__ . '/../config/texts.php');

// ✅ DEBUG CEST
$configPath = __DIR__ . '/../config/config.php';
$textsPath = __DIR__ . '/../config/texts.php';
if (isset($_GET['lang'])) {
    $_SESSION['language'] = $_GET['lang'];
}
// Načti configy
Config::load($configPath);
Config::load($textsPath);

// Získání URL
$url = $_GET['url'] ?? '';
if (empty($url)) {
    $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $basePath = Config::site('base_path') ?? '';
    $url = str_replace($basePath, '', $url);
    $url = ltrim($url, '/');
}

// Base URL pro odkazy
$baseUrl = Config::site('base_path') ?? '';

// Inicializace služeb
$session = new SessionManager();
$db = new DatabaseConnection(Config::get('database'));
$csrf = new CsrfProtection($session);
$authService = new LoginService($db, $session, $csrf);
$template = new Template(Config::get('templates')['dir']);
$adminMenu = new AdminMenu($authService); // ← Předáme pouze authService
$adminLayout = new AdminLayout($authService, $baseUrl); // ← Správné parametry

// Vytvoření routeru a zpracování požadavku
$router = new Router($authService, $db, $csrf, $adminLayout);
$urlParts = explode('/', $url);

echo $router->handleRequest($url, $urlParts);