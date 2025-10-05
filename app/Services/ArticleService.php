<?php
//app/Services/ArticleService.php
declare(strict_types=1);

namespace App\Services;

use App\Database\DatabaseConnection;

/**
 * Služba pro správu článků
 *
 * Poskytuje kompletní CRUD operace pro články včetně vytváření, editace,
 * mazání, obnovování a práce s různými stavy článků. Implementuje soft delete
 * a generování SEO-friendly slugů.
 *
 * @package App\Services
 * @author KRS3
 * @version 2.0
 */
class ArticleService
{
    /**
     * Konstruktor
     *
     * @param DatabaseConnection $db Databázové připojení
     */
    public function __construct(private DatabaseConnection $db) {}

    /**
     * Vytvoří nový článek
     *
     * @param array $data Data článku
     * @return int ID vytvořeného článku
     * @throws \InvalidArgumentException Pokud chybí povinná data
     */
    public function createArticle(array $data): int
    {
        // Validace povinných polí
        if (empty($data['title']) || empty($data['content']) || empty($data['author_id'])) {
            throw new \InvalidArgumentException('Chybí povinná data pro vytvoření článku');
        }

        $slug = $this->generateSlug($data['title']);

        $this->db->query(
            "INSERT INTO articles (title, slug, content, excerpt, author_id, status, published_at)
             VALUES (:title, :slug, :content, :excerpt, :author_id, :status, :published_at)",
            [
                ':title' => $data['title'],
                ':slug' => $slug,
                ':content' => $data['content'],
                ':excerpt' => $data['excerpt'] ?? '',
                ':author_id' => $data['author_id'],
                ':status' => $data['status'] ?? 'draft',
                ':published_at' => $data['status'] === 'published' ? date('Y-m-d H:i:s') : null
            ]
        );

        return $this->db->getLastInsertId();
    }

    /**
     * Aktualizuje existující článek
     *
     * @param int $id ID článku
     * @param array $data Nová data článku
     * @return bool True pokud byl článek úspěšně aktualizován
     */
    public function updateArticle(int $id, array $data): bool
    {
        $article = $this->getArticle($id);
        if (!$article) {
            return false;
        }

        $slug = $data['slug'] ?? $this->generateSlug($data['title']);

        $result = $this->db->query(
            "UPDATE articles SET
                title = :title,
                slug = :slug,
                content = :content,
                excerpt = :excerpt,
                status = :status,
                published_at = :published_at,
                updated_at = NOW()
             WHERE id = :id",
            [
                ':id' => $id,
                ':title' => $data['title'],
                ':slug' => $slug,
                ':content' => $data['content'],
                ':excerpt' => $data['excerpt'] ?? '',
                ':status' => $data['status'] ?? 'draft',
                ':published_at' => $data['status'] === 'published' ? date('Y-m-d H:i:s') : null
            ]
        );

        return $result->rowCount() > 0;
    }

    /**
     * Získá článek podle ID
     *
     * @param int $id ID článku
     * @return array|null Data článku nebo null pokud neexistuje
     */
    public function getArticle(int $id): ?array
    {
        $stmt = $this->db->query(
            "SELECT a.*, u.username as author_name
             FROM articles a
             LEFT JOIN users u ON a.author_id = u.id
             WHERE a.id = :id AND a.deleted_at IS NULL",
            [':id' => $id]
        );

        return $stmt->fetch() ?: null;
    }

    /**
     * Získá všechny aktivní články
     *
     * @return array Seznam článků
     */
    public function getAllArticles(): array
    {
        $stmt = $this->db->query(
            "SELECT a.*, u.username as author_name
             FROM articles a
             LEFT JOIN users u ON a.author_id = u.id
             WHERE a.deleted_at IS NULL
             ORDER BY a.created_at DESC"
        );

        return $stmt->fetchAll();
    }

    /**
     * Smaže článek (soft delete)
     *
     * @param int $id ID článku
     * @return bool True pokud byl článek úspěšně smazán
     */
    public function deleteArticle(int $id): bool
    {
        $result = $this->db->query(
            "UPDATE articles SET deleted_at = NOW() WHERE id = :id",
            [':id' => $id]
        );

        return $result->rowCount() > 0;
    }

