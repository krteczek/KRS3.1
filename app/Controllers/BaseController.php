<?php
// app/Controllers/BaseController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Template;
use App\Core\Config;

abstract class BaseController
{
    public function __construct(
        protected Template $template,
        protected string $baseUrl
    ) {}

    protected function renderPage(string $contentView, array $contentData = [], string $pageKey = 'home', array $titleParams = []): string
    {
        $siteName = Config::site('name');

        // Dynamický title z konfigurace
        $title = Config::text('pages.' . $pageKey, array_merge([
            'site_name' => $siteName
        ], $titleParams), $siteName); // fallback na siteName

        $layoutData = [
            'title' => $title,
            'content' => $this->template->render($contentView, array_merge($contentData, [
                'baseUrl' => $this->baseUrl,
                'siteName' => $siteName
            ])),
            'baseUrl' => $this->baseUrl,
            'siteName' => $siteName,
            'user' => ['isLoggedIn' => false]
        ];

        return $this->template->render('layouts/frontend.php', $layoutData);
    }
}