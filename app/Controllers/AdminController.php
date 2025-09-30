<?php
// app/Controllers/AdminController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\AdminLayout;
use App\Auth\LoginService;
use App\Core\Config;

class AdminController
{
    public function __construct(
        private LoginService $authService,
        private string $baseUrl,
        private \App\Core\AdminLayout $adminLayout
    ) {}

    public function dashboard(): string
    {
        $content = <<<HTML
<h1>{$this->translate('admin.titles.administration')}</h1>
<p>{$this->translate('admin.messages.welcome_admin')}</p>

<h2>{$this->translate('admin.titles.quick_actions')}:</h2>
<ul>
    <li><a href='{$this->baseUrl}admin/articles'>{$this->translate('admin.titles.manage_articles')}</a></li>
    <li><a href='{$this->baseUrl}admin/gallery'>{$this->translate('admin.titles.manage_gallery')}</a></li>
    <li><a href='{$this->baseUrl}admin/users'>{$this->translate('admin.titles.manage_users')}</a></li>
</ul>
HTML;

        return $this->adminLayout->wrap($content, $this->translate('admin.navigation.dashboard'));
    }

    private function translate(string $key): string
    {
        return Config::text($key);
    }
}