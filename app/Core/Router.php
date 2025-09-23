<?php
//app/Core/Router.php

declare(strict_types=1);

namespace App\Core;

use App\Auth\LoginService;
use App\Database\DatabaseConnection;
use App\Security\CsrfProtection;
use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Controllers\ArticleController;
use App\Services\ArticleService;


class Router
{
private string $baseUrl;

    public function __construct(
        private LoginService $authService,
        private DatabaseConnection $db,
        private CsrfProtection $csrf,
        private AdminLayout $adminLayout
    ) {
        // Získej base URL z konfigurace
        $this->baseUrl = Config::site('base_path', '');
    }
    public function requireLoggedIn(): void {
        if (!$this->authService->isLoggedIn()) {
            header('Location: ' . $this->baseUrl . '/login');
            exit;
        }
    }

    public function showHomepage(): void {
        echo "<!DOCTYPE html>
        <html lang='cs'>
        <head>
            <meta charset='UTF-8'>
            <title>KRS Redakční systém</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; }
                .menu { background: #f4f4f4; padding: 15px; margin-bottom: 20px; }
                .menu a { margin-right: 15px; text-decoration: none; color: #333; }
                .menu a:hover { color: #007bff; }
            </style>
        </head>
        <body>
            <div class='menu'>" . $this->generateMenu() . "</div>
            <h1>Vítejte v redakčním systému!</h1>
            <p>Toto je úvodní stránka vašeho redakčního systému.</p>
            <p>Zkuste si: <a href='{$this->baseUrl}/login'>Přihlášení</a></p>
        </body>
        </html>";
    }

    public function generateMenu(): string {
        $menu = "<a href='{$this->baseUrl}/'>Domů</a>";

        if ($this->authService->isLoggedIn()) {
            $user = $this->authService->getUser();
            $menu .= "<a href='{$this->baseUrl}/admin'>Administrace</a>";
            $menu .= "<a href='{$this->baseUrl}/logout'>Odhlásit ({$user['username']})</a>";
        } else {
            $menu .= "<a href='{$this->baseUrl}/login'>Přihlásit</a>";
        }

        return $menu;
    }


}