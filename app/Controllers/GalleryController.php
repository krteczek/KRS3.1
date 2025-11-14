<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\GalleryService;
use App\Auth\LoginService;
use App\Security\CsrfProtection;
use App\Core\AdminLayout;
use App\Core\Config;
use App\Logger\Logger;

/**
 * Controller pro správu galerií
 *
 * Poskytuje funkcionalitu pro správu stromové struktury galerií,
 * včetně práce s košem a trvalým mazáním.
 *
 * @package App\Controllers
 * @author KRS3
 * @version 1.0
 */
class GalleryController
{
    private \App\Logger\Logger $logger;

    /**
     * Konstruktor
     *
     * @param GalleryService $galleryService Služba pro práci s galerií
     * @param LoginService $authService Služba pro autentizaci
     * @param CsrfProtection $csrf Ochrana proti CSRF útokům
     * @param string $baseUrl Základní URL aplikace
     * @param AdminLayout $adminLayout Layout administračního rozhraní
     */
    public function __construct(
        private GalleryService $galleryService,
        private LoginService $authService,
        private CsrfProtection $csrf,
        private string $baseUrl,
        private AdminLayout $adminLayout
    ) {
        $this->logger = \App\Logger\Logger::getInstance();
    }

    /**
     * Zobrazí hlavní stránku galerie se stromovou strukturou
     *
     * @return string HTML obsah galerie
     */
    public function index(): string
    {
        $this->requireAdmin();

        $showTrash = isset($_GET['show']) && $_GET['show'] === 'trash';

        if ($showTrash) {
            $galleries = $this->galleryService->getTrashedGalleries();
            $activeTab = 'trash';
        } else {
            $galleries = $this->galleryService->getAllGalleries();
            $activeTab = 'active';
        }

        $content = '<div class="admin-container">';
        $content .= '<div class="admin-content">';

        // Page header s tlačítkem
        $content .= '<div class="page-header">';
        $content .= '<h1>' . $this->t('admin.gallery.manage.title') . '</h1>';

        if (!$showTrash) {
            $content .= '<a href="' . $this->baseUrl . 'admin/gallery/create" class="btn btn-primary">';
            $content .= '＋ ' . $this->t('admin.gallery.create_gallery_button');
            $content .= '</a>';
        }

        $content .= '</div>';

        // Taby (aktivní/koš)
        $content .= '<div class="tabs">';
        $content .= '<a href="' . $this->baseUrl . 'admin/gallery" class="tab ' . ($activeTab === 'active' ? 'active' : '') . '">';
        $content .= $this->t('admin.gallery.tabs.active');
        $content .= '</a>';
        $content .= '<a href="' . $this->baseUrl . 'admin/gallery/manage?show=trash" class="tab ' . ($activeTab === 'trash' ? 'active' : '') . '">';
        $content .= $this->t('admin.gallery.tabs.trash');
        $content .= '</a>';
        $content .= '</div>';

        if (empty($galleries)) {
            $content .= '<div class="empty-state">';
            if ($showTrash) {
                $content .= '<h3>' . $this->t('admin.gallery.trash.empty_title') . '</h3>';
                $content .= '<p>' . $this->t('admin.gallery.trash.empty_text') . '</p>';
            } else {
                $content .= '<h3>' . $this->t('admin.gallery.empty.title') . '</h3>';
                $content .= '<p>' . $this->t('admin.gallery.manage.empty_text') . '</p>';
            }
            $content .= '</div>';
        } else {
            $content .= '<div class="table-responsive">';
            $content .= '<table class="table table-striped">';
            $content .= '<thead>';
            $content .= '<tr>';
            $content .= '<th>' . $this->t('admin.gallery.manage.table.name') . '</th>';
            $content .= '<th>' . $this->t('admin.gallery.manage.table.parent') . '</th>';
            $content .= '<th>' . $this->t('admin.gallery.manage.table.images_count') . '</th>';
            $content .= '<th>' . $this->t('admin.gallery.manage.table.status') . '</th>';
            if ($showTrash) {
                $content .= '<th>' . $this->t('admin.gallery.manage.table.deleted_at') . '</th>';
            }
            $content .= '<th>' . $this->t('admin.gallery.manage.table.actions') . '</th>';
            $content .= '</tr>';
            $content .= '</thead>';
            $content .= '<tbody>';

            foreach ($galleries as $gallery) {
                $content .= $this->renderGalleryRow($gallery, $showTrash);
            }

            $content .= '</tbody>';
            $content .= '</table>';
            $content .= '</div>';
        }

        $content .= '</div>';
        $content .= '</div>';

        return $this->adminLayout->wrap($content, $this->t('admin.gallery.manage.title'));
    }

    /**
     * Zobrazí formulář pro vytvoření nové galerie
     *
     * @return string HTML obsah formuláře
     */
    public function create(): string
    {
        $this->requireAdmin();

        $allowedParents = $this->galleryService->getAllowedParentGalleries();

        $content = '<div class="page-header">';
        $content .= '<h1>' . $this->t('admin.gallery.create.title') . '</h1>';
        $content .= '<div class="header-actions">';
        $content .= '<a href="' . $this->baseUrl . 'admin/gallery" class="btn btn-secondary">' . $this->t('admin.gallery.back_to_gallery') . '</a>';
        $content .= '</div>';
        $content .= '</div>';

        $content .= $this->renderGalleryForm(null, $allowedParents);

        return $this->adminLayout->wrap($content, $this->t('admin.gallery.create.title'));
    }

