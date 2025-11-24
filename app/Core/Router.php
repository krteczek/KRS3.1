<?php
declare(strict_types=1);

namespace App\Core;

use App\Controllers\ArticleController;
use App\Controllers\CategoryController;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Controllers\GalleryController;
use App\Controllers\ImagesController;

use App\Services\ArticleService;
use App\Services\CategoryService;
use App\Services\GalleryService;
use App\Services\MenuService;
use App\Services\ImageService;

use App\Auth\LoginService;
use App\Database\DatabaseConnection;
use App\Security\CsrfProtection;
use App\Logger\Logger;

/**
 * Array-based Router pro KRS3 CMS
 * 
 * @package App\Core
 * @author KRS3
 * @version 3.0
 */
class Router
{
    /** @var array<string, array<string, array>> Routing configuration */
    private array $routes = [];

    /** @var Logger Logger instance */
    private Logger $logger;

    /**
     * Constructor
     *
     * @param LoginService $authService Authentication service
     * @param DatabaseConnection $db Database connection
     * @param CsrfProtection $csrf CSRF protection service
     * @param AdminLayout $adminLayout Admin layout renderer
     * @param Template $template Template engine
     * @param MenuService $menuService Menu service
     */
    public function __construct(
        private LoginService $authService,
        private DatabaseConnection $db,
        private CsrfProtection $csrf,
        private AdminLayout $adminLayout,
        private Template $template,
        private MenuService $menuService
    ) {
        $this->logger = Logger::getInstance();
        $this->loadRoutes();
        
        $this->logger->info('Router initialized successfully', [
            'total_routes_loaded' => $this->countTotalRoutes(),
            'auth_service' => get_class($authService),
            'csrf_protection' => get_class($csrf)
        ]);
    }

    /**
     * Načte routy z konfiguračního souboru
     * 
     * @throws \RuntimeException Pokud se soubor s routami nenajde
     * @return void
     */
    private function loadRoutes(): void
    {
        $routesPath = __DIR__ . '/../../config/routes.php';
        
        if (!file_exists($routesPath)) {
            $this->logger->critical('Routes configuration file not found', ['path' => $routesPath]);
            throw new \RuntimeException("Routes configuration file not found: {$routesPath}");
        }

        $this->routes = require $routesPath;
        
        $this->logger->info('Routes configuration loaded successfully', [
            'path' => $routesPath,
            'total_routes' => $this->countTotalRoutes(),
            'methods_available' => array_keys($this->routes)
        ]);
    }

    /**
     * Spočítá celkový počet rout v konfiguraci
     *
     * @return int Celkový počet rout
     */
    private function countTotalRoutes(): int
    {
        $count = 0;
        foreach ($this->routes as $methodGroup) {
            $count += count($methodGroup);
        }
        return $count;
    }

    /**
     * Zpracuje HTTP požadavek
     *
     * @param string $path Request path
     * @param array<string> $urlParts URL parts
     * @return string Response content
     */
    public function handleRequest(string $path, array $urlParts): string
    {
        $fixedPath = $path === '' ? '/' : '/' . $path;
        $startTime = microtime(true);
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $clientIp = $this->getClientIp();
        $userAgent = $this->getUserAgent();

        $this->logger->info('HTTP request received', [
            'method' => $requestMethod,
            'path' => $fixedPath,
            'url_parts' => $urlParts,
            'client_ip' => $clientIp,
            'user_agent' => $userAgent
        ]);

        try {
            $routeConfig = $this->findMatchingRoute($requestMethod, $fixedPath, $urlParts);
            
            if (!$routeConfig) {
                $this->logger->warning('Route not found - returning 404');
                return $this->handleNotFound();
            }

            $this->logger->debug('Route matched successfully', [
                'route_name' => $routeConfig['name'],
                'controller' => $routeConfig['controller'],
                'method' => $routeConfig['method']
            ]);

            // Kontrola autentizace
            if ($routeConfig['requires_auth'] ?? false) {
                $this->checkAuthentication($fixedPath, $clientIp);
            }

            // CSRF ochrana
            if ($routeConfig['csrf'] ?? false) {
                $this->validateCsrfProtection($routeConfig['name']);
            }

            $result = $this->executeRoute($routeConfig);
            $this->logRequestComplete($startTime, $routeConfig['name'], 200);
            
            return $result;

        } catch (\Exception $e) {
            return $this->handleException($e, $startTime, $fixedPath, $requestMethod, $clientIp);
        }
    }

