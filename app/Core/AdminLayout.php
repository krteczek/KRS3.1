<?php
// app/Core/AdminLayout.php
declare(strict_types=1);

namespace App\Core;

use App\Auth\LoginService;
use App\Core\Config;

/**
 * T��da pro spr�vu layoutu administra�n�ho rozhran�
 *
 * Poskytuje metody pro obalen� obsahu administra�n�ch str�nek
 * do kompletn�ho HTML layoutu v�etn� menu, titulku a styl�.
 * Automaticky �e�� lokalizaci defaultn�ho titulku.
 *
 * @package App\Core
 * @author KRS3
 * @version 3.0
 */
class AdminLayout
{
    /**
     * @param LoginService $authService Slu�ba pro autentizaci u�ivatel�
     * @param string $baseUrl Z�kladn� URL aplikace
     */
    public function __construct(
        private LoginService $authService,
        private string $baseUrl
    ) {}

    /**
     * Obal� obsah administra�n� str�nky do kompletn�ho HTML layoutu
     *
     * Vytvo�� kompletn� HTML str�nku s admin menu, titulkem a styly.
     * Pokud nen� zad�n titulok, pou�ije se lokalizovan� default z konfigurace.
     *
     * @param string $content HTML obsah str�nky
     * @param string $title Titulok str�nky (pr�zdn� = pou�ije se defaultn� lokalizovan�)
     * @return string Kompletn� HTML str�nka s admin layoutem
     *
     * @example
     * $html = $adminLayout->wrap($content, 'Spr�va �l�nk�');
     * $html = $adminLayout->wrap($content); // pou�ije defaultn� titulok
     */
    public function wrap(string $content, string $title = ''): string
    {
        $menu = (new AdminMenu($this->authService))->render();

        // Pokud nen� p�ed�n title, pou�ij lokalizovan� default
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