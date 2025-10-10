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
use App\Services\MenuService;
use App\Auth\LoginService;
use App\Database\DatabaseConnection;
use App\Security\CsrfProtection;
use App\Logger\Logger;

/**
 * Router pro zpracování HTTP požadavků
 *
 * @package App\Core
 * @author KRS3
 * @version 1.2
 */
class Router
{
	private $logger = null;

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
        try {
            // ✅ ZÍSKÁME BASE URL JEDNOU
            $baseUrl = $this->template->getData()['baseUrl'] ?? '';

            // ✅ VYTVOŘÍME SLUŽBY JEDNOU
            $articleService = new ArticleService($this->db);
            $categoryService = new CategoryService($this->db);


            // ✅ SPRÁVNÉ PARAMETRY PRO VŠECHNY CONTROLLERY
            $homeController = new HomeController(
				$articleService,
                $categoryService, // Přidáno
                $this->template,
                $baseUrl,
                $this->authService,
                $this->menuService
			);

            $authController = new AuthController(
                $this->authService,   // LoginService
                $this->csrf,          // CsrfProtection
                $baseUrl,             // string $baseUrl
                $this->template       // Template
            );

            $adminController = new AdminController(
                $this->authService,   // LoginService
                $baseUrl,             // string $baseUrl
                $this->adminLayout    // AdminLayout
            );

			$AuthController = new AuthController(
				$this->authService,
				$this->csrf,
				$baseUrl,
				$this->template
			);

            // ✅ ZPRACOVÁNÍ HOMEPAGE
            if (empty($urlParts[0]) || $urlParts[0] === 'home') {
                return $homeController->showHomepage();
            }

            switch ($urlParts[0]) {
                case 'article':
                    if (!empty($urlParts[1])) {
                        return $homeController->showArticleDetail($urlParts[1]);
                    }
                    break;

                case 'category':
                    if (!empty($urlParts[1])) {
                        return $homeController->showCategoryArticles($urlParts[1]);
                    }
                    break;

                case 'login':
				    // ✅ PŘIDÁNO ZPRACOVÁNÍ POST PRO LOGIN
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $authController->processLogin();
                        return '';
                    }
                    return $AuthController->showLoginForm();

                case 'logout':
                    return $AuthController->logout();

                case 'admin':
                    return $this->handleAdmin($urlParts, $adminController, $articleService, $categoryService, $baseUrl);

                default:
                    return $this->template->render('pages/404.php', [
                        'message' => 'Stránka nebyla nalezena'
                    ]);
            }

            // Pokud jsme se sem dostali, URL nebyla rozpoznána
            return $this->template->render('pages/404.php', [
                'message' => 'Stránka nebyla nalezena'
            ]);

        } catch (\Exception $e) {
	        if (isset($this->logger)) {
	            $this->logger->exception($e, "Router error: " . $e->getMessage());
	        } else {
	            // Fallback: zápis do error_log pokud logger není dostupný
	            error_log("Router error: " . $e->getMessage());

	        }
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
    ): string
	{
        $this->requireLoggedIn();

        $subPage = $urlParts[1] ?? 'dashboard';
        $action = $urlParts[2] ?? '';
        $id = $urlParts[3] ?? null;

        switch ($subPage) {
            case 'articles':
                return $this->handleAdminArticles($action, $id, $articleService, $categoryService, $baseUrl);

            case 'categories':
                return $this->handleAdminCategories($action, $id, $categoryService, $baseUrl);

            case 'dashboard':
            default:
                return $adminController->showDashboard();
        }
    }

    /**
     * Zpracuje administrační požadavky pro články
     */
    private function handleAdminArticles(
        string $action,
        $id,
        ArticleService $articleService,
        CategoryService $categoryService,
        string $baseUrl
    ): string
	{
        $controller = new ArticleController(
            $articleService,
            $this->authService,
            $this->csrf,
            $baseUrl,
            $this->adminLayout,
            $categoryService
        );

        switch ($action) {
            case 'new':
                return $controller->showCreateForm();
            case 'create':
                $controller->createArticle();
                return '';
            case 'edit':
                if ($id) {
                    return $controller->showEditForm((int)$id);
                }
                break;
            case 'update':
                if ($id) {
                    $controller->updateArticle((int)$id);
                }
                return '';
            case 'delete':
                if ($id) {
                    $controller->deleteArticle((int)$id);
                }
                return '';
            case 'restore':
                if ($id) {
                    $controller->restoreArticle((int)$id);
                }
                return '';
            case 'permanent-delete':
                if ($id) {
                    $controller->permanentDeleteArticle((int)$id);
                }
                return '';
            default:
                return $controller->showArticles();
        }

        return $this->template->render('pages/404.php', [
            'message' => 'Akce nebyla nalezena'
        ]);
    }

    /**
     * Zpracuje administrační požadavky pro kategorie
     */
    private function handleAdminCategories(
        string $action,
        $id,
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

        switch ($action) {
            case 'create':
                return $controller->create();
            case 'store':
                $controller->store();
                return '';
            case 'edit':
                if ($id) {
                    return $controller->edit((int)$id);
                }
                break;
            case 'update':
                if ($id) {
                    $controller->update((int)$id);
                }
                return '';
            case 'delete':
                if ($id) {
                    $controller->delete((int)$id);
                }
                return '';
            case 'restore':
                if ($id) {
                    $controller->restore((int)$id);
                }
                return '';
            case 'permanent-delete':
                if ($id) {
                    $controller->permanentDelete((int)$id);
                }
                return '';
            default:
                return $controller->index();
        }

        return $this->template->render('pages/404.php', [
            'message' => 'Akce nebyla nalezena'
        ]);
    }

    /**
     * Vyžaduje přihlášení uživatele
     */
    private function requireLoggedIn(): void
    {
        if (!$this->authService->isLoggedIn()) {
            $baseUrl = $this->template->getData()['baseUrl'] ?? '';
            header('Location: ' . $baseUrl . '/login');
            exit;
        }
    }

    /**
     * Zpracuje chybovou stránku 404
     */
    private function handleNotFound(): string
    {
        $siteName = \App\Core\Config::site('name');
        $baseUrl = $this->template->getData()['baseUrl'] ?? '';

        return $this->template->render('layouts/frontend.php', [
            'title' => \App\Core\Config::text('pages.404', ['site_name' => $siteName]),
            'content' => $this->template->render('pages/404.php', [
                'baseUrl' => $baseUrl,
                'message' => \App\Core\Config::text('messages.404'),
                'backLinkText' => \App\Core\Config::text('ui.back_to_home'),
                'siteName' => $siteName,
                'user' => ['isLoggedIn' => false]
            ]),
            'baseUrl' => $baseUrl,
            'siteName' => $siteName,
            'user' => ['isLoggedIn' => false]
        ]);
    }
}