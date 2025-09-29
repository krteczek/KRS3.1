<?php
// app/Core/Router.php
declare(strict_types=1);

namespace App\Core;

use App\Auth\LoginService;
use App\Database\DatabaseConnection;
use App\Security\CsrfProtection;
use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Controllers\ArticleController;
use App\Controllers\HomeController;
use App\Services\ArticleService;



class Router
{
    private string $baseUrl;
    private Template $template;

    public function __construct(
        private LoginService $authService,
        private DatabaseConnection $db,
        private CsrfProtection $csrf,
        private AdminLayout $adminLayout
    ) {
        $this->baseUrl = Config::site('base_path', '');
        $this->template = new Template();
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
                return $this->handleAdminArticles($action, $id);

            case 'dashboard':
            default:
                $adminController = new AdminController($this->authService, $this->baseUrl, $this->adminLayout);
                return $adminController->dashboard();
        }
    }

    private function handleAdminArticles(string $action, ?string $id): string
    {
        $articleService = new ArticleService($this->db);
        $articleController = new ArticleController(
            $articleService,
            $this->authService,
            $this->csrf,
            $this->baseUrl,
            $this->adminLayout
        );

        switch ($action) {
            case 'new':
                return $articleController->showCreateForm();

            case 'create':
                $articleController->createArticle();
                return '';

            case 'edit':
                if ($id) {
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $articleController->updateArticle((int)$id);
                        return '';
                    }
                    return $articleController->showEditForm((int)$id);
                }
                break;

            case 'delete':
                if ($id) {
                    $articleController->deleteArticle((int)$id);
                    return '';
                }
                break;

            case 'restore':
                if ($id) {
                    $articleController->restoreArticle((int)$id);
                    return '';
                }
                break;

            case 'permanent-delete':
                if ($id) {
                    $articleController->permanentDeleteArticle((int)$id);
                    return '';
                }
                break;

            default:
                return $articleController->showArticles();
        }

        header("Location: {$this->baseUrl}/admin/articles");
        return '';
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