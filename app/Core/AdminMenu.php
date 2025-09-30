<?php
// app/Core/AdminMenu.php
declare(strict_types=1);

namespace App\Core;

use App\Auth\LoginService;

class AdminMenu
{
    private LoginService $authService;
    private string $baseUrl;

    public function __construct(LoginService $authService)
    {
        $this->authService = $authService;
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

    private function translate(string $key): string
    {
        return Config::text($key);
    }
}