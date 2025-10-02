<?php
// app/Controllers/HomeController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\ArticleService;
use App\Core\Template;
use App\Core\Config;
use App\Auth\LoginService;

/**
 * Controller pro hlavní stránku a detail článku
 *
 * Zpracovává zobrazení úvodní stránky s přehledem článků
 * a detailních stránek jednotlivých článků.
 *
 * @package App\Controllers
 * @author KRS3
 * @version 3.0
 */
class HomeController extends BaseController
{
    /**
     * @param ArticleService $articleService Služba pro práci s články
     * @param Template $template Šablonovací systém
     * @param string $baseUrl Základní URL aplikace
     * @param LoginService $authService Služba pro autentizaci uživatelů
     */
    public function __construct(
        private ArticleService $articleService,
        Template $template,
        string $baseUrl,
        LoginService $authService
    ) {
        parent::__construct($template, $baseUrl, $authService);
    }

    /**
     * Zobrazí úvodní stránku s přehledem publikovaných článků
     *
     * Načte všechny publikované články a zobrazí je
     * v přehledové stránce s úvodním textem.
     *
     * @return string HTML obsah úvodní stránky
     *
     * @uses ArticleService::getPublishedArticles()
     * @uses BaseController::renderPage()
     */
    public function showHomepage(): string
    {
        $articles = $this->articleService->getPublishedArticles();

        return $this->renderPage('pages/home.php', [
            'articles' => $articles,
            'welcomeMessage' => Config::text('messages.welcome'),
            'noArticlesMessage' => Config::text('messages.no_articles'),
            'readMoreText' => Config::text('ui.read_more')
        ], 'home');
    }

    /**
     * Zobrazí detailní stránku konkrétního článku podle slug
     *
     * Na základě URL slug najde a zobrazí detail článku.
     * Pokud článek neexistuje, zobrazí chybovou stránku.
     *
     * @param string $slug URL identifikátor článku
     * @return string HTML obsah detailu článku nebo chybové stránky
     *
     * @uses ArticleService::getArticleBySlug()
     * @uses BaseController::renderPage()
     */
    public function showArticleDetail(string $slug): string
    {
        $article = $this->articleService->getArticleBySlug($slug);

        if (!$article) {
            return $this->renderPage('partials/error.php', [
                'message' => Config::text('messages.article_not_found'),
                'backLinkText' => Config::text('ui.back_to_home')
            ], 'article_not_found');
        }

        return $this->renderPage('pages/article-detail.php', [
            'article' => $article,
            'backLinkText' => Config::text('ui.back_to_home')
        ], 'article_detail', [
            'title' => $article['title'] // Toto se použije v {title} v konfiguraci
        ]);
    }
}