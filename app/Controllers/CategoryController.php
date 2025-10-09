<?php
// app/Controllers/CategoryController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Logger\Logger;
use App\Services\CategoryService;
use App\Auth\LoginService;
use App\Security\CsrfProtection;
use App\Core\AdminLayout;
use App\Core\Config;

/**
 * Správa kategorií článků v administraci
 *
 * @package App\Controllers
 * @author KRS3
 * @version 3.0
 */
class CategoryController
{

	 private Logger $logger;

    /**
     * Konstruktor
     *
     * @param CategoryService $categoryService Služba pro práci s kategoriemi
     * @param LoginService $authService Služba pro autentizaci
     * @param CsrfProtection $csrf Ochrana proti CSRF útokům
     * @param string $baseUrl Základní URL aplikace
     * @param AdminLayout $adminLayout Layout administračního rozhraní
     */
    public function __construct(
        private CategoryService $categoryService,
        private LoginService $authService,
        private CsrfProtection $csrf,
        private string $baseUrl,
        private AdminLayout $adminLayout
    ) {
		$this->logger = Logger::getInstance();
	}

    /**
     * Zobrazí seznam kategorií (aktivní a koš)
     *
     * @return string HTML obsah seznamu kategorií
     */
    public function index(): string
    {
        $this->requireAdmin();

        // Zjisti, zda se má zobrazit koš
        $isTrashView = isset($_GET['show']) && $_GET['show'] === 'trash';

        if ($isTrashView) {
            $categories = $this->categoryService->getDeletedCategories();
        } else {
            $categories = $this->categoryService->getCategoryTree();
        }

        $content = $this->renderCategoryList($categories, $isTrashView);
        return $this->adminLayout->wrap($content, $this->t('admin.categories.manage'));
    }

    /**
     * Přesune kategorii do koše
     *
     * @param int $id ID kategorie
     * @return void
     */
    public function delete(int $id): void
    {
        $this->requireAdmin();

        try {
            $success = $this->categoryService->deleteCategory($id);

            if ($success) {
                header("Location: {$this->baseUrl}admin/categories?deleted=1");
            } else {
                header("Location: {$this->baseUrl}admin/categories?error=1");
            }
        } catch (\InvalidArgumentException $e) {
			$this->logger->exception($e, "Nelze smazat výchozí kategorii.");
            header("Location: {$this->baseUrl}admin/categories?error=default_category");

        }
        exit;
    }

    /**
     * Obnoví kategorii z koše
     *
     * @param int $id ID kategorie
     * @return void
     */
    public function restore(int $id): void
    {
        $this->requireAdmin();
        try {
            $success = $this->categoryService->restoreCategory($id);

            if ($success) {
            	header("Location: {$this->baseUrl}admin/categories?show=trash&restored=1");
            } else {
            	header("Location: {$this->baseUrl}admin/categories?show=trash&error=1");
            }
        } catch (\InvalidArgumentException $e) {
			$this->logger->exception($e, "Nepodařilo se obnovit kategorii.");

        }
        exit;
    }

    /**
     * Trvale smaže kategorii z databáze
     *
     * @param int $id ID kategorie
     * @return void
     */
    public function permanentDelete(int $id): void
    {
        $this->requireAdmin();

        try {
            $success = $this->categoryService->permanentDeleteCategory($id);

            if ($success) {
                header("Location: {$this->baseUrl}admin/categories?show=trash&permanent_deleted=1");
            } else {
                header("Location: {$this->baseUrl}admin/categories?show=trash&error=1");
            }
        } catch (\InvalidArgumentException $e) {
			$this->logger->exception($e, "Nepodařilo se Smazat kategorii.");
            header("Location: {$this->baseUrl}admin/categories?show=trash&error=default_category");
        }
        exit;
    }