    /**
     * Zobrazí formulář pro úpravu existující galerie
     *
     * @param int $id ID galerie
     * @return string HTML obsah formuláře
     */
    public function edit(int $id): string
    {
        $this->requireAdmin();

        $gallery = $this->galleryService->getGallery($id);

        if (!$gallery) {
            header("Location: {$this->baseUrl}admin/gallery?error=not_found");
            exit;
        }

        $allowedParents = $this->galleryService->getAllowedParentGalleries($id);

        $content = '<div class="page-header">';
        $content .= '<h1>' . $this->t('admin.gallery.edit.title') . '</h1>';
        $content .= '<div class="header-actions">';
        $content .= '<a href="' . $this->baseUrl . 'admin/gallery" class="btn btn-secondary">' . $this->t('admin.gallery.back_to_gallery') . '</a>';
        $content .= '</div>';
        $content .= '</div>';

        $content .= $this->renderGalleryForm($gallery, $allowedParents);

        return $this->adminLayout->wrap($content, $this->t('admin.gallery.edit.title'));
    }

    /**
     * Zobrazí detail galerie s obrázky
     *
     * @param int $id ID galerie
     * @return string HTML obsah detailu galerie
     */
    public function view(int $id): string
    {
        $this->requireAdmin();

        $gallery = $this->galleryService->getGallery($id);
        $images = $this->galleryService->getGalleryImages($id);

        if (!$gallery) {
            header("Location: {$this->baseUrl}admin/gallery");
            exit;
        }

        $content = '<div class="page-header">';
        $content .= '<h1>' . htmlspecialchars($gallery['name']) . '</h1>';
        $content .= '<div class="header-actions">';
        $content .= '<a href="' . $this->baseUrl . 'admin/gallery" class="btn btn-secondary">' . $this->t('admin.gallery.back_to_gallery') . '</a>';
        $content .= '<a href="' . $this->baseUrl . 'admin/gallery/edit/' . $id . '" class="btn btn-primary">' . $this->t('admin.gallery.actions.edit') . '</a>';
        $content .= '</div>';
        $content .= '</div>';

        if (!empty($gallery['description'])) {
            $content .= '<div class="gallery-description">';
            $content .= '<p>' . htmlspecialchars($gallery['description']) . '</p>';
            $content .= '</div>';
        }

        if (empty($images)) {
            $content .= '<div class="empty-state">';
            $content .= '<h3>' . $this->t('admin.gallery.view.empty_images') . '</h3>';
            $content .= '<p>' . $this->t('admin.gallery.view.empty_images_text') . '</p>';
            $content .= '<a href="' . $this->baseUrl . 'admin/images/upload" class="btn btn-primary">' . $this->t('admin.images.upload_button') . '</a>';
            $content .= '</div>';
        } else {
            $content .= '<div class="gallery-stats">';
            $content .= '<p>' . $this->t('admin.gallery.stats.total', ['count' => count($images)]) . '</p>';
            $content .= '</div>';
            $content .= '<div class="gallery-grid">';
            foreach ($images as $image) {
                $content .= $this->renderImageCard($image);
            }
            $content .= '</div>';
        }

        return $this->adminLayout->wrap($content, htmlspecialchars($gallery['name']));
    }

    /**
     * Uloží novou galerii
     *
     * @return void
     */
	public function store(): void
	{
	    $this->requireAdmin();

	    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	        header("Location: {$this->baseUrl}admin/gallery/create");
	        exit;
	    }

	    $data = [
	        'name' => $_POST['name'] ?? '',
	        'description' => $_POST['description'] ?? '',
	        'parent_id' => $_POST['parent_id'] ?? null,
	        'featured_image_id' => !empty($_POST['featured_image_id']) ? (int)$_POST['featured_image_id'] : null
	    ];

	    if (empty($data['name'])) {
	        header("Location: {$this->baseUrl}admin/gallery/create?error=name_required");
	        exit;
	    }

	    $result = $this->galleryService->createGalleryWithValidation($data);

	    if ($result['success']) {
	        $_SESSION['flash_message'] = [
	            'type' => 'success',
	            'message' => $result['message']
	        ];
	        header("Location: {$this->baseUrl}admin/gallery?created=success");
	    } else {
	        $_SESSION['flash_message'] = [
	            'type' => 'error',
	            'message' => $result['message']
	        ];
	        header("Location: {$this->baseUrl}admin/gallery/create?error=validation_failed");
	    }
	    exit;
	}

    /**
     * Aktualizuje existující galerii
     *
     * @param int $id ID galerie
     * @return void
     */
/**
 * Aktualizuje existující galerii
 */
