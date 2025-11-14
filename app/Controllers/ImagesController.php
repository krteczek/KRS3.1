<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\ImageService;
use App\Services\GalleryService;
use App\Auth\LoginService;
use App\Security\CsrfProtection;
use App\Core\AdminLayout;
use App\Core\Config;
use App\Logger\Logger;

/**
 * Controller pro správu obrázků
 *
 * Poskytuje funkcionalitu pro nahrávání, správu a mazání obrázků,
 * včetně práce s košem a trvalým mazáním.
 *
 * @package App\Controllers
 * @author KRS3
 * @version 1.0
 */
class ImagesController
{
    private \App\Logger\Logger $logger;

    /**
     * Konstruktor
     *
     * @param ImageService $imageService Služba pro práci s obrázky
     * @param LoginService $authService Služba pro autentizaci
     * @param CsrfProtection $csrf Ochrana proti CSRF útokům
     * @param string $baseUrl Základní URL aplikace
     * @param AdminLayout $adminLayout Layout administračního rozhraní
     */
    public function __construct(
        private ImageService $imageService,
        private LoginService $authService,
        private CsrfProtection $csrf,
        private string $baseUrl,
        private AdminLayout $adminLayout,
		private \App\Database\DatabaseConnection $db
    ) {
        $this->logger = \App\Logger\Logger::getInstance();
    }

    /**
     * Zobrazí správu obrázků (včetně koše)
     *
     * @return string HTML obsah správy obrázků
     */
    public function manage(): string
    {
        $this->requireAdmin();

        $showTrash = isset($_GET['show']) && $_GET['show'] === 'trash';

        if ($showTrash) {
            $images = $this->imageService->getTrashedImages();
            $activeTab = 'trash';
        } else {
            $images = $this->imageService->getAllImages();
            $activeTab = 'active';
        }

        $content = '<div class="admin-container">';
        $content .= '<div class="admin-content">';

        // ZOBRAZENÍ FLASH ZPRÁVY
        if (isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            $content .= '<div class="alert alert-' . $flash['type'] . '">' . $flash['message'] . '</div>';
            unset($_SESSION['flash_message']);
        }

        // Page header
        $content .= '<div class="page-header">';
        $content .= '<h1>' . $this->t('admin.images.manage.title') . '</h1>';

        if (!$showTrash) {
            $content .= '<a href="' . $this->baseUrl . 'admin/images/upload" class="btn btn-primary">';
            $content .= '＋ ' . $this->t('admin.images.upload_button');
            $content .= '</a>';
        }

        $content .= '</div>';

        // Taby (aktivní/koš)
        $content .= '<div class="tabs">';
        $content .= '<a href="' . $this->baseUrl . 'admin/images/manage" class="tab ' . ($activeTab === 'active' ? 'active' : '') . '">';
        $content .= $this->t('admin.images.tabs.active');
        $content .= '</a>';
        $content .= '<a href="' . $this->baseUrl . 'admin/images/manage?show=trash" class="tab ' . ($activeTab === 'trash' ? 'active' : '') . '">';
        $content .= $this->t('admin.images.tabs.trash');
        $content .= '</a>';
        $content .= '</div>';

        if (empty($images)) {
            $content .= '<div class="empty-state">';
            if ($showTrash) {
                $content .= '<h3>' . $this->t('admin.images.trash.empty_title') . '</h3>';
                $content .= '<p>' . $this->t('admin.images.trash.empty_text') . '</p>';
            } else {
                $content .= '<h3>' . $this->t('admin.images.empty.title') . '</h3>';
                $content .= '<p>' . $this->t('admin.images.empty.text') . '</p>';
            }
            $content .= '</div>';
        } else {
            $content .= '<div class="table-responsive">';
            $content .= '<table class="table table-striped">';
            $content .= '<thead>';
            $content .= '<tr>';
            $content .= '<th>' . $this->t('admin.images.manage.table.image') . '</th>';
            $content .= '<th>' . $this->t('admin.images.manage.table.name') . '</th>';
            $content .= '<th>' . $this->t('admin.images.manage.table.description') . '</th>';
            $content .= '<th>' . $this->t('admin.images.manage.table.size') . '</th>';
            $content .= '<th>' . $this->t('admin.images.manage.table.dimensions') . '</th>';
            $content .= '<th>' . $this->t('admin.images.manage.table.status') . '</th>';
            if ($showTrash) {
                $content .= '<th>' . $this->t('admin.images.manage.table.deleted_at') . '</th>';
            }
            $content .= '<th>' . $this->t('admin.images.manage.table.actions') . '</th>';
            $content .= '</tr>';
            $content .= '</thead>';
            $content .= '<tbody>';

            foreach ($images as $image) {
                $content .= $this->renderImageRow($image, $showTrash);
            }

            $content .= '</tbody>';
            $content .= '</table>';
            $content .= '</div>';
        }

        $content .= '</div>';
        $content .= '</div>';

        return $this->adminLayout->wrap($content, $this->t('admin.images.manage.title'));
    }

