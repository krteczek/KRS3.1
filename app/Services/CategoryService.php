<?php
//app/Services/CategoryService.php

declare(strict_types=1);

namespace App\Services;

use App\Database\DatabaseConnection;

/**
 * Služba pro správu kategorií článků
 *
 * Poskytuje metody pro CRUD operace s kategoriemi a pro přidělování kategorií článkům.
 * Zajišťuje generování SEO-friendly slugů a správu vztahů mezi články a kategoriemi.
 *
 * @package App\Services
 * @author KRS3
 * @version 1.0
 */
class CategoryService
{
    /**
     * Konstruktor
     *
     * @param DatabaseConnection $db Databázové připojení
     */
    public function __construct(private DatabaseConnection $db) {}

    /**
     * Získá všechny kategorie seřazené podle názvu
     *
     * @return array Seznam všech kategorií
     */
    public function getAllCategories(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM categories ORDER BY name ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Získá kategorii podle ID
     *
     * @param int $id ID kategorie
     * @return array|null Data kategorie nebo null pokud neexistuje
     */
    public function getCategory(int $id): ?array
    {
        $stmt = $this->db->query(
            "SELECT * FROM categories WHERE id = ?",
            [$id]
        );
        return $stmt->fetch() ?: null;
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
     * Smaže kategorii
     *
     * @param int $id ID kategorie
     * @return bool True pokud byla kategorie úspěšně smazána
     */
    public function deleteCategory(int $id): bool
    {
        // Nejprve odstraníme vazby na články
        $this->db->query(
            "DELETE FROM article_categories WHERE category_id = ?",
            [$id]
        );

        $result = $this->db->query(
            "DELETE FROM categories WHERE id = ?",
            [$id]
        );

        return $result->rowCount() > 0;
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
            "SELECT c.* FROM categories c
             INNER JOIN article_categories ac ON c.id = ac.category_id
             WHERE ac.article_id = ?",
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
        // Nejprve smažeme staré vazby
        $this->db->query(
            "DELETE FROM article_categories WHERE article_id = ?",
            [$articleId]
        );

        // Pak přidáme nové
        foreach ($categoryIds as $categoryId) {
            $this->db->query(
                "INSERT INTO article_categories (article_id, category_id) VALUES (?, ?)",
                [$articleId, $categoryId]
            );
        }

        return true;
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
}