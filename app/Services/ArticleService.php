<?php
// app/Services/ArticleService.php
declare(strict_types=1);

namespace App\Services;

use App\Database\DatabaseConnection;
use App\Logger\Logger;

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
    private Logger $logger;

    /**
     * Konstruktor
     *
     * @param DatabaseConnection $db Databázové připojení
     */
    public function __construct(private DatabaseConnection $db)
    {
        $this->logger = Logger::getInstance();
    }

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

        $this->logger->info('Creating new article', [
            'title' => $data['title'],
            'author_id' => $data['author_id'],
            'status' => $data['status'] ?? 'draft',
            'category_ids' => $data['category_ids'] ?? []
        ]);

        // Začátek transakce
        $this->db->beginTransaction();

        try {
            // Vložení článku
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

            $articleId = $this->db->getLastInsertId();

            // Přidání kategorií
            if (!empty($data['category_ids'])) {
                $this->addCategoriesToArticle($articleId, $data['category_ids']);
            }

            // Commit transakce
            $this->db->commit();

            $this->logger->info('Article created successfully', [
                'article_id' => $articleId,
                'title' => $data['title']
            ]);

            return $articleId;

        } catch (\Exception $e) {
            // Rollback v případě chyby
            $this->db->rollBack();
            $this->logger->error('Article creation failed', [
                'error' => $e->getMessage(),
                'title' => $data['title']
            ]);
            throw $e;
        }
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
// DEBUG na začátku
    $this->logger->debug('ARTICLE SERVICE UPDATE - START', [
        'article_id' => $id,
        'data' => $data
    ]);

    $article = $this->getArticle($id);
    if (!$article) {
        $this->logger->warning('Article not found for update', ['article_id' => $id]);
        return false;
    }
        $slug = $data['slug'] ?? $this->generateSlug($data['title']);

        $this->logger->info('Updating article', [
            'article_id' => $id,
            'title' => $data['title'],
            'status' => $data['status'] ?? 'draft',
            'category_ids' => $data['category_ids'] ?? []
        ]);

        // Začátek transakce
        $this->db->beginTransaction();

        try {
            // Aktualizace článku
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

            $success = $result->rowCount() > 0;

            // Aktualizace kategorií
            if (isset($data['category_ids'])) {
                $this->updateArticleCategories($id, $data['category_ids']);
            }

            // Commit transakce
            $this->db->commit();

            if ($success) {
                $this->logger->info('Article updated successfully', ['article_id' => $id]);
            } else {
                $this->logger->warning('Article update had no effect', ['article_id' => $id]);
            }
 $this->logger->debug('ARTICLE SERVICE UPDATE - BEFORE RETURN', [
        'article_id' => $id,
        'success' => $success
    ]);
            return $success;

        } catch (\Exception $e) {
            // Rollback v případě chyby
            $this->db->rollBack();
            $this->logger->error('Article update failed', [
                'article_id' => $id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
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

        $article = $stmt->fetch() ?: null;

        if ($article) {
            // Načtení kategorií
            $article['categories'] = $this->getArticleCategories($id);
            $article['category_ids'] = array_column($article['categories'], 'id');
        }

        $this->logger->debug('Article lookup', [
            'article_id' => $id,
            'found' => $article !== null
        ]);

        return $article;
    }

    /**
     * Získá všechny články s kategoriemi (pro administraci)
     *
     * @return array Seznam článků s kategoriemi
     */
    public function getArticlesWithCategories(): array
    {
        $stmt = $this->db->query(
            "SELECT a.*, u.username as author_name
             FROM articles a
             LEFT JOIN users u ON a.author_id = u.id
             WHERE a.deleted_at IS NULL
             ORDER BY a.created_at DESC"
        );

        $articles = $stmt->fetchAll();

        // Načtení kategorií pro každý článek
        foreach ($articles as &$article) {
            $article['categories'] = $this->getArticleCategories($article['id']);
        }

        $this->logger->debug('Retrieved articles with categories', ['count' => count($articles)]);

        return $articles;
    }

    /**
     * Získá všechny aktivní články
     *
     * @return array Seznam článků
     */
    public function getAllArticles(): array
    {
        return $this->getArticlesWithCategories();
    }

    /**
     * Smaže článek (soft delete)
     *
     * @param int $id ID článku
     * @return bool True pokud byl článek úspěšně smazán
     */
    public function deleteArticle(int $id): bool
    {
        $this->logger->info('Soft deleting article', ['article_id' => $id]);

        $result = $this->db->query(
            "UPDATE articles SET deleted_at = NOW() WHERE id = :id",
            [':id' => $id]
        );

        $success = $result->rowCount() > 0;

        if ($success) {
            $this->logger->info('Article soft deleted successfully', ['article_id' => $id]);
        } else {
            $this->logger->warning('Article soft delete failed', ['article_id' => $id]);
        }

        return $success;
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

        $articles = $stmt->fetchAll();

        // Načtení kategorií pro každý článek
        foreach ($articles as &$article) {
            $article['categories'] = $this->getArticleCategories($article['id']);
        }

        $this->logger->debug('Retrieved published articles', ['count' => count($articles)]);

        return $articles;
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

        $article = $stmt->fetch() ?: null;

        if ($article) {
            // Načtení kategorií
            $article['categories'] = $this->getArticleCategories($article['id']);
        }

        $this->logger->debug('Article lookup by slug', [
            'slug' => $slug,
            'found' => $article !== null
        ]);

        return $article;
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

        $articles = $stmt->fetchAll();

        // Načtení kategorií pro každý článek
        foreach ($articles as &$article) {
            $article['categories'] = $this->getArticleCategories($article['id']);
        }

        $this->logger->debug('Retrieved deleted articles', ['count' => count($articles)]);

        return $articles;
    }

    /**
     * Obnoví smazaný článek
     *
     * @param int $id ID článku
     * @return bool True pokud byl článek úspěšně obnoven
     */
    public function restoreArticle(int $id): bool
    {
        $this->logger->info('Restoring article from trash', ['article_id' => $id]);

        $result = $this->db->query(
            "UPDATE articles SET deleted_at = NULL WHERE id = :id",
            [':id' => $id]
        );

        $success = $result->rowCount() > 0;

        if ($success) {
            $this->logger->info('Article restored successfully', ['article_id' => $id]);
        } else {
            $this->logger->warning('Article restoration failed', ['article_id' => $id]);
        }

        return $success;
    }

    /**
     * Trvale smaže článek z databáze
     *
     * @param int $id ID článku
     * @return bool True pokud byl článek úspěšně trvale smazán
     */
    public function permanentDeleteArticle(int $id): bool
    {
        $this->logger->warning('Permanently deleting article', ['article_id' => $id]);

        // Začátek transakce
        $this->db->beginTransaction();

        try {
            // Smazání kategorií
            $this->db->query(
                "DELETE FROM article_categories WHERE article_id = :id",
                [':id' => $id]
            );

            // Smazání článku
            $result = $this->db->query(
                "DELETE FROM articles WHERE id = :id",
                [':id' => $id]
            );

            $success = $result->rowCount() > 0;

            // Commit transakce
            $this->db->commit();

            if ($success) {
                $this->logger->warning('Article permanently deleted', ['article_id' => $id]);
            } else {
                $this->logger->error('Article permanent deletion failed', ['article_id' => $id]);
            }

            return $success;

        } catch (\Exception $e) {
            // Rollback v případě chyby
            $this->db->rollBack();
            $this->logger->error('Article permanent deletion failed', [
                'article_id' => $id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Získá články pro konkrétní kategorii
     *
     * @param int $categoryId ID kategorie
     * @param int $limit Počet článků
     * @return array Seznam článků
     */
    public function getArticlesByCategory(int $categoryId, int $limit = 10): array
    {
        $stmt = $this->db->query(
            "SELECT a.*, u.username as author_name
             FROM articles a
             LEFT JOIN users u ON a.author_id = u.id
             INNER JOIN article_categories ac ON a.id = ac.article_id
             WHERE ac.category_id = ?
             AND a.status = 'published'
             AND a.published_at <= NOW()
             AND a.deleted_at IS NULL
             ORDER BY a.published_at DESC
             LIMIT ?",
            [$categoryId, $limit]
        );

        $articles = $stmt->fetchAll();

        // Načtení kategorií pro každý článek
        foreach ($articles as &$article) {
            $article['categories'] = $this->getArticleCategories($article['id']);
        }

        $this->logger->debug('Retrieved articles by category', [
            'category_id' => $categoryId,
            'count' => count($articles)
        ]);

        return $articles;
    }

    /**
     * Získá publikované články s kategoriemi (pro administraci)
     *
     * @return array Seznam publikovaných článků s kategoriemi
     */
    public function getPublishedArticlesWithCategories(): array
    {
        $stmt = $this->db->query(
            "SELECT a.*, u.username as author_name
             FROM articles a
             LEFT JOIN users u ON a.author_id = u.id
             WHERE a.status = 'published'
             AND a.deleted_at IS NULL
             ORDER BY a.created_at DESC"
        );

        $articles = $stmt->fetchAll();

        // Načtení kategorií pro každý článek
        foreach ($articles as &$article) {
            $article['categories'] = $this->getArticleCategories($article['id']);
        }

        $this->logger->debug('Retrieved published articles with categories', [
            'count' => count($articles)
        ]);

        return $articles;
    }

    /**
     * Získá smazané články s jejich kategoriemi
     *
     * @return array Seznam smazaných článků s informacemi o kategoriích
     */
    public function getDeletedArticlesWithCategories(): array
    {
        $stmt = $this->db->query(
            "SELECT a.*, u.username as author_name
             FROM articles a
             LEFT JOIN users u ON a.author_id = u.id
             WHERE a.deleted_at IS NOT NULL
             ORDER BY a.deleted_at DESC"
        );

        $articles = $stmt->fetchAll();

        // Načtení kategorií pro každý článek
        foreach ($articles as &$article) {
            $article['categories'] = $this->getArticleCategories($article['id']);
        }

        $this->logger->debug('Retrieved deleted articles with categories', [
            'count' => count($articles)
        ]);

        return $articles;
    }

    /**
     * Získá nejnovější články s jejich kategoriemi
     *
     * @param int $limit Počet článků
     * @return array Seznam článků s kategoriemi
     */
    public function getLatestArticlesWithCategories(int $limit = 10): array
    {
        $stmt = $this->db->query(
            "SELECT a.*, u.username as author_name
             FROM articles a
             LEFT JOIN users u ON a.author_id = u.id
             WHERE a.status = 'published'
             AND a.published_at IS NOT NULL
             AND a.published_at <= NOW()
             AND a.deleted_at IS NULL
             ORDER BY a.published_at DESC
             LIMIT ?",
            [$limit]
        );

        $articles = $stmt->fetchAll();

        // Načtení kategorií a formátování data pro každý článek
        foreach ($articles as &$article) {
            $article['categories'] = $this->getArticleCategories($article['id']);
            $article['formatted_date'] = $this->formatDate($article['published_at']);
        }

        $this->logger->debug('Retrieved latest articles with categories', [
            'limit' => $limit,
            'count' => count($articles)
        ]);

        return $articles;
    }

    /**
     * Získá kategorie článku
     *
     * @param int $articleId ID článku
     * @return array Seznam kategorií
     */
    private function getArticleCategories(int $articleId): array
    {
        $stmt = $this->db->query(
            "SELECT c.id, c.name, c.slug
             FROM categories c
             INNER JOIN article_categories ac ON c.id = ac.category_id
             WHERE ac.article_id = ?
             ORDER BY c.name",
            [$articleId]
        );

        return $stmt->fetchAll();
    }

    /**
     * Přidá kategorie k článku
     *
     * @param int $articleId ID článku
     * @param array $categoryIds Pole ID kategorií
     */
    private function addCategoriesToArticle(int $articleId, array $categoryIds): void
    {
        foreach ($categoryIds as $categoryId) {
            $this->db->query(
                "INSERT INTO article_categories (article_id, category_id)
                 VALUES (:article_id, :category_id)",
                [
                    ':article_id' => $articleId,
                    ':category_id' => $categoryId
                ]
            );
        }
    }

    /**
     * Aktualizuje kategorie článku
     *
     * @param int $articleId ID článku
     * @param array $categoryIds Pole ID kategorií
     */
    private function updateArticleCategories(int $articleId, array $categoryIds): void
    {
        // Smazání stávajících kategorií
        $this->db->query(
            "DELETE FROM article_categories WHERE article_id = :article_id",
            [':article_id' => $articleId]
        );

        // Přidání nových kategorií
        if (!empty($categoryIds)) {
            $this->addCategoriesToArticle($articleId, $categoryIds);
        }
    }

    /**
     * Formátuje datum do českého formátu
     *
     * @param string $date Datum
     * @return string Formátované datum
     */
    private function formatDate(string $date): string
    {
        return date('j. n. Y', strtotime($date));
    }
}