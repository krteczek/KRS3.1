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
 * @version 4.1
 */
class ArticleController
{
    private Logger $logger;

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

        $this->logger->debug('Displaying article create form', [
            'user' => $this->authService->getUsername()
        ]);

        $csrfField = $this->csrf->getTokenField();
        $categories = $this->categoryService->getCategoriesForSelect();
        $categoryOptions = $this->renderCategorySelect($categories, []);

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
        <select id="categories" name="categories" class="form-control" size="1">
            {$categoryOptions}
        </select>
        <small class="form-text">{$this->t('admin.articles.form.categories_help')}</small>
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
            $this->logger->warning('Invalid HTTP method for article creation', [
                'method' => $_SERVER['REQUEST_METHOD'],
                'user' => $this->authService->getUsername()
            ]);
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
            $categoryIds = [$_POST['categories']] ?? [];
            $this->categoryService->assignCategoriesToArticle($articleId, $categoryIds);

            $this->logger->info('Article created successfully', [
                'article_id' => $articleId,
                'title' => $_POST['title'],
                'status' => $_POST['status'] ?? 'draft',
                'categories_count' => count($categoryIds),
                'user' => $this->authService->getUsername()
            ]);

            header("Location: {$this->baseUrl}admin/articles?created=1");
            exit;

        } catch (\Exception $e) {
            $this->logger->error('Article creation failed', [
                'title' => $_POST['title'] ?? '',
                'error' => $e->getMessage(),
                'user' => $this->authService->getUsername()
            ]);

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

        $isTrashView = isset($_GET['show']) && $_GET['show'] === 'trash';

        $this->logger->debug('Displaying articles list', [
            'is_trash_view' => $isTrashView,
            'user' => $this->authService->getUsername()
        ]);

        try {
            if ($isTrashView) {
                $articles = $this->articleService->getDeletedArticlesWithCategories();
            } else {
                $articles = $this->articleService->getArticlesWithCategories();
            }

            $message = $this->renderMessages();
            $activeClass = 'active';
            $mainTabClass = !$isTrashView ? $activeClass : '';
            $trashTabClass = $isTrashView ? $activeClass : '';

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
                $html .= $this->renderArticlesTable($articles, $isTrashView, $tableHeader);
            }

            $html .= '</div>';

            return $this->adminLayout->wrap($html, $this->t('admin.articles.manage'));
        } catch (\Exception $e) {
            $this->logger->error('Failed to display articles list', [
                'is_trash_view' => $isTrashView,
                'error' => $e->getMessage(),
                'user' => $this->authService->getUsername()
            ]);
            throw $e;
        }
    }

    /**
     * Vykreslí tabulku článků
     *
     * @param array $articles Seznam článků
     * @param bool $isTrashView Zda se jedná o koš
     * @param string $tableHeader Název sloupce s datem
     * @return string HTML obsah
     */
    private function renderArticlesTable(array $articles, bool $isTrashView, string $tableHeader): string
    {
        $html = <<<HTML
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
                foreach ($categoryNames as $categoryName) {
                    $categoriesHtml .= '<span class="category-badge">' . $this->escape(trim($categoryName)) . '</span>';
                }
            } else {
                $categoriesHtml = '<span class="no-categories">' . $this->t('admin.articles.table.no_categories') . '</span>';
            }

            $actions = $isTrashView ? $this->renderTrashActions($article) : $this->renderActiveActions($article);

            $html .= <<<HTML
