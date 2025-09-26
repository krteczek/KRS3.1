<?php
//app/Controllers/ArticleController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\ArticleService;
use App\Auth\LoginService;
use App\Security\CsrfProtection;
use App\Core\AdminLayout;

class ArticleController
{
    public function __construct(
        private ArticleService $articleService,
        private LoginService $authService,
        private CsrfProtection $csrf,
        private string $baseUrl,
        private AdminLayout $adminLayout
    ) {}

    public function showCreateForm(): string
    {
        $this->requireAdmin();
        $csrfField = $this->csrf->getTokenField();

        $content = <<<HTML
<h1>Nový článek</h1>
<form method="POST" action="{$this->baseUrl}/admin/articles/create" class="article-form">
    <div class="form-group">
        <label for="title">Název článku:</label>
        <input type="text" id="title" name="title" required class="form-control">
    </div>

    <div class="form-group">
        <label for="excerpt">Krátký popis:</label>
        <textarea id="excerpt" name="excerpt" class="form-control"></textarea>
    </div>

    <div class="form-group">
        <label for="content">Obsah článku:</label>
        <textarea id="content" name="content" required class="form-control"></textarea>
    </div>

    <div class="form-group">
        <label for="status">Stav:</label>
        <select id="status" name="status" class="form-control">
            <option value="draft">Koncept</option>
            <option value="published">Publikováno</option>
        </select>
    </div>

    <div class="form-actions">
        {$csrfField}
        <button type="submit" class="btn btn-primary">Vytvořit článek</button>
        <a href="{$this->baseUrl}/admin/articles" class="btn btn-secondary">Zrušit</a>
    </div>
</form>
HTML;

        return $this->adminLayout->wrap($content, 'Nový článek');
    }