public function update(int $id): void
{
    $this->requireAdmin();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: {$this->baseUrl}admin/gallery/edit/" . $id);
        exit;
    }

    $data = [
        'name' => $_POST['name'] ?? '',
        'description' => $_POST['description'] ?? '',
        'parent_id' => $_POST['parent_id'] ?? null,
        'featured_image_id' => !empty($_POST['featured_image_id']) ? (int)$_POST['featured_image_id'] : null
    ];

    if (empty($data['name'])) {
        header("Location: {$this->baseUrl}admin/gallery/edit/" . $id . "?error=name_required");
        exit;
    }

    // Použijeme novou metodu pro aktualizaci s tématickým obrázkem
    $success = $this->galleryService->updateGalleryWithFeaturedImage($id, $data);

    if ($success) {
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => 'Galerie byla úspěšně aktualizována.'
        ];
        header("Location: {$this->baseUrl}admin/gallery?updated=success");
    } else {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => 'Chyba při aktualizaci galerie.'
        ];
        header("Location: {$this->baseUrl}admin/gallery/edit/" . $id . "?error=update_failed");
    }
    exit;
}
    /**
     * Zobrazí potvrzovací stránku pro smazání galerie
     *
     * @param int $id ID galerie
     * @return string HTML obsah potvrzovací stránky
     */
    public function confirmDelete(int $id): string
    {
        $this->requireAdmin();

        $deleteInfo = $this->galleryService->getGalleryDeleteInfo($id);

        if (!$deleteInfo['exists']) {
            header("Location: {$this->baseUrl}admin/gallery?error=not_found");
            exit;
        }

        $gallery = $deleteInfo['gallery'];

        $content = '<div class="page-header">';
        $content .= '<h1>' . $this->t('admin.gallery.delete.confirm_title') . '</h1>';
        $content .= '<div class="header-actions">';
        $content .= '<a href="' . $this->baseUrl . 'admin/gallery" class="btn btn-secondary">' . $this->t('admin.gallery.back_to_gallery') . '</a>';
        $content .= '</div>';
        $content .= '</div>';

        $content .= '<div class="alert alert-warning">';
        $content .= '<h4>' . $this->t('admin.gallery.delete.warning') . '</h4>';
        $content .= '<p>' . $this->t('admin.gallery.delete.confirm_message', ['name' => htmlspecialchars($gallery['name'])]) . '</p>';
        $content .= '</div>';

        // Informace o galerii
        $content .= '<div class="card mb-4">';
        $content .= '<div class="card-header">';
        $content .= '<h5>' . $this->t('admin.gallery.delete.gallery_info') . '</h5>';
        $content .= '</div>';
        $content .= '<div class="card-body">';
        $content .= '<p><strong>' . $this->t('admin.gallery.form.name') . ':</strong> ' . htmlspecialchars($gallery['name']) . '</p>';
        if (!empty($gallery['description'])) {
            $content .= '<p><strong>' . $this->t('admin.gallery.form.description') . ':</strong> ' . htmlspecialchars($gallery['description']) . '</p>';
        }
        $content .= '<p><strong>' . $this->t('admin.gallery.delete.images_count') . ':</strong> ' . $deleteInfo['images_count'] . '</p>';
        $content .= '</div>';
        $content .= '</div>';

        // Informace o dětech, které budou přesunuty
        if ($deleteInfo['children_count'] > 0) {
            $content .= '<div class="card mb-4">';
            $content .= '<div class="card-header">';
            $content .= '<h5>' . $this->t('admin.gallery.delete.children_will_be_promoted', ['count' => $deleteInfo['children_count']]) . '</h5>';
            $content .= '</div>';
            $content .= '<div class="card-body">';
            $content .= '<p>' . $this->t('admin.gallery.delete.children_promote_message') . '</p>';

            $content .= '<ul>';
            foreach ($deleteInfo['children'] as $child) {
                $content .= '<li>' . htmlspecialchars($child['name']) . '</li>';
            }
            $content .= '</ul>';
            $content .= '</div>';
            $content .= '</div>';
        }

        // Formulář pro potvrzení smazání
        $csrfField = $this->csrf->getTokenField();

        $content .= '<div class="card">';
        $content .= '<div class="card-body">';
        $content .= '<form action="' . $this->baseUrl . 'admin/gallery/delete/' . $id . '" method="post">';
        $content .= $csrfField;

        $content .= '<div class="form-check mb-3">';
        $content .= '<input type="checkbox" id="confirm_delete" name="confirm_delete" class="form-check-input" required>';
        $content .= '<label for="confirm_delete" class="form-check-label">';
        $content .= $this->t('admin.gallery.delete.confirm_checkbox', ['name' => htmlspecialchars($gallery['name'])]);
        $content .= '</label>';
        $content .= '</div>';

        $content .= '<div class="form-actions">';
        $content .= '<button type="submit" class="btn btn-danger">' . $this->t('admin.gallery.delete.confirm_button') . '</button>';
        $content .= '<a href="' . $this->baseUrl . 'admin/gallery" class="btn btn-secondary">' . $this->t('admin.gallery.delete.cancel_button') . '</a>';
        $content .= '</div>';

        $content .= '</form>';
        $content .= '</div>';
        $content .= '</div>';

        return $this->adminLayout->wrap($content, $this->t('admin.gallery.delete.confirm_title'));
    }

    /**
     * Smaže galerii a přesune děti na vyšší úroveň
     *
     * @param int $id ID galerie
     * @return void
     */
    public function delete(int $id): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}admin/gallery/confirm-delete/" . $id);
            exit;
        }

        // Kontrola potvrzení
        if (!isset($_POST['confirm_delete']) || $_POST['confirm_delete'] !== 'on') {
            header("Location: {$this->baseUrl}admin/gallery/confirm-delete/" . $id . "?error=not_confirmed");
            exit;
        }

        $result = $this->galleryService->deleteGalleryAndPromoteChildren($id);

        if ($result['success']) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => $this->t('admin.gallery.delete.success_message', [
                    'name' => $result['gallery_name'],
                    'children_count' => $result['promoted_children']
                ])
            ];
        } else {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'message' => $result['message']
            ];
        }

        header("Location: {$this->baseUrl}admin/gallery");
        exit;
    }

    /**
     * Obnoví galerii z koše
     *
     * @param int $id ID galerie
     * @return void
     */
    public function restore(int $id): void
    {
        $this->requireAdmin();

        $result = $this->galleryService->restoreGallery($id);

        if ($result) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => 'Galerie byla úspěšně obnovena z koše.'
            ];
        } else {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'message' => 'Galerii se nepodařilo obnovit.'
            ];
        }

        header("Location: {$this->baseUrl}admin/gallery/manage?show=trash");
        exit;
    }

    /**
     * Zobrazí potvrzení trvalého smazání galerie
     *
     * @param int $id ID galerie
     * @return string HTML obsah potvrzovací stránky
     */
    public function confirmPermanentDelete(int $id): string
    {
        $this->requireAdmin();

        $gallery = $this->galleryService->getGalleryIncludingTrashed($id);

        if (!$gallery || !$gallery['deleted_at']) {
            header("Location: {$this->baseUrl}admin/gallery/manage?show=trash&error=not_found");
            exit;
        }

        $content = '<div class="admin-container">';
        $content .= '<div class="admin-content">';

        $content .= '<div class="page-header">';
        $content .= '<h1>' . $this->t('admin.gallery.permanent_delete.confirm_title') . '</h1>';
        $content .= '<div class="header-actions">';
        $content .= '<a href="' . $this->baseUrl . 'admin/gallery/manage?show=trash" class="btn btn-secondary">' . $this->t('admin.gallery.back_to_trash') . '</a>';
        $content .= '</div>';
        $content .= '</div>';

        $content .= '<div class="alert alert-danger">';
        $content .= '<h4>' . $this->t('admin.gallery.permanent_delete.warning') . '</h4>';
        $content .= '<p>' . $this->t('admin.gallery.permanent_delete.confirm_message', ['name' => htmlspecialchars($gallery['name'])]) . '</p>';
        $content .= '<p><strong>' . $this->t('admin.gallery.permanent_delete.irreversible') . '</strong></p>';
        $content .= '</div>';

        // Informace o galerii
        $content .= '<div class="card mb-4">';
        $content .= '<div class="card-header">';
        $content .= '<h5>' . $this->t('admin.gallery.permanent_delete.gallery_info') . '</h5>';
        $content .= '</div>';
        $content .= '<div class="card-body">';
        $content .= '<p><strong>' . $this->t('admin.gallery.form.name') . ':</strong> ' . htmlspecialchars($gallery['name']) . '</p>';
        if (!empty($gallery['description'])) {
            $content .= '<p><strong>' . $this->t('admin.gallery.form.description') . ':</strong> ' . htmlspecialchars($gallery['description']) . '</p>';
        }

        // Získat informace o obrázcích v galerii
        $images = $this->galleryService->getGalleryImages($id);
        $content .= '<p><strong>' . $this->t('admin.gallery.delete.images_count') . ':</strong> ' . count($images) . '</p>';

        if (count($images) > 0) {
            $content .= '<div class="alert alert-warning mt-3">';
            $content .= '<strong>' . $this->t('admin.gallery.permanent_delete.images_warning') . '</strong><br>';
            $content .= $this->t('admin.gallery.permanent_delete.images_warning_text');
            $content .= '</div>';
        }
        $content .= '</div>';
        $content .= '</div>';

        // Formulář pro potvrzení
        $csrfField = $this->csrf->getTokenField();

        $content .= '<div class="card">';
        $content .= '<div class="card-body">';
        $content .= '<form action="' . $this->baseUrl . 'admin/gallery/permanent-delete/' . $id . '" method="post">';
        $content .= $csrfField;

        $content .= '<div class="form-check mb-3">';
        $content .= '<input type="checkbox" id="confirm_permanent_delete" name="confirm_permanent_delete" class="form-check-input" required>';
        $content .= '<label for="confirm_permanent_delete" class="form-check-label">';
        $content .= $this->t('admin.gallery.permanent_delete.confirm_checkbox', ['name' => htmlspecialchars($gallery['name'])]);
        $content .= '</label>';
        $content .= '</div>';

        $content .= '<div class="form-actions">';
        $content .= '<button type="submit" class="btn btn-danger">' . $this->t('admin.gallery.permanent_delete.confirm_button') . '</button>';
        $content .= '<a href="' . $this->baseUrl . 'admin/gallery/manage?show=trash" class="btn btn-secondary">' . $this->t('admin.gallery.permanent_delete.cancel_button') . '</a>';
        $content .= '</div>';

        $content .= '</form>';
        $content .= '</div>';
        $content .= '</div>';

        $content .= '</div>';
        $content .= '</div>';

        return $this->adminLayout->wrap($content, $this->t('admin.gallery.permanent_delete.confirm_title'));
    }

    /**
     * Trvale smaže galerii z koše
     *
     * @param int $id ID galerie
     * @return void
     */
    public function permanentDelete(int $id): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}admin/gallery/confirm-permanent-delete/" . $id);
            exit;
        }

        // Kontrola CSRF tokenu
        if (!$this->csrf->validateToken($_POST['csrf_token'] ?? '')) {
            header("Location: {$this->baseUrl}admin/gallery/confirm-permanent-delete/" . $id . "?error=csrf");
            exit;
        }

        // Kontrola potvrzení
        if (!isset($_POST['confirm_permanent_delete']) || $_POST['confirm_permanent_delete'] !== 'on') {
            header("Location: {$this->baseUrl}admin/gallery/confirm-permanent-delete/" . $id . "?error=not_confirmed");
            exit;
        }

        // POUŽÍVÁME NOVOU METODU PRO KONTROLU EXISTENCE
        $gallery = $this->galleryService->getGalleryIncludingTrashed($id);
        if (!$gallery || !$gallery['deleted_at']) {
            header("Location: {$this->baseUrl}admin/gallery/manage?show=trash&error=not_found");
            exit;
        }

        $result = $this->galleryService->permanentDeleteGallery($id);

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

        header("Location: {$this->baseUrl}admin/gallery/manage?show=trash");
        exit;
    }

    /**
     * Vykreslí řádek galerie v tabulce
     *
     * @param array $gallery Data galerie
     * @param bool $isTrashView Zda se zobrazuje koš
     * @return string HTML řádku tabulky
     */
    private function renderGalleryRow(array $gallery, bool $isTrashView = false): string
    {
        $parentName = $this->getParentGalleryName((int)$gallery['parent_id']);
        $imagesCount = count($this->galleryService->getGalleryImages((int)$gallery['id']));

        // Status badge
        $statusBadge = '<span class="badge badge-success">' . $this->t('admin.gallery.status.active') . '</span>';
        if ($gallery['deleted_at']) {
            $statusBadge = '<span class="badge badge-danger">' . $this->t('admin.gallery.status.deleted') . '</span>';
        }

        // Formátování data smazání
        $deletedAt = '';
        if ($gallery['deleted_at']) {
            $deletedDate = new \DateTime($gallery['deleted_at']);
            $deletedAt = $deletedDate->format('d. m. Y H:i');
        }

        $content = '<tr>';

        // Název a popis
        $content .= '<td>';
        $content .= '<div class="gallery-title"><b>' . htmlspecialchars($gallery['name']) . '</b></div>';
        if (!empty($gallery['description'])) {
            $content .= '<div class="gallery-description">' . htmlspecialchars($gallery['description']) . '</div>';
        }
        $content .= '</td>';

        // Nadřazená galerie
        $content .= '<td>';
        $content .= '<div class="gallery-parent">' . htmlspecialchars($parentName) . '</div>';
        $content .= '</td>';

        // Počet obrázků
        $content .= '<td>';
        $content .= '<div class="gallery-images-count">' . $imagesCount . '</div>';
        $content .= '</td>';

        // Status
        $content .= '<td>' . $statusBadge . '</td>';

        // Datum smazání (pouze v koši)
        if ($isTrashView) {
            $content .= '<td>';
            $content .= '<div class="gallery-deleted-at">' . $deletedAt . '</div>';
            $content .= '</td>';
        }

        // Akce
        $content .= '<td>';
        $content .= '<div class="action-buttons">';

        if (!$isTrashView) {
            // Aktivní galerie
            $content .= '<a href="' . $this->baseUrl . 'admin/gallery/view/' . $gallery['id'] . '" class="btn btn-sm btn-info" title="' . $this->t('admin.gallery.view.button') . '">👁️</a>';
            $content .= '<a href="' . $this->baseUrl . 'admin/gallery/edit/' . $gallery['id'] . '" class="btn btn-sm btn-primary" title="' . $this->t('admin.gallery.edit.button') . '">✏️</a>';
            $content .= '<a href="' . $this->baseUrl . 'admin/gallery/confirm-delete/' . $gallery['id'] . '" class="btn btn-sm btn-danger" title="' . $this->t('admin.gallery.delete.button') . '">🗑️</a>';
        } else {
            // Galerie v koši - obnovit nebo trvale smazat
            $content .= '<a href="' . $this->baseUrl . 'admin/gallery/restore/' . $gallery['id'] . '" class="btn btn-sm btn-success" title="' . $this->t('admin.gallery.restore.button') . '">↶</a>';
            $content .= '<a href="' . $this->baseUrl . 'admin/gallery/confirm-permanent-delete/' . $gallery['id'] . '" class="btn btn-sm btn-danger" title="' . $this->t('admin.gallery.permanent_delete.button') . '">␡</a>';
        }

        $content .= '</div>';
        $content .= '</td>';

        $content .= '</tr>';

        return $content;
    }

    /**
     * Vykreslí formulář pro galerii
     *
     * @param array|null $gallery Existující data galerie pro editaci
     * @param array $allowedParents Povolené nadřazené galerie
     * @return string HTML formuláře
     */
	private function renderGalleryForm(?array $gallery = null, array $allowedParents = []): string
	{
	    $csrfField = $this->csrf->getTokenField();

	    $action = $gallery
	        ? $this->baseUrl . 'admin/gallery/update/' . $gallery['id']
	        : $this->baseUrl . 'admin/gallery/store';

	    $name = $gallery['name'] ?? '';
	    $description = $gallery['description'] ?? '';
	    $parentId = $gallery['parent_id'] ?? '';

	    // OPRAVA: Správná inicializace proměnných pro tématický obrázek
	    $featuredImageId = $gallery['featured_image_id'] ?? null;
	    $currentFeaturedImage = null;

	    // Načtení informací o aktuálním tématickém obrázku pouze pokud máme galerii a featured_image_id
	    if ($gallery && !empty($gallery['featured_image_id'])) {
	        $currentFeaturedImage = $this->galleryService->getFeaturedImage((int)$gallery['id']);
	    }

	    $content = '<div class="form-container">';
	    $content .= '<form action="' . $action . '" method="post" id="galleryForm">';
	    $content .= $csrfField;

	    // Formulářové pole pro název
	    $content .= '<div class="form-group">';
	    $content .= '<label for="gallery_name">' . $this->t('admin.gallery.form.name') . ' *</label>';
	    $content .= '<input type="text" id="gallery_name" name="name" value="' . htmlspecialchars($name) . '" class="form-control" required>';
	    $content .= '</div>';

	    // Formulářové pole pro popis
	    $content .= '<div class="form-group">';
	    $content .= '<label for="gallery_description">' . $this->t('admin.gallery.form.description') . '</label>';
	    $content .= '<textarea id="gallery_description" name="description" class="form-control">' . htmlspecialchars($description) . '</textarea>';
	    $content .= '</div>';

	    // Formulářové pole pro nadřazenou galerii
	    $content .= '<div class="form-group">';
	    $content .= '<label for="gallery_parent">' . $this->t('admin.gallery.form.parent') . '</label>';
	    $content .= '<select id="gallery_parent" name="parent_id" class="form-control">';
	    $content .= '<option value="">' . $this->t('admin.gallery.form.no_parent') . '</option>';

	    foreach ($allowedParents as $parentGallery) {
	        $selected = $parentId == $parentGallery['id'] ? 'selected' : '';

	        // Přidáme odsazení pro lepší přehlednost hierarchie
	        $level = $this->getGalleryLevel($parentGallery);
	        $indent = str_repeat('&nbsp;&nbsp;&nbsp;', $level);

	        $content .= '<option value="' . $parentGallery['id'] . '" ' . $selected . '>' . $indent . htmlspecialchars($parentGallery['name']) . '</option>';
	    }

	    $content .= '</select>';
	    $content .= '<small class="form-text">' . $this->t('admin.gallery.form.parent_help') . '</small>';

	    // Informace o omezeních
	    if ($gallery) {
	        $content .= '<div class="alert alert-info mt-2">';
	        $content .= '<small>' . $this->t('admin.gallery.form.parent_restrictions') . '</small>';
	        $content .= '</div>';
	    }

	    $content .= '</div>';

	    // Pole pro tématický obrázek
	    $content .= $this->renderFeaturedImageField($featuredImageId, $currentFeaturedImage);

	    $content .= '<div class="form-actions">';
	    $content .= '<button type="submit" class="btn btn-primary">';
	    $content .= $gallery ? $this->t('admin.gallery.edit.submit') : $this->t('admin.gallery.create.submit');
	    $content .= '</button>';
	    $content .= '<a href="' . $this->baseUrl . 'admin/gallery" class="btn btn-secondary">' . $this->t('admin.gallery.form.cancel') . '</a>';
	    $content .= '</div>';

	    $content .= '</form>';
	    $content .= '</div>';

	    return $content;
	}

    /**
     * Vykreslí kartu obrázku
     *
     * @param array $image Data obrázku
     * @return string HTML karty obrázku
     */
    private function renderImageCard(array $image): string
    {
        $imageUrl = $this->baseUrl . 'uploads/gallery/' . $image['file_path'];
        $thumbUrl = $this->baseUrl . 'uploads/gallery/' . $image['thumb_path'];

        $content = '<div class="gallery-card">';
        $content .= '<img src="' . $thumbUrl . '" alt="' . htmlspecialchars($image['title']) . '" class="gallery-card-image">';
        $content .= '<div class="gallery-card-info">';
        $content .= '<h3>' . htmlspecialchars($image['title']) . '</h3>';
        $content .= '<p>' . htmlspecialchars($image['description']) . '</p>';
        $content .= '<p><small>' . $this->t('admin.gallery.image.size') . ': ' . $this->formatBytes((int)$image['file_size']) . '</small></p>';
        $content .= '<p><small>' . $this->t('admin.gallery.image.dimensions') . ': ' . $image['width'] . 'x' . $image['height'] . '</small></p>';

        $content .= '<div class="gallery-card-actions">';
        $content .= '<a href="' . $imageUrl . '" target="_blank" class="btn btn-sm btn-primary">' . $this->t('admin.gallery.image.view') . '</a>';
        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';

        return $content;
    }

    /**
     * Získá název nadřazené galerie
     *
     * @param int|null $parentId ID nadřazené galerie
     * @return string Název galerie nebo text "Žádná"
     */
    private function getParentGalleryName(?int $parentId): string
    {
        if (!$parentId) {
            return $this->t('admin.gallery.form.no_parent');
        }

        $parent = $this->galleryService->getGallery($parentId);
        return $parent ? $parent['name'] : $this->t('admin.gallery.unknown_parent');
    }

    /**
     * Zjistí úroveň galerie v hierarchii
     *
     * @param array $gallery Data galerie
     * @return int Úroveň (0 = nejvyšší úroveň)
     */
    private function getGalleryLevel(array $gallery): int
    {
        $level = 0;
        $currentGallery = $gallery;

        while ($currentGallery['parent_id'] !== null) {
            $level++;
            $parent = $this->galleryService->getGallery((int)$currentGallery['parent_id']);
            if (!$parent) break;
            $currentGallery = $parent;
        }

        return $level;
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

	    public function featuredImageModal(): string
    {
        $this->requireAdmin();

        $page = (int)($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        $perPage = 20;

        $imageService = new \App\Services\ImageService($this->db);
        $imagesData = $imageService->getAvailableImages($page, $perPage, $search);

        $content = $this->renderFeaturedImageModal($imagesData);

        // Vrátíme HTML pro AJAX
        header('Content-Type: application/json');
        return json_encode([
            'success' => true,
            'html' => $content,
            'pagination' => $imagesData['pagination']
        ]);
    }

    /**
     * Vykreslí modal pro výběr tématického obrázku
     *
     * @param array $imagesData Data obrázků a stránkování
     * @return string HTML modalu
     */
    private function renderFeaturedImageModal(array $imagesData): string
    {
        $images = $imagesData['images'];
        $pagination = $imagesData['pagination'];

        $content = '<div class="featured-image-modal">';
        $content .= '<div class="modal-header">';
        $content .= '<h3>' . $this->t('admin.gallery.featured_image.modal_title') . '</h3>';
        $content .= '<button type="button" class="close-modal" onclick="closeFeaturedImageModal()">&times;</button>';
        $content .= '</div>';

        $content .= '<div class="modal-body">';

        // Vyhledávací pole
        $content .= '<div class="search-box">';
        $content .= '<input type="text" id="featuredImageSearch"
                            placeholder="' . $this->t('admin.gallery.featured_image.search_placeholder') . '"
                            onkeyup="searchFeaturedImages(this.value)">';
        $content .= '</div>';

        // Mřížka obrázků
        $content .= '<div class="images-grid" id="featuredImagesGrid">';

        if (empty($images)) {
            $content .= '<div class="no-images">';
            $content .= '<p>' . $this->t('admin.gallery.featured_image.no_images') . '</p>';
            $content .= '</div>';
        } else {
            foreach ($images as $image) {
                $content .= $this->renderImageThumbnail($image);
            }
        }

        $content .= '</div>';

        // Stránkování
        if ($pagination['total_pages'] > 1) {
            $content .= $this->renderPagination($pagination);
        }

        $content .= '</div>'; // .modal-body
        $content .= '</div>'; // .featured-image-modal

        return $content;
    }

    /**
     * Vykreslí thumbnail obrázku pro výběr
     *
     * @param array $image Data obrázku
     * @return string HTML thumbnailu
     */
    private function renderImageThumbnail(array $image): string
    {
        $thumbUrl = $this->baseUrl . 'uploads/gallery/' . $image['thumb_path'];
        $dimensions = $image['width'] . '×' . $image['height'];
        $fileSize = $this->formatBytes((int)$image['file_size']);
        $uploadDate = date('d.m.Y', strtotime($image['created_at']));

        $content = '<div class="image-thumbnail" data-image-id="' . $image['id'] . '">';
        $content .= '<div class="image-preview">';
        $content .= '<img src="' . $thumbUrl . '" alt="' . htmlspecialchars($image['title']) . '"
                         onclick="selectFeaturedImage(' . $image['id'] . ', this)">';
        $content .= '</div>';

        $content .= '<div class="image-info">';
        $content .= '<div class="image-title">' . htmlspecialchars($image['title'] ?: $image['original_name']) . '</div>';
        $content .= '<div class="image-meta">';
        $content .= '<span class="dimensions">' . $dimensions . '</span>';
        $content .= '<span class="size">' . $fileSize . '</span>';
        $content .= '</div>';
        $content .= '<div class="image-date">' . $uploadDate . '</div>';
        $content .= '</div>';

        $content .= '<button type="button" class="select-btn"
                            onclick="selectFeaturedImage(' . $image['id'] . ', this)">
                    ' . $this->t('admin.gallery.featured_image.select') . '
                    </button>';
        $content .= '</div>';

        return $content;
    }

    /**
     * Vykreslí stránkování pro modal
     *
     * @param array $pagination Data stránkování
     * @return string HTML stránkování
     */
    private function renderPagination(array $pagination): string
    {
        $content = '<div class="modal-pagination">';

        if ($pagination['current_page'] > 1) {
            $content .= '<button type="button" class="page-btn"
                                onclick="loadFeaturedImagesPage(' . ($pagination['current_page'] - 1) . ')">
                        ← Předchozí
                        </button>';
        }

        $content .= '<span class="page-info">';
        $content .= 'Stránka ' . $pagination['current_page'] . ' z ' . $pagination['total_pages'];
        $content .= '</span>';

        if ($pagination['current_page'] < $pagination['total_pages']) {
            $content .= '<button type="button" class="page-btn"
                                onclick="loadFeaturedImagesPage(' . ($pagination['current_page'] + 1) . ')">
                        Další →
                        </button>';
        }

        $content .= '</div>';
        return $content;
    }

    /**
     * Zpracuje výběr tématického obrázku
     *
     * @param int $galleryId ID galerie
     * @return string JSON response
     */
    public function selectFeaturedImage(int $galleryId): string
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return json_encode(['success' => false, 'message' => 'Neplatný požadavek']);
        }

        // Kontrola CSRF tokenu
        if (!$this->csrf->validateToken($_POST['csrf_token'] ?? '')) {
            return json_encode(['success' => false, 'message' => 'Neplatný CSRF token']);
        }

        $imageId = isset($_POST['image_id']) ? (int)$_POST['image_id'] : null;

        $success = $this->galleryService->setFeaturedImage($galleryId, $imageId);

        header('Content-Type: application/json');
        return json_encode([
            'success' => $success,
            'message' => $success ? 'Tématický obrázek byl nastaven.' : 'Chyba při nastavování obrázku.',
            'image_id' => $imageId
        ]);
    }

    /**
     * Získá informace o obrázku pro AJAX
     *
     * @param int $imageId ID obrázku
     * @return string JSON response
     */
    public function getImageInfo(int $imageId): string
    {
        $this->requireAdmin();

        $image = $this->galleryService->getImagePreview($imageId);

        header('Content-Type: application/json');
        if ($image) {
            $image['thumb_url'] = $this->baseUrl . 'uploads/gallery/' . $image['thumb_path'];
            return json_encode(['success' => true, 'image' => $image]);
        } else {
            return json_encode(['success' => false, 'message' => 'Obrázek nebyl nalezen']);
        }
    }


	/**
	 * Vykreslí pole pro výběr tématického obrázku
	 */
	private function renderFeaturedImageField(?int $currentImageId, ?array $currentImage): string
	{
	    $content = '<div class="form-group">';
	    $content .= '<label>' . $this->t('admin.gallery.form.featured_image') . '</label>';
	    $content .= '<div class="featured-image-selector">';

	    if ($currentImageId && $currentImage) {
	        // Máme platný obrázek - zobrazíme náhled
	        $thumbUrl = $this->baseUrl . 'uploads/gallery/' . $currentImage['thumb_path'];
	        $content .= '<div class="current-featured-image">';
	        $content .= '<img src="' . $thumbUrl . '" alt="' . htmlspecialchars($currentImage['title'] ?? '') . '">';
	        $content .= '<div class="image-details">';
	        $content .= '<strong>' . htmlspecialchars($currentImage['title'] ?? $currentImage['original_name']) . '</strong>';
	        $content .= '<br><small>' . ($currentImage['width'] ?? '0') . '×' . ($currentImage['height'] ?? '0') . '</small>';
	        $content .= '</div>';
	        $content .= '</div>';
	    } else {
	        // Nemáme obrázek - zobrazíme placeholder
	        $content .= '<div class="no-image-selected">';
	        $content .= '<span>' . $this->t('admin.gallery.featured_image.no_image_selected') . '</span>';
	        $content .= '</div>';
	    }

	    $content .= '<div class="featured-image-actions">';
	    $content .= '<button type="button" class="btn btn-outline-primary"
	                        onclick="openFeaturedImageModal()">
	                ' . $this->t('admin.gallery.featured_image.button') . '
	                </button>';

	    if ($currentImageId) {
	        $content .= '<button type="button" class="btn btn-outline-danger"
	                            onclick="removeFeaturedImage()">
	                    ' . $this->t('admin.gallery.featured_image.remove') . '
	                    </button>';
	    }

	    $content .= '</div>'; // .featured-image-actions

	    // Skryté pole pro ID obrázku
	    $content .= '<input type="hidden" name="featured_image_id" id="featuredImageId" value="' . ($currentImageId ?: '') . '">';

	    $content .= '</div>'; // .featured-image-selector
	    $content .= '</div>'; // .form-group

	    return $content;
	}
}