<tr>
    <td>
        <div class="article-title"><b>{$this->escape($article['title'])}</b></div>
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

        return $html;
    }

    /**
     * Vykreslí akce pro aktivní článek
     *
     * @param array $article Data článku
     * @return string HTML obsah
     */
    private function renderActiveActions(array $article): string
    {
        return <<<HTML
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

    /**
     * Vykreslí akce pro článek v koši
     *
     * @param array $article Data článku
     * @return string HTML obsah
     */
    private function renderTrashActions(array $article): string
    {
        return <<<HTML
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
    }

    /**
     * Vykreslí zprávy (success, error)
     *
     * @return string HTML zpráv
     */
    private function renderMessages(): string
    {
        if (isset($_GET['created'])) {
            return '<div class="alert alert-success">' . $this->t('admin.articles.messages.created') . '</div>';
        } elseif (isset($_GET['restored'])) {
            return '<div class="alert alert-success">' . $this->t('admin.articles.messages.restored') . '</div>';
        } elseif (isset($_GET['deleted'])) {
            return '<div class="alert alert-success">' . $this->t('admin.articles.messages.deleted') . '</div>';
        } elseif (isset($_GET['error'])) {
            return '<div class="alert alert-error">' . $this->t('admin.articles.messages.error') . '</div>';
        }
        return '';
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

        try {
            $article = $this->articleService->getArticle($id);

            if (!$article) {
                $this->logger->warning('Article not found for editing', [
                    'article_id' => $id,
                    'user' => $this->authService->getUsername()
                ]);
                header("Location: {$this->baseUrl}admin/articles?error=not_found");
                exit;
            }

            $this->logger->debug('Displaying article edit form', [
                'article_id' => $id,
                'article_title' => $article['title'],
                'user' => $this->authService->getUsername()
            ]);

            $csrfField = $this->csrf->getTokenField();

            // Načtení kategorií článku
            $articleCategories = $this->categoryService->getCategoriesForArticle($id);
            $selectedCategoryIds = array_column($articleCategories, 'id');

            $categories = $this->categoryService->getCategoriesForSelect();
            $categoryOptions = $this->renderCategorySelect($categories, $selectedCategoryIds);

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

<form method="POST" action="{$this->baseUrl}admin/articles/update/{$id}" class="article-form">
    <div class="form-group">
        <label for="title">{$this->t('admin.articles.form.title')}:</label>
        <input type="text" id="title" name="title" value="{$this->escape($title)}" required>
    </div>

    <div class="form-group">
        <label for="categories">{$this->t('admin.articles.form.categories')}:</label>
        <select id="categories" name="categories[]" class="form-control" size="1">
            {$categoryOptions}
        </select>
        <small class="form-text">{$this->t('admin.articles.form.categories_help')}</small>
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
        } catch (\Exception $e) {
            $this->logger->error('Failed to display article edit form', [
                'article_id' => $id,
                'error' => $e->getMessage(),
                'user' => $this->authService->getUsername()
            ]);
            throw $e;
        }
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
	        $this->logger->warning('Invalid HTTP method for article update', [
	            'method' => $_SERVER['REQUEST_METHOD'],
	            'article_id' => $id,
	            'user' => $this->authService->getUsername()
	        ]);
	        header("Location: {$this->baseUrl}admin/articles/edit/{$id}");
	        exit;
	    }

	    try {
	        // DEBUG: Logujeme co přišlo v POST
	        $this->logger->debug('UPDATE ARTICLE - POST DATA', [
	            'article_id' => $id,
	            'post_data' => $_POST,
	            'user' => $this->authService->getUsername()
	        ]);

	        $categoryIds = $_POST['categories'] ?? [];
	        // Pokud je categories pole, vezmeme první prvek (jedna kategorie)
	        if (is_array($categoryIds) && !empty($categoryIds)) {
	            $categoryIds = [reset($categoryIds)]; // vezmeme první vybranou kategorii
	        } else {
	            $categoryIds = [];
	        }

	        $updateData = [
	            'title' => $_POST['title'],
	            'content' => $_POST['content'],
	            'excerpt' => $_POST['excerpt'] ?? '',
	            'status' => $_POST['status'] ?? 'draft',
	            'category_ids' => $categoryIds
	        ];

	        // DEBUG: Logujeme data co posíláme do service
	        $this->logger->debug('UPDATE ARTICLE - DATA TO SERVICE', [
	            'article_id' => $id,
	            'update_data' => $updateData
	        ]);

	        $success = $this->articleService->updateArticle($id, $updateData);

	        // DEBUG: Logujeme výsledek
	        $this->logger->debug('UPDATE ARTICLE - RESULT', [
	            'article_id' => $id,
	            'success' => $success
	        ]);

	        if ($success) {
	            $this->logger->info('Article updated successfully', [
	                'article_id' => $id,
	                'title' => $_POST['title'],
	                'status' => $_POST['status'] ?? 'draft',
	                'categories_count' => count($categoryIds),
	                'user' => $this->authService->getUsername()
	            ]);

	            header("Location: {$this->baseUrl}admin/articles/edit/{$id}?saved=1");
	        } else {
	            $this->logger->warning('Article update returned false', [
	                'article_id' => $id,
	                'user' => $this->authService->getUsername()
	            ]);
	            header("Location: {$this->baseUrl}admin/articles/edit/{$id}?error=1");
	        }
	        exit;

	    } catch (\Exception $e) {
	        $this->logger->error('Article update failed', [
	            'article_id' => $id,
	            'error' => $e->getMessage(),
	            'user' => $this->authService->getUsername()
	        ]);
	        header("Location: {$this->baseUrl}admin/articles/edit/{$id}?error=1");
	        exit;
	    }
	}
	/**
public function updateArticle(int $id): void
{
    $this->requireAdmin();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->logger->warning('Invalid HTTP method for article update', [
            'method' => $_SERVER['REQUEST_METHOD'],
            'article_id' => $id,
            'user' => $this->authService->getUsername()
        ]);
        header("Location: {$this->baseUrl}admin/articles/edit/{$id}");
        exit;
    }

    try {
        // DEBUG: Logujeme co přišlo v POST
        $this->logger->debug('UPDATE ARTICLE - POST DATA', [
            'article_id' => $id,
            'post_data' => $_POST,
            'user' => $this->authService->getUsername()
        ]);

        $categoryIds = $_POST['categories'] ?? [];
        // Pokud je categories pole, vezmeme první prvek (jedna kategorie)
        if (is_array($categoryIds) && !empty($categoryIds)) {
            $categoryIds = [reset($categoryIds)]; // vezmeme první vybranou kategorii
        } else {
            $categoryIds = [];
        }

        $updateData = [
            'title' => $_POST['title'],
            'content' => $_POST['content'],
            'excerpt' => $_POST['excerpt'] ?? '',
            'status' => $_POST['status'] ?? 'draft',
            'category_ids' => $categoryIds
        ];

        // DEBUG: Logujeme data co posíláme do service
        $this->logger->debug('UPDATE ARTICLE - DATA TO SERVICE', [
            'article_id' => $id,
            'update_data' => $updateData
        ]);

        $success = $this->articleService->updateArticle($id, $updateData);

        // DEBUG: Logujeme výsledek
        $this->logger->debug('UPDATE ARTICLE - RESULT', [
            'article_id' => $id,
            'success' => $success
        ]);

        if ($success) {
            $this->logger->info('Article updated successfully', [
                'article_id' => $id,
                'title' => $_POST['title'],
                'status' => $_POST['status'] ?? 'draft',
                'categories_count' => count($categoryIds),
                'user' => $this->authService->getUsername()
            ]);

            header("Location: {$this->baseUrl}admin/articles/edit/{$id}?saved=1");
        } else {
            $this->logger->warning('Article update returned false', [
                'article_id' => $id,
                'user' => $this->authService->getUsername()
            ]);
            header("Location: {$this->baseUrl}admin/articles/edit/{$id}?error=1");
        }
        exit;

    } catch (\Exception $e) {
        $this->logger->error('Article update failed', [
            'article_id' => $id,
            'error' => $e->getMessage(),
            'user' => $this->authService->getUsername()
        ]);
        header("Location: {$this->baseUrl}admin/articles/edit/{$id}?error=1");
        exit;
    }
}
	**/

    /**
     * Vykreslí SELECT dropdown pro výběr kategorií s hierarchií
     *
     * @param array $categories Seznam kategorií (id => name s odsazením)
     * @param array $selectedIds Pole ID vybraných kategorií
     * @return string HTML obsah options
     */
    private function renderCategorySelect(array $categories, array $selectedIds): string
    {
        $options = '';

        foreach ($categories as $id => $name) {
            $selected = in_array($id, $selectedIds) ? 'selected' : '';
            $options .= sprintf(
                '<option value="%d" %s>%s</option>',
                $id,
                $selected,
                $this->escape($name)
            );
        }

        return $options;
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

        try {
            $success = $this->articleService->deleteArticle($id);

            if ($success) {
                $this->logger->warning('Article moved to trash', [
                    'article_id' => $id,
                    'user' => $this->authService->getUsername(),
                    'client_ip' => $this->getClientIp()
                ]);
                header("Location: {$this->baseUrl}admin/articles?deleted=1");
            } else {
                header("Location: {$this->baseUrl}admin/articles?error=1");
            }
        } catch (\Exception $e) {
            $this->logger->error('Article deletion failed', [
                'article_id' => $id,
                'error' => $e->getMessage(),
                'user' => $this->authService->getUsername()
            ]);
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

        try {
            $success = $this->articleService->restoreArticle($id);

            if ($success) {
                $this->logger->info('Article restored from trash', [
                    'article_id' => $id,
                    'user' => $this->authService->getUsername()
                ]);
                header("Location: {$this->baseUrl}admin/articles?show=trash&restored=1");
            } else {
                header("Location: {$this->baseUrl}admin/articles?show=trash&error=1");
            }
        } catch (\Exception $e) {
            $this->logger->error('Article restoration failed', [
                'article_id' => $id,
                'error' => $e->getMessage(),
                'user' => $this->authService->getUsername()
            ]);
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

        try {
            $success = $this->articleService->permanentDeleteArticle($id);

            if ($success) {
                $this->logger->critical('Article permanently deleted', [
                    'article_id' => $id,
                    'user' => $this->authService->getUsername(),
                    'client_ip' => $this->getClientIp()
                ]);
                header("Location: {$this->baseUrl}admin/articles?show=trash&deleted=1");
            } else {
                header("Location: {$this->baseUrl}admin/articles?show=trash&error=1");
            }
        } catch (\Exception $e) {
            $this->logger->error('Article permanent deletion failed', [
                'article_id' => $id,
                'error' => $e->getMessage(),
                'user' => $this->authService->getUsername()
            ]);
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
            $this->logger->warning('Unauthorized access to articles admin', [
                'client_ip' => $this->getClientIp()
            ]);
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

    /**
     * Získá IP adresu klienta
     *
     * @return string IP adresa
     */
    private function getClientIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}