<?php
// app/Services/CategoryService.php
declare(strict_types=1);

namespace App\Services;

use App\Database\DatabaseConnection;
use App\Core\Config;

/**
 * Služba pro správu kategorií článků
 *
 * @package App\Services
 * @author KRS3
 * @version 3.0
 */
class CategoryService
{
    /**
     * @var int ID výchozí kategorie pro sirotky
     */
    private int $defaultCategoryId;

    /**
     * Konstruktor
     *
     * @param DatabaseConnection $db Databázové připojení
     */
    public function __construct(private DatabaseConnection $db)
    {
        // Načteme ID výchozí kategorie z konfigurace
        $this->defaultCategoryId = Config::get('categories.default_category_id', 1);
    }

    /**
     * Získá všechny aktivní kategorie (bez smazaných)
     *
     * @return array Seznam aktivních kategorií
     */
    public function getAllCategories(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM categories WHERE deleted_at IS NULL ORDER BY name ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Získá kategorii podle ID (pouze aktivní)
     *
     * @param int $id ID kategorie
     * @return array|null Data kategorie nebo null pokud neexistuje nebo je smazaná
     */
    public function getCategory(int $id): ?array
    {
        $stmt = $this->db->query(
            "SELECT * FROM categories WHERE id = ? AND deleted_at IS NULL",
            [$id]
        );
        return $stmt->fetch() ?: null;
    }

    /**
     * Získá smazané kategorie (pouze v koši)
     *
     * @return array Seznam smazaných kategorií
     */
    public function getDeletedCategories(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM categories WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Přesune kategorii do koše (soft delete) a převede její potomky pod výchozí kategorii
     *
     * @param int $id ID kategorie
     * @return bool True pokud byla kategorie úspěšně přesunuta do koše
     */
    public function deleteCategory(int $id): bool
    {
        // Zabráníme smazání výchozí kategorie
        if ($id === $this->defaultCategoryId) {
            throw new \InvalidArgumentException('Nelze smazat výchozí kategorii');
        }

        // Začneme transakci pro bezpečnost
        $this->db->beginTransaction();

        try {
            // 1. Přesuneme potomky této kategorie pod výchozí kategorii
            $this->moveChildrenToParent($id, $this->defaultCategoryId);

            // 2. Přesuneme kategorii do koše
            $result = $this->db->query(
                "UPDATE categories SET deleted_at = NOW() WHERE id = ?",
                [$id]
            );

            $this->db->commit();
            return $result->rowCount() > 0;

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Chyba při mazání kategorie: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obnoví kategorii z koše
     *
     * @param int $id ID kategorie
     * @return bool True pokud byla kategorie úspěšně obnovena
     */
    public function restoreCategory(int $id): bool
    {
        $result = $this->db->query(
            "UPDATE categories SET deleted_at = NULL WHERE id = ?",
            [$id]
        );

        return $result->rowCount() > 0;
    }

    /**
     * Trvale smaže kategorii z databáze
     *
     * @param int $id ID kategorie
     * @return bool True pokud byla kategorie úspěšně trvale smazána
     */
    public function permanentDeleteCategory(int $id): bool
    {
        // Zabráníme smazání výchozí kategorie
        if ($id === $this->defaultCategoryId) {
            throw new \InvalidArgumentException('Nelze smazat výchozí kategorii');
        }

        // Začneme transakci pro bezpečnost
        $this->db->beginTransaction();

        try {
            // 1. Přesuneme potomky pod výchozí kategorii
            $this->moveChildrenToParent($id, $this->defaultCategoryId);

            // 2. Smažeme vazby na články
            $this->db->query(
                "DELETE FROM article_categories WHERE category_id = ?",
                [$id]
            );

            // 3. Smažeme kategorii z databáze
            $result = $this->db->query(
                "DELETE FROM categories WHERE id = ?",
                [$id]
            );

            $this->db->commit();
            return $result->rowCount() > 0;

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Chyba při trvalém mazání kategorie: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Přesune potomky kategorie pod jiného rodiče
     *
     * @param int $categoryId ID mazané kategorie
     * @param int|null $newParentId Nový rodič pro potomky
     * @return bool True pokud se přesun podařil
     */
    public function moveChildrenToParent(int $categoryId, ?int $newParentId = null): bool
    {
        try {
            // Pokud je newParentId 0, nastavíme na NULL (kořenová úroveň)
            $actualParentId = ($newParentId === 0) ? null : $newParentId;

            $this->db->query(
                "UPDATE categories SET parent_id = ? WHERE parent_id = ? AND deleted_at IS NULL",
                [$actualParentId, $categoryId]
            );
            return true;
        } catch (\Exception $e) {
            error_log("Chyba při přesunu potomků: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Získá kategorie ve stromové struktuře (pouze aktivní)
     *
     * @return array Stromová struktura kategorií
     */
    public function getCategoryTree(): array
    {
        $categories = $this->getAllCategories();
        return $this->buildTree($categories);
    }

    /**
     * Sestaví stromovou strukturu z plochého pole kategorií
     *
     * @param array $categories Ploché pole kategorií
     * @param int|null $parentId ID nadřazené kategorie
     * @param int $depth Hloubka zanoření
     * @return array Stromová struktura
     */
    private function buildTree(array $categories, ?int $parentId = null, int $depth = 0): array
    {
        $branch = [];

        foreach ($categories as $category) {
            if ($category['parent_id'] == $parentId) {
                $category['depth'] = $depth;
                $children = $this->buildTree($categories, $category['id'], $depth + 1);
                if ($children) {
                    $category['children'] = $children;
                }
                $branch[] = $category;
            }
        }

        return $branch;
    }

    /**
     * Získá kategorie ve formátu pro výběr (s odsazením podle hierarchie)
     *
     * @param int|null $excludeId ID kategorie, kterou vynechat
     * @return array Kategorie připravené pro výběr v selectu
     */
	public function getCategoriesForSelect(int $excludeId = null): array
	{
	    $categories = $this->getCategoryTree();
	    $result = [];

	    $flatten = function($categories, $level = 0) use (&$flatten, &$result, $excludeId) {
	        foreach ($categories as $category) {
	            if ($excludeId && $category['id'] == $excludeId) {
	                continue;
	            }

	            $prefix = str_repeat('--', $level);
	            $result[$category['id']] = $prefix . ' ' . $category['name'];

	            if (!empty($category['children'])) {
	                $flatten($category['children'], $level + 1);
	            }
	        }
	    };

	    $flatten($categories);
	    return $result;
	}


    /**
     * Zploští stromovou strukturu pro výběr v selectu
     *
     * @param array $tree Stromová struktura
     * @param int $depth Hloubka
     * @param int|null $excludeId ID kategorie, kterou vynechat
     * @return array Zploštěné pole s odsazením
     */
    private function flattenTreeForSelect(array $tree, int $depth = 0, ?int $excludeId = null): array
    {
        $result = [];
        $prefix = str_repeat('--', $depth);

        foreach ($tree as $category) {
            // Vynecháme kategorii, která má být vyloučena
            if ($excludeId !== null && $category['id'] === $excludeId) {
                continue;
            }

            $result[$category['id']] = ($prefix ? $prefix . ' ' : '') . $category['name'];

            if (!empty($category['children'])) {
                $result += $this->flattenTreeForSelect($category['children'], $depth + 1, $excludeId);
            }
        }

        return $result;
    }


    /**
     * Vygeneruje slug z názvu kategorie
     *
     * @param string $name Název kategorie
     * @return string Vygenerovaný slug
     */
    private function generateSlug(string $name): string
    {
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $name);
        $slug = preg_replace('/[^a-zA-Z0-9 -]/', '', $slug);
        $slug = strtolower(str_replace(' ', '-', $slug));
        return preg_replace('/-+/', '-', $slug);
    }


    /**
     * Vytvoří novou kategorii
     *
     * @param array $data Data kategorie
     * @return int ID vytvořené kategorie
     * @throws \InvalidArgumentException Pokud chybí povinná data
     */
    public function createCategory(array $data): int
    {
        if (empty($data['name'])) {
            throw new \InvalidArgumentException('Název kategorie je povinný');
        }

        $slug = $this->generateSlug($data['name']);

        $this->db->query(
            "INSERT INTO categories (name, slug, description, parent_id)
             VALUES (?, ?, ?, ?)",
            [
                $data['name'],
                $slug,
                $data['description'] ?? '',
                $data['parent_id'] ?? null
            ]
        );

        return $this->db->getLastInsertId();
    }

	/**
     * Aktualizuje existující kategorii
     *
     * @param int $id ID kategorie
     * @param array $data Nová data kategorie
     * @return bool True pokud byla kategorie úspěšně aktualizována
     */
    public function updateCategory(int $id, array $data): bool
    {
        if (empty($data['name'])) {
            throw new \InvalidArgumentException('Název kategorie je povinný');
        }

        $slug = $data['slug'] ?? $this->generateSlug($data['name']);

        $result = $this->db->query(
            "UPDATE categories SET
                name = ?, slug = ?, description = ?, parent_id = ?
             WHERE id = ?",
            [
                $data['name'],
                $slug,
                $data['description'] ?? '',
                $data['parent_id'] ?? null,
                $id
            ]
        );

        return $result->rowCount() > 0;
    }

	/**
	 * Získá všechny kategorie pro dropdown
	 *
	 * @return array
	 */
	public function getAllCategoriesForDropdown(): array
	{
	    $categories = $this->getAllCategories();
	    $result = [];

	    foreach ($categories as $category) {
	        $result[] = [
	            'id' => $category['id'],
	            'name' => $category['name'],
	            'level' => $this->calculateCategoryLevel($category['id'])
	        ];
	    }

	    return $result;
	}


	/**
	 * Vypočítá úroveň kategorie v hierarchii
	 *
	 * @param int $categoryId
	 * @param int $level
	 * @return int
	 */
	private function calculateCategoryLevel(int $categoryId, int $level = 0): int
	{
	    $category = $this->getCategory($categoryId);
	    if ($category && $category['parent_id']) {
	        return $this->calculateCategoryLevel($category['parent_id'], $level + 1);
	    }
	    return $level;
	}


    /**
     * Získá kategorie pro konkrétní článek
     *
     * @param int $articleId ID článku
     * @return array Seznam kategorií článku
     */
	public function getCategoriesForArticle(int $articleId): array
	{
	    $stmt = $this->db->query(
	        "SELECT c.*
	         FROM categories c
	         INNER JOIN article_categories ac ON c.id = ac.category_id
	         WHERE ac.article_id = ? AND c.deleted_at IS NULL
	         ORDER BY c.name",
	        [$articleId]
	    );

	    return $stmt->fetchAll();
	}


    /**
     * Přiřadí kategorie k článku
     *
     * @param int $articleId ID článku
     * @param array $categoryIds Pole ID kategorií
     * @return bool True pokud byly kategorie úspěšně přiřazeny
     */
	public function assignCategoriesToArticle(int $articleId, array $categoryIds): bool
	{
	    try {
	        // Nejprve odstraníme stávající přiřazení
	        $this->db->query(
	            "DELETE FROM article_categories WHERE article_id = ?",
	            [$articleId]
	        );

	        // Pak přidáme nová přiřazení
	        foreach ($categoryIds as $categoryId) {
	            if (!empty($categoryId)) {
	                $this->db->query(
	                    "INSERT INTO article_categories (article_id, category_id) VALUES (?, ?)",
	                    [$articleId, (int)$categoryId]
	                );
	            }
	        }

	        return true;
	    } catch (\Exception $e) {
	        error_log("Error assigning categories: " . $e->getMessage());
	        return false;
	    }
	}

    /**
     * Zkontroluje, zda je kategorie potomkem jiné kategorie
     *
     * @param int $categoryId ID kategorie
     * @param int $potentialParentId ID potenciálního rodiče
     * @return bool True pokud je potomkem
     */
    public function isDescendantOf(int $categoryId, int $potentialParentId): bool
    {
        $category = $this->getCategory($categoryId);
        if (!$category) {
            return false;
        }

        // Procházíme rodiče až ke kořeni
        while ($category && $category['parent_id'] !== null) {
            if ($category['parent_id'] === $potentialParentId) {
                return true;
            }
            $category = $this->getCategory($category['parent_id']);
        }

        return false;
    }

}