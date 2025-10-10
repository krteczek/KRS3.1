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
 * @package App\Controllers
 * @author KRS3
 * @version 3.1
 */
class HomeController extends BaseController
{
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
     * @return string HTML obsah úvodní stránky
     */
    public function showHomepage(): string
    {
        // Získáme 10 nejnovějších článků s kategoriemi
        $articles = $this->articleService->getLatestArticlesWithCategories(10);

        return $this->renderPage('pages/home.php', [
            'articles' => $articles,
            'welcomeMessage' => Config::text('messages.welcome'),
            'noArticlesMessage' => Config::text('messages.no_articles'),
            'readMoreText' => Config::text('ui.read_more')
        ], 'home');
    }

    /**
     * Zobrazí články v konkrétní kategorii
     *
     * @param string $categorySlug Slug kategorie
     * @return string HTML obsah stránky kategorie
     */
    public function showCategoryArticles(string $categorySlug): string
    {
        // Zde bychom potřebovali CategoryService pro získání kategorií
        // Prozatím vrátíme základní informace
        return $this->renderPage('pages/category.php', [
            'categorySlug' => $categorySlug,
            'message' => "Články v kategorii: " . htmlspecialchars($categorySlug)
        ], 'category');
    }

    /**
     * Zobrazí detailní stránku konkrétního článku podle slug
     *
     * @param string $slug URL identifikátor článku
     * @return string HTML obsah detailu článku nebo chybové stránky
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

        // Získáme kategorie pro tento článek
        $articleWithCategories = $this->articleService->getLatestArticlesWithCategories(1);
        $currentArticle = !empty($articleWithCategories) ? $articleWithCategories[0] : $article;

        return $this->renderPage('pages/article-detail.php', [
            'article' => $currentArticle,
            'backLinkText' => Config::text('ui.back_to_home')
        ], 'article_detail', [
            'title' => $article['title']
        ]);
    }
}