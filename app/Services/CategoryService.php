<?php
// app/Services/CategoryService.php
declare(strict_types=1);

namespace App\Services;

use App\Database\DatabaseConnection;
use App\Core\Config;
use App\Logger\Logger;

/**
 * Služba pro správu kategorií článků
 *
 * @package App\Services
 * @author KRS3
 * @version 3.1
 */
class CategoryService
{
    private Logger $logger;

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
        $this->logger = Logger::getInstance();

        // Načteme ID výchozí kategorie z konfigurace
        $this->defaultCategoryId = Config::get('categories.default_category_id', 1);

        $this->logger->debug('CategoryService initialized', [
            'default_category_id' => $this->defaultCategoryId
        ]);
    }

    /**
     * Získá všechny aktivní kategorie (bez smazaných)
     *
     * @return array Seznam aktivních kategorií
     */
    public function getAllCategories(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT * FROM categories WHERE deleted_at IS NULL ORDER BY name ASC"
            );
            $categories = $stmt->fetchAll();

            $this->logger->debug('Retrieved all active categories', [
                'count' => count($categories)
            ]);

            return $categories;
        } catch (\Exception $e) {
            $this->logger->error('Failed to retrieve categories', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Získá kategorii podle ID (pouze aktivní)
     *
     * @param int $id ID kategorie
     * @return array|null Data kategorie nebo null pokud neexistuje nebo je smazaná
     */
    public function getCategory(int $id): ?array
    {
        try {
            $stmt = $this->db->query(
                "SELECT * FROM categories WHERE id = ? AND deleted_at IS NULL",
                [$id]
            );
            $category = $stmt->fetch() ?: null;

            $this->logger->debug('Category lookup', [
                'category_id' => $id,
                'found' => $category !== null
            ]);

            return $category;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get category', [
                'category_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Získá smazané kategorie (pouze v koši)
     *
     * @return array Seznam smazaných kategorií
     */
    public function getDeletedCategories(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT * FROM categories WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC"
            );
            $categories = $stmt->fetchAll();

            $this->logger->debug('Retrieved deleted categories', [
                'count' => count($categories)
            ]);

            return $categories;
        } catch (\Exception $e) {
            $this->logger->error('Failed to retrieve deleted categories', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Přesune kategorii do koše (soft delete) a převede její potomky pod výchozí kategorii
     *
     * @param int $id ID kategorie
     * @return bool True pokud byla kategorie úspěšně přesunuta do koše
     * @throws \InvalidArgumentException Pokud se pokusíme smazat výchozí kategorii
     */
    public function deleteCategory(int $id): bool
    {
        // Zabráníme smazání výchozí kategorie
        if ($id === $this->defaultCategoryId) {
            $this->logger->warning('Attempt to delete default category blocked', [
                'category_id' => $id
            ]);
            throw new \InvalidArgumentException('Nelze smazat výchozí kategorii');
        }

        $category = $this->getCategory($id);
        if (!$category) {
            $this->logger->warning('Attempt to delete non-existent category', [
                'category_id' => $id
            ]);
            return false;
        }

        $this->db->beginTransaction();

        try {
            // Přesuneme potomky této kategorie pod výchozí kategorii
            $movedChildren = $this->moveChildrenToParent($id, $this->defaultCategoryId);

            // Přesuneme kategorii do koše
            $result = $this->db->execute(
                "UPDATE categories SET deleted_at = NOW() WHERE id = ?",
                [$id]
            );

            $this->db->commit();

            $this->logger->info('Category moved to trash', [
                'category_id' => $id,
                'category_name' => $category['name'],
                'children_moved' => $movedChildren
            ]);

            return $result > 0;

        } catch (\Exception $e) {
            $this->db->rollBack();

            $this->logger->error('Failed to delete category', [
                'category_id' => $id,
                'error' => $e->getMessage()
            ]);

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
        try {
            $result = $this->db->execute(
                "UPDATE categories SET deleted_at = NULL WHERE id = ?",
                [$id]
            );

            if ($result > 0) {
                $category = $this->getCategory($id);
                $this->logger->info('Category restored from trash', [
                    'category_id' => $id,
                    'category_name' => $category['name'] ?? 'unknown'
                ]);
            }

            return $result > 0;
        } catch (\Exception $e) {
            $this->logger->error('Failed to restore category', [
                'category_id' => $id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Trvale smaže kategorii z databáze
     *
     * @param int $id ID kategorie
     * @return bool True pokud byla kategorie úspěšně trvale smazána
     * @throws \InvalidArgumentException Pokud se pokusíme smazat výchozí kategorii
     */
    public function permanentDeleteCategory(int $id): bool
    {
        if ($id === $this->defaultCategoryId) {
            $this->logger->warning('Attempt to permanently delete default category blocked', [
                'category_id' => $id
            ]);
            throw new \InvalidArgumentException('Nelze smazat výchozí kategorii');
        }

        $this->db->beginTransaction();

        try {
            // Přesuneme potomky pod výchozí kategorii
            $movedChildren = $this->moveChildrenToParent($id, $this->defaultCategoryId);

            // Smažeme vazby na články
            $deletedLinks = $this->db->execute(
                "DELETE FROM article_categories WHERE category_id = ?",
                [$id]
            );

            // Smažeme kategorii z databáze
            $result = $this->db->execute(
                "DELETE FROM categories WHERE id = ?",
                [$id]
            );

            $this->db->commit();

            $this->logger->critical('Category permanently deleted', [
                'category_id' => $id,
                'children_moved' => $movedChildren,
                'article_links_removed' => $deletedLinks
            ]);

            return $result > 0;

        } catch (\Exception $e) {
            $this->db->rollBack();

            $this->logger->error('Failed to permanently delete category', [
                'category_id' => $id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Přesune potomky kategorie pod jiného rodiče
     *
     * @param int $categoryId ID mazané kategorie
     * @param int|null $newParentId Nový rodič pro potomky
     * @return int Počet přesunutých potomků
     */
    private function moveChildrenToParent(int $categoryId, ?int $newParentId = null): int
    {
        try {
            $actualParentId = ($newParentId === 0) ? null : $newParentId;

            $result = $this->db->execute(
                "UPDATE categories SET parent_id = ? WHERE parent_id = ? AND deleted_at IS NULL",
                [$actualParentId, $categoryId]
            );

            if ($result > 0) {
                $this->logger->debug('Category children moved', [
                    'from_parent_id' => $categoryId,
                    'to_parent_id' => $actualParentId,
                    'children_count' => $result
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Failed to move category children', [
                'category_id' => $categoryId,
                'new_parent_id' => $newParentId,
                'error' => $e->getMessage()
            ]);
            return 0;
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
        $tree = $this->buildTree($categories);

        $this->logger->debug('Category tree built', [
            'total_categories' => count($categories),
            'root_categories' => count($tree)
        ]);

        return $tree;
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
     * Získá kategorie ve formátu pro select dropdown s odsazením podle hierarchie
     *
     * @param int|null $excludeId ID kategorie, kterou vynechat
     * @return array Kategorie připravené pro select (id => name s odsazením)
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

                $prefix = str_repeat('— ', $level);
                $result[$category['id']] = $prefix . $category['name'];

                if (!empty($category['children'])) {
                    $flatten($category['children'], $level + 1);
                }
            }
        };

        $flatten($categories);

        $this->logger->debug('Categories prepared for select', [
            'excluded_id' => $excludeId,
            'count' => count($result)
        ]);

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
        $slug = preg_replace('/-+/', '-', trim($slug, '-'));

        $this->logger->debug('Slug generated', [
            'name' => $name,
            'slug' => $slug
        ]);

        return $slug;
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
            $this->logger->warning('Attempt to create category without name');
            throw new \InvalidArgumentException('Název kategorie je povinný');
        }

        try {
            $slug = $this->generateSlug($data['name']);

            $this->db->execute(
                "INSERT INTO categories (name, slug, description, parent_id)
                 VALUES (?, ?, ?, ?)",
                [
                    $data['name'],
                    $slug,
                    $data['description'] ?? '',
                    $data['parent_id'] ?? null
                ]
            );

            $categoryId = $this->db->getLastInsertId();

            $this->logger->info('Category created', [
                'category_id' => $categoryId,
                'name' => $data['name'],
                'slug' => $slug,
                'parent_id' => $data['parent_id'] ?? null
            ]);

            return $categoryId;
        } catch (\Exception $e) {
            $this->logger->error('Failed to create category', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Aktualizuje existující kategorii
     *
     * @param int $id ID kategorie
     * @param array $data Nová data kategorie
     * @return bool True pokud byla kategorie úspěšně aktualizována
     * @throws \InvalidArgumentException Pokud chybí povinná data
     */
    public function updateCategory(int $id, array $data): bool
    {
        if (empty($data['name'])) {
            $this->logger->warning('Attempt to update category without name', [
                'category_id' => $id
            ]);
            throw new \InvalidArgumentException('Název kategorie je povinný');
        }

        try {
            $slug = $data['slug'] ?? $this->generateSlug($data['name']);

            $result = $this->db->execute(
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

            if ($result > 0) {
                $this->logger->info('Category updated', [
                    'category_id' => $id,
                    'name' => $data['name'],
                    'slug' => $slug,
                    'parent_id' => $data['parent_id'] ?? null
                ]);
            }

            return $result > 0;
        } catch (\Exception $e) {
            $this->logger->error('Failed to update category', [
                'category_id' => $id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Získá kategorie pro konkrétní článek
     *
     * @param int $articleId ID článku
     * @return array Seznam kategorií článku
     */
    public function getCategoriesForArticle(int $articleId): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT c.*
                 FROM categories c
                 INNER JOIN article_categories ac ON c.id = ac.category_id
                 WHERE ac.article_id = ? AND c.deleted_at IS NULL
                 ORDER BY c.name",
                [$articleId]
            );

            $categories = $stmt->fetchAll();

            $this->logger->debug('Retrieved categories for article', [
                'article_id' => $articleId,
                'count' => count($categories)
            ]);

            return $categories;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get categories for article', [
                'article_id' => $articleId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
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
            // Odstraníme stávající přiřazení
            $removed = $this->db->execute(
                "DELETE FROM article_categories WHERE article_id = ?",
                [$articleId]
            );

            // Přidáme nová přiřazení
            $added = 0;
            foreach ($categoryIds as $categoryId) {
                if (!empty($categoryId)) {
                    $this->db->execute(
                        "INSERT INTO article_categories (article_id, category_id) VALUES (?, ?)",
                        [$articleId, (int)$categoryId]
                    );
                    $added++;
                }
            }

            $this->logger->info('Categories assigned to article', [
                'article_id' => $articleId,
                'removed_count' => $removed,
                'added_count' => $added,
                'category_ids' => $categoryIds
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to assign categories to article', [
                'article_id' => $articleId,
                'category_ids' => $categoryIds,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Získá kategorii podle slugu
     *
     * @param string $slug Slug kategorie
     * @return array|null Data kategorie nebo null
     */
    public function getCategoryBySlug(string $slug): ?array
    {
        try {
            $stmt = $this->db->query(
                "SELECT * FROM categories WHERE slug = ? AND deleted_at IS NULL",
                [$slug]
            );

            $category = $stmt->fetch() ?: null;

            $this->logger->debug('Category lookup by slug', [
                'slug' => $slug,
                'found' => $category !== null
            ]);

            return $category;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get category by slug', [
                'slug' => $slug,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Získá počet publikovaných článků v kategorii
     *
     * @param int $categoryId ID kategorie
     * @return int Počet článků
     */
    public function getArticleCount(int $categoryId): int
    {
        try {
            $stmt = $this->db->query(
                "SELECT COUNT(DISTINCT a.id) as article_count
                 FROM articles a
                 INNER JOIN article_categories ac ON a.id = ac.article_id
                 WHERE ac.category_id = ?
                 AND a.status = 'published'
                 AND a.published_at <= NOW()
                 AND a.deleted_at IS NULL",
                [$categoryId]
            );

            $result = $stmt->fetch();
            $count = (int)($result['article_count'] ?? 0);

            $this->logger->debug('Article count retrieved for category', [
                'category_id' => $categoryId,
                'count' => $count
            ]);

            return $count;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get article count for category', [
                'category_id' => $categoryId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Získá breadcrumb navigaci pro kategorii
     *
     * @param int $categoryId ID kategorie
     * @return array Pole s breadcrumb položkami
     */
    public function getCategoryBreadcrumb(int $categoryId): array
    {
        $breadcrumbs = [];
        $currentCategory = $this->getCategory($categoryId);

        if (!$currentCategory) {
            return $breadcrumbs;
        }

        $breadcrumbs[] = $currentCategory;

        $parentId = $currentCategory['parent_id'];
        while ($parentId) {
            $parentCategory = $this->getCategory($parentId);
            if ($parentCategory) {
                array_unshift($breadcrumbs, $parentCategory);
                $parentId = $parentCategory['parent_id'];
            } else {
                break;
            }
        }

        return $breadcrumbs;
    }

    /**
     * Získá populární kategorie (s nejvíce články)
     *
     * @param int $limit Počet kategorií
     * @return array Seznam populárních kategorií
     */
    public function getPopularCategories(int $limit = 5): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT c.*, COUNT(ac.article_id) as article_count
                 FROM categories c
                 LEFT JOIN article_categories ac ON c.id = ac.category_id
                 LEFT JOIN articles a ON ac.article_id = a.id
                    AND a.status = 'published'
                    AND a.published_at <= NOW()
                    AND a.deleted_at IS NULL
                 WHERE c.deleted_at IS NULL
                 GROUP BY c.id
                 ORDER BY article_count DESC, c.name ASC
                 LIMIT ?",
                [$limit]
            );

            $categories = $stmt->fetchAll();

            $this->logger->debug('Popular categories retrieved', [
                'limit' => $limit,
                'count' => count($categories)
            ]);

            return $categories;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get popular categories', [
                'limit' => $limit,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}