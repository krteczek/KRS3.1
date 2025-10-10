<?php
// app/Services/MenuService.php - VYLEPŠENÁ VERZE
declare(strict_types=1);

namespace App\Services;

use App\Core\Config;

/**
 * Služba pro generování navigačních menu
 *
 * @package App\Services
 * @author KRS3
 * @version 1.1
 */
class MenuService
{
    public function __construct(
        private CategoryService $categoryService,
        private string $baseUrl
    ) {}

    /**
     * Vygeneruje horizontální menu s podporou zanoření
     */
    public function generateHorizontalMenu(): string
    {
        $categoryTree = $this->categoryService->getCategoryTree();

        if (empty($categoryTree)) {
            return '<ul class="navbar-nav me-auto mb-2 mb-lg-0"></ul>';
        }

        return $this->buildHorizontalMenu($categoryTree);
    }

    /**
     * Vygeneruje vertikální sidebar menu
     */
    public function generateSidebarMenu(): string
    {
        $categoryTree = $this->categoryService->getCategoryTree();

        if (empty($categoryTree)) {
            return '<div class="sidebar"><ul class="sidebar-nav"></ul></div>';
        }

        return $this->buildSidebarMenu($categoryTree);
    }

    /**
     * Rekurzivně sestaví horizontální menu
     */
    private function buildHorizontalMenu(array $tree, int $depth = 0): string
    {
        $html = '<ul class="navbar-nav me-auto mb-2 mb-lg-0">';

        foreach ($tree as $category) {
            $hasChildren = !empty($category['children']);
            $isDropdown = $hasChildren && $depth === 0;

            $html .= '<li class="nav-item' . ($isDropdown ? ' dropdown' : '') . '">';

            $linkClass = 'nav-link' . ($isDropdown ? ' dropdown-toggle' : '');
            $linkAttributes = $isDropdown ? ' role="button" data-bs-toggle="dropdown" aria-expanded="false"' : '';
            $url = $this->baseUrl . 'category/' . $category['slug'];

            $html .= '<a class="' . $linkClass . '" href="' . $url . '"' . $linkAttributes . '>';
            $html .= $this->getCategoryIcon($category) . htmlspecialchars($category['name']);
            $html .= '</a>';

            if ($hasChildren) {
                $dropdownClass = $isDropdown ? 'dropdown-menu' : 'nav-item-children';
                $html .= '<ul class="' . $dropdownClass . '">';
                $html .= $this->buildHorizontalMenu($category['children'], $depth + 1);
                $html .= '</ul>';
            }

            $html .= '</li>';
        }

        $html .= '</ul>';
        return $html;
    }

    /**
     * Rekurzivně sestaví sidebar menu
     */
    private function buildSidebarMenu(array $tree, int $depth = 0): string
    {
        $html = '<div class="sidebar">';
        $html .= '<div class="sidebar-header">';
        $html .= '<h5>' . Config::text('ui.categories', [], 'Kategorie') . '</h5>';
        $html .= '</div>';
        $html .= '<ul class="sidebar-nav">';

        foreach ($tree as $category) {
            $hasChildren = !empty($category['children']);
            $isActive = $this->isCategoryActive($category);
            $activeClass = $isActive ? ' active' : '';

            $html .= '<li class="sidebar-item' . $activeClass . '">';

            $url = $this->baseUrl . 'category/' . $category['slug'];

            $html .= '<a class="sidebar-link" href="' . $url . '" data-depth="' . $depth . '">';
            $html .= $this->getCategoryIcon($category);
            $html .= '<span class="sidebar-text">' . htmlspecialchars($category['name']) . '</span>';

            if ($hasChildren) {
                $html .= '<span class="sidebar-arrow">›</span>';
            }

            // Počet článků v kategorii
            $articleCount = $this->getArticleCountForCategory($category['id']);
            if ($articleCount > 0) {
                $html .= '<span class="sidebar-badge">' . $articleCount . '</span>';
            }

            $html .= '</a>';

            if ($hasChildren) {
                $html .= $this->buildSidebarMenu($category['children'], $depth + 1);
            }

            $html .= '</li>';
        }

        $html .= '</ul>';
        $html .= '</div>';
        return $html;
    }

    /**
     * Získá ikonu pro kategorii
     */
    private function getCategoryIcon(array $category): string
    {
        $icons = [
            'technologie' => 'fas fa-microchip',
            'vesmir' => 'fas fa-rocket',
            'novinky' => 'fas fa-newspaper',
            'galerie-zivota' => 'fas fa-images',
            'default' => 'fas fa-folder'
        ];

        $slug = $category['slug'];
        $icon = $icons[$slug] ?? $icons['default'];

        return '<i class="' . $icon . ' me-2"></i>';
    }

    /**
     * Zkontroluje, zda je kategorie aktivní
     */
    private function isCategoryActive(array $category): bool
    {
        $currentUrl = $_SERVER['REQUEST_URI'] ?? '';
        $categoryUrl = 'category/' . $category['slug'];

        return strpos($currentUrl, $categoryUrl) !== false;
    }

    /**
     * Získá počet článků v kategorii
     */
    private function getArticleCountForCategory(int $categoryId): int
    {
        try {
            // Tuto metodu přidáme do CategoryService
            return $this->categoryService->getArticleCount($categoryId);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Vygeneruje breadcrumb navigaci pro kategorii
     */
    public function generateCategoryBreadcrumb(int $categoryId): string
    {
        $breadcrumbs = $this->categoryService->getCategoryBreadcrumb($categoryId);

        if (empty($breadcrumbs)) {
            return '';
        }

        $html = '<nav aria-label="breadcrumb">';
        $html .= '<ol class="breadcrumb">';

        // Domů
        $html .= '<li class="breadcrumb-item">';
        $html .= '<a href="' . $this->baseUrl . '">';
        $html .= '<i class="fas fa-home me-1"></i>';
        $html .= Config::text('ui.home', [], 'Domů');
        $html .= '</a>';
        $html .= '</li>';

        // Kategorie
        foreach ($breadcrumbs as $index => $breadcrumb) {
            $isLast = $index === count($breadcrumbs) - 1;

            $html .= '<li class="breadcrumb-item' . ($isLast ? ' active' : '') . '">';

            if (!$isLast) {
                $html .= '<a href="' . $this->baseUrl . 'category/' . $breadcrumb['slug'] . '">';
            }

            $html .= htmlspecialchars($breadcrumb['name']);

            if (!$isLast) {
                $html .= '</a>';
            }

            $html .= '</li>';
        }

        $html .= '</ol>';
        $html .= '</nav>';

        return $html;
    }
}