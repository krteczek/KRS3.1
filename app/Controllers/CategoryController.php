<?php
// app/Controllers/CategoryController.php
declare(strict_types=1);

namespace App\Controllers;

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
 * @version 1.0
 */
class CategoryController
{
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
    ) {}

	/**
     * Zobrazí seznam kategorií
     *
     * @return string HTML obsah seznamu kategorií
     */
    public function index(): string
    {
        $this->requireAdmin();
        $categories = $this->categoryService->getAllCategories();

        $content = $this->renderCategoryList($categories);
        return $this->adminLayout->wrap($content, $this->t('admin.categories.manage'));
    }

    /**
     * Zobrazí formulář pro vytvoření kategorie
     *
     * @return string HTML obsah formuláře
     */
    public function create(): string
    {
        $this->requireAdmin();
        $csrfField = $this->csrf->getTokenField();
        $categories = $this->categoryService->getAllCategories();

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

        try {
            $categoryId = $this->categoryService->createCategory([
                'name' => $_POST['name'],
                'description' => $_POST['description'] ?? '',
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
        $categories = $this->categoryService->getAllCategories();

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
     * Smaže kategorii
     *
     * @param int $id ID kategorie
     * @return void
     */
    public function delete(int $id): void
    {
        $this->requireAdmin();

        $success = $this->categoryService->deleteCategory($id);

        if ($success) {
            header("Location: {$this->baseUrl}admin/categories?deleted=1");
        } else {
            header("Location: {$this->baseUrl}admin/categories?error=1");
        }
        exit;
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
     * Vykreslí seznam kategorií
     *
     * @param array $categories Seznam kategorií
     * @return string HTML obsah
     */
    private function renderCategoryList(array $categories): string
    {
        $message = '';
        if (isset($_GET['created'])) {
            $message = '<div class="alert alert-success">' . $this->t('admin.categories.messages.created') . '</div>';
        } elseif (isset($_GET['updated'])) {
            $message = '<div class="alert alert-success">' . $this->t('admin.categories.messages.updated') . '</div>';
        } elseif (isset($_GET['deleted'])) {
            $message = '<div class="alert alert-success">' . $this->t('admin.categories.messages.deleted') . '</div>';
        } elseif (isset($_GET['error'])) {
            $message = '<div class="alert alert-error">' . $this->t('admin.categories.messages.error') . '</div>';
        }

        $html = <<<HTML
<div class="page-header">
    <h1>{$this->t('admin.categories.manage')}</h1>
    <a href="{$this->baseUrl}admin/categories/create" class="btn btn-primary">
        ＋ {$this->t('admin.categories.create')}
    </a>
</div>

{$message}

<div class="categories-table-container">
    <table class="categories-table">
        <thead>
            <tr>
                <th>{$this->t('admin.categories.table.name')}</th>
                <th>{$this->t('admin.categories.table.slug')}</th>
                <th>{$this->t('admin.categories.table.description')}</th>
                <th>{$this->t('admin.categories.table.actions')}</th>
            </tr>
        </thead>
        <tbody>
HTML;

        if (empty($categories)) {
            $html .= <<<HTML
<tr>
    <td colspan="4" class="text-center">
        <div class="empty-state">
            <h3>{$this->t('admin.categories.messages.empty')}</h3>
            <p>{$this->t('admin.categories.messages.empty_text')}</p>
            <a href="{$this->baseUrl}admin/categories/create" class="btn btn-primary">
                {$this->t('admin.categories.messages.create_first')}
            </a>
        </div>
    </td>
</tr>
HTML;
        } else {
            foreach ($categories as $category) {
                $description = mb_substr($category['description'] ?? '', 0, 100);
                $html .= <<<HTML
<tr>
    <td>{$this->escape($category['name'])}</td>
    <td>{$this->escape($category['slug'])}</td>
    <td>{$this->escape($description)}...</td>
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
            }
        }

        $html .= <<<HTML
        </tbody>
    </table>
</div>
HTML;

        return $html;
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
            : "{$this->baseUrl}admin/categories/create";

        $name = $this->escape($category['name'] ?? '');
        $description = $this->escape($category['description'] ?? '');
        $parentId = $category['parent_id'] ?? '';

        $parentOptions = '<option value="">' . $this->t('admin.categories.form.no_parent') . '</option>';
        foreach ($categories as $cat) {
            if ($isEdit && $cat['id'] === $category['id']) {
                continue; // Nemůže být rodičem sama sobě
            }
            $selected = $parentId == $cat['id'] ? 'selected' : '';
            $parentOptions .= "<option value=\"{$cat['id']}\" {$selected}>{$this->escape($cat['name'])}</option>";
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