    /**
     * Zobrazí formulář pro nahrání obrázku
     *
     * @return string HTML obsah upload formuláře
     */
    public function upload(): string
    {
        $this->requireAdmin();

        // Potřebujeme GalleryService pro seznam galerií
        $galleryService = new \App\Services\GalleryService($this->db);
        $galleries = $galleryService->getAllGalleries();

        $content = '<div class="page-header">';
        $content .= '<h1>' . $this->t('admin.images.upload.title') . '</h1>';
        $content .= '<div class="header-actions">';
        $content .= '<a href="' . $this->baseUrl . 'admin/images/manage" class="btn btn-secondary">' . $this->t('admin.images.back_to_images') . '</a>';
        $content .= '</div>';
        $content .= '</div>';

        $content .= $this->renderUploadForm($galleries);

        return $this->adminLayout->wrap($content, $this->t('admin.images.upload.title'));
    }

    /**
     * Zpracuje upload obrázku
     *
     * @return void
     */
    public function uploadImage(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}admin/images/upload");
            exit;
        }

        // Kontrola CSRF tokenu
        if (!$this->csrf->validateToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'message' => $this->t('admin.images.upload.csrf_error')
            ];
            header("Location: {$this->baseUrl}admin/images/upload?error=csrf");
            exit;
        }

        // Zpracování uploadu
        $result = $this->imageService->processImageUpload($_FILES['image_file'] ?? null, $_POST);

        if ($result['success']) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => $result['message']
            ];
            header("Location: {$this->baseUrl}admin/images/manage?upload=success");
        } else {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'message' => $result['message']
            ];
            header("Location: {$this->baseUrl}admin/images/upload?error=upload_failed");
        }
        exit;
    }

    /**
     * Smaže obrázek (soft delete)
     *
     * @param int $id ID obrázku
     * @return void
     */
    public function deleteImage(int $id): void
    {
        $this->requireAdmin();

        $this->logger->debug('Delete image called', [
            'image_id' => $id,
            'method' => $_SERVER['REQUEST_METHOD'],
            'post_data' => $_POST
        ]);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->logger->warning('Invalid method for delete image', ['method' => $_SERVER['REQUEST_METHOD']]);
            header("Location: {$this->baseUrl}admin/images/manage?error=invalid_method");
            exit;
        }

        // Kontrola CSRF tokenu
        if (!$this->csrf->validateToken($_POST['csrf_token'] ?? '')) {
            $this->logger->warning('CSRF validation failed for delete image');
            header("Location: {$this->baseUrl}admin/images/manage?error=csrf");
            exit;
        }

        // SMAZAT OBRAZEK
        $result = $this->imageService->deleteImage($id);
        $this->logger->info('Image deleted', ['image_id' => $id, 'success' => $result]);

        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => 'Obrázek byl úspěšně smazán.'
        ];

        header("Location: {$this->baseUrl}admin/images/manage");
        exit;
    }

    /**
     * Obnoví obrázek z koše
     *
     * @param int $id ID obrázku
     * @return void
     */
    public function restoreImage(int $id): void
    {
        $this->requireAdmin();

        $result = $this->imageService->restoreImage($id);

        if ($result) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => 'Obrázek byl úspěšně obnoven z koše.'
            ];
        } else {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'message' => 'Obrázek se nepodařilo obnovit.'
            ];
        }

        header("Location: {$this->baseUrl}admin/images/manage?show=trash");
        exit;
    }

    /**
     * Zobrazí potvrzení trvalého smazání obrázku
     *
     * @param int $id ID obrázku
     * @return string HTML obsah potvrzovací stránky
     */
    public function confirmPermanentDeleteImage(int $id): string
    {
        $this->requireAdmin();

        $image = $this->imageService->getImageIncludingTrashed($id);

        if (!$image || !$image['deleted_at']) {
            header("Location: {$this->baseUrl}admin/images/manage?show=trash&error=not_found");
            exit;
        }

        $content = '<div class="admin-container">';
        $content .= '<div class="admin-content">';

        $content .= '<div class="page-header">';
        $content .= '<h1>' . $this->t('admin.images.permanent_delete.confirm_title') . '</h1>';
        $content .= '<div class="header-actions">';
        $content .= '<a href="' . $this->baseUrl . 'admin/images/manage?show=trash" class="btn btn-secondary">' . $this->t('admin.images.back_to_trash') . '</a>';
        $content .= '</div>';
        $content .= '</div>';

        $content .= '<div class="alert alert-danger">';
        $content .= '<h4>' . $this->t('admin.images.permanent_delete.warning') . '</h4>';
        $content .= '<p>' . $this->t('admin.images.permanent_delete.confirm_message', ['name' => htmlspecialchars($image['title'] ?: $image['original_name'])]) . '</p>';
        $content .= '<p><strong>' . $this->t('admin.images.permanent_delete.irreversible') . '</strong></p>';
        $content .= '</div>';

        // Informace o obrázku
        $content .= '<div class="card mb-4">';
        $content .= '<div class="card-header">';
        $content .= '<h5>' . $this->t('admin.images.permanent_delete.image_info') . '</h5>';
        $content .= '</div>';
        $content .= '<div class="card-body">';
        $content .= '<div class="row">';
        $content .= '<div class="col-md-3">';
        $content .= '<img src="' . $this->baseUrl . 'uploads/gallery/' . $image['thumb_path'] . '" alt="' . htmlspecialchars($image['title']) . '" style="max-width: 100%;">';
        $content .= '</div>';
        $content .= '<div class="col-md-9">';
        $content .= '<p><strong>' . $this->t('admin.images.form.name') . ':</strong> ' . htmlspecialchars($image['title'] ?: $image['original_name']) . '</p>';
        $content .= '<p><strong>' . $this->t('admin.images.form.original_name') . ':</strong> ' . htmlspecialchars($image['original_name']) . '</p>';
        if (!empty($image['description'])) {
            $content .= '<p><strong>' . $this->t('admin.images.form.description') . ':</strong> ' . htmlspecialchars($image['description']) . '</p>';
        }
        $content .= '<p><strong>' . $this->t('admin.images.form.size') . ':</strong> ' . $this->formatBytes((int)$image['file_size']) . '</p>';
        $content .= '<p><strong>' . $this->t('admin.images.form.dimensions') . ':</strong> ' . $image['width'] . '×' . $image['height'] . '</p>';
        $content .= '<p><strong>' . $this->t('admin.images.form.format') . ':</strong> ' . $image['mime_type'] . '</p>';

        // Využití obrázku
        $usage = $this->imageService->getImageUsage($id);
        if (!empty($usage['articles']) || !empty($usage['galleries'])) {
            $content .= '<div class="alert alert-warning mt-3">';
            $content .= '<strong>' . $this->t('admin.images.permanent_delete.usage_warning') . '</strong><br>';
            if (!empty($usage['articles'])) {
                $content .= '<small>' . $this->t('admin.images.permanent_delete.in_articles') . ': ' . count($usage['articles']) . '</small><br>';
            }
            if (!empty($usage['galleries'])) {
                $content .= '<small>' . $this->t('admin.images.permanent_delete.in_galleries') . ': ' . count($usage['galleries']) . '</small>';
            }
            $content .= '</div>';
        }

        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';

        // Formulář pro potvrzení
        $csrfField = $this->csrf->getTokenField();

        $content .= '<div class="card">';
        $content .= '<div class="card-body">';
        $content .= '<form action="' . $this->baseUrl . 'admin/images/permanent-delete/' . $id . '" method="post">';
        $content .= $csrfField;

        $content .= '<div class="form-check mb-3">';
        $content .= '<input type="checkbox" id="confirm_permanent_delete" name="confirm_permanent_delete" class="form-check-input" required>';
        $content .= '<label for="confirm_permanent_delete" class="form-check-label">';
        $content .= $this->t('admin.images.permanent_delete.confirm_checkbox', ['name' => htmlspecialchars($image['title'] ?: $image['original_name'])]);
        $content .= '</label>';
        $content .= '</div>';

        $content .= '<div class="form-actions">';
        $content .= '<button type="submit" class="btn btn-danger">' . $this->t('admin.images.permanent_delete.confirm_button') . '</button>';
        $content .= '<a href="' . $this->baseUrl . 'admin/images/manage?show=trash" class="btn btn-secondary">' . $this->t('admin.images.permanent_delete.cancel_button') . '</a>';
        $content .= '</div>';

        $content .= '</form>';
        $content .= '</div>';
        $content .= '</div>';

        $content .= '</div>';
        $content .= '</div>';

        return $this->adminLayout->wrap($content, $this->t('admin.images.permanent_delete.confirm_title'));
    }

    /**
     * Trvale smaže obrázek z koše
     *
     * @param int $id ID obrázku
     * @return void
     */
    public function permanentDeleteImage(int $id): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}admin/images/confirm-permanent-delete-image/" . $id);
            exit;
        }

        // Kontrola potvrzení
        if (!isset($_POST['confirm_permanent_delete']) || $_POST['confirm_permanent_delete'] !== 'on') {
            header("Location: {$this->baseUrl}admin/images/confirm-permanent-delete-image/" . $id . "?error=not_confirmed");
            exit;
        }

        $result = $this->imageService->permanentDeleteImage($id);

        if ($result['success']) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => $result['message']
            ];
        } else {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'message' => $result['message']
            ];
        }

        header("Location: {$this->baseUrl}admin/images/manage?show=trash");
        exit;
    }

    /**
     * Vykreslí řádek obrázku v tabulce
     *
     * @param array $image Data obrázku
     * @param bool $isTrashView Zda se zobrazuje koš
     * @return string HTML řádku tabulky
     */
    private function renderImageRow(array $image, bool $isTrashView = false): string
    {
        $thumbUrl = $this->baseUrl . 'uploads/gallery/' . $image['thumb_path'];
        $imageUrl = $this->baseUrl . 'uploads/gallery/' . $image['file_path'];

        // Status badge
        $statusBadge = '<span class="badge badge-success">' . $this->t('admin.images.status.active') . '</span>';
        if ($image['deleted_at']) {
            $statusBadge = '<span class="badge badge-danger">' . $this->t('admin.images.status.deleted') . '</span>';
        }

        // Formátování data smazání
        $deletedAt = '';
        if ($image['deleted_at']) {
            $deletedDate = new \DateTime($image['deleted_at']);
            $deletedAt = $deletedDate->format('d. m. Y H:i');
        }

        $content = '<tr>';

        // Náhled obrázku
        $content .= '<td>';
        $content .= '<img src="' . $thumbUrl . '" alt="' . htmlspecialchars($image['title']) . '" style="max-width: 80px; max-height: 60px;">';
        $content .= '</td>';

        // Název
        $content .= '<td>';
        $content .= '<div class="image-title"><b>' . htmlspecialchars($image['title'] ?: $image['original_name']) . '</b></div>';
        $content .= '<div class="image-original-name"><small>' . htmlspecialchars($image['original_name']) . '</small></div>';
        $content .= '</td>';

        // Popis
        $content .= '<td>';
        $content .= '<div class="image-description">' . htmlspecialchars($image['description']) . '</div>';
        $content .= '</td>';

        // Velikost
        $content .= '<td>';
        $content .= '<div class="image-size">' . $this->formatBytes((int)$image['file_size']) . '</div>';
        $content .= '</td>';

        // Rozměry
        $content .= '<td>';
        $content .= '<div class="image-dimensions">' . $image['width'] . '×' . $image['height'] . '</div>';
        $content .= '</td>';

        // Status
        $content .= '<td>' . $statusBadge . '</td>';

        // Datum smazání (pouze v koši)
        if ($isTrashView) {
            $content .= '<td>';
            $content .= '<div class="image-deleted-at">' . $deletedAt . '</div>';
            $content .= '</td>';
        }

        // Akce
        $content .= '<td>';
        $content .= '<div class="action-buttons">';

        if (!$isTrashView) {
            // Aktivní obrázek
			$content .= '<a href="' . $this->baseUrl . 'admin/images/edit/' . $image['id'] . '" class="btn btn-sm btn-primary" title="' . $this->t('admin.images.actions.edit') . '">✏️</a>';
            $content .= '<a href="' . $imageUrl . '" target="_blank" class="btn btn-sm btn-info" title="' . $this->t('admin.images.actions.view') . '">👁️</a>';
            $content .= '<form action="' . $this->baseUrl . 'admin/images/delete/' . $image['id'] . '" method="post" style="display: inline;">';
            $content .= '<input type="hidden" name="csrf_token" value="' . $this->csrf->getToken() . '">';
            $content .= '<button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'' . $this->t('admin.images.confirm.delete') . '\')" title="' . $this->t('admin.images.actions.delete') . '">🗑️</button>';
            $content .= '</form>';

        } else {
            // Obrázek v koši - obnovit nebo trvale smazat
            $content .= '<a href="' . $this->baseUrl . 'admin/images/restore/' . $image['id'] . '" class="btn btn-sm btn-success" title="' . $this->t('admin.images.actions.restore') . '">↶</a>';
            $content .= '<a href="' . $this->baseUrl . 'admin/images/confirm-permanent-delete/' . $image['id'] . '" class="btn btn-sm btn-danger" title="' . $this->t('admin.images.actions.permanent_delete') . '">␡</a>';
        }

        $content .= '</div>';
        $content .= '</td>';

        $content .= '</tr>';

        return $content;
    }

    /**
     * Vykreslí formulář pro nahrání obrázku
     *
     * @param array $galleries Seznam galerií pro přiřazení
     * @return string HTML upload formuláře
     */
    private function renderUploadForm(array $galleries): string
    {
        $csrfField = $this->csrf->getTokenField();

        $content = '<div class="form-container">';
        $content .= '<form action="' . $this->baseUrl . 'admin/images/upload-image" method="post" enctype="multipart/form-data">';
        $content .= $csrfField;

        $content .= '<div class="form-group">';
        $content .= '<label for="image_file">' . $this->t('admin.images.upload.select_file') . ':</label>';
        $content .= '<input type="file" id="image_file" name="image_file" accept="image/*" class="form-control-file" required>';
        $content .= '<small class="form-text">' . $this->t('admin.images.upload.file_help') . '</small>';
        $content .= '</div>';

        $content .= '<div class="form-group">';
        $content .= '<label for="image_title">' . $this->t('admin.images.upload.title_label') . ':</label>';
        $content .= '<input type="text" id="image_title" name="title" class="form-control" placeholder="' . $this->t('admin.images.upload.title_placeholder') . '">';
        $content .= '</div>';

        $content .= '<div class="form-group">';
        $content .= '<label for="image_description">' . $this->t('admin.images.upload.description') . ':</label>';
        $content .= '<textarea id="image_description" name="description" class="form-control" placeholder="' . $this->t('admin.images.upload.description_placeholder') . '"></textarea>';
        $content .= '</div>';

        $content .= '<div class="form-group">';
        $content .= '<label>' . $this->t('admin.images.upload.assign_to_galleries') . ':</label>';
        $content .= '<div class="gallery-checkboxes">';

        foreach ($galleries as $gallery) {
            $content .= '<div class="form-check">';
            $content .= '<input type="checkbox" id="gallery_' . $gallery['id'] . '" name="galleries[]" value="' . $gallery['id'] . '" class="form-check-input">';
            $content .= '<label for="gallery_' . $gallery['id'] . '" class="form-check-label">' . htmlspecialchars($gallery['name']) . '</label>';
            $content .= '</div>';
        }

        $content .= '</div>';
        $content .= '</div>';

        $content .= '<div class="form-actions">';
        $content .= '<button type="submit" class="btn btn-primary">' . $this->t('admin.images.upload.submit') . '</button>';
        $content .= '<a href="' . $this->baseUrl . 'admin/images/manage" class="btn btn-secondary">' . $this->t('admin.images.upload.cancel') . '</a>';
        $content .= '</div>';

        $content .= '</form>';
        $content .= '</div>';

        return $content;
    }

    /**
     * Formátuje velikost souboru
     *
     * @param int $bytes Velikost v bytech
     * @return string Formátovaná velikost
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Přeloží textový klíč
     *
     * @param string $key Klíč pro překlad
     * @param array $params Parametry pro nahrazení
     * @return string Přeložený text
     */
    private function t(string $key, array $params = []): string
    {
        return Config::text($key, $params);
    }

    /**
     * Ověří administrátorská oprávnění
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
	 * Zobrazí formulář pro úpravu obrázku
	 *
	 * @param int $id ID obrázku
	 * @return string HTML obsah edit formuláře
	 */
	public function edit(int $id): string
	{
	    $this->requireAdmin();

	    $image = $this->imageService->getImage($id);

	    if (!$image) {
	        header("Location: {$this->baseUrl}admin/images/manage?error=not_found");
	        exit;
	    }

	    // Potřebujeme GalleryService pro seznam galerií
	    $galleryService = new \App\Services\GalleryService($this->db);
	    $allGalleries = $galleryService->getAllGalleries();
	    $currentGalleries = $this->imageService->getImageGalleries($id);

	    $currentGalleryIds = array_column($currentGalleries, 'id');

	    $content = '<div class="page-header">';
	    $content .= '<h1>' . $this->t('admin.images.edit.title') . '</h1>';
	    $content .= '<div class="header-actions">';
	    $content .= '<a href="' . $this->baseUrl . 'admin/images/manage" class="btn btn-secondary">' . $this->t('admin.images.back_to_images') . '</a>';
	    $content .= '</div>';
	    $content .= '</div>';

	    $content .= $this->renderEditForm($image, $allGalleries, $currentGalleryIds);

	    return $this->adminLayout->wrap($content, $this->t('admin.images.edit.title'));
	}

	/**
	 * Zpracuje aktualizaci obrázku
	 *
	 * @param int $id ID obrázku
	 * @return void
	 */
	public function update(int $id): void
	{
	    $this->requireAdmin();

	    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	        header("Location: {$this->baseUrl}admin/images/edit/" . $id);
	        exit;
	    }

	    // Kontrola CSRF tokenu
	    if (!$this->csrf->validateToken($_POST['csrf_token'] ?? '')) {
	        $_SESSION['flash_message'] = [
	            'type' => 'error',
	            'message' => $this->t('admin.images.edit.csrf_error')
	        ];
	        header("Location: {$this->baseUrl}admin/images/edit/" . $id . "?error=csrf");
	        exit;
	    }

	    $data = [
	        'title' => $_POST['title'] ?? '',
	        'description' => $_POST['description'] ?? '',
	        'galleries' => $_POST['galleries'] ?? []
	    ];

	    // Aktualizovat informace o obrázku
	    $imageUpdated = $this->imageService->updateImage($id, $data);

	    // Aktualizovat galerie
	    $galleriesUpdated = $this->imageService->updateImageGalleries($id, $data['galleries']);

	    if ($imageUpdated || $galleriesUpdated) {
	        $_SESSION['flash_message'] = [
	            'type' => 'success',
	            'message' => $this->t('admin.images.edit.success_message')
	        ];
	        header("Location: {$this->baseUrl}admin/images/manage?updated=success");
	    } else {
	        $_SESSION['flash_message'] = [
	            'type' => 'error',
	            'message' => $this->t('admin.images.edit.error_message')
	        ];
	        header("Location: {$this->baseUrl}admin/images/edit/" . $id . "?error=update_failed");
	    }
	    exit;
	}

	/**
	 * Vykreslí formulář pro úpravu obrázku
	 *
	 * @param array $image Data obrázku
	 * @param array $allGalleries Seznam všech galerií
	 * @param array $currentGalleryIds Pole ID aktuálně přiřazených galerií
	 * @return string HTML edit formuláře
	 */
	private function renderEditForm(array $image, array $allGalleries, array $currentGalleryIds): string
	{
	    $csrfField = $this->csrf->getTokenField();

	    $thumbUrl = $this->baseUrl . 'uploads/gallery/' . $image['thumb_path'];
	    $imageUrl = $this->baseUrl . 'uploads/gallery/' . $image['file_path'];

	    $content = '<div class="form-container">';
	    $content .= '<form action="' . $this->baseUrl . 'admin/images/update/' . $image['id'] . '" method="post">';
	    $content .= $csrfField;

	    // Náhled obrázku
	    $content .= '<div class="image-preview mb-4">';
	    $content .= '<div class="card">';
	    $content .= '<div class="card-header">';
	    $content .= '<h5>' . $this->t('admin.images.edit.image_preview') . '</h5>';
	    $content .= '</div>';
	    $content .= '<div class="card-body text-center">';
	    $content .= '<img src="' . $thumbUrl . '" alt="' . htmlspecialchars($image['title']) . '" style="max-width: 300px; max-height: 200px;" class="mb-2">';
	    $content .= '<br>';
	    $content .= '<a href="' . $imageUrl . '" target="_blank" class="btn btn-sm btn-outline-primary">' . $this->t('admin.images.edit.view_original') . '</a>';
	    $content .= '</div>';
	    $content .= '</div>';
	    $content .= '</div>';

	    // Informace o souboru
	    $content .= '<div class="card mb-4">';
	    $content .= '<div class="card-header">';
	    $content .= '<h5>' . $this->t('admin.images.edit.file_info') . '</h5>';
	    $content .= '</div>';
	    $content .= '<div class="card-body">';
	    $content .= '<p><strong>' . $this->t('admin.images.form.original_name') . ':</strong> ' . htmlspecialchars($image['original_name']) . '</p>';
	    $content .= '<p><strong>' . $this->t('admin.images.form.size') . ':</strong> ' . $this->formatBytes((int)$image['file_size']) . '</p>';
	    $content .= '<p><strong>' . $this->t('admin.images.form.dimensions') . ':</strong> ' . $image['width'] . '×' . $image['height'] . '</p>';
	    $content .= '<p><strong>' . $this->t('admin.images.form.format') . ':</strong> ' . $image['mime_type'] . '</p>';
	    $content .= '<p><strong>' . $this->t('admin.images.edit.uploaded') . ':</strong> ' . date('d.m.Y H:i', strtotime($image['created_at'])) . '</p>';
	    $content .= '</div>';
	    $content .= '</div>';

	    // Formulářové pole pro název
	    $content .= '<div class="form-group">';
	    $content .= '<label for="image_title">' . $this->t('admin.images.form.name') . ' *</label>';
	    $content .= '<input type="text" id="image_title" name="title" value="' . htmlspecialchars($image['title']) . '" class="form-control" required>';
	    $content .= '</div>';

	    // Formulářové pole pro popis
	    $content .= '<div class="form-group">';
	    $content .= '<label for="image_description">' . $this->t('admin.images.form.description') . '</label>';
	    $content .= '<textarea id="image_description" name="description" class="form-control" rows="4">' . htmlspecialchars($image['description']) . '</textarea>';
	    $content .= '</div>';

	    // Výběr galerií
	    $content .= '<div class="form-group">';
	    $content .= '<label>' . $this->t('admin.images.edit.assign_to_galleries') . '</label>';
	    $content .= '<div class="gallery-checkboxes">';

	    foreach ($allGalleries as $gallery) {
	        $checked = in_array($gallery['id'], $currentGalleryIds) ? 'checked' : '';
	        $content .= '<div class="form-check">';
	        $content .= '<input type="checkbox" id="gallery_' . $gallery['id'] . '" name="galleries[]" value="' . $gallery['id'] . '" class="form-check-input" ' . $checked . '>';
	        $content .= '<label for="gallery_' . $gallery['id'] . '" class="form-check-label">' . htmlspecialchars($gallery['name']) . '</label>';
	        $content .= '</div>';
	    }

	    $content .= '</div>';
	    $content .= '<small class="form-text">' . $this->t('admin.images.edit.galleries_help') . '</small>';
	    $content .= '</div>';

	    // Využití obrázku
	    $usage = $this->imageService->getImageUsage((int)$image['id']);
	    if (!empty($usage['articles']) || !empty($usage['galleries'])) {
	        $content .= '<div class="card mb-4">';
	        $content .= '<div class="card-header">';
	        $content .= '<h5>' . $this->t('admin.images.edit.usage_info') . '</h5>';
	        $content .= '</div>';
	        $content .= '<div class="card-body">';

	        if (!empty($usage['articles'])) {
	            $content .= '<p><strong>' . $this->t('admin.images.edit.used_in_articles') . ':</strong> ' . count($usage['articles']) . '</p>';
	        }

	        if (!empty($usage['galleries'])) {
	            $content .= '<p><strong>' . $this->t('admin.images.edit.used_in_galleries') . ':</strong> ' . count($usage['galleries']) . '</p>';
	        }

	        $content .= '</div>';
	        $content .= '</div>';
	    }

	    $content .= '<div class="form-actions">';
	    $content .= '<button type="submit" class="btn btn-primary">' . $this->t('admin.images.edit.submit') . '</button>';
	    $content .= '<a href="' . $this->baseUrl . 'admin/images/manage" class="btn btn-secondary">' . $this->t('admin.images.edit.cancel') . '</a>';
	    $content .= '</div>';

	    $content .= '</form>';
	    $content .= '</div>';

	    return $content;
	}
}