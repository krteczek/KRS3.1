<?php
// app/Core/AdminLayout.php
declare(strict_types=1);

namespace App\Core;

use App\Auth\LoginService;
use App\Core\Config;

/**
 * Tøída pro správu layoutu administraèního rozhraní
 *
 * Poskytuje metody pro obalení obsahu administraèních stránek
 * do kompletního HTML layoutu vèetnì menu, titulku a stylù.
 * Automaticky øeší lokalizaci defaultního titulku.
 *
 * @package App\Core
 * @author KRS3
 * @version 3.0
 */
class AdminLayout
{
    /**
     * @param LoginService $authService Služba pro autentizaci uživatelù
     * @param string $baseUrl Základní URL aplikace
     */
    public function __construct(
        private LoginService $authService,
        private string $baseUrl
    ) {}

    /**
     * Obalí obsah administraèní stránky do kompletního HTML layoutu
     *
     * Vytvoøí kompletní HTML stránku s admin menu, titulkem a styly.
     * Pokud není zadán titulok, použije se lokalizovaný default z konfigurace.
     *
     * @param string $content HTML obsah stránky
     * @param string $title Titulok stránky (prázdný = použije se defaultní lokalizovaný)
     * @return string Kompletní HTML stránka s admin layoutem
     *
     * @example
     * $html = $adminLayout->wrap($content, 'Správa èlánkù');
     * $html = $adminLayout->wrap($content); // použije defaultní titulok
     */
    public function wrap(string $content, string $title = ''): string
    {
        $menu = (new AdminMenu($this->authService))->render();

        // Pokud není pøedán title, použij lokalizovaný default
        if (empty($title)) {
            $title = Config::text('admin.navigation.administration');
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <link rel="stylesheet" href="{$this->baseUrl}/css/admin.css">
</head>
<body>
    {$menu}
    <div class="admin-container">
        <div class="admin-content">
            {$content}
        </div>
    </div>
</body>
</html>
HTML;
    }
}