    public function createArticle(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}/admin/articles/new");
            exit;
        }

        $user = $this->authService->getUser();

        try {
            $articleId = $this->articleService->createArticle([
                'title' => $_POST['title'],
                'content' => $_POST['content'],
                'excerpt' => $_POST['excerpt'] ?? '',
                'author_id' => $user['id'],
                'status' => $_POST['status'] ?? 'draft'
            ]);

            header("Location: {$this->baseUrl}/admin/articles?created=1");
            exit;

        } catch (\Exception $e) {
            header("Location: {$this->baseUrl}/admin/articles/new?error=1");
            exit;
        }
    }

    public function showArticles(): string
    {
        $this->requireAdmin();

        // Zjisti, zda se má zobrazit koš
        $isTrashView = isset($_GET['show']) && $_GET['show'] === 'trash';

        if ($isTrashView) {
            $articles = $this->articleService->getDeletedArticles();
        } else {
            $articles = $this->articleService->getAllArticles();
        }

        // Připrav zprávy
        $message = '';
        if (isset($_GET['restored'])) {
            $message = '<div class="alert alert-success">Článek byl obnoven!</div>';
        } elseif (isset($_GET['deleted'])) {
            $message = '<div class="alert alert-success">Článek byl smazán!</div>';
        } elseif (isset($_GET['error'])) {
            $message = '<div class="alert alert-error">Došlo k chybě!</div>';
        }

        // Připrav proměnné pro tabs
        $activeClass = 'active';
        $mainTabClass = !$isTrashView ? $activeClass : '';
        $trashTabClass = $isTrashView ? $activeClass : '';

        // Připrav proměnné pro obsah
        $emptyStateTitle = $isTrashView ? 'Koš je prázdný' : 'Žádné články';
        $emptyStateText = $isTrashView ? 'Nejsou žádné smazané články.' : 'Zatím nemáte žádné články. Vytvořte první!';
        $emptyStateButton = $isTrashView ? '' : "<a href='{$this->baseUrl}/admin/articles/new' class='btn btn-primary'>Vytvořit první článek</a>";
        $tableHeader = $isTrashView ? 'Smazáno' : 'Vytvořeno';

        $html = <<<HTML
<div class="page-header">
    <h1>Správa článků</h1>
    <a href="{$this->baseUrl}/admin/articles/new" class="btn btn-primary">
        ＋ Nový článek
    </a>
</div>

{$message}

<!-- TABS -->
<div class="tabs">
    <a href="{$this->baseUrl}/admin/articles" class="tab {$mainTabClass}">
        Aktivní články
    </a>
    <a href="{$this->baseUrl}/admin/articles?show=trash" class="tab {$trashTabClass}">
        Koš
    </a>
</div>

<div class="articles-container">
HTML;

        if (empty($articles)) {
            $html .= <<<HTML
<div class="empty-state">
    <h3>{$emptyStateTitle}</h3>
    <p>{$emptyStateText}</p>
    {$emptyStateButton}
</div>
HTML;
        } else {
            $html .= <<<HTML
<div class="articles-table-container">
    <table class="articles-table">
        <thead>
            <tr>
                <th>Název</th>
                <th>Stav</th>
                <th>Autor</th>
                <th>{$tableHeader}</th>
                <th>Akce</th>
            </tr>
        </thead>
        <tbody>
HTML;

            foreach ($articles as $article) {
                $dateColumn = $isTrashView
                    ? date('j. n. Y H:i', strtotime($article['deleted_at']))
                    : date('j. n. Y H:i', strtotime($article['created_at']));

                $statusBadge = $this->getStatusBadge($article['status']);

                if ($isTrashView) {
                    $actions = <<<HTML
<div class="action-buttons">
    <a href="{$this->baseUrl}/admin/articles/restore/{$article['id']}"
       class="btn btn-sm btn-success" title="Obnovit">
        ↶
    </a>
    <a href="{$this->baseUrl}/admin/articles/permanent-delete/{$article['id']}"
       class="btn btn-sm btn-danger"
       onclick="return confirm('Opravdu NAVŽDY smazat článek „{$this->escapeJs($article['title'])}‟? Tato akce je nevratná!')"
       title="Smazat navždy">
        🗑️
    </a>
</div>
HTML;
                } else {
                    $actions = <<<HTML
<div class="action-buttons">
    <a href="{$this->baseUrl}/admin/articles/edit/{$article['id']}"
       class="btn btn-sm btn-primary" title="Editovat">
        ✏️
    </a>
    <a href="{$this->baseUrl}/admin/articles/delete/{$article['id']}"
       class="btn btn-sm btn-danger"
       onclick="return confirm('Opravdu smazat článek „{$this->escapeJs($article['title'])}‟?')"
       title="Smazat">
        🗑️
    </a>
</div>
HTML;
                }

                $html .= <<<HTML
<tr>
    <td>
        <div class="article-title">{$this->escape($article['title'])}</div>
        <div class="article-excerpt">{$this->escape(mb_substr($article['excerpt'] ?? '', 0, 100))}...</div>
    </td>
    <td>{$statusBadge}</td>
    <td>{$this->escape($article['author_name'])}</td>
    <td>{$dateColumn}</td>
    <td>{$actions}</td>
</tr>
HTML;
            }

            $html .= <<<HTML
        </tbody>
    </table>
</div>
HTML;
        }

        $html .= '</div>';

        return $this->adminLayout->wrap($html, 'Správa článků');
    }

    public function showEditForm(int $id): string
    {
        $this->requireAdmin();
        $article = $this->articleService->getArticle($id);

        if (!$article) {
            header("Location: {$this->baseUrl}/admin/articles?error=not_found");
            exit;
        }

        $csrfField = $this->csrf->getTokenField();

        // Zajistíme, že hodnoty nejsou null
        $title = $article['title'] ?? '';
        $excerpt = $article['excerpt'] ?? '';
        $content = $article['content'] ?? '';
        $status = $article['status'] ?? 'draft';

        $backButton = "<a href='{$this->baseUrl}/admin/articles' class='btn btn-secondary'>← Zpět na seznam</a>";

        $message = '';
        if (isset($_GET['saved'])) {
            $message = '<div class="alert alert-success">Článek byl úspěšně uložen!</div>';
        } elseif (isset($_GET['error'])) {
            $message = '<div class="alert alert-error">Chyba při ukládání článku!</div>';
        }

        $content = <<<HTML
<div class="edit-header">
    {$backButton}
    <h1>Editace článku: {$this->escape($title)}</h1>
</div>

{$message}

<form method="POST" action="{$this->baseUrl}/admin/articles/edit/{$id}" class="article-form">
    <div class="form-group">
        <label for="title">Název článku:</label>
        <input type="text" id="title" name="title" value="{$this->escape($title)}" required>
    </div>

    <div class="form-group">
        <label for="excerpt">Krátký popis:</label>
        <textarea id="excerpt" name="excerpt">{$this->escape($excerpt)}</textarea>
    </div>

    <div class="form-group">
        <label for="content">Obsah článku:</label>
        <textarea id="content" name="content" required>{$this->escape($content)}</textarea>
    </div>

    <div class="form-group">
        <label for="status">Stav:</label>
        <select id="status" name="status">
            <option value="draft" {$this->isSelected('draft', $status)}>Koncept</option>
            <option value="published" {$this->isSelected('published', $status)}>Publikováno</option>
        </select>
    </div>

    <div class="form-actions">
        {$csrfField}
        <button type="submit" class="btn btn-primary">Uložit změny</button>
        <a href="{$this->baseUrl}/admin/articles" class="btn btn-secondary">Zrušit</a>
    </div>
</form>
HTML;

        return $this->adminLayout->wrap($content, 'Editace článku');
    }

    public function updateArticle(int $id): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}/admin/articles/edit/{$id}");
            exit;
        }

        try {
            $success = $this->articleService->updateArticle($id, [
                'title' => $_POST['title'],
                'content' => $_POST['content'],
                'excerpt' => $_POST['excerpt'] ?? '',
                'status' => $_POST['status'] ?? 'draft'
            ]);

            if ($success) {
                header("Location: {$this->baseUrl}/admin/articles/edit/{$id}?saved=1");
            } else {
                header("Location: {$this->baseUrl}/admin/articles/edit/{$id}?error=1");
            }
            exit;

        } catch (\Exception $e) {
            header("Location: {$this->baseUrl}/admin/articles/edit/{$id}?error=1");
            exit;
        }
    }

    public function deleteArticle(int $id): void
    {
        $this->requireAdmin();

        $success = $this->articleService->deleteArticle($id);

        if ($success) {
            header("Location: {$this->baseUrl}/admin/articles?deleted=1");
        } else {
            header("Location: {$this->baseUrl}/admin/articles?error=1");
        }
        exit;
    }

    public function restoreArticle(int $id): void
    {
        $this->requireAdmin();

        $success = $this->articleService->restoreArticle($id);

        if ($success) {
            header("Location: {$this->baseUrl}/admin/articles?show=trash&restored=1");
        } else {
            header("Location: {$this->baseUrl}/admin/articles?show=trash&error=1");
        }
        exit;
    }

    public function permanentDeleteArticle(int $id): void
    {
        $this->requireAdmin();

        $success = $this->articleService->permanentDeleteArticle($id);

        if ($success) {
            header("Location: {$this->baseUrl}/admin/articles?show=trash&deleted=1");
        } else {
            header("Location: {$this->baseUrl}/admin/articles?show=trash&error=1");
        }
        exit;
    }

    private function requireAdmin(): void
    {
        if (!$this->authService->isLoggedIn()) {
            header("Location: {$this->baseUrl}/login");
            exit;
        }
    }

    private function escape(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function escapeJs(string $value): string
    {
        return str_replace(["'", "\"", "\n", "\r"], ["\\'", "\\\"", "\\n", "\\r"], $value);
    }

    private function isSelected(string $value, ?string $current): string
    {
        return $value === ($current ?? '') ? 'selected' : '';
    }

    private function getStatusBadge(string $status): string
    {
        $statuses = [
            'draft' => ['text' => 'Koncept', 'class' => 'badge-warning'],
            'published' => ['text' => 'Publikováno', 'class' => 'badge-success'],
            'archived' => ['text' => 'Archivováno', 'class' => 'badge-secondary']
        ];

        $statusInfo = $statuses[$status] ?? ['text' => $status, 'class' => 'badge-secondary'];

        return '<span class="badge ' . $statusInfo['class'] . '">' . $statusInfo['text'] . '</span>';
    }
}