<?php
declare(strict_types=1);

namespace App\Services;

use App\Database\DatabaseConnection;

/**
 * Služba pro správu galerií
 *
 * Poskytuje metody pro správu stromové struktury galerií,
 * včetně práce s košem a trvalým mazáním.
 *
 * @package App\Services
 * @author KRS3
 * @version 1.0
 */
class GalleryService
{
    /**
     * Konstruktor
     *
     * @param DatabaseConnection $db Databázové spojení
     */
    public function __construct(private DatabaseConnection $db) {}

    /**
     * Získá všechny aktivní galerie
     *
     * @return array Seznam všech aktivních galerií
     */
    public function getAllGalleries(): array
    {
        $stmt = $this->db->query("
            SELECT * FROM galleries
            WHERE deleted_at IS NULL
            ORDER BY parent_id, name
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Získá galerii podle ID
     *
     * @param int $id ID galerie
     * @return array|null Data galerie nebo null pokud neexistuje
     */
    public function getGallery(int $id): ?array
    {
        $stmt = $this->db->query("
            SELECT * FROM galleries
            WHERE id = ? AND deleted_at IS NULL
        ", [$id]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Získá galerii podle ID (včetně smazaných)
     *
     * @param int $id ID galerie
     * @return array|null Data galerie nebo null pokud neexistuje
     */
    public function getGalleryIncludingTrashed(int $id): ?array
    {
        $stmt = $this->db->query("
            SELECT * FROM galleries
            WHERE id = ?
        ", [$id]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Vytvoří stromovou strukturu galerií
     *
     * @return array Stromová struktura galerií
     */
    public function getGalleryTree(): array
    {
        $galleries = $this->getAllGalleries();
        return $this->buildTree($galleries);
    }

    /**
     * Rekurzivně vytvoří stromovou strukturu z plochého pole
     *
     * @param array $elements Pole galerií
     * @param int|null $parentId ID nadřazené galerie
     * @return array Stromová struktura
     */
    private function buildTree(array $elements, ?int $parentId = null): array
    {
        $branch = [];

        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = $this->buildTree($elements, (int)$element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }

    /**
     * Vytvoří novou galerii
     *
     * @param array $data Data galerie
     * @return bool True pokud byla galerie úspěšně vytvořena
     */
    public function createGallery(array $data): bool
    {
        $stmt = $this->db->query("
            INSERT INTO galleries (parent_id, name, slug, description)
            VALUES (?, ?, ?, ?)
        ", [
            $data['parent_id'] ?: null,
            $data['name'],
            $this->slugify($data['name']),
            $data['description'] ?? ''
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Aktualizuje existující galerii
     *
     * @param int $id ID galerie
     * @param array $data Nová data galerie
     * @return bool True pokud byla galerie úspěšně aktualizována
     */
    public function updateGallery(int $id, array $data): bool
    {
        $stmt = $this->db->query("
            UPDATE galleries
            SET parent_id = ?, name = ?, slug = ?, description = ?, updated_at = NOW()
            WHERE id = ?
        ", [
            $data['parent_id'] ?: null,
            $data['name'],
            $this->slugify($data['name']),
            $data['description'] ?? '',
            $id
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Přesune galerii do koše (soft delete)
     *
     * @param int $id ID galerie
     * @return bool True pokud byla galerie úspěšně smazána
     */
    public function deleteGallery(int $id): bool
    {
        $stmt = $this->db->query("UPDATE galleries SET deleted_at = NOW() WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Získá všechny obrázky v galerii
     *
     * @param int $galleryId ID galerie
     * @return array Seznam obrázků v galerii
     */
    public function getGalleryImages(int $galleryId): array
    {
        $stmt = $this->db->query("
            SELECT i.* FROM images i
            INNER JOIN gallery_images gi ON i.id = gi.image_id
            WHERE gi.gallery_id = ? AND i.deleted_at IS NULL
            ORDER BY i.created_at DESC
        ", [$galleryId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Přidá obrázek do galerie
     *
     * @param int $galleryId ID galerie
     * @param int $imageId ID obrázku
     * @return bool True pokud byl obrázek úspěšně přidán
     */
    public function addImageToGallery(int $galleryId, int $imageId): bool
    {
        $stmt = $this->db->query("
            INSERT IGNORE INTO gallery_images (gallery_id, image_id)
            VALUES (?, ?)
        ", [$galleryId, $imageId]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Odebere obrázek z galerie
     *
     * @param int $galleryId ID galerie
     * @param int $imageId ID obrázku
     * @return bool True pokud byl obrázek úspěšně odebrán
     */
    public function removeImageFromGallery(int $galleryId, int $imageId): bool
    {
        $stmt = $this->db->query("
            DELETE FROM gallery_images
            WHERE gallery_id = ? AND image_id = ?
        ", [$galleryId, $imageId]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Získá galerie obsahující daný obrázek
     *
     * @param int $imageId ID obrázku
     * @return array Seznam galerií obsahujících obrázek
     */
    public function getImageGalleries(int $imageId): array
    {
        $stmt = $this->db->query("
            SELECT g.* FROM galleries g
            INNER JOIN gallery_images gi ON g.id = gi.gallery_id
            WHERE gi.image_id = ? AND g.deleted_at IS NULL
            ORDER BY g.name
        ", [$imageId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Vygeneruje slug z textu
     *
     * @param string $text Vstupní text
     * @return string Vygenerovaný slug
     */
    private function slugify(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);

        return $text ?: 'gallery';
    }

    /**
     * Smaže galerii a přesune její děti na nejvyšší úroveň
     *
     * @param int $id ID galerie
     * @return array Výsledek operace s informacemi
     */
    public function deleteGalleryAndPromoteChildren(int $id): array
    {
        try {
            // Nejprve zjistíme informace o galerii a jejích dětech
            $gallery = $this->getGallery($id);
            if (!$gallery) {
                return [
                    'success' => false,
                    'message' => 'Galerie nebyla nalezena.',
                    'promoted_children' => 0
                ];
            }

            $children = $this->getGalleryChildren($id);
            $childrenCount = count($children);

            // Začneme transakci pro bezpečnost
            $this->db->beginTransaction();

            // Přesuneme všechny děti na nejvyšší úroveň
            if ($childrenCount > 0) {
                $updateStmt = $this->db->query("
                    UPDATE galleries
                    SET parent_id = NULL, updated_at = NOW()
                    WHERE parent_id = ? AND deleted_at IS NULL
                ", [$id]);

                $affectedChildren = $updateStmt->rowCount();
            } else {
                $affectedChildren = 0;
            }

            // Smažeme rodičovskou galerii
            $deleteStmt = $this->db->query("
                UPDATE galleries
                SET deleted_at = NOW()
                WHERE id = ?
            ", [$id]);

            $parentDeleted = $deleteStmt->rowCount() > 0;

            // Potvrdíme transakci
            $this->db->commit();

            return [
                'success' => $parentDeleted,
                'message' => $parentDeleted ? 'Galerie byla úspěšně smazána.' : 'Chyba při mazání galerie.',
                'promoted_children' => $affectedChildren,
                'gallery_name' => $gallery['name'],
                'children_count' => $childrenCount
            ];

        } catch (\Exception $e) {
            // Vrátíme transakci zpět v případě chyby
            $this->db->rollBack();

            return [
                'success' => false,
                'message' => 'Chyba při mazání galerie: ' . $e->getMessage(),
                'promoted_children' => 0,
                'gallery_name' => '',
                'children_count' => 0
            ];
        }
    }

    /**
     * Získá všechny přímé děti galerie
     *
     * @param int $parentId ID nadřazené galerie
     * @return array Seznam podgalerií
     */
    public function getGalleryChildren(int $parentId): array
    {
        $stmt = $this->db->query("
            SELECT id, name, slug FROM galleries
            WHERE parent_id = ? AND deleted_at IS NULL
            ORDER BY name
        ", [$parentId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Získá kompletní informace o galerii před smazáním
     *
     * @param int $id ID galerie
     * @return array Informace o galerii a jejích dětech
     */
    public function getGalleryDeleteInfo(int $id): array
    {
        $gallery = $this->getGallery($id);
        if (!$gallery) {
            return [
                'exists' => false,
                'gallery' => null,
                'children' => [],
                'children_count' => 0,
                'images_count' => 0
            ];
        }

        $children = $this->getGalleryChildren($id);
        $images = $this->getGalleryImages($id);

        return [
            'exists' => true,
            'gallery' => $gallery,
            'children' => $children,
            'children_count' => count($children),
            'images_count' => count($images)
        ];
    }

    /**
     * Zkontroluje, zda může být galerie přiřazena jako nadřazená
     *
     * @param int $galleryId ID galerie, která má být přiřazena jako parent
     * @param int|null $currentId ID aktuální galerie (pro editaci) nebo null pro novou galerii
     * @return array Výsledek kontroly
     */
    public function validateParentAssignment(int $galleryId, ?int $currentId = null): array
    {
        // Galerie nemůže být nadřazená sama sobě
        if ($currentId && $galleryId === $currentId) {
            return [
                'valid' => false,
                'message' => 'Galerie nemůže být nadřazená sama sobě.'
            ];
        }

        // Kontrola cyklických referencí - galerie nemůže být nadřazená své vlastní nadřazené
        if ($currentId) {
            $isCircular = $this->checkCircularReference($galleryId, $currentId);
            if ($isCircular) {
                return [
                    'valid' => false,
                    'message' => 'Galerie nemůže být nadřazená galerii, která je již jejím potomkem.'
                ];
            }
        }

        // Kontrola existence galerie
        $parentGallery = $this->getGallery($galleryId);
        if (!$parentGallery) {
            return [
                'valid' => false,
                'message' => 'Zvolená nadřazená galerie neexistuje.'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Přiřazení je platné.'
        ];
    }

    /**
     * Zkontroluje cyklické reference v hierarchii
     *
     * @param int $potentialParentId ID potenciálního rodiče
     * @param int $currentId ID aktuální galerie
     * @return bool True pokud by došlo k cyklické referenci
     */
    private function checkCircularReference(int $potentialParentId, int $currentId): bool
    {
        // Pokud se potenciální rodič rovná aktuální galerii - cyklus
        if ($potentialParentId === $currentId) {
            return true;
        }

        // Rekurzivně projdeme všechny nadřazené galerie potenciálního rodiče
        $parentGallery = $this->getGallery($potentialParentId);
        while ($parentGallery && $parentGallery['parent_id'] !== null) {
            if ($parentGallery['parent_id'] == $currentId) {
                return true; // Nalezen cyklus
            }
            $parentGallery = $this->getGallery((int)$parentGallery['parent_id']);
        }

        return false;
    }

    /**
     * Získá seznam galerií, které mohou být nadřazené pro danou galerii
     *
     * @param int|null $currentId ID aktuální galerie (pro editaci) nebo null pro novou galerii
     * @return array Seznam povolených nadřazených galerií
     */
    public function getAllowedParentGalleries(?int $currentId = null): array
    {
        $allGalleries = $this->getAllGalleries();

        if (!$currentId) {
            return $allGalleries; // Pro novou galerii jsou povoleny všechny
        }

        // Filtrujeme galerie, které by vytvořily cyklické reference
        $allowedGalleries = [];
        foreach ($allGalleries as $gallery) {
            if ($gallery['id'] != $currentId && !$this->checkCircularReference((int)$gallery['id'], $currentId)) {
                $allowedGalleries[] = $gallery;
            }
        }

        return $allowedGalleries;
    }

    /**
     * Vytvoří novou galerii s kontrolou platnosti nadřazené galerie
     *
     * @param array $data Data galerie
     * @return array Výsledek operace
     */
    public function createGalleryWithValidation(array $data): array
    {
        // Kontrola nadřazené galerie
        if (!empty($data['parent_id'])) {
            $validation = $this->validateParentAssignment((int)$data['parent_id']);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message']
                ];
            }
        }

        $success = $this->createGallery($data);

        return [
            'success' => $success,
            'message' => $success ? 'Galerie byla úspěšně vytvořena.' : 'Chyba při vytváření galerie.',
            'gallery_id' => $success ? $this->db->getLastInsertId() : null
        ];
    }

    /**
     * Aktualizuje galerii s kontrolou platnosti nadřazené galerie
     *
     * @param int $id ID galerie
     * @param array $data Nová data galerie
     * @return array Výsledek operace
     */
    public function updateGalleryWithValidation(int $id, array $data): array
    {
        // Kontrola nadřazené galerie
        if (!empty($data['parent_id'])) {
            $validation = $this->validateParentAssignment((int)$data['parent_id'], $id);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message']
                ];
            }
        }

        $success = $this->updateGallery($id, $data);

        return [
            'success' => $success,
            'message' => $success ? 'Galerie byla úspěšně aktualizována.' : 'Chyba při aktualizaci galerie.'
        ];
    }

    /**
     * Získá galerie z koše (soft deleted)
     *
     * @return array Seznam smazaných galerií
     */
    public function getTrashedGalleries(): array
    {
        $stmt = $this->db->query("
            SELECT * FROM galleries
            WHERE deleted_at IS NOT NULL
            ORDER BY deleted_at DESC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Obnoví galerii z koše
     *
     * @param int $id ID galerie
     * @return bool True pokud byla galerie úspěšně obnovena
     */
    public function restoreGallery(int $id): bool
    {
        $stmt = $this->db->query("UPDATE galleries SET deleted_at = NULL WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Trvale smaže galerii z koše
     *
     * @param int $id ID galerie
     * @return array Výsledek operace
     */
    public function permanentDeleteGallery(int $id): array
    {
        try {
            // Začneme transakci
            $this->db->beginTransaction();

            // 1. Nejprve smažeme vazby v gallery_images
            $this->db->query("DELETE FROM gallery_images WHERE gallery_id = ?", [$id]);

            // 2. Pak smažeme samotnou galerii
            $stmt = $this->db->query("DELETE FROM galleries WHERE id = ?", [$id]);
            $deleted = $stmt->rowCount() > 0;

            // Potvrdit transakci
            $this->db->commit();

            return [
                'success' => $deleted,
                'message' => $deleted ? 'Galerie byla trvale smazána.' : 'Galerie nebyla nalezena.'
            ];

        } catch (\Exception $e) {
            // Vrátit transakci zpět
            $this->db->rollBack();

            return [
                'success' => false,
                'message' => 'Chyba při trvalém mazání galerie: ' . $e->getMessage()
            ];
        }
    }

	 /**
     * Nastaví tématický obrázek pro galerii
     *
     * @param int $galleryId ID galerie
     * @param int|null $imageId ID obrázku nebo null pro odstranění
     * @return bool True pokud bylo nastavení úspěšné
     */
    public function setFeaturedImage(int $galleryId, ?int $imageId): bool
    {
        // Validace, že obrázek existuje (pokud není null)
        if ($imageId !== null) {
            $image = $this->getImage($imageId);
            if (!$image) {
                return false;
            }
        }

        $stmt = $this->db->query("
            UPDATE galleries
            SET featured_image_id = ?, updated_at = NOW()
            WHERE id = ?
        ", [$imageId, $galleryId]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Získá tématický obrázek galerie
     *
     * @param int $galleryId ID galerie
     * @return array|null Data obrázku nebo null pokud není nastaven
     */
    public function getFeaturedImage(int $galleryId): ?array
    {
        $stmt = $this->db->query("
            SELECT i.* FROM images i
            INNER JOIN galleries g ON i.id = g.featured_image_id
            WHERE g.id = ? AND i.deleted_at IS NULL
        ", [$galleryId]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Získá všechny dostupné obrázky pro výběr tématického obrázku
     *
     * @param int $page Číslo stránky
     * @param int $perPage Počet obrázků na stránku
     * @param string $search Hledaný výraz
     * @return array Pole s obrázky a metadaty
     */
	public function getAvailableImages(int $page = 1, int $perPage = 20, string $search = ''): array
	{
	    $offset = ($page - 1) * $perPage;

	    $whereConditions = ["deleted_at IS NULL"];
	    $params = [];

	    if (!empty($search)) {
	        $whereConditions[] = "(title LIKE ? OR original_name LIKE ?)";
	        $searchTerm = "%{$search}%";
	        $params[] = $searchTerm;
	        $params[] = $searchTerm;
	    }

	    $whereClause = implode(' AND ', $whereConditions);

	    // Celkový počet
	    $countStmt = $this->db->query("
	        SELECT COUNT(*) as total FROM images WHERE {$whereClause}
	    ", $params);
	    $total = (int)$countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

	    // Data obrázků
	    $stmt = $this->db->query("
	        SELECT id, title, thumb_path, file_path, original_name,
	               width, height, file_size, created_at
	        FROM images
	        WHERE {$whereClause}
	        ORDER BY created_at DESC
	        LIMIT ? OFFSET ?
	    ", array_merge($params, [$perPage, $offset]));

	    $images = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

	    return [
	        'images' => $images,
	        'pagination' => [
	            'current_page' => $page,
	            'per_page' => $perPage,
	            'total' => $total,
	            'total_pages' => ceil($total / $perPage)
	        ]
	    ];
	}


    /**
     * Získá základní informace o obrázku pro rychlý náhled
     *
     * @param int $imageId ID obrázku
     * @return array|null Základní data obrázku
     */
    public function getImagePreview(int $imageId): ?array
    {
        $stmt = $this->db->query("
            SELECT id, title, thumb_path, file_path, width, height
            FROM images
            WHERE id = ? AND deleted_at IS NULL
        ", [$imageId]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Aktualizuje galerii včetně tématického obrázku
     *
     * @param int $id ID galerie
     * @param array $data Data galerie
     * @return bool True pokud byla galerie úspěšně aktualizována
     */
	public function updateGalleryWithFeaturedImage(int $id, array $data): bool
	{
	    $stmt = $this->db->query("
	        UPDATE galleries
	        SET parent_id = ?, name = ?, slug = ?, description = ?,
	            featured_image_id = ?, updated_at = NOW()
	        WHERE id = ?
	    ", [
	        $data['parent_id'] ?: null,
	        $data['name'],
	        $this->slugify($data['name']),
	        $data['description'] ?? '',
	        !empty($data['featured_image_id']) ? (int)$data['featured_image_id'] : null,
	        $id
	    ]);

	    return $stmt->rowCount() > 0;
	}
}