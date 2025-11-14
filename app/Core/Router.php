<?php
// app/Core/Router.php
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
 * Router pro zpracování HTTP požadavků
 *
 * @package App\Core
 * @author KRS3
 * @version 2.0
 */
class Router
{
    private Logger $logger;

    public function __construct(
        private LoginService $authService,
        private DatabaseConnection $db,
        private CsrfProtection $csrf,
        private AdminLayout $adminLayout,
        private Template $template,
        private MenuService $menuService
    ) {
        $this->logger = Logger::getInstance();
    }

    public function handleRequest(string $path, array $urlParts): string
    {
        $startTime = microtime(true);
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $clientIp = $this->getClientIp();
        $userAgent = $this->getUserAgent();

        $this->logger->info('Request received', [
            'method' => $requestMethod,
            'path' => $path,
            'url_parts' => $urlParts,
            'client_ip' => $clientIp,
            'user_agent' => $userAgent,
            'referer' => $_SERVER['HTTP_REFERER'] ?? null
        ]);

        try {
            // Získáme base URL
            $baseUrl = $this->template->getData()['baseUrl'] ?? '';

            // Vytvoříme služby
            $articleService = new ArticleService($this->db);
            $categoryService = new CategoryService($this->db);

            // Vytvoříme controllery
            $homeController = new HomeController(
                $articleService,
                $categoryService,
                $this->template,
                $baseUrl,
                $this->authService,
                $this->menuService
            );

            $authController = new AuthController(
                $this->authService,
                $this->csrf,
                $baseUrl,
                $this->template
            );

            $adminController = new AdminController(
                $this->authService,
                $baseUrl,
                $this->adminLayout
            );

            // Zpracování homepage
            if (empty($urlParts[0]) || $urlParts[0] === 'home') {
                $this->logger->debug('Routing to homepage');
                $result = $homeController->showHomepage();
                $this->logRequestComplete($startTime, 'homepage', 200);
                return $result;
            }

            // Routing podle prvního segmentu URL
            $route = $urlParts[0];
            $this->logger->debug('Processing route', ['route' => $route]);

            switch ($route) {
                case 'article':
                    if (!empty($urlParts[1])) {
                        $this->logger->debug('Routing to article detail', ['slug' => $urlParts[1]]);
                        $result = $homeController->showArticleDetail($urlParts[1]);
                        $this->logRequestComplete($startTime, "article/{$urlParts[1]}", 200);
                        return $result;
                    }
                    break;

                case 'category':
                    if (!empty($urlParts[1])) {
                        $this->logger->debug('Routing to category', ['slug' => $urlParts[1]]);
                        $result = $homeController->showCategoryArticles($urlParts[1]);
                        $this->logRequestComplete($startTime, "category/{$urlParts[1]}", 200);
                        return $result;
                    }
                    break;

                case 'login':
                    if ($requestMethod === 'POST') {
                        $this->logger->debug('Processing login POST request');
                        $authController->processLogin();
                        $this->logRequestComplete($startTime, 'login POST', 302);
                        return '';
                    }

                    $this->logger->debug('Showing login form');
                    $result = $authController->showLoginForm();
                    $this->logRequestComplete($startTime, 'login GET', 200);
                    return $result;

                case 'logout':
                    $this->logger->info('Processing logout request', [
                        'user' => $this->authService->getUsername(),
                        'client_ip' => $clientIp
                    ]);
                    $authController->logout();
                    $this->logRequestComplete($startTime, 'logout', 302);
                    return '';

                case 'admin':
                    $result = $this->handleAdmin($urlParts, $adminController, $articleService, $categoryService, $baseUrl);
                    $this->logRequestComplete($startTime, 'admin/' . ($urlParts[1] ?? 'dashboard'), 200);
                    return $result;

                default:
                    $this->logger->warning('Route not found', [
                        'path' => $path,
                        'route' => $route,
                        'client_ip' => $clientIp
                    ]);

                    $result = $this->handleNotFound();
                    $this->logRequestComplete($startTime, '404', 404);
                    return $result;
            }

            // Pokud jsme se sem dostali, URL nebyla rozpoznána
            $this->logger->warning('Route matched but not handled properly', [
                'path' => $path,
                'url_parts' => $urlParts
            ]);

            $result = $this->handleNotFound();
            $this->logRequestComplete($startTime, '404', 404);
            return $result;

        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            $this->logger->exception($e, "Router exception occurred");
            $this->logger->error('Request failed with exception', [
                'path' => $path,
                'method' => $requestMethod,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'execution_time_ms' => $executionTime,
                'client_ip' => $clientIp
            ]);

            return $this->template->render('pages/500.php', [
                'message' => 'Došlo k chybě na serveru: ' . $e->getMessage(),
                'baseUrl' => $this->template->getData()['baseUrl'] ?? '',
                'backLinkText' => 'Zpět na úvodní stránku'
            ]);
        }
    }

