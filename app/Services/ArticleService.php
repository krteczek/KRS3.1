<?php
declare(strict_types=1);

namespace App\Services;

use App\Database\DatabaseConnection;

class ArticleService
{
    public function __construct(private DatabaseConnection $db) {}

    public function createArticle(array $data): int
    {
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

    public function updateArticle(int $id, array $data): bool
    {
        $article = $this->getArticle($id);
        if (!$article) return false;

        $slug = $data['slug'] ?? $this->generateSlug($data['title']);

        return (bool) $this->db->query(
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
    }

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

    public function deleteArticle(int $id): bool
	{
	    // Místo DELETE použij UPDATE - SOFT DELETE
	    return (bool) $this->db->query(
	        "UPDATE articles SET deleted_at = NOW() WHERE id = :id",
	        [':id' => $id]
	    );
	}
    private function generateSlug(string $title): string
    {
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $title);
        $slug = preg_replace('/[^a-zA-Z0-9 -]/', '', $slug);
        $slug = strtolower(str_replace(' ', '-', $slug));
        $slug = preg_replace('/-+/', '-', $slug);

        return $slug . '-' . time();
    }



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

	public function getArticleBySlug(string $slug): ?array
	{
	    $stmt = $this->db->query(
	        "SELECT a.*, u.username as author_name
	         FROM articles a
	         LEFT JOIN users u ON a.author_id = u.id
	         WHERE a.slug = :slug
	         AND a.status = 'published'
	         AND a.published_at <= NOW()",
	        [':slug' => $slug]
	    );

	    return $stmt->fetch() ?: null;
	}
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
	public function restoreArticle(int $id): bool
	{
	    return (bool) $this->db->query(
	        "UPDATE articles SET deleted_at = NULL WHERE id = :id",
	        [':id' => $id]
	    );
	}

	public function permanentDeleteArticle(int $id): bool
	{
	    // Skutečné smazání z databáze - POZOR, nevratné!
	    return (bool) $this->db->query(
	        "DELETE FROM articles WHERE id = :id",
	        [':id' => $id]
	    );
	}

}