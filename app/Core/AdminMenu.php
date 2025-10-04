<?php
// app/Core/AdminMenu.php
declare(strict_types=1);

namespace App\Core;

use App\Auth\LoginService;
use App\Core\Config;

/**
 * T��da pro generov�n� naviga�n�ho menu administra�n�ho rozhran�
 *
 * Vytv��� HTML naviga�n� menu pro administra�n� ��st aplikace
 * s odkazy na jednotliv� sekce. Menu se zobraz� pouze p�ihl�en�m u�ivatel�m.
 * V�echny texty jsou lokalizov�ny pomoc� konfigura�n�ho syst�mu.
 *
 * @package App\Core
 * @author KRS3
 * @version 3.0
 */
class AdminMenu
{
    /**
     * @var LoginService Slu�ba pro ov��en� p�ihl�en� u�ivatele
     */
    private LoginService $authService;

    /**
     * @var string Z�kladn� URL aplikace
     */
    private string $baseUrl;

    /**
     * @param LoginService $authService Slu�ba pro autentizaci u�ivatel�
     */
    public function __construct(LoginService $authService)
    {
        $this->authService = $authService;
        $this->baseUrl = Config::site('base_path', '');
    }

    /**
     * Vykresl� naviga�n� menu administra�n�ho rozhran�
     *
     * Generuje HTML menu s odkazy na hlavn� sekce administrace.
     * Pokud u�ivatel nen� p�ihl�en, vr�t� pr�zdn� �et�zec.
     *
     * @return string HTML k�d menu nebo pr�zdn� �et�zec
     *
     * @example
     * $menu = $adminMenu->render();
     * echo $menu; // vyp�e naviga�n� menu
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
     * P�elo�� textov� kl�� pomoc� konfigura�n�ho syst�mu
     *
     * @param string $key Kl�� pro p�eklad
     * @return string P�elo�en� text
     */
    private function translate(string $key): string
    {
        return Config::text($key);
    }
}