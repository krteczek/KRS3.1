<?php
// app/Controllers/BaseController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Template;
use App\Core\Config;
use App\Auth\LoginService;

/**
 * Abstraktní základní controller pro společné funkce všech controllerů
 *
 * Poskytuje základní funkcionalitu pro renderování stránek,
 * správu šablon a přístup k autentizační službě.
 * Všechny specifické controllery by měly dědit z této třídy.
 *
 * @package App\Controllers
 * @author KRS3
 * @version 3.0
 */
abstract class BaseController
{
    /**
     * @param Template $template Šablonovací systém
     * @param string $baseUrl Základní URL aplikace
     * @param LoginService $authService Služba pro autentizaci uživatelů
     */
    public function __construct(
        protected Template $template,
        protected string $baseUrl,
        protected LoginService $authService
    ) {}

    /**
     * Vykreslí kompletní HTML stránku s layoutem
     *
     * Zkombinuje obsahovou část s layoutem, přidá metadata,
     * title a uživatelská data. Používá se pro frontend stránky.
     *
     * @param string $contentView Cesta k šabloně obsahu (např. 'home.php')
     * @param array $contentData Data specifická pro obsahovou část
     * @param string $pageKey Klíč pro lokalizaci názvu stránky (např. 'home', 'login')
     * @param array $titleParams Parametry pro dynamický title stránky
     * @return string Kompletní vyrenderované HTML
     *
     * @example
     * $this->renderPage('home.php', ['articles' => $articles], 'home');
     * $this->renderPage('article_detail.php', $articleData, 'article_detail', ['title' => $articleTitle]);
     */
    protected function renderPage(string $contentView, array $contentData = [], string $pageKey = 'home', array $titleParams = []): string
    {
        // Získá název webu z konfigurace
        $siteName = Config::site('name');

        // Sestaví title stránky s podporou lokalizace
        $title = Config::text('pages.' . $pageKey, array_merge([
            'site_name' => $siteName
        ], $titleParams), $siteName);

        // Získá data o přihlášeném uživateli
        $userData = $this->authService->getUser();

        // Připraví data pro layout
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
                'username' => $userData['username'] ?? null, // ← z getUser()
                'id' => $userData['id'] ?? null,
                'role' => $userData['role'] ?? null
            ]
        ];

        // Vykreslí kompletní stránku s layoutem
        return $this->template->render('layouts/frontend.php', $layoutData);
    }
}