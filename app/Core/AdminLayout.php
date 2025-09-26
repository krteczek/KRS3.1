<?php
// app/Core/AdminLayout.php
declare(strict_types=1);

namespace App\Core;

use App\Auth\LoginService;

class AdminLayout
{
    private LoginService $authService;
    private string $baseUrl;

    public function __construct(LoginService $authService, string $baseUrl)
    {
        $this->authService = $authService;
        $this->baseUrl = $baseUrl;
    }

    public function wrap(string $content, string $title = 'Administrace'): string
    {
        $menu = (new AdminMenu($this->authService))->render();

        return <<<HTML
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title} - KRS</title>
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