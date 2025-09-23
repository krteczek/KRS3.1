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

// Získání URL
$url = $_GET['url'] ?? '';
if (empty($url)) {
    $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $basePath = Config::site('base_path', '');
    $url = str_replace($basePath, '', $url);
    $url = ltrim($url, '/');
}

// Inicializace služeb
$session = new SessionManager();
$db = new DatabaseConnection(Config::database());
$csrf = new CsrfProtection($session);
$authService = new LoginService($db, $session, $csrf);

// Inicializace layoutu
$adminMenu = new AdminMenu($authService, Config::site('base_path'));
$adminLayout = new AdminLayout($adminMenu, Config::site('base_path'));

// Vytvoření routeru a zpracování požadavku
$router = new Router($authService, $db, $csrf, $adminLayout);
$urlParts = explode('/', $url);

echo $router->handleRequest($url, $urlParts);