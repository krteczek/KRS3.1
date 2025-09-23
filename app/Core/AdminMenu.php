<?php
//app/Core/AdminMenu.php
declare(strict_types=1);

namespace App\Core;

use App\Auth\LoginService;

class AdminMenu
{
	public function __construct(
	    private LoginService $authService
	) {
	    // Získej base URL z konfigurace
	    $this->baseUrl = Config::site('base_path', '');
	}
    public function render(): string
    {
        if (!$this->authService->isLoggedIn()) {
            return '';
        }

        $user = $this->authService->getUser();

	return <<<HTML
<nav class="admin-menu">
    <div class="menu-header">
        <strong>Administrace</strong>
        <span>({$user['username']})</span>
    </div>
    <ul>
        <li><a href="{$this->baseUrl}/admin">Dashboard</a></li>
        <li><a href="{$this->baseUrl}/admin/articles">Články</a></li>
        <li><a href="{$this->baseUrl}/admin/gallery">Galerie</a></li>
        <li><a href="{$this->baseUrl}/admin/users">Uživatelé</a></li>
        <li><a href="{$this->baseUrl}/logout">Odhlásit se</a></li>
    </ul>
</nav>
HTML;
    }
}