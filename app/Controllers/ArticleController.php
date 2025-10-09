<?php
// app/Controllers/ArticleController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\ArticleService;
use App\Services\CategoryService;
use App\Auth\LoginService;
use App\Security\CsrfProtection;
use App\Core\AdminLayout;
use App\Core\Config;
use App\Logger\Logger;


/**
 * Správa článků v administraci
 *
 * Poskytuje kompletní CRUD operace pro články včetně formulářů,
 * validace, změny stavů a správy koše.
 *
 * @package App\Controllers
 * @author KRS3
 * @version 4.0
 */
class ArticleController
{
	private $logger = null;
    /**
     * @param ArticleService $articleService Služba pro práci s články
     * @param LoginService $authService Služba pro autentizaci
     * @param CsrfProtection $csrf Ochrana proti CSRF útokům
     * @param string $baseUrl Základní URL aplikace
     * @param AdminLayout $adminLayout Layout administračního rozhraní
     * @param CategoryService $categoryService Služba pro práci s kategoriemi
     */
    public function __construct(
        private ArticleService $articleService,
        private LoginService $authService,
        private CsrfProtection $csrf,
        private string $baseUrl,
        private AdminLayout $adminLayout,
        private CategoryService $categoryService
    ) {
		$this->logger = Logger::getInstance();
	}

		 /**
     * Zobrazí formulář pro vytvoření nového článku
     *
     * @return string HTML obsah formuláře
     */
	public function showCreateForm(): string
	    {
	        $this->requireAdmin();
	        $csrfField = $this->csrf->getTokenField();
	        $categories = $this->categoryService->getAllCategories();
	        $categoryOptions = $this->renderCategoryOptions($categories, []);

	        $content = <<<HTML
	<h1>{$this->t('admin.articles.create')}</h1>
	<form method="POST" action="{$this->baseUrl}admin/articles/create" class="article-form">
	    <div class="form-group">
	        <label for="title">{$this->t('admin.articles.form.title')}:</label>
	        <input type="text" id="title" name="title" required class="form-control">
	    </div>

	    <div class="form-group">
	        <label for="excerpt">{$this->t('admin.articles.form.excerpt')}:</label>
	        <textarea id="excerpt" name="excerpt" class="form-control"></textarea>
	    </div>

	    <div class="form-group">
	        <label for="content">{$this->t('admin.articles.form.content')}:</label>
	        <textarea id="content" name="content" required class="form-control"></textarea>
	    </div>

	    <div class="form-group">
	        <label for="categories">{$this->t('admin.articles.form.categories')}:</label>
	        <div class="categories-checkbox-group">
	            {$categoryOptions}
	        </div>
	    </div>

	    <div class="form-group">
	        <label for="status">{$this->t('admin.articles.form.status')}:</label>
	        <select id="status" name="status" class="form-control">
	            <option value="draft">{$this->t('admin.articles.status.draft')}</option>
	            <option value="published">{$this->t('admin.articles.status.published')}</option>
	        </select>
	    </div>

	    <div class="form-actions">
	        {$csrfField}
	        <button type="submit" class="btn btn-primary">{$this->t('admin.articles.form.create_button')}</button>
	        <a href="{$this->baseUrl}admin/articles" class="btn btn-secondary">{$this->t('admin.articles.form.cancel')}</a>
	    </div>
	</form>
HTML;

        return $this->adminLayout->wrap($content, $this->t('admin.articles.create'));
    }


