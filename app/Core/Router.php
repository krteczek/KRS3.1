<?php
// app/Core/Router.php
declare(strict_types=1);

namespace App\Core;

use App\Controllers\ArticleController;
use App\Controllers\CategoryController;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Services\ArticleService;
use App\Services\CategoryService;
use App\Auth\LoginService;
use App\Database\DatabaseConnection;
use App\Security\CsrfProtection;

/**
 * Router pro zpracování HTTP požadavků
 *
 * @package App\Core
 * @author KRS3
 * @version 1.1
 */
class Router
{
    private string $baseUrl;
    private Template $template;
    private ArticleService $articleService;
    private CategoryService $categoryService;

    public function __construct(
        private LoginService $authService,
        private DatabaseConnection $db,
        private CsrfProtection $csrf,
        private AdminLayout $adminLayout
    ) {
        $this->baseUrl = Config::site('base_path', '');
        $this->template = new Template();

        // Inicializace služeb
        $this->articleService = new ArticleService($db);
        $this->categoryService = new CategoryService($db);
    }

    public function handleRequest(string $path, array $urlParts): string
    {
        $page = $urlParts[0] ?? 'home';

        switch ($page) {
            case '':
            case 'home':
                return $this->handleHomepage();

            case 'login':
                return $this->handleLogin();

            case 'logout':
                return $this->handleLogout();

            case 'admin':
                return $this->handleAdmin($urlParts);

            case 'clanek':
                return $this->handleArticleDetail($urlParts);

            default:
                return $this->handleNotFound();
        }
    }

    private function handleHomepage(): string
    {
        $articleService = new ArticleService($this->db);
        $homeController = new HomeController(
            $articleService,
            $this->template,
            $this->baseUrl,
            $this->authService
        );
        return $homeController->showHomepage();
    }

    private function handleLogin(): string
    {
        $authController = new AuthController(
            $this->authService,
            $this->csrf,
            $this->baseUrl,
            $this->template,
        );

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authController->processLogin();
            return '';
        }

        return $authController->showLoginForm();
    }

    private function handleLogout(): string
    {
        $authController = new AuthController(
            $this->authService,
            $this->csrf,
            $this->baseUrl,
            $this->template
        );
        $authController->logout();
        return '';
    }

    private function handleAdmin(array $urlParts): string
    {
        $this->requireLoggedIn();

        $subPage = $urlParts[1] ?? 'dashboard';
        $action = $urlParts[2] ?? '';
        $id = $urlParts[3] ?? null;

        switch ($subPage) {
            case 'articles':
                $this->handleAdminArticles($action, $id);
                return '';
            case 'categories':
                $this->handleAdminCategories($action, $id);
                return '';
            case 'dashboard':
            default:
                $adminController = new AdminController($this->authService, $this->baseUrl, $this->adminLayout);
                return $adminController->dashboard();
        }
    }

    /**
     * Zpracuje administrační požadavky pro články
     *
     * @param string $action Akce
     * @param mixed $id ID článku
     * @return void
     */
    private function handleAdminArticles(string $action, $id = null): void
    {
        $controller = new ArticleController(
            $this->articleService,
            $this->authService,
            $this->csrf,
            $this->baseUrl,
            $this->adminLayout,
            $this->categoryService
        );

        switch ($action) {
            case 'new':
                echo $controller->showCreateForm();
                break;
            case 'create':
                $controller->createArticle();
                break;
            case 'edit':
                if ($id) {
                    echo $controller->showEditForm((int)$id);
                }
                break;
            case 'update':
                if ($id) {
                    $controller->updateArticle((int)$id);
                }
                break;
            case 'delete':
                if ($id) {
                    $controller->deleteArticle((int)$id);
                }
                break;
            case 'restore':
                if ($id) {
                    $controller->restoreArticle((int)$id);
                }
                break;
            case 'permanent-delete':
                if ($id) {
                    $controller->permanentDeleteArticle((int)$id);
                }
                break;
            default:
                echo $controller->showArticles();
                break;
        }
    }

    /**
     * Zpracuje administrační požadavky pro kategorie
     *
     * @param string $action Akce
     * @param mixed $id ID kategorie
     * @return void
     */
    private function handleAdminCategories(string $action, $id = null): void
    {
        $controller = new CategoryController(
            $this->categoryService,
            $this->authService,
            $this->csrf,
            $this->baseUrl,
            $this->adminLayout
        );

        switch ($action) {
            case 'create':
                echo $controller->create();
                break;
            case 'store':
                $controller->store();
                break;
            case 'edit':
                if ($id) {
                    echo $controller->edit((int)$id);
                }
                break;
            case 'update':
                if ($id) {
                    $controller->update((int)$id);
                }
                break;
            case 'delete':
                if ($id) {
                    $controller->delete((int)$id);
                }
                break;
	        case 'restore': // ← přidáno
	            if ($id) {
	                $controller->restore((int)$id);
	            }
	            break;
			case 'permanent-delete': // ← přidáno
	            if ($id) {
	                $controller->permanentDelete((int)$id);
	            }
	            break;
			default:
                echo $controller->index();
                break;
        }
    }

    private function handleArticleDetail(array $urlParts): string
    {
        $slug = $urlParts[1] ?? '';
        if ($slug) {
            $articleService = new ArticleService($this->db);
            $homeController = new HomeController(
                $articleService,
                $this->template,
                $this->baseUrl,
                $this->authService
            );
            return $homeController->showArticleDetail($slug);
        }

        header("Location: {$this->baseUrl}/");
        return '';
    }

    private function handleNotFound(): string
    {
        http_response_code(404);
        $siteName = \App\Core\Config::site('name');

        return $this->template->render('layouts/frontend.php', [
            'title' => \App\Core\Config::text('pages.404', ['site_name' => \App\Core\Config::site('name')]),
            'content' => $this->template->render('pages/404.php', [
                'baseUrl' => $this->baseUrl,
                'message' => \App\Core\Config::text('messages.404'),
                'backLinkText' => \App\Core\Config::text('ui.back_to_home'),
                'siteName' => $siteName,
                'user' => ['isLoggedIn' => false]
            ]),
            'baseUrl' => $this->baseUrl,
            'siteName' => $siteName,
            'user' => ['isLoggedIn' => false]
        ]);
    }

    private function requireLoggedIn(): void
    {
        if (!$this->authService->isLoggedIn()) {
            header('Location: ' . $this->baseUrl . '/login');
            exit;
        }
    }
}