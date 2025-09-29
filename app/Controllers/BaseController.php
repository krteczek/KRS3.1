<?php
// app/Controllers/BaseController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Template;
use App\Core\Config;
use App\Auth\LoginService;

abstract class BaseController
{
    public function __construct(
        protected Template $template,
        protected string $baseUrl,
        protected LoginService $authService // ← PŘIDEJ
    ) {}

protected function renderPage(string $contentView, array $contentData = [], string $pageKey = 'home', array $titleParams = []): string
{
    $siteName = Config::site('name');

    $title = Config::text('pages.' . $pageKey, array_merge([
        'site_name' => $siteName
    ], $titleParams), $siteName);

    $userData = $this->authService->getUser();

    $layoutData = [
        'title' => $title,
        'content' => $this->template->render($contentView, array_merge($contentData, [
            'baseUrl' => $this->baseUrl,
            'siteName' => $siteName
        ])),
        'baseUrl' => $this->baseUrl,
        'siteName' => $siteName,
        'user' => [
            'isLoggedIn' => $this->authService->isLoggedIn(),
            'username' => $userData['username'] ?? null, // ← z getUser()
            'id' => $userData['id'] ?? null,
            'role' => $userData['role'] ?? null
        ]
    ];

    return $this->template->render('layouts/frontend.php', $layoutData);
}
}