<?php
//app/Controllers/AdminControllers.php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\AdminLayout;

use App\Auth\LoginService;

class AdminController
{
	public function __construct(
	    private LoginService $authService,
	    private string $baseUrl,
	    private \App\Core\AdminLayout $adminLayout // ← přidej tento parametr
	) {}

	public function dashboard(): string
	{
    $content = <<<HTML
<h1>Administrace</h1>
<p>Vítejte v administraci redakčního systému</p>

<h2>Rychlé akce:</h2>
<ul>
    <li><a href='{$this->baseUrl}admin/articles'>Správa článků</a></li>
    <li><a href='{$this->baseUrl}admin/gallery'>Správa galerie</a></li>
    <li><a href='{$this->baseUrl}admin/users'>Správa uživatelů</a></li>
</ul>
HTML;

    return $this->adminLayout->wrap($content, 'Dashboard');
}
}