<?php
// app/Controllers/BaseController.php - VYLEPŠENÁ VERZE
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Template;
use App\Core\Config;
use App\Auth\LoginService;
use App\Services\MenuService;

/**
 * Abstraktní základní controller pro společné funkce všech controllerů
 *
 * @package App\Controllers
 * @author KRS3
 * @version 3.3
 */
abstract class BaseController
{
    public function __construct(
        protected Template $template,
        protected string $baseUrl,
        protected LoginService $authService,
        protected MenuService $menuService
    ) {}

    /**
     * Vykreslí kompletní HTML stránku s layoutem
     *
     * @param string $contentView Cesta k šabloně obsahu
     * @param array $contentData Data specifická pro obsahovou část
     * @param string $pageKey Klíč pro lokalizaci názvu stránky
     * @param array $titleParams Parametry pro dynamický title stránky
     * @return string Kompletní vyrenderované HTML
     */
protected function renderPage(string $contentView, array $contentData = [], string $pageKey = 'home', array $titleParams = [], string $layout = 'layouts/frontend.php'): string
    {
        $siteName = Config::site('name');
        $title = Config::text('pages.' . $pageKey, array_merge([
            'site_name' => $siteName
        ], $titleParams), $siteName);

        $userData = $this->authService->getUser();

        // Připravíme data pro layout
        $layoutData = [
            'title' => $title,
            'content' => $this->template->render($contentView, array_merge($contentData, [
                'baseUrl' => $this->baseUrl,
                'siteName' => $siteName
            ])),
            'baseUrl' => $this->baseUrl,
            'siteName' => $siteName,
            'user' => [
                'isLoggedIn' => $this->authService->isLoggedIn(),
                'username' => $userData['username'] ?? null,
                'id' => $userData['id'] ?? null,
                'role' => $userData['role'] ?? null
            ],
            'menuService' => $this->menuService
        ];

        // Přidáme další data pro layout (např. popularCategories pro sidebar)
        $layoutData = array_merge($layoutData, $contentData);

        return $this->template->render($layout, $layoutData);
    }

    /**
     * Vykreslí stránku se sidebar layoutem
     */
    protected function renderPageWithSidebar(string $contentView, array $contentData = [], string $pageKey = 'home', array $titleParams = []): string
    {
        return $this->renderPage($contentView, $contentData, $pageKey, $titleParams, 'layouts/with-sidebar.php');
    }


    /**
     * Přesměruje na jinou URL
     *
     * @param string $url Cílová URL
     * @return void
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . $this->baseUrl . $url);
        exit;
    }

    /**
     * Vrátí JSON odpověď
     *
     * @param array $data Data pro odpověď
     * @param int $statusCode HTTP status code
     * @return string JSON řetězec
     */
    protected function jsonResponse(array $data, int $statusCode = 200): string
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        return json_encode($data);
    }
}