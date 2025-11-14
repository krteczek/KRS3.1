<?php
declare(strict_types=1);

namespace App\Services;

use App\Database\DatabaseConnection;
use App\Core\Config;

/**
 * Služba pro správu obrázků
 *
 * Poskytuje metody pro nahrávání, správu a mazání obrázků,
 * včetně práce s košem a trvalým mazáním.
 *
 * @package App\Services
 * @author KRS3
 * @version 1.0
 */
class ImageService
{
    /**
     * Konstruktor
     *
     * @param DatabaseConnection $db Databázové spojení
     */
    public function __construct(private DatabaseConnection $db) {}

    /**
     * Získá všechny aktivní obrázky
     *
     * @return array Seznam všech aktivních obrázků
     */
    public function getAllImages(): array
    {
        $stmt = $this->db->query("
            SELECT * FROM images
            WHERE deleted_at IS NULL
            ORDER BY created_at DESC
        ");

        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Získá smazané obrázky (z koše)
     *
     * @return array Seznam smazaných obrázků
     */
    public function getTrashedImages(): array
    {
        $stmt = $this->db->query("
            SELECT * FROM images
            WHERE deleted_at IS NOT NULL
            ORDER BY deleted_at DESC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Získá obrázek podle ID
     *
     * @param int $id ID obrázku
     * @return array|null Data obrázku nebo null pokud neexistuje
     */
    public function getImage(int $id): ?array
    {
        $stmt = $this->db->query("
            SELECT * FROM images
            WHERE id = ? AND deleted_at IS NULL
        ", [$id]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Získá obrázek podle ID (včetně smazaných)
     *
     * @param int $id ID obrázku
     * @return array|null Data obrázku nebo null pokud neexistuje
     */
    public function getImageIncludingTrashed(int $id): ?array
    {
        $stmt = $this->db->query("
            SELECT * FROM images
            WHERE id = ?
        ", [$id]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Smaže obrázek (soft delete)
     *
     * @param int $id ID obrázku
     * @return bool True pokud byl obrázek úspěšně smazán
     */
    public function deleteImage(int $id): bool
    {
        $stmt = $this->db->query("UPDATE images SET deleted_at = NOW() WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Obnoví obrázek z koše
     *
     * @param int $id ID obrázku
     * @return bool True pokud byl obrázek úspěšně obnoven
     */
    public function restoreImage(int $id): bool
    {
        $stmt = $this->db->query("UPDATE images SET deleted_at = NULL WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Trvale smaže obrázek (i soubory z disku)
     *
     * @param int $id ID obrázku
     * @return array Výsledek operace
     */
    public function permanentDeleteImage(int $id): array
    {
        try {
            // Začneme transakci
            $this->db->beginTransaction();

            // 1. Získat informace o obrázku (I TEN SMAZANÝ!)
            $image = $this->getImageIncludingTrashed($id);
            if (!$image) {
                return [
                    'success' => false,
                    'message' => 'Obrázek nebyl nalezen.'
                ];
            }

            // 2. SMAZAT PŘÍMO Z IMAGES - databáze se postará o CASCADE
            $stmt = $this->db->query("DELETE FROM images WHERE id = ?", [$id]);
            $deleted = $stmt->rowCount() > 0;

            // 3. Smazat soubory z disku
            if ($deleted) {
                $uploadPath = Config::get('uploads.gallery.path') . '/';
                $filesToDelete = [
                    $uploadPath . $image['file_path'],
                    $uploadPath . $image['thumb_path']
                ];

                foreach ($filesToDelete as $file) {
                    if (file_exists($file)) {
                        unlink($file);
                    } else {
                        // Logovat že soubor neexistuje, ale pokračovat
                        error_log("Soubor pro mazání neexistuje: " . $file);
                    }
                }
            }

            // Potvrdit transakci
            $this->db->commit();

            return [
                'success' => $deleted,
                'message' => $deleted ? 'Obrázek byl trvale smazán.' : 'Obrázek nebyl nalezen.'
            ];

        } catch (\Exception $e) {
            // Vrátit transakci zpět
            $this->db->rollBack();

            return [
                'success' => false,
                'message' => 'Chyba při trvalém mazání obrázku: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Zpracuje upload obrázku
     *
     * @param array|null $uploadedFile Data nahraného souboru z $_FILES
     * @param array $postData Data z formuláře
     * @return array Výsledek operace
     */
    public function processImageUpload(?array $uploadedFile, array $postData): array
    {
        try {
            // Validace souboru
            $validation = $this->validateUploadedFile($uploadedFile);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message']
                ];
            }

            // Připravit data obrázku
            $imageData = [
                'title' => $postData['title'] ?? '',
                'description' => $postData['description'] ?? '',
                'galleries' => $postData['galleries'] ?? []
            ];

            // Zpracovat a uložit soubor
            $fileInfo = $this->processAndSaveImage($uploadedFile);

            // Spojit data
            $imageData = array_merge($imageData, $fileInfo);

            // Uložit do databáze
            $imageId = $this->saveImage($imageData);

            // Přidat do galerií
            if (!empty($imageData['galleries'])) {
                foreach ($imageData['galleries'] as $galleryId) {
                    $this->addImageToGallery((int)$galleryId, $imageId);
                }
            }

            return [
                'success' => true,
                'message' => 'Obrázek byl úspěšně nahrán.',
                'image_id' => $imageId
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Chyba při nahrávání obrázku: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Uloží informace o nahraném obrázku
     *
     * @param array $imageData Data obrázku
     * @return int ID nově vytvořeného obrázku
     */
    public function saveImage(array $imageData): int
    {
        $stmt = $this->db->query("
            INSERT INTO images (file_path, thumb_path, original_name, title, description, file_size, width, height, mime_type)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $imageData['file_path'],
            $imageData['thumb_path'],
            $imageData['original_name'],
            $imageData['title'] ?? '',
            $imageData['description'] ?? '',
            $imageData['file_size'],
            $imageData['width'],
            $imageData['height'],
            $imageData['mime_type']
        ]);

        return $this->db->getLastInsertId();
    }

    /**
     * Zjistí využití obrázku v článcích a galeriích
     *
     * @param int $imageId ID obrázku
     * @return array Pole s informacemi o využití obrázku
     */
    public function getImageUsage(int $imageId): array
    {
        // Články používající obrázek
        $stmtArticles = $this->db->query("
            SELECT a.id, a.title FROM articles a
            INNER JOIN article_images ai ON a.id = ai.article_id
            WHERE ai.image_id = ? AND a.deleted_at IS NULL
        ", [$imageId]);
        $articles = $stmtArticles->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // Galerie obsahující obrázek
        $galleries = $this->getImageGalleries($imageId);

        return [
            'articles' => $articles,
            'galleries' => $galleries
        ];
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
     * Přidá obrázek k článku
     *
     * @param int $articleId ID článku
     * @param int $imageId ID obrázku
     * @return bool True pokud byl obrázek úspěšně přidán
     */
    public function addImageToArticle(int $articleId, int $imageId): bool
    {
        $stmt = $this->db->query("
            INSERT IGNORE INTO article_images (article_id, image_id)
            VALUES (?, ?)
        ", [$articleId, $imageId]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Odebere obrázek z článku
     *
     * @param int $articleId ID článku
     * @param int $imageId ID obrázku
     * @return bool True pokud byl obrázek úspěšně odebrán
     */
    public function removeImageFromArticle(int $articleId, int $imageId): bool
    {
        $stmt = $this->db->query("
            DELETE FROM article_images
            WHERE article_id = ? AND image_id = ?
        ", [$articleId, $imageId]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Získá obrázky přiřazené k článku
     *
     * @param int $articleId ID článku
     * @return array Seznam obrázků přiřazených k článku
     */
    public function getArticleImages(int $articleId): array
    {
        $stmt = $this->db->query("
            SELECT i.* FROM images i
            INNER JOIN article_images ai ON i.id = ai.image_id
            WHERE ai.article_id = ? AND i.deleted_at IS NULL
            ORDER BY i.created_at DESC
        ", [$articleId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Validuje nahraný soubor
     *
     * @param array|null $uploadedFile
     * @return array Výsledek validace
     */
    private function validateUploadedFile(?array $uploadedFile): array
    {
        if (!$uploadedFile || $uploadedFile['error'] !== UPLOAD_ERR_OK) {
            return [
                'valid' => false,
                'message' => 'Soubor se nepodařilo nahrát. Zkuste to prosím znovu.'
            ];
        }

        // Kontrola velikosti souboru
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($uploadedFile['size'] > $maxSize) {
            return [
                'valid' => false,
                'message' => 'Soubor je příliš velký. Maximální povolená velikost je 10MB.'
            ];
        }

        // Kontrola typu souboru
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $uploadedFile['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            return [
                'valid' => false,
                'message' => 'Nepodporovaný typ souboru. Povolené formáty: JPEG, PNG, GIF, WebP.'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Soubor je validní.'
        ];
    }

    /**
     * Zpracuje a uloží obrázek včetně náhledu
     *
     * @param array $uploadedFile
     * @return array Informace o uloženém souboru
     */
    private function processAndSaveImage(array $uploadedFile): array
    {
        $uploadConfig = [
            'path' => Config::get('uploads.gallery.path'),
            'thumb_width' => Config::get('uploads.gallery.thumb_width'),
            'thumb_height' => Config::get('uploads.gallery.thumb_height')
        ];

        // Vytvořit složky pokud neexistují
        if (!is_dir($uploadConfig['path'])) {
            mkdir($uploadConfig['path'], 0755, true);
        }

        // Generovat unikátní názvy souborů
        $extension = pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
        $baseName = uniqid('img_', true);
        $fileName = $baseName . '.' . $extension;
        $thumbName = $baseName . '_thumb.' . $extension;

        // Cesty k souborům
        $filePath = $uploadConfig['path'] . '/' . $fileName;
        $thumbPath = $uploadConfig['path'] . '/' . $thumbName;

        // Přesunout původní soubor
        if (!move_uploaded_file($uploadedFile['tmp_name'], $filePath)) {
            throw new \Exception('Nepodařilo se uložit soubor.');
        }

        // Získat informace o obrázku
        $imageInfo = getimagesize($filePath);
        if (!$imageInfo) {
            unlink($filePath);
            throw new \Exception('Neplatný obrázek.');
        }

        // Vytvořit náhled
        $this->createThumbnail($filePath, $thumbPath, $uploadConfig['thumb_width'], $uploadConfig['thumb_height']);

        return [
            'file_path' => $fileName,
            'thumb_path' => $thumbName,
            'original_name' => $uploadedFile['name'],
            'file_size' => $uploadedFile['size'],
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'mime_type' => $imageInfo['mime']
        ];
    }

    /**
     * Vytvoří náhled obrázku
     *
     * @param string $sourcePath Cesta k původnímu obrázku
     * @param string $thumbPath Cesta k náhledu
     * @param int $maxWidth Maximální šířka náhledu
     * @param int $maxHeight Maximální výška náhledu
     * @return bool True pokud se náhled podařilo vytvořit
     */
    private function createThumbnail(string $sourcePath, string $thumbPath, int $maxWidth, int $maxHeight): bool
    {
        // Kontrola dostupnosti GD extension
        if (!extension_loaded('gd') || !function_exists('gd_info')) {
            // Pokud GD není dostupné, prostě zkopírujeme původní obrázek jako "náhled"
            return copy($sourcePath, $thumbPath);
        }

        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }

        $mimeType = $imageInfo['mime'];
        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];

        // Načíst obrázek podle typu
        switch ($mimeType) {
            case 'image/jpeg':
                if (!function_exists('imagecreatefromjpeg')) {
                    return copy($sourcePath, $thumbPath);
                }
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                if (!function_exists('imagecreatefrompng')) {
                    return copy($sourcePath, $thumbPath);
                }
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                if (!function_exists('imagecreatefromgif')) {
                    return copy($sourcePath, $thumbPath);
                }
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            case 'image/webp':
                if (!function_exists('imagecreatefromwebp')) {
                    return copy($sourcePath, $thumbPath);
                }
                $sourceImage = imagecreatefromwebp($sourcePath);
                break;
            default:
                return copy($sourcePath, $thumbPath);
        }

        if (!$sourceImage) {
            return copy($sourcePath, $thumbPath);
        }

        // Vypočítat rozměry náhledu
        $ratio = min($maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
        $thumbWidth = (int)($sourceWidth * $ratio);
        $thumbHeight = (int)($sourceHeight * $ratio);

        // Vytvořit náhled
        $thumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);

        // Zachovat průhlednost pro PNG a GIF
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagecolortransparent($thumbImage, imagecolorallocatealpha($thumbImage, 0, 0, 0, 127));
            imagealphablending($thumbImage, false);
            imagesavealpha($thumbImage, true);
        }

        imagecopyresampled($thumbImage, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $sourceWidth, $sourceHeight);

        // Uložit náhled
        $result = false;
        switch ($mimeType) {
            case 'image/jpeg':
                $result = imagejpeg($thumbImage, $thumbPath, 85);
                break;
            case 'image/png':
                $result = imagepng($thumbImage, $thumbPath, 8);
                break;
            case 'image/gif':
                $result = imagegif($thumbImage, $thumbPath);
                break;
            case 'image/webp':
                $result = imagewebp($thumbImage, $thumbPath, 85);
                break;
            default:
                $result = copy($sourcePath, $thumbPath);
        }

        // Uvolnit paměť
        if (is_resource($sourceImage)) {
            imagedestroy($sourceImage);
        }
        if (is_resource($thumbImage)) {
            imagedestroy($thumbImage);
        }

        return $result;
    }
}