    /**
     * Najde matching route z konfigurace
     *
     * @param string $method HTTP method
     * @param string $path Request path
     * @param array<string> $urlParts URL parts
     * @return array<string, mixed>|null Route configuration or null
     */
    private function findMatchingRoute(string $method, string $path, array $urlParts): ?array
    {
        $methodKey = $this->getMethodKey($method);
        $routeGroup = $this->routes[$methodKey] ?? [];

        $this->logger->debug('Searching for matching route', [
            'method' => $method,
            'method_key' => $methodKey,
            'path' => $path,
            'routes_in_group' => count($routeGroup)
        ]);

        foreach ($routeGroup as $routePattern => $config) {
            $params = [];
            if ($this->matchesRoutePattern($routePattern, $path, $params)) {
                $config['params'] = $params;
                
                $this->logger->debug('Route pattern matched', [
                    'pattern' => $routePattern,
                    'params_found' => $params
                ]);
                
                return $config;
            }
        }

        $this->logger->debug('No route pattern matched', [
            'method' => $method,
            'path' => $path,
            'patterns_tried' => array_keys($routeGroup)
        ]);

        return null;
    }

    /**
     * Získá správný klíč metody pro routing skupinu
     *
     * @param string $method HTTP method
     * @return string Method key
     */
private function getMethodKey(string $method): string
{
    // Case-insensitive kontrola pro admin routy
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    if (stripos($requestUri, '/admin') === 0) {
        return $method === 'POST' ? 'ADMIN_POST' : 'ADMIN_GET';
    }

    return $method;
}
    /**
     * Zkontroluje shodu URL s route patternem
     *
     * @param string $pattern Route pattern
     * @param string $path Request path
     * @param array<string, string> $params Extracted parameters
     * @return bool True if pattern matches
     */
    private function matchesRoutePattern(string $pattern, string $path, array &$params): bool
    {
        $basePath = $this->getBasePath();
        $actualPath = $path === '' ? '/' : $path;
        
        // Patterny jsou kompletní, nepřidáváme base path
        $fullPattern = $pattern;

        $regexPattern = preg_replace('/\{([^}]+)\}/', '(?P<$1>[^/]+)', $fullPattern);
        $regexPattern = '#^' . str_replace('/', '\/', $regexPattern) . '$#';
        
        if (preg_match($regexPattern, $actualPath, $matches)) {
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }
            return true;
        }

        return false;
    }

    /**
     * Kontroluje autentizaci uživatele
     * 
     * @param string $path Request path
     * @param string $clientIp Client IP address
     * @throws \RuntimeException Pokud uživatel není přihlášen
     * @return void
     */
    private function checkAuthentication(string $path, string $clientIp): void
    {
        if (!$this->authService->isLoggedIn()) {
            $this->logger->warning('Unauthorized access attempt to protected route', [
                'path' => $path,
                'client_ip' => $clientIp,
                'user_agent' => $this->getUserAgent(),
                'session_id' => session_id() ?: 'no-session'
            ]);

            $baseUrl = $this->template->getData()['baseUrl'] ?? '';
            header('Location: ' . $baseUrl . 'login');
            exit;
        }

        $this->logger->debug('User authentication verified', [
            'user' => $this->authService->getUsername(),
            'user_id' => $this->authService->getUser()['id'] ?? 'unknown',
            'path' => $path
        ]);
    }

    /**
     * Validuje CSRF token
     * 
     * @param string $routeName Route name
     * @throws \RuntimeException Pokud CSRF token není validní
     * @return void
     */
/**
 * Validuje CSRF token
 * 
 * @param string $routeName Route name
 * @throws \RuntimeException Pokud CSRF token není validní
 * @return void
 */
private function validateCsrfProtection(string $routeName): void
{
 
    // ✅ POUŽIJ ECHO PRO DEBUG POST DAT
    echo "=== POST DATA DEBUG ===";
    echo "POST keys: " . implode(', ', array_keys($_POST));
    echo "CSRF token: " . ($_POST['csrf_token'] ?? 'NOT_FOUND');
    echo "POST count: " . count($_POST);
    echo "====================";

   // ✅ PŘESUŇ DEBUG SEM - NA ZAČÁTEK METODY
    $this->logger->debug('POST DATA DEBUG', [
        'all_post_keys' => array_keys($_POST),
        'csrf_token_value' => $_POST['csrf_token'] ?? 'NOT_FOUND',
        'token_name' => $this->csrf->getTokenName(),
        'post_data_count' => count($_POST)
    ]);
	$this->logger->debug('POST DATA DEBUG', [
        'all_post_keys' => array_keys($_POST),
        'csrf_token_value' => $_POST['csrf_token'] ?? 'NOT_FOUND',
        'token_name' => $this->csrf->getTokenName()
    ]);
    try {
        // ✅ OPRAVA: Předáme token z POST data
        $tokenName = $this->csrf->getTokenName();
        $token = $_POST[$tokenName] ?? '';
        
        $this->logger->debug('CSRF token validation attempt', [
            'route' => $routeName,
            'token_name' => $tokenName,
            'token_present' => !empty($token),
            'token_length' => strlen($token)
        ]);
        
        $this->csrf->validateToken($token);
        
        $this->logger->debug('CSRF token validation successful', [
            'route' => $routeName
        ]);
    } catch (\Exception $e) {
        $this->logger->warning('CSRF token validation failed', [
            'route' => $routeName,
            'error' => $e->getMessage(),
            'client_ip' => $this->getClientIp(),
            'user_agent' => $this->getUserAgent()
        ]);
        throw $e;
    }
}

    /**
     * Spustí nalezenou routu
     *
     * @param array<string, mixed> $config Route configuration
     * @return string Response content
     */
    private function executeRoute(array $config): string
    {
        $controller = $this->createController($config['controller']);
        $method = $config['method'];
        $params = $config['params'] ?? [];

        $this->logger->info('Executing controller action', [
            'controller' => get_class($controller),
            'method' => $method,
            'params' => $params
        ]);

        try {
            $result = call_user_func_array([$controller, $method], array_values($params));
            
            $this->logger->debug('Controller action executed successfully', [
                'controller' => get_class($controller),
                'method' => $method,
                'result_type' => gettype($result)
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            $this->logger->error('Controller action execution failed', [
                'controller' => get_class($controller),
                'method' => $method,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
    }

    /**
     * Vytvoří instanci controlleru s potřebnými závislostmi
     * 
     * @param string $controllerName Controller class name
     * @throws \RuntimeException Pokud controller neexistuje
     * @return object Controller instance
     */
    private function createController(string $controllerName): object
    {
        $baseUrl = $this->template->getData()['baseUrl'] ?? '';

        $this->logger->debug('Creating controller instance', [
            'controller_name' => $controllerName,
            'base_url' => $baseUrl
        ]);

        switch ($controllerName) {
            case 'HomeController':
                $articleService = new ArticleService($this->db);
                $categoryService = new CategoryService($this->db);
                return new HomeController(
                    $articleService,
                    $categoryService,
                    $this->template,
                    $baseUrl,
                    $this->authService,
                    $this->menuService
                );

            case 'AuthController':
                return new AuthController(
                    $this->authService,
                    $this->csrf,
                    $baseUrl,
                    $this->template
                );

            case 'AdminController':
                return new AdminController(
                    $this->authService,
                    $baseUrl,
                    $this->adminLayout
                );

            case 'ArticleController':
                $articleService = new ArticleService($this->db);
                $categoryService = new CategoryService($this->db);
                return new ArticleController(
                    $articleService,
                    $this->authService,
                    $this->csrf,
                    $baseUrl,
                    $this->adminLayout,
                    $categoryService
                );

            case 'CategoryController':
                $categoryService = new CategoryService($this->db);
                return new CategoryController(
                    $categoryService,
                    $this->authService,
                    $this->csrf,
                    $baseUrl,
                    $this->adminLayout
                );

            case 'GalleryController':
                $galleryService = new GalleryService($this->db);
                return new GalleryController(
                    $galleryService,
                    $this->authService,
                    $this->csrf,
                    $baseUrl,
                    $this->adminLayout
                );

            case 'ImagesController':
                $imageService = new ImageService($this->db);
                return new ImagesController(
                    $imageService,
                    $this->authService,
                    $this->csrf,
                    $baseUrl,
                    $this->adminLayout,
                    $this->db
                );

            default:
                $this->logger->critical('Unknown controller requested', [
                    'controller_name' => $controllerName,
                    'available_controllers' => [
                        'HomeController', 'AuthController', 'AdminController', 
                        'ArticleController', 'CategoryController', 'GalleryController', 'ImagesController'
                    ]
                ]);
                throw new \RuntimeException("Unknown controller: {$controllerName}");
        }
    }

    /**
     * Zpracuje chybovou stránku 404
     *
     * @return string 404 page content
     */
    private function handleNotFound(): string
    {
        $siteName = Config::site('name');
        $baseUrl = $this->template->getData()['baseUrl'] ?? '';

        $this->logger->info('Rendering 404 page', [
            'site_name' => $siteName,
            'base_url' => $baseUrl
        ]);

        return $this->template->render('layouts/frontend.php', [
            'title' => Config::text('pages.404', ['site_name' => $siteName]),
            'content' => $this->template->render('pages/404.php', [
                'baseUrl' => $baseUrl,
                'message' => Config::text('messages.404'),
                'backLinkText' => Config::text('ui.back_to_home'),
                'siteName' => $siteName,
                'user' => null
            ]),
            'baseUrl' => $baseUrl,
            'siteName' => $siteName,
            'user' => null
        ]);
    }

    /**
     * Zpracuje výjimku
     *
     * @param \Exception $e Exception
     * @param float $startTime Request start time
     * @param string $path Request path
     * @param string $method HTTP method
     * @param string $clientIp Client IP
     * @return string Error page content
     */
    private function handleException(
        \Exception $e, 
        float $startTime, 
        string $path, 
        string $method, 
        string $clientIp
    ): string {
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);

        $this->logger->exception($e, "Router exception occurred during request processing");
        $this->logger->error('Request processing failed', [
            'path' => $path,
            'method' => $method,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'execution_time_ms' => $executionTime,
            'client_ip' => $clientIp,
            'user_agent' => $this->getUserAgent()
        ]);

        return $this->template->render('pages/500.php', [
            'message' => 'Došlo k chybě na serveru: ' . $e->getMessage(),
            'baseUrl' => $this->template->getData()['baseUrl'] ?? '',
            'backLinkText' => 'Zpět na úvodní stránku'
        ]);
    }

    /**
     * Zaloguje dokončení requestu
     *
     * @param float $startTime Request start time
     * @param string $route Route name
     * @param int $statusCode HTTP status code
     * @return void
     */
    private function logRequestComplete(float $startTime, string $route, int $statusCode): void
    {
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);

        $this->logger->info('Request completed successfully', [
            'route' => $route,
            'status_code' => $statusCode,
            'execution_time_ms' => $executionTime,
            'memory_peak_mb' => round(memory_get_peak_usage() / 1024 / 1024, 2),
            'memory_usage_mb' => round(memory_get_usage() / 1024 / 1024, 2)
        ]);
    }

    /**
     * Získá IP adresu klienta
     *
     * @return string Client IP address
     */
    private function getClientIp(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        if ($this->logger->isLevelEnabled('DEBUG')) {
            $this->logger->debug('Client IP detection', [
                'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'not_set',
                'http_x_forwarded_for' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'not_set',
                'http_client_ip' => $_SERVER['HTTP_CLIENT_IP'] ?? 'not_set',
                'detected_ip' => $ip
            ]);
        }
        
        return $ip;
    }

    /**
     * Získá User Agent
     *
     * @return string User agent string
     */
    private function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    }

    /**
     * Zjistí base path automaticky z konfigurace nebo URL
     *
     * @return string Base path
     */
    private function getBasePath(): string
    {
        // 1. Zkusíme z configu
        $templateData = $this->template->getData();
        $baseUrl = $templateData['baseUrl'] ?? 'NOT_SET_IN_TEMPLATE';
        
        if ($baseUrl && $baseUrl !== '/' && $baseUrl !== 'NOT_SET_IN_TEMPLATE') {
            $path = parse_url($baseUrl, PHP_URL_PATH) ?? $baseUrl;
            return rtrim($path, '/');
        }
        
        return '';
    }

    /**
     * Debug metoda pro získání všech rout (pouze pro vývoj)
     *
     * @return array<string, array> All routes
     */
    public function getRoutes(): array
    {
        $this->logger->debug('Router configuration accessed via getRoutes()');
        return $this->routes;
    }

    /**
     * Vrátí statistiku rout (pro monitoring)
     *
     * @return array<string, mixed> Route statistics
     */
    public function getRouteStats(): array
    {
        $stats = [
            'total_routes' => $this->countTotalRoutes(),
            'methods' => []
        ];

        foreach ($this->routes as $method => $routes) {
            $stats['methods'][$method] = count($routes);
        }

        $this->logger->debug('Route statistics generated', $stats);
        
        return $stats;
    }
}