    /**
     * Zpracuje vytvoření nového článku
     *
     * @return void
     * @throws \Exception Pokud dojde k chybě při vytváření článku
     */
public function createArticle(): void
{
    $this->requireAdmin();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: {$this->baseUrl}admin/articles/new");
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

        // Přiřazení kategorií k článku
        $categoryIds = $_POST['categories'] ?? [];
        $this->categoryService->assignCategoriesToArticle($articleId, $categoryIds);

        header("Location: {$this->baseUrl}admin/articles?created=1");
        exit;

    } catch (\Exception $e) {
        // BEZPEČNÉ použití loggeru s kontrolou
        if (isset($this->logger)) {
            $this->logger->exception($e, 'článek se nepodařilo uložit.');
        } else {
            // Fallback: zápis do error_log pokud logger není dostupný
            error_log('Chyba při vytváření článku: ' . $e->getMessage());
        }

        header("Location: {$this->baseUrl}admin/articles/new?error=1");
        exit;
    }
}


	/**
	 * Zobrazí seznam článků s možností zobrazení koše
	 *
	 * @return string HTML obsah seznamu článků
	 */
	public function showArticles(): string
	{
	    $this->requireAdmin();

	    // Zjisti, zda se má zobrazit koš
	    $isTrashView = isset($_GET['show']) && $_GET['show'] === 'trash';

	    if ($isTrashView) {
	        $articles = $this->articleService->getDeletedArticlesWithCategories();
	    } else {
	        $articles = $this->articleService->getArticlesWithCategories();
	    }

	    // BEZPEČNÉ logování s kontrolou existence loggeru
		
	    if (isset($this->logger)) {
	        $this->logger->info("Total articles: " . count($articles));
	        foreach ($articles as $index => $article) {
	            $this->logger->info("Article {$index}: " . $article['title']);
	            $this->logger->info("Categories: " . ($article['category_names'] ?? 'none'));
	            $this->logger->info("Category IDs: " . ($article['category_ids'] ?? 'none'));
	        }
	    }
		// Debug: zkontrolujte první článek (pokud existuje)
		    if (!empty($articles)) {
		        $this->logger->info("First article title: " . $articles[0]['title']);
		        $this->logger->info("First article deleted_at: " . ($articles[0]['deleted_at'] ?? 'NULL'));
		    }

	    // Připrav zprávy
	    $message = '';
	    if (isset($_GET['created'])) {
	        $message = '<div class="alert alert-success">' . $this->t('admin.articles.messages.created') . '</div>';
	    } elseif (isset($_GET['restored'])) {
	        $message = '<div class="alert alert-success">' . $this->t('admin.articles.messages.restored') . '</div>';
	    } elseif (isset($_GET['deleted'])) {
	        $message = '<div class="alert alert-success">' . $this->t('admin.articles.messages.deleted') . '</div>';
	    } elseif (isset($_GET['error'])) {
	        $message = '<div class="alert alert-error">' . $this->t('admin.articles.messages.error') . '</div>';
	    }

	    // Připrav proměnné pro tabs
	    $activeClass = 'active';
	    $mainTabClass = !$isTrashView ? $activeClass : '';
	    $trashTabClass = $isTrashView ? $activeClass : '';

	    // Připrav proměnné pro obsah
	    $emptyStateTitle = $isTrashView ?
	        $this->t('admin.articles.messages.empty_trash') :
	        $this->t('admin.articles.messages.empty_active');
	    $emptyStateText = $isTrashView ?
	        $this->t('admin.articles.messages.empty_text_trash') :
	        $this->t('admin.articles.messages.empty_text_active');
	    $emptyStateButton = $isTrashView ? '' :
	        "<a href='{$this->baseUrl}admin/articles/new' class='btn btn-primary'>" .
	        $this->t('admin.articles.messages.create_first') . "</a>";
	    $tableHeader = $isTrashView ?
	        $this->t('admin.articles.table.deleted') :
	        $this->t('admin.articles.table.created');

	    $html = <<<HTML
	<div class="page-header">
	    <h1>{$this->t('admin.articles.manage')}</h1>
	    <a href="{$this->baseUrl}admin/articles/new" class="btn btn-primary">
	        ＋ {$this->t('admin.articles.create')}
	    </a>
	</div>

	{$message}

	<!-- TABS -->
	<div class="tabs">
	    <a href="{$this->baseUrl}admin/articles" class="tab {$mainTabClass}">
	        {$this->t('admin.articles.active')}
	    </a>
	    <a href="{$this->baseUrl}admin/articles?show=trash" class="tab {$trashTabClass}">
	        {$this->t('admin.articles.trash')}
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
	                <th>{$this->t('admin.articles.table.title')}</th>
	                <th>{$this->t('admin.articles.table.categories')}</th>
	                <th>{$this->t('admin.articles.table.status')}</th>
	                <th>{$this->t('admin.articles.table.author')}</th>
	                <th>{$tableHeader}</th>
	                <th>{$this->t('admin.articles.table.actions')}</th>
	            </tr>
	        </thead>
	        <tbody>
	HTML;

	        foreach ($articles as $article) {
	            $dateColumn = $isTrashView
	                ? date('j. n. Y H:i', strtotime($article['deleted_at']))
	                : date('j. n. Y H:i', strtotime($article['created_at']));

	            $statusBadge = $this->getStatusBadge($article['status']);

	            // Zpracování kategorií
	            $categoriesHtml = '';
	            if (!empty($article['category_names'])) {
	                $categoryNames = explode(',', $article['category_names']);
	                $categoryIds = explode(',', $article['category_ids']);

	                foreach ($categoryNames as $index => $categoryName) {
	                    $categoryId = $categoryIds[$index] ?? '';
	                    $categoriesHtml .= '<span class="category-badge">' . $this->escape(trim($categoryName)) . '</span>';
	                }
	            } else {
	                $categoriesHtml = '<span class="no-categories">' . $this->t('admin.articles.table.no_categories') . '</span>';
	            }

	            if ($isTrashView) {
	                $actions = <<<HTML
	<div class="action-buttons">
	    <a href="{$this->baseUrl}admin/articles/restore/{$article['id']}"
	       class="btn btn-sm btn-success" title="{$this->t('admin.articles.actions.restore')}">
	        ↶
	    </a>
	    <a href="{$this->baseUrl}admin/articles/permanent-delete/{$article['id']}"
	       class="btn btn-sm btn-danger"
	       onclick="return confirm('{$this->t('admin.articles.confirm.permanent_delete')} „{$this->escapeJs($article['title'])}‟? Tato akce je nevratná!')"
	       title="{$this->t('admin.articles.actions.permanent_delete')}">
	        🗑️
	    </a>
	</div>
	HTML;
	            } else {
	                $actions = <<<HTML
	<div class="action-buttons">
	    <a href="{$this->baseUrl}admin/articles/edit/{$article['id']}"
	       class="btn btn-sm btn-primary" title="{$this->t('admin.articles.actions.edit')}">
	        ✏️
	    </a>
	    <a href="{$this->baseUrl}admin/articles/delete/{$article['id']}"
	       class="btn btn-sm btn-danger"
	       onclick="return confirm('{$this->t('admin.articles.confirm.delete')} „{$this->escapeJs($article['title'])}‟?')"
	       title="{$this->t('admin.articles.actions.delete')}">
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
	    <td>
	        <div class="article-categories">
	            {$categoriesHtml}
	        </div>
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

	    return $this->adminLayout->wrap($html, $this->t('admin.articles.manage'));
	}

    /**
     * Zobrazí formulář pro editaci existujícího článku
     *
     * @param int $id ID článku k editaci
     * @return string HTML obsah editačního formuláře
     */
    public function showEditForm(int $id): string
    {
        $this->requireAdmin();
        $article = $this->articleService->getArticle($id);

        if (!$article) {
            header("Location: {$this->baseUrl}admin/articles?error=not_found");
            exit;
        }

        $csrfField = $this->csrf->getTokenField();

		// Načtení kategorií článku
        $articleCategories = $this->categoryService->getCategoriesForArticle($id);
        $selectedCategoryIds = array_column($articleCategories, 'id');

        $categories = $this->categoryService->getAllCategories();
        $categoryOptions = $this->renderCategoryOptions($categories, $selectedCategoryIds);

        // Zajistíme, že hodnoty nejsou null
        $title = $article['title'] ?? '';
        $excerpt = $article['excerpt'] ?? '';
        $content = $article['content'] ?? '';
        $status = $article['status'] ?? 'draft';

        $backButton = "<a href='{$this->baseUrl}admin/articles' class='btn btn-secondary'>{$this->t('admin.articles.form.back')}</a>";

        $message = '';
        if (isset($_GET['saved'])) {
            $message = '<div class="alert alert-success">' . $this->t('admin.articles.messages.updated') . '</div>';
        } elseif (isset($_GET['error'])) {
            $message = '<div class="alert alert-error">' . $this->t('admin.articles.messages.error') . '</div>';
        }

        $content = <<<HTML
<div class="edit-header">
    {$backButton}
    <h1>{$this->t('admin.articles.edit')}: {$this->escape($title)}</h1>
</div>

{$message}

<form method="POST" action="{$this->baseUrl}admin/articles/edit/{$id}" class="article-form">
    <div class="form-group">
        <label for="title">{$this->t('admin.articles.form.title')}:</label>
        <input type="text" id="title" name="title" value="{$this->escape($title)}" required>
    </div>

	<div class="form-group">
	    <label for="categories">{$this->t('admin.articles.form.categories')}:</label>
	    <div class="categories-checkbox-group">
	        {$categoryOptions}
	    </div>
	</div>

    <div class="form-group">
        <label for="excerpt">{$this->t('admin.articles.form.excerpt')}:</label>
        <textarea id="excerpt" name="excerpt">{$this->escape($excerpt)}</textarea>
    </div>

    <div class="form-group">
        <label for="content">{$this->t('admin.articles.form.content')}:</label>
        <textarea id="content" name="content" required>{$this->escape($content)}</textarea>
    </div>

    <div class="form-group">
        <label for="status">{$this->t('admin.articles.form.status')}:</label>
        <select id="status" name="status">
            <option value="draft" {$this->isSelected('draft', $status)}>{$this->t('admin.articles.status.draft')}</option>
            <option value="published" {$this->isSelected('published', $status)}>{$this->t('admin.articles.status.published')}</option>
        </select>
    </div>

    <div class="form-actions">
        {$csrfField}
        <button type="submit" class="btn btn-primary">{$this->t('admin.articles.form.save_button')}</button>
        <a href="{$this->baseUrl}admin/articles" class="btn btn-secondary">{$this->t('admin.articles.form.cancel')}</a>
    </div>
</form>
HTML;

        return $this->adminLayout->wrap($content, $this->t('admin.articles.edit'));
    }

    /**
     * Zpracuje aktualizaci existujícího článku
     *
     * @param int $id ID článku k aktualizaci
     * @return void
     */
    public function updateArticle(int $id): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}admin/articles/edit/{$id}");
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
				// Aktualizace kategorií článku
                $categoryIds = $_POST['categories'] ?? [];
                $this->categoryService->assignCategoriesToArticle($id, $categoryIds);


                header("Location: {$this->baseUrl}admin/articles/edit/{$id}?saved=1");
            } else {
                header("Location: {$this->baseUrl}admin/articles/edit/{$id}?error=1");
            }
            exit;

        } catch (\Exception $e) {
            header("Location: {$this->baseUrl}admin/articles/edit/{$id}?error=1");
            exit;
        }
    }


    /**
     * Vykreslí checkboxy pro výběr kategorií
     *
     * @param array $categories Seznam všech kategorií
     * @param array $selectedIds Pole ID vybraných kategorií
     * @return string HTML obsah checkboxů
     */
    private function renderCategoryOptions(array $categories, array $selectedIds): string
    {
        $html = '';
        foreach ($categories as $category) {
            $checked = in_array($category['id'], $selectedIds) ? 'checked' : '';
            $html .= <<<HTML
<div class="checkbox-group">
    <input type="checkbox" id="category_{$category['id']}" name="categories[]" value="{$category['id']}" {$checked}>
    <label for="category_{$category['id']}">{$this->escape($category['name'])}</label>
</div>
HTML;
        }
        return $html;
    }



    /**
     * Přesune článek do koše (soft delete)
     *
     * @param int $id ID článku ke smazání
     * @return void
     */
    public function deleteArticle(int $id): void
    {
        $this->requireAdmin();

        $success = $this->articleService->deleteArticle($id);

        if ($success) {
            header("Location: {$this->baseUrl}admin/articles?deleted=1");
        } else {
            header("Location: {$this->baseUrl}admin/articles?error=1");
        }
        exit;
    }

    /**
     * Obnoví článek z koše
     *
     * @param int $id ID článku k obnovení
     * @return void
     */
    public function restoreArticle(int $id): void
    {
        $this->requireAdmin();

        $success = $this->articleService->restoreArticle($id);

        if ($success) {
            header("Location: {$this->baseUrl}admin/articles?show=trash&restored=1");
        } else {
            header("Location: {$this->baseUrl}admin/articles?show=trash&error=1");
        }
        exit;
    }

    /**
     * Trvale smaže článek z databáze
     *
     * @param int $id ID článku k trvalému smazání
     * @return void
     */
    public function permanentDeleteArticle(int $id): void
    {
        $this->requireAdmin();

        $success = $this->articleService->permanentDeleteArticle($id);

        if ($success) {
            header("Location: {$this->baseUrl}admin/articles?show=trash&deleted=1");
        } else {
            header("Location: {$this->baseUrl}admin/articles?show=trash&error=1");
        }
        exit;
    }

    /**
     * Ověří, že uživatel je přihlášen jako admin
     *
     * @return void
     * @throws \RuntimeException Pokud uživatel není přihlášen
     */
    private function requireAdmin(): void
    {
        if (!$this->authService->isLoggedIn()) {
            header("Location: {$this->baseUrl}login");
            exit;
        }
    }

    /**
     * Přeloží textový klíč
     *
     * @param string $key Klíč pro překlad
     * @param array $parametry Parametry pro nahrazení
     * @return string Přeložený text
     */
    private function t(string $key, array $parametry = []): string
    {
        return Config::text($key, $parametry);
    }

    /**
     * Escape HTML speciálních znaků
     *
     * @param string|null $value Hodnota k escapování
     * @return string Escapovaná hodnota
     */
    private function escape(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Escape řetězce pro použití v JavaScriptu
     *
     * @param string $value Hodnota k escapování
     * @return string Escapovaná hodnota
     */
    private function escapeJs(string $value): string
    {
        return str_replace(["'", "\"", "\n", "\r"], ["\\'", "\\\"", "\\n", "\\r"], $value);
    }

    /**
     * Vrátí selected atribut pro option element
     *
     * @param string $value Hodnota optionu
     * @param string|null $current Aktuální hodnota
     * @return string 'selected' nebo prázdný řetězec
     */
    private function isSelected(string $value, ?string $current): string
    {
        return $value === ($current ?? '') ? 'selected' : '';
    }

    /**
     * Vrátí HTML badge pro stav článku
     *
     * @param string $status Stav článku
     * @return string HTML badge
     */
    private function getStatusBadge(string $status): string
    {
        $statuses = [
            'draft' => ['text' => $this->t('admin.articles.status.draft'), 'class' => 'badge-warning'],
            'published' => ['text' => $this->t('admin.articles.status.published'), 'class' => 'badge-success'],
            'archived' => ['text' => $this->t('admin.articles.status.archived'), 'class' => 'badge-secondary']
        ];

        $statusInfo = $statuses[$status] ?? ['text' => $status, 'class' => 'badge-secondary'];

        return '<span class="badge ' . $statusInfo['class'] . '">' . $statusInfo['text'] . '</span>';
    }
}