    /**
     * Vykreslí seznam kategorií s hierarchií nebo koš
     *
     * @param array $categories Strom kategorií nebo seznam smazaných
     * @param bool $isTrashView Zda se zobrazuje koš
     * @return string HTML obsah
     */
    private function renderCategoryList(array $categories, bool $isTrashView): string
    {
        $message = '';
        if (isset($_GET['restored'])) {
            $message = '<div class="alert alert-success">' . $this->t('admin.categories.messages.restored') . '</div>';
        } elseif (isset($_GET['deleted'])) {
            $message = '<div class="alert alert-success">' . $this->t('admin.categories.messages.deleted') . '</div>';
        } elseif (isset($_GET['permanent_deleted'])) {
            $message = '<div class="alert alert-success">' . $this->t('admin.categories.messages.permanent_deleted') . '</div>';
        } elseif (isset($_GET['error'])) {
            if ($_GET['error'] === 'default_category') {
                $message = '<div class="alert alert-error">' . $this->t('admin.categories.messages.cannot_delete_default') . '</div>';
            } else {
                $message = '<div class="alert alert-error">' . $this->t('admin.categories.messages.error') . '</div>';
            }
        }

        // Připrav proměnné pro tabs
        $activeClass = 'active';
        $mainTabClass = !$isTrashView ? $activeClass : '';
        $trashTabClass = $isTrashView ? $activeClass : '';

        $html = <<<HTML
<div class="page-header">
    <h1>{$this->t('admin.categories.manage')}</h1>
    <a href="{$this->baseUrl}admin/categories/create" class="btn btn-primary">
        ＋ {$this->t('admin.categories.create')}
    </a>
</div>

{$message}

<!-- TABS -->
<div class="tabs">
    <a href="{$this->baseUrl}admin/categories" class="tab {$mainTabClass}">
        {$this->t('admin.categories.active')}
    </a>
    <a href="{$this->baseUrl}admin/categories?show=trash" class="tab {$trashTabClass}">
        {$this->t('admin.categories.trash')}
    </a>
</div>

<div class="categories-container">
HTML;

        if (empty($categories)) {
            $html .= $this->renderEmptyState($isTrashView);
        } else {
            if ($isTrashView) {
                $html .= $this->renderTrashTable($categories);
            } else {
                $html .= $this->renderCategoryTree($categories);
            }
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Vykreslí strom kategorií s odsazením
     *
     * @param array $categories Strom kategorií
     * @return string HTML obsah
     */
    private function renderCategoryTree(array $categories): string
    {
        $html = <<<HTML
<div class="categories-table-container">
    <table class="categories-table">
        <thead>
            <tr>
                <th>{$this->t('admin.categories.table.name')}</th>
                <th>{$this->t('admin.categories.table.slug')}</th>
                <th>{$this->t('admin.categories.table.description')}</th>
                <th>{$this->t('admin.categories.table.parent')}</th>
                <th>{$this->t('admin.categories.table.actions')}</th>
            </tr>
        </thead>
        <tbody>
HTML;

        $html .= $this->renderCategoryTreeRecursive($categories);

        $html .= <<<HTML
        </tbody>
    </table>
</div>
HTML;

        return $html;
    }

    /**
     * Rekurzivně vykreslí strom kategorií
     *
     * @param array $categories Strom kategorií
     * @return string HTML obsah
     */
    private function renderCategoryTreeRecursive(array $categories): string
    {
        $html = '';

        foreach ($categories as $category) {
            $description = mb_substr($category['description'] ?? '', 0, 100);
            $indentation = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $category['depth'] ?? 0);

            // Získáme název rodičovské kategorie
            $parentName = '';
            if ($category['parent_id']) {
                $parent = $this->categoryService->getCategory($category['parent_id']);
                $parentName = $parent ? $parent['name'] : $this->t('admin.categories.unknown_parent');
            }

            $html .= <<<HTML
<tr>
    <td>
        <div style="padding-left: {$category['depth']}em;">
            {$indentation}{$this->escape($category['name'])}
        </div>
    </td>
    <td>{$this->escape($category['slug'])}</td>
    <td>{$this->escape($description)}...</td>
    <td>{$this->escape($parentName)}</td>
    <td>
        <div class="action-buttons">
            <a href="{$this->baseUrl}admin/categories/edit/{$category['id']}"
               class="btn btn-sm btn-primary"
               title="{$this->t('admin.categories.actions.edit')}">
                ✏️
            </a>
            <a href="{$this->baseUrl}admin/categories/delete/{$category['id']}"
               class="btn btn-sm btn-danger"
               onclick="return confirm('{$this->t('admin.categories.confirm.delete')} „{$this->escapeJs($category['name'])}‟?')"
               title="{$this->t('admin.categories.actions.delete')}">
                🗑️
            </a>
        </div>
    </td>
</tr>
HTML;

            // Rekurzivně vykreslíme děti
            if (!empty($category['children'])) {
                $html .= $this->renderCategoryTreeRecursive($category['children']);
            }
        }

        return $html;
    }

    /**
     * Vykreslí tabulku pro koš (smazané kategorie)
     *
     * @param array $categories Seznam smazaných kategorií
     * @return string HTML obsah
     */
    private function renderTrashTable(array $categories): string
    {
        $html = <<<HTML
<div class="categories-table-container">
    <table class="categories-table">
        <thead>
            <tr>
                <th>{$this->t('admin.categories.table.name')}</th>
                <th>{$this->t('admin.categories.table.slug')}</th>
                <th>{$this->t('admin.categories.table.deleted')}</th>
                <th>{$this->t('admin.categories.table.actions')}</th>
            </tr>
        </thead>
        <tbody>
HTML;

        foreach ($categories as $category) {
            $deletedAt = date('j. n. Y H:i', strtotime($category['deleted_at']));

            $html .= <<<HTML
<tr>
    <td>{$this->escape($category['name'])}</td>
    <td>{$this->escape($category['slug'])}</td>
    <td>{$deletedAt}</td>
    <td>
        <div class="action-buttons">
            <a href="{$this->baseUrl}admin/categories/restore/{$category['id']}"
               class="btn btn-sm btn-success"
               title="{$this->t('admin.categories.actions.restore')}">
                ↶
            </a>
            <a href="{$this->baseUrl}admin/categories/permanent-delete/{$category['id']}"
               class="btn btn-sm btn-danger"
               onclick="return confirm('{$this->t('admin.categories.confirm.permanent_delete')} „{$this->escapeJs($category['name'])}‟? Tato akce je nevratná!')"
               title="{$this->t('admin.categories.actions.permanent_delete')}">
                🗑️
            </a>
        </div>
    </td>
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
     * Vykreslí prázdný stav
     *
     * @param bool $isTrashView Zda se zobrazuje koš
     * @return string HTML obsah
     */
    private function renderEmptyState(bool $isTrashView): string
    {
        $emptyStateTitle = $isTrashView ?
            $this->t('admin.categories.messages.empty_trash') :
            $this->t('admin.categories.messages.empty_active');

        $emptyStateText = $isTrashView ?
            $this->t('admin.categories.messages.empty_text_trash') :
            $this->t('admin.categories.messages.empty_text_active');

        $emptyStateButton = $isTrashView ? '' :
            "<a href='{$this->baseUrl}admin/categories/create' class='btn btn-primary'>" .
            $this->t('admin.categories.messages.create_first') . "</a>";

        return <<<HTML
<div class="empty-state">
    <h3>{$emptyStateTitle}</h3>
    <p>{$emptyStateText}</p>
    {$emptyStateButton}
</div>
HTML;
    }

    // ... ostatní metody (create, store, edit, update, renderCategoryForm) zůstávají stejné ...
    // ... a také pomocné metody (requireAdmin, t, escape, escapeJs) ...

	/**
     * Zobrazí formulář pro vytvoření kategorie
     *
     * @return string HTML obsah formuláře
     */
	public function create(): string
	    {
	        $this->requireAdmin();
	        $csrfField = $this->csrf->getTokenField();
	        // Získáme kategorie ve formátu pro select s odsazením
	        $categories = $this->categoryService->getCategoriesForSelect();

	        $content = $this->renderCategoryForm([], $categories, $csrfField);
	        return $this->adminLayout->wrap($content, $this->t('admin.categories.create'));
	    }

    /**
     * Zpracuje vytvoření nové kategorie
     *
     * @return void
     */
    public function store(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}admin/categories/create");
            exit;
        }

        // Kontrola CSRF tokenu
        if (!$this->csrf->validateToken($_POST['csrf_token'] ?? '')) {
            header("Location: {$this->baseUrl}admin/categories/create?error=csrf");
            exit;
        }

        // Validace povinných polí
        if (empty($_POST['name'])) {
            header("Location: {$this->baseUrl}admin/categories/create?error=validation");
            exit;
        }

        try {
            $categoryId = $this->categoryService->createCategory([
                'name' => trim($_POST['name']),
                'description' => trim($_POST['description'] ?? ''),
                'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null
            ]);

            header("Location: {$this->baseUrl}admin/categories?created=1");
            exit;

        } catch (\Exception $e) {
            header("Location: {$this->baseUrl}admin/categories/create?error=1");
            exit;
        }
    }


    /**
     * Zobrazí formulář pro editaci kategorie
     *
     * @param int $id ID kategorie
     * @return string HTML obsah editačního formuláře
     */
	public function edit(int $id): string
	    {
	        $this->requireAdmin();
	        $category = $this->categoryService->getCategory($id);

	        if (!$category) {
	            header("Location: {$this->baseUrl}admin/categories?error=not_found");
	            exit;
	        }

	        $csrfField = $this->csrf->getTokenField();
	        // Získáme kategorie pro select, ale vynecháme aktuálně editovanou kategorii
	        // (aby nemohla být rodičem sama sobě)
	        $categories = $this->categoryService->getCategoriesForSelect($id);

	        $content = $this->renderCategoryForm($category, $categories, $csrfField);
	        return $this->adminLayout->wrap($content, $this->t('admin.categories.edit'));
	    }


    /**
     * Zpracuje aktualizaci kategorie
     *
     * @param int $id ID kategorie
     * @return void
     */
    public function update(int $id): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}admin/categories/edit/{$id}");
            exit;
        }

        try {
            $success = $this->categoryService->updateCategory($id, [
                'name' => $_POST['name'],
                'description' => $_POST['description'] ?? '',
                'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null
            ]);

            if ($success) {
                header("Location: {$this->baseUrl}admin/categories?updated=1");
            } else {
                header("Location: {$this->baseUrl}admin/categories/edit/{$id}?error=1");
            }
            exit;
        } catch (\Exception $e) {
            header("Location: {$this->baseUrl}admin/categories/edit/{$id}?error=1");
            exit;
        }
    }



    /**
     * Ověří, že uživatel je přihlášen jako admin
     *
     * @return void
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
     * @return string Přeložený text
     */
    private function t(string $key): string
    {
        return Config::text($key);
    }



    /**
     * Vykreslí formulář pro kategorii
     *
     * @param array $category Data kategorie (pro editaci)
     * @param array $categories Seznam všech kategorií (pro rodičovský výběr)
     * @param string $csrfField CSRF token field
     * @return string HTML obsah formuláře
     */
    private function renderCategoryForm(array $category, array $categories, string $csrfField): string
    {
        $isEdit = !empty($category);
        $action = $isEdit
            ? "{$this->baseUrl}admin/categories/update/{$category['id']}"
            : "{$this->baseUrl}admin/categories/store";

        $name = $this->escape($category['name'] ?? '');
        $description = $this->escape($category['description'] ?? '');
        $parentId = $category['parent_id'] ?? '';

        $parentOptions = '<option value="">' . $this->t('admin.categories.form.no_parent') . '</option>';
        foreach ($categories as $id => $nameWithIndent) {
            $selected = $parentId == $id ? 'selected' : '';
            $parentOptions .= "<option value=\"{$id}\" {$selected}>{$this->escape($nameWithIndent)}</option>";
        }

        $message = '';
        if (isset($_GET['error'])) {
            $message = '<div class="alert alert-error">' . $this->t('admin.categories.messages.error') . '</div>';
        }

        return <<<HTML
<div class="page-header">
    <h1>{$this->t($isEdit ? 'admin.categories.edit' : 'admin.categories.create')}</h1>
</div>

{$message}

<form method="POST" action="{$action}" class="category-form">
    <div class="form-group">
        <label for="name">{$this->t('admin.categories.form.name')}:</label>
        <input type="text" id="name" name="name" value="{$name}" required class="form-control">
    </div>

    <div class="form-group">
        <label for="description">{$this->t('admin.categories.form.description')}:</label>
        <textarea id="description" name="description" class="form-control">{$description}</textarea>
    </div>

    <div class="form-group">
        <label for="parent_id">{$this->t('admin.categories.form.parent')}:</label>
        <select id="parent_id" name="parent_id" class="form-control">
            {$parentOptions}
        </select>
        <small class="form-text">{$this->t('admin.categories.form.parent_help')}</small>
    </div>

    <div class="form-actions">
        {$csrfField}
        <button type="submit" class="btn btn-primary">
            {$this->t($isEdit ? 'admin.categories.form.save_button' : 'admin.categories.form.create_button')}
        </button>
        <a href="{$this->baseUrl}admin/categories" class="btn btn-secondary">
            {$this->t('admin.categories.form.cancel')}
        </a>
    </div>
</form>
HTML;
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
}