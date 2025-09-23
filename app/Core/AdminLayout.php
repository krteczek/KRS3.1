<?php
//app/Core/AdminLayout.php
declare(strict_types=1);

namespace App\Core;

class AdminLayout
{
	public function __construct(private LoginService $authService) {
	    // Získej base URL z konfigurace
	    $this->baseUrl = Config::site('base_path', '');
	}
    public function wrap(string $content, string $title = 'Administrace'): string
    {
        $menu = $this->adminMenu->render();

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