    /**
     * Vygeneruje SEO-friendly slug z názvu
     *
     * @param string $title Název článku
     * @return string Vygenerovaný slug
     */
    private function generateSlug(string $title): string
    {
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $title);
        $slug = preg_replace('/[^a-zA-Z0-9 -]/', '', $slug);
        $slug = strtolower(str_replace(' ', '-', $slug));
        $slug = preg_replace('/-+/', '-', $slug);

        return $slug . '-' . time();
    }

    /**
     * Získá publikované články
     *
     * @return array Seznam publikovaných článků
     */
    public function getPublishedArticles(): array
    {
        $stmt = $this->db->query(
            "SELECT a.*, u.username as author_name
             FROM articles a
             LEFT JOIN users u ON a.author_id = u.id
             WHERE a.status = 'published'
             AND a.published_at IS NOT NULL
             AND a.published_at <= NOW()
             AND a.deleted_at IS NULL
             ORDER BY a.published_at DESC"
        );

        return $stmt->fetchAll();
    }

    /**
     * Získá článek podle slugu
     *
     * @param string $slug Slug článku
     * @return array|null Data článku nebo null pokud neexistuje
     */
    public function getArticleBySlug(string $slug): ?array
    {
        $stmt = $this->db->query(
            "SELECT a.*, u.username as author_name
             FROM articles a
             LEFT JOIN users u ON a.author_id = u.id
             WHERE a.slug = :slug
             AND a.status = 'published'
             AND a.published_at <= NOW()
             AND a.deleted_at IS NULL",
            [':slug' => $slug]
        );

        return $stmt->fetch() ?: null;
    }

    /**
     * Získá smazané články (soft delete)
     *
     * @return array Seznam smazaných článků
     */
    public function getDeletedArticles(): array
    {
        $stmt = $this->db->query(
            "SELECT a.*, u.username as author_name
             FROM articles a
             LEFT JOIN users u ON a.author_id = u.id
             WHERE a.deleted_at IS NOT NULL
             ORDER BY a.deleted_at DESC"
        );

        return $stmt->fetchAll();
    }

    /**
     * Obnoví smazaný článek
     *
     * @param int $id ID článku
     * @return bool True pokud byl článek úspěšně obnoven
     */
    public function restoreArticle(int $id): bool
    {
        $result = $this->db->query(
            "UPDATE articles SET deleted_at = NULL WHERE id = :id",
            [':id' => $id]
        );

        return $result->rowCount() > 0;
    }

    /**
     * Trvale smaže článek z databáze
     *
     * @param int $id ID článku
     * @return bool True pokud byl článek úspěšně trvale smazán
     */
    public function permanentDeleteArticle(int $id): bool
    {
        $result = $this->db->query(
            "DELETE FROM articles WHERE id = :id",
            [':id' => $id]
        );

        return $result->rowCount() > 0;
    }

/**
     * Získá články v konkrétní kategorii
     *
     * @param int $categoryId ID kategorie
     * @return array Seznam článků v kategorii
     */
    public function getArticlesByCategory(int $categoryId): array
    {
        $stmt = $this->db->query(
            "SELECT a.*, u.username as author_name
             FROM articles a
             LEFT JOIN users u ON a.author_id = u.id
             INNER JOIN article_categories ac ON a.id = ac.article_id
             WHERE ac.category_id = ?
             AND a.status = 'published'
             AND a.deleted_at IS NULL
             ORDER BY a.published_at DESC",
            [$categoryId]
        );

        return $stmt->fetchAll();
    }

    /**
     * Získá články s jejich kategoriemi
     *
     * @return array Seznam článků s informacemi o kategoriích
     */
    public function getArticlesWithCategories(): array
    {
        $stmt = $this->db->query(
            "SELECT a.*,
                GROUP_CONCAT(c.name) as category_names,
                GROUP_CONCAT(c.id) as category_ids
             FROM articles a
             LEFT JOIN article_categories ac ON a.id = ac.article_id
             LEFT JOIN categories c ON ac.category_id = c.id
             WHERE a.deleted_at IS NULL
             GROUP BY a.id
             ORDER BY a.created_at DESC"
        );

        return $stmt->fetchAll();
    }
}