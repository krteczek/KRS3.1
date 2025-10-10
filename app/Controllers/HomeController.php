<?php
// app/Controllers/HomeController.php - OPRAVENÁ VERZE
declare(strict_types=1);

namespace App\Controllers;

use App\Services\ArticleService;
use App\Services\CategoryService;
use App\Core\Template;
use App\Core\Config;
use App\Auth\LoginService;
use App\Services\MenuService;

class HomeController extends BaseController
{
    public function __construct(
        private ArticleService $articleService,
        private CategoryService $categoryService,
        Template $template,
        string $baseUrl,
        LoginService $authService,
        MenuService $menuService
    ) {
        parent::__construct($template, $baseUrl, $authService, $menuService);
    }

    /**
     * Zobrazí úvodní stránku s přehledem článků a sidebar menu
     */
    public function showHomepage(): string
    {
        $articles = $this->articleService->getLatestArticlesWithCategories(10);
        $popularCategories = $this->categoryService->getPopularCategories(5);

        // Použijeme sidebar layout pro homepage
        return $this->renderPageWithSidebar('pages/home.php', [
            'articles' => $articles,
            'popularCategories' => $popularCategories,
            'welcomeMessage' => Config::text('messages.welcome', [], 'Vítejte na našem webu'),
            'noArticlesMessage' => Config::text('messages.no_articles', [], 'Zatím zde nejsou žádné články.'),
            'readMoreText' => Config::text('ui.read_more', [], 'Číst více'),
            'categoriesTitle' => Config::text('ui.categories', [], 'Kategorie')
        ], 'home');
    }

    /**
     * Zobrazí články v konkrétní kategorii
     */
    public function showCategoryArticles(string $categorySlug): string
    {
        $category = $this->categoryService->getCategoryBySlug($categorySlug);

        if (!$category) {
            return $this->renderPage('partials/error.php', [
                'message' => Config::text('messages.category_not_found', [], 'Kategorie nebyla nalezena.'),
                'backLinkText' => Config::text('ui.back_to_home', [], 'Zpět na úvodní stránku')
            ], 'category_not_found');
        }

        $articles = $this->articleService->getArticlesByCategory($category['id']);
        $popularCategories = $this->categoryService->getPopularCategories(5);
        $breadcrumb = $this->menuService->generateCategoryBreadcrumb($category['id']);

        // Pro kategorii také použijeme sidebar layout
        return $this->renderPageWithSidebar('pages/category.php', [
            'category' => $category,
            'articles' => $articles,
            'popularCategories' => $popularCategories,
            'breadcrumb' => $breadcrumb,
            'noArticlesMessage' => Config::text('messages.no_articles_in_category', ['category' => $category['name']], "V kategorii {$category['name']} zatím nejsou žádné články.")
        ], 'category', [
            'category' => $category['name']
        ]);
    }

    /**
     * Zobrazí detail článku
     */
    public function showArticleDetail(string $slug): string
    {
        $article = $this->articleService->getArticleBySlug($slug);

        if (!$article) {
            return $this->renderPage('partials/error.php', [
                'message' => Config::text('messages.article_not_found', [], 'Článek nebyl nalezen.'),
                'backLinkText' => Config::text('ui.back_to_home', [], 'Zpět na úvodní stránku')
            ], 'article_not_found');
        }

        $popularCategories = $this->categoryService->getPopularCategories(5);

        // Pro detail článku také použijeme sidebar layout
        return $this->renderPageWithSidebar('pages/article-detail.php', [
            'article' => $article,
            'popularCategories' => $popularCategories,
            'backLinkText' => Config::text('ui.back_to_home', [], 'Zpět na úvodní stránku')
        ], 'article_detail', [
            'title' => $article['title']
        ]);
    }
}