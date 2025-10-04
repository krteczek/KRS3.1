<?php
// app/Core/AdminMenu.php
declare(strict_types=1);

namespace App\Core;

use App\Auth\LoginService;
use App\Core\Config;

/**
 * Třída pro generování navigačního menu administračního rozhraní
 *
 * Vytváří HTML navigační menu pro administrační část aplikace
 * s odkazy na jednotlivé sekce. Menu se zobrazí pouze přihlášeným uživatelům.
 * Všechny texty jsou lokalizovány pomocí konfiguračního systému.
 *
 * @package App\Core
 * @author KRS3
 * @version 3.0
 */
class AdminMenu
{
    /**
     * @var LoginService Služba pro ověření přihlášení uživatele
     */
    private LoginService $authService;

    /**
     * @var string Základní URL aplikace
     */
    private string $baseUrl;

    /**
     * @param LoginService $authService Služba pro autentizaci uživatelů
     */
    public function __construct(LoginService $authService)
    {
        $this->authService = $authService;
        $this->baseUrl = Config::site('base_path', '');
    }

    /**
     * Vykreslí navigační menu administračního rozhraní
     *
     * Generuje HTML menu s odkazy na hlavní sekce administrace.
     * Pokud uživatel není přihlášen, vrátí prázdný řetězec.
     *
     * @return string HTML kód menu nebo prázdný řetězec
     *
     * @example
     * $menu = $adminMenu->render();
     * echo $menu; // vypíše navigační menu
     */
    public function render(): string
    {
        if (!$this->authService->isLoggedIn()) {
            return '';
        }

        $user = $this->authService->getUser();

        return <<<HTML
<nav class="admin-menu">
    <div class="menu-header">
        <strong>{$this->translate('admin.navigation.administration')}</strong>
        <span>({$user['username']})</span>
    </div>
    <ul>
        <li><a href="{$this->baseUrl}">{$this->translate('navigation.home')}</a></li>
        <li><a href="{$this->baseUrl}admin">{$this->translate('admin.navigation.dashboard')}</a></li>
        <li><a href="{$this->baseUrl}admin/articles">{$this->translate('admin.navigation.articles')}</a></li>
        <li><a href="{$this->baseUrl}admin/gallery">{$this->translate('admin.navigation.gallery')}</a></li>
        <li><a href="{$this->baseUrl}admin/users">{$this->translate('admin.navigation.users')}</a></li>
        <li><a href="{$this->baseUrl}logout">{$this->translate('navigation.logout')}</a></li>
    </ul>
</nav>
HTML;
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