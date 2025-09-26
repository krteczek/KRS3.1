<?php
// app/Controllers/HomeController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\ArticleService;
use App\Core\Template;
use App\Core\Config;

class HomeController extends BaseController
{
    public function __construct(
        private ArticleService $articleService,
        Template $template,
        string $baseUrl
    ) {
        parent::__construct($template, $baseUrl);
    }

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