<?php
// app/Services/MenuService.php
declare(strict_types=1);

namespace App\Services;

/**
 * Služba pro generování navigačních menu
 *
 * @package App\Services
 * @author KRS3
 * @version 1.0
 */
class MenuService
{
    public function __construct(
        private CategoryService $categoryService,
        private string $baseUrl
    ) {}

    /**
     * Vygeneruje horizontální menu s podporou zanoření
     *
     * @return string HTML kód menu
     */
    public function generateHorizontalMenu(): string
    {
        $categoryTree = $this->categoryService->getCategoryTree();
        return $this->buildMenuFromTree($categoryTree);
    }

    /**
     * Rekurzivně sestaví menu ze stromu kategorií
     *
     * @param array $tree Stromová struktura kategorií
     * @param int $depth Hloubka zanoření
     * @return string HTML kód menu
     */
    private function buildMenuFromTree(array $tree, int $depth = 0): string
    {
        $html = '<ul class="navbar-nav me-auto mb-2 mb-lg-0">';

        foreach ($tree as $category) {
            $hasChildren = !empty($category['children']);
            $isDropdown = $hasChildren && $depth === 0; // Dropdown pouze na první úrovni

            $html .= '<li class="nav-item' . ($isDropdown ? ' dropdown' : '') . '">';

            $linkClass = 'nav-link' . ($isDropdown ? ' dropdown-toggle' : '');
            $linkAttributes = $isDropdown ? ' role="button" data-bs-toggle="dropdown" aria-expanded="false"' : '';
            $url = $this->baseUrl . '/category/' . $category['slug'];

            $html .= '<a class="' . $linkClass . '" href="' . $url . '"' . $linkAttributes . '>';
            $html .= htmlspecialchars($category['name']);
            $html .= '</a>';

            if ($hasChildren) {
                $dropdownClass = $isDropdown ? 'dropdown-menu' : 'nav-item-children';
                $html .= '<ul class="' . $dropdownClass . '">';
                $html .= $this->buildMenuFromTree($category['children'], $depth + 1);
                $html .= '</ul>';
            }

            $html .= '</li>';
        }

        $html .= '</ul>';
        return $html;
    }

    /**
     * Vygeneruje vertikální sidebar menu
     *
     * @return string HTML kód sidebar menu
     */
    public function generateSidebarMenu(): string
    {
        $categoryTree = $this->categoryService->getCategoryTree();
        return $this->buildSidebarFromTree($categoryTree);
    }

    /**
     * Rekurzivně sestaví sidebar menu
     *
     * @param array $tree Stromová struktura kategorií
     * @param int $depth Hloubka zanoření
     * @return string HTML kód sidebar menu
     */
    private function buildSidebarFromTree(array $tree, int $depth = 0): string
    {
        $html = '<ul class="sidebar-nav">';

        foreach ($tree as $category) {
            $hasChildren = !empty($category['children']);
            $padding = $depth * 20;

            $html .= '<li class="sidebar-item">';
            $url = $this->baseUrl . '/category/' . $category['slug'];

            $html .= '<a class="sidebar-link" href="' . $url . '" style="padding-left: ' . $padding . 'px;">';
            $html .= htmlspecialchars($category['name']);
            if ($hasChildren) {
                $html .= ' <span class="sidebar-arrow">›</span>';
            }
            $html .= '</a>';

            if ($hasChildren) {
                $html .= $this->buildSidebarFromTree($category['children'], $depth + 1);
            }

            $html .= '</li>';
        }

        $html .= '</ul>';
        return $html;
    }
}