    /**
     * Zpracuje admin routy
     */
    private function handleAdmin(
        array $urlParts,
        AdminController $adminController,
        ArticleService $articleService,
        CategoryService $categoryService,
        string $baseUrl
    ): string {
        $user = $this->authService->getUser();
        $clientIp = $this->getClientIp();

        // Kontrola přihlášení
        if (!$this->authService->isLoggedIn()) {
            $this->logger->warning('Unauthorized admin access attempt', [
                'path' => implode('/', $urlParts),
                'client_ip' => $clientIp,
                'user_agent' => $this->getUserAgent()
            ]);

            header('Location: ' . $baseUrl . 'login');
            exit;
        }

        $subPage = $urlParts[1] ?? 'dashboard';
        $action = $urlParts[2] ?? '';
        $id = isset($urlParts[3]) ? (int)$urlParts[3] : null;

        $this->logger->info('Admin request', [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'sub_page' => $subPage,
            'action' => $action,
            'id' => $id,
            'client_ip' => $clientIp
        ]);

        switch ($subPage) {
            case 'articles':
                return $this->handleAdminArticles($action, $id, $articleService, $categoryService, $baseUrl);

            case 'categories':
                return $this->handleAdminCategories($action, $id, $categoryService, $baseUrl);

            case 'gallery':
                return $this->handleAdminGallery($action, $id, $baseUrl);

            case 'images':
                return $this->handleAdminImages($action, $id, $baseUrl);

            case 'dashboard':
            default:
                $this->logger->debug('Showing admin dashboard');
                return $adminController->showDashboard();
        }
    }

    /**
     * Zpracuje administrační požadavky pro články
     */
    private function handleAdminArticles(
        string $action,
        ?int $id,
        ArticleService $articleService,
        CategoryService $categoryService,
        string $baseUrl
    ): string {
        $controller = new ArticleController(
            $articleService,
            $this->authService,
            $this->csrf,
            $baseUrl,
            $this->adminLayout,
            $categoryService
        );

        $this->logger->debug('Admin articles action', [
            'action' => $action,
            'article_id' => $id,
            'user' => $this->authService->getUsername()
        ]);

        switch ($action) {
            case 'new':
                $this->logger->debug('Showing article create form');
                return $controller->showCreateForm();

            case 'create':
                $this->logger->info('Creating new article', [
                    'user' => $this->authService->getUsername()
                ]);
                $controller->createArticle();
                return '';

            case 'edit':
                if ($id) {
                    $this->logger->debug('Showing article edit form', ['article_id' => $id]);
                    return $controller->showEditForm($id);
                }
                break;

            case 'update':
                if ($id) {
                    $this->logger->info('Updating article', [
                        'article_id' => $id,
                        'user' => $this->authService->getUsername()
                    ]);
                    $controller->updateArticle($id);
                }
                return '';

            case 'delete':
                if ($id) {
                    $this->logger->warning('Soft deleting article', [
                        'article_id' => $id,
                        'user' => $this->authService->getUsername(),
                        'client_ip' => $this->getClientIp()
                    ]);
                    $controller->deleteArticle($id);
                }
                return '';

            case 'restore':
                if ($id) {
                    $this->logger->info('Restoring article', [
                        'article_id' => $id,
                        'user' => $this->authService->getUsername()
                    ]);
                    $controller->restoreArticle($id);
                }
                return '';

            case 'permanent-delete':
                if ($id) {
                    $this->logger->critical('Permanently deleting article', [
                        'article_id' => $id,
                        'user' => $this->authService->getUsername(),
                        'client_ip' => $this->getClientIp()
                    ]);
                    $controller->permanentDeleteArticle($id);
                }
                return '';

            default:
                $this->logger->debug('Showing articles list');
                return $controller->showArticles();
        }

        $this->logger->warning('Invalid article action', [
            'action' => $action,
            'id' => $id
        ]);

        return $this->handleNotFound();
    }

    /**
     * Zpracuje administrační požadavky pro kategorie
     */
    private function handleAdminCategories(
        string $action,
        ?int $id,
        CategoryService $categoryService,
        string $baseUrl
    ): string {
        $controller = new CategoryController(
            $categoryService,
            $this->authService,
            $this->csrf,
            $baseUrl,
            $this->adminLayout
        );

        $this->logger->debug('Admin categories action', [
            'action' => $action,
            'category_id' => $id,
            'user' => $this->authService->getUsername()
        ]);

        switch ($action) {
            case 'create':
                $this->logger->debug('Showing category create form');
                return $controller->create();

            case 'store':
                $this->logger->info('Creating new category', [
                    'user' => $this->authService->getUsername()
                ]);
                $controller->store();
                return '';

            case 'edit':
                if ($id) {
                    $this->logger->debug('Showing category edit form', ['category_id' => $id]);
                    return $controller->edit($id);
                }
                break;

            case 'update':
                if ($id) {
                    $this->logger->info('Updating category', [
                        'category_id' => $id,
                        'user' => $this->authService->getUsername()
                    ]);
                    $controller->update($id);
                }
                return '';

            case 'delete':
                if ($id) {
                    $this->logger->warning('Soft deleting category', [
                        'category_id' => $id,
                        'user' => $this->authService->getUsername(),
                        'client_ip' => $this->getClientIp()
                    ]);
                    $controller->delete($id);
                }
                return '';

            case 'restore':
                if ($id) {
                    $this->logger->info('Restoring category', [
                        'category_id' => $id,
                        'user' => $this->authService->getUsername()
                    ]);
                    $controller->restore($id);
                }
                return '';

            case 'permanent-delete':
                if ($id) {
                    $this->logger->critical('Permanently deleting category', [
                        'category_id' => $id,
                        'user' => $this->authService->getUsername(),
                        'client_ip' => $this->getClientIp()
                    ]);
                    $controller->permanentDelete($id);
                }
                return '';

            default:
                $this->logger->debug('Showing categories list');
                return $controller->index();
        }

        $this->logger->warning('Invalid category action', [
            'action' => $action,
            'id' => $id
        ]);

        return $this->handleNotFound();
    }

    /**
     * Zpracuje administrační požadavky pro Galerii
     */
    private function handleAdminGallery(
        string $action,
        ?int $id,
        string $baseUrl
    ): string {
        $galleryService = new GalleryService($this->db);
        $controller = new GalleryController(
            $galleryService,
            $this->authService,
            $this->csrf,
            $baseUrl,
            $this->adminLayout
        );

        $this->logger->debug('Admin gallery action', [
            'action' => $action,
            'gallery_id' => $id,
            'user' => $this->authService->getUsername()
        ]);

        switch ($action) {
            case 'create':
                $this->logger->debug('Showing gallery create form');
                return $controller->create();

            case 'store':
                $this->logger->info('Creating new gallery', [
                    'user' => $this->authService->getUsername()
                ]);
                $controller->store();
                return '';

            case 'restore':
                if ($id) {
                    $this->logger->info('Restoring deleted gallery', [
                        'user' => $this->authService->getUsername(),
                        'gallery_id' => $id
                    ]);
                    $controller->restore($id);
                }
                return '';

            case 'edit':
                if ($id) {
                    $this->logger->debug('Showing gallery edit form', ['gallery_id' => $id]);
                    return $controller->edit($id);
                }
                break;

            case 'update':
                if ($id) {
                    $this->logger->info('Updating gallery', [
                        'gallery_id' => $id,
                        'user' => $this->authService->getUsername()
                    ]);
                    $controller->update($id);
                }
                return '';

            case 'view':
                if ($id) {
                    $this->logger->debug('Showing gallery view', ['gallery_id' => $id]);
                    return $controller->view($id);
                }
                break;

            case 'manage':
                $this->logger->debug('Showing gallery management');
                return $controller->index();

            case 'confirm-delete':
                if ($id) {
                    $this->logger->debug('Showing gallery delete confirmation', ['gallery_id' => $id]);
                    return $controller->confirmDelete($id);
                }
                break;

            case 'delete':
                if ($id) {
                    $this->logger->warning('Deleting gallery', [
                        'gallery_id' => $id,
                        'user' => $this->authService->getUsername(),
                        'client_ip' => $this->getClientIp()
                    ]);
                    $controller->delete($id);
                }
                return '';

			case 'confirm-permanent-delete':
			    if ($id) {
			        $this->logger->debug('Showing gallery permanent delete confirmation', ['gallery_id' => $id]);
			        return $controller->confirmPermanentDelete($id);
			    }
			    break;

			case 'permanent-delete':
			    if ($id) {
			        $this->logger->critical('Permanently deleting gallery', [
			            'gallery_id' => $id,
			            'user' => $this->authService->getUsername(),
			            'client_ip' => $this->getClientIp()
			        ]);
			        $controller->permanentDelete($id);
			    }
			    return '';
            default:
                $this->logger->debug('Showing gallery index');
                return $controller->index();
        }

        $this->logger->warning('Invalid gallery action', [
            'action' => $action,
            'id' => $id
        ]);

        return $this->handleNotFound();
    }

    /**
     * Zpracuje administrační požadavky pro Obrázky
     */
    private function handleAdminImages(
        string $action,
        ?int $id,
        string $baseUrl
    ): string {
        $imageService = new \App\Services\ImageService($this->db);
        $controller = new \App\Controllers\ImagesController(
            $imageService,
            $this->authService,
            $this->csrf,
            $baseUrl,
            $this->adminLayout,
            $this->db
        );

        $this->logger->debug('Admin images action', [
            'action' => $action,
            'image_id' => $id,
            'user' => $this->authService->getUsername()
        ]);

        switch ($action) {
            case 'manage':
                $this->logger->debug('Showing images management');
                return $controller->manage();

            case 'upload':
                $this->logger->debug('Showing upload form');
                return $controller->upload();

            case 'upload-image':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $this->logger->info('Uploading image', [
                        'user' => $this->authService->getUsername()
                    ]);
                    $controller->uploadImage();
                }
                return '';

			case 'edit':
			    if ($id) {
			        $this->logger->debug('Showing image edit form', ['image_id' => $id]);
			        return $controller->edit($id);
			    }
			    break;

			case 'update':
			    if ($id) {
			        $this->logger->info('Updating image', [
			            'image_id' => $id,
			            'user' => $this->authService->getUsername()
			        ]);
			        $controller->update($id);
			    }
			    return '';


            case 'delete':
                if ($id) {
                    $this->logger->info('Deleting image', [
                        'image_id' => $id,
                        'user' => $this->authService->getUsername(),
                        'client_ip' => $this->getClientIp()
                    ]);
                    $controller->deleteImage($id);
                }
                return '';

            case 'restore':
                if ($id) {
                    $this->logger->info('Restoring image from trash', [
                        'image_id' => $id,
                        'user' => $this->authService->getUsername()
                    ]);
                    $controller->restoreImage($id);
                }
                return '';

            case 'confirm-permanent-delete':
                if ($id) {
                    $this->logger->debug('Showing image permanent delete confirmation', ['image_id' => $id]);
                    return $controller->confirmPermanentDeleteImage($id);
                }
                break;

            case 'permanent-delete':
                if ($id) {
                    $this->logger->critical('Permanently deleting image', [
                        'image_id' => $id,
                        'user' => $this->authService->getUsername(),
                        'client_ip' => $this->getClientIp()
                    ]);
                    $controller->permanentDeleteImage($id);
                }
                return '';
			case 'featured-image-modal':
			    $this->logger->debug('Showing featured image modal');
			    return $controller->featuredImageModal();

			case 'select-featured-image':
			    if ($id) {
			        $this->logger->info('Selecting featured image', [
			            'gallery_id' => $id,
			            'user' => $this->authService->getUsername()
			        ]);
			        return $controller->selectFeaturedImage($id);
			    }
			    return '';

			case 'image-info':
			    if ($id) {
			        $this->logger->debug('Getting image info', ['image_id' => $id]);
			        return $controller->getImageInfo($id);
			    }
			    return '';
            default:
                $this->logger->debug('Showing images management (default)');
                return $controller->manage();
        }

        $this->logger->warning('Invalid image action', [
            'action' => $action,
            'id' => $id
        ]);

        return $this->handleNotFound();
    }

    /**
     * Zpracuje chybovou stránku 404
     */
    private function handleNotFound(): string
    {
        $siteName = Config::site('name');
        $baseUrl = $this->template->getData()['baseUrl'] ?? '';

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
     * Zaloguje dokončení requestu
     */
    private function logRequestComplete(float $startTime, string $route, int $statusCode): void
    {
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);

        $this->logger->info('Request completed', [
            'route' => $route,
            'status_code' => $statusCode,
            'execution_time_ms' => $executionTime,
            'memory_peak_mb' => round(memory_get_peak_usage() / 1024 / 1024, 2)
        ]);
    }

    /**
     * Získá IP adresu klienta
     */
    private function getClientIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Získá User Agent
     */
    private function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    }
}