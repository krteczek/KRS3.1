<?php
// app/Controllers/AdminController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\AdminLayout;
use App\Auth\LoginService;
use App\Core\Config;

/**
 * Controller pro hlavní administrační rozhraní
 *
 * Zpracovává zobrazení administračního dashboardu s přehledem
 * rychlých akcí a základních informací o systému.
 * Slouží jako vstupní bod do administrační části aplikace.
 *
 * @package App\Controllers
 * @author KRS3
 * @version 3.0
 */
class AdminController
{
    /**
     * @param LoginService $authService Služba pro ověření přihlášení
     * @param string $baseUrl Základní URL aplikace
     * @param AdminLayout $adminLayout Layout administračního rozhraní
     */
    public function __construct(
        private LoginService $authService,
        private string $baseUrl,
        private \App\Core\AdminLayout $adminLayout
    ) {}

    /**
     * Zobrazí administrační dashboard s přehledem a rychlými akcemi
     *
     * Vytvoří přehledovou stránku administrace s uvítací zprávou
     * a odkazy na hlavní správcovské sekce systému.
     *
     * @return string HTML obsah administračního dashboardu
     *
     * @example
     * $dashboard = $adminController->dashboard();
     * echo $dashboard; // zobrazí administrační přehled
     */
    public function dashboard(): string
    {
        $content = <<<HTML
<h1>{$this->translate('admin.titles.administration')}</h1>
<p>{$this->translate('admin.messages.welcome_admin')}</p>

<h2>{$this->translate('admin.titles.quick_actions')}:</h2>
<ul>
    <li><a href='{$this->baseUrl}admin/articles'>{$this->translate('admin.titles.manage_articles')}</a></li>
    <li><a href='{$this->baseUrl}admin/categories'>{$this->translate('admin.titles.manage_categories')}</a></li>
    <li><a href='{$this->baseUrl}admin/gallery'>{$this->translate('admin.titles.manage_gallery')}</a></li>
    <li><a href='{$this->baseUrl}admin/users'>{$this->translate('admin.titles.manage_users')}</a></li>
</ul>
HTML;

        return $this->adminLayout->wrap($content, $this->translate('admin.navigation.dashboard'));
    }

    /**
     * Přeloží textový klíč pomocí konfiguračního systému
     *
     * @param string $key Klíč pro překlad
     * @return string Přeložený text
     */
    private function translate(string $key): string
    {
        return Config::text($key);
    }
}