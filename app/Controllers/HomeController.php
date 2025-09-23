<?php
// app/Controllers/HomeController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\ArticleService;
use App\Core\AdminLayout;

class HomeController
{
    public function __construct(
        private ArticleService $articleService,
        private string $baseUrl
    ) {}

    public function showHomepage(): string
    {
        $articles = $this->articleService->getPublishedArticles();

        $html = <<<HTML
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redakční systém - Úvod</title>
    <link rel="stylesheet" href="{$this->baseUrl}/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <h1>Redakční systém</h1>
            <nav class="main-nav">
                <a href="{$this->baseUrl}/">Úvod</a>
                <a href="{$this->baseUrl}/login">Administrace</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <section class="hero">
            <h2>Vítejte na našem webu</h2>
            <p>Nejnovější články a zprávy</p>
        </section>

        <section class="articles-grid">
HTML;

        if (empty($articles)) {
            $html .= <<<HTML
            <div class="empty-state">
                <h3>Zatím žádné články</h3>
                <p>Zkuste to prosím později.</p>
            </div>
HTML;
        } else {
            foreach ($articles as $article) {
                $html .= $this->renderArticleCard($article);
            }
        }

        $html .= <<<HTML
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Redakční systém. Všechna práva vyhrazena.</p>
        </div>
    </footer>
</body>
</html>
HTML;

        return $html;
    }

    private function renderArticleCard(array $article): string
    {
        $excerpt = $article['excerpt'] ?? mb_substr(strip_tags($article['content']), 0, 150) . '...';
        $publishedDate = date('j. n. Y', strtotime($article['published_at']));
        $articleUrl = $this->baseUrl . '/clanek/' . $article['slug'];

        return <<<HTML
<article class="article-card">
    <div class="article-content">
        <h3><a href="{$articleUrl}">{$this->escape($article['title'])}</a></h3>
        <div class="article-meta">
            <span class="author">{$this->escape($article['author_name'])}</span>
            <span class="date">{$publishedDate}</span>
        </div>
        <p class="excerpt">{$this->escape($excerpt)}</p>
        <a href="{$articleUrl}" class="read-more">Číst více →</a>
    </div>
</article>
HTML;
    }

    private function escape(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}