<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth\LoginService;
use App\Security\CsrfProtection;

class AuthController
{
    public function __construct(
        private LoginService $loginService,
        private CsrfProtection $csrf,
        private string $baseUrl
    ) {}

    public function showLoginForm(): string
    {
        // Pokud je uživatel již přihlášen, přesměruj do administrace
        if ($this->loginService->isLoggedIn()) {
            header('Location: ' . $this->baseUrl . '/admin');
            exit;
        }

        $csrfField = $this->csrf->getTokenField();
        $error = '';

        // Zobrazení chyby pokud existuje
        if (isset($_GET['error'])) {
            $error = match($_GET['error']) {
                '1' => '<div style="color: red; padding: 10px; margin: 10px 0; border: 1px solid red;">Špatné přihlašovací údaje!</div>',
                'csrf' => '<div style="color: red; padding: 10px; margin: 10px 0; border: 1px solid red;">Chyba zabezpečení CSRF!</div>',
                default => '<div style="color: red; padding: 10px; margin: 10px 0; border: 1px solid red;">Neplatný požadavek!</div>'
            };
        }

        return <<<HTML
        <!DOCTYPE html>
        <html lang="cs">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Přihlášení - KRS</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background: #f4f4f4;
                    margin: 0;
                    padding: 0;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                }
                .login-container {
                    background: white;
                    padding: 2rem;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    width: 100%;
                    max-width: 400px;
                }
                .login-header {
                    text-align: center;
                    margin-bottom: 2rem;
                }
                .form-group {
                    margin-bottom: 1rem;
                }
                .form-group label {
                    display: block;
                    margin-bottom: 0.5rem;
                    font-weight: bold;
                }
                .form-group input {
                    width: 100%;
                    padding: 0.75rem;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    box-sizing: border-box;
                }
                .btn {
                    width: 100%;
                    padding: 0.75rem;
                    background: #007bff;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 1rem;
                }
                .btn:hover {
                    background: #0056b3;
                }
                .error {
                    background: #ffebee;
                    color: #c62828;
                    padding: 0.75rem;
                    border-radius: 4px;
                    margin-bottom: 1rem;
                    border: 1px solid #ffcdd2;
                }
                .back-link {
                    display: block;
                    text-align: center;
                    margin-top: 1rem;
                    color: #666;
                    text-decoration: none;
                }
                .back-link:hover {
                    color: #333;
                }
            </style>
        </head>
        <body>
            <div class="login-container">
                <div class="login-header">
                    <h1>Přihlášení</h1>
                    <p>Redakční systém KRS</p>
                </div>

                {$error}

                <form method="POST" action="{$this->baseUrl}/login">
                    <div class="form-group">
                        <label for="username">Uživatelské jméno:</label>
                        <input type="text" id="username" name="username" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Heslo:</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    {$csrfField}

                    <button type="submit" class="btn">Přihlásit se</button>
                </form>

                <a href="{$this->baseUrl}/" class="back-link">← Zpět na úvodní stránku</a>
            </div>
        </body>
        </html>
        HTML;
    }

    public function processLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->baseUrl . '/login?error=invalid');
            exit;
        }

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';

        try {
            if ($this->loginService->authenticate($username, $password, $csrfToken)) {
                header('Location: ' . $this->baseUrl . '/admin');
                exit;
            } else {
                header('Location: ' . $this->baseUrl . '/login?error=1');
                exit;
            }
        } catch (\RuntimeException $e) {
            header('Location: ' . $this->baseUrl . '/login?error=csrf');
            exit;
        }
    }

    public function logout(): void
    {
        $this->loginService->logout();
        header('Location: ' . $this->baseUrl . '/login?logout=1');
        exit;
    }
}