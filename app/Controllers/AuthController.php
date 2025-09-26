<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth\LoginService;
use App\Security\CsrfProtection;
use App\Core\Template;

class AuthController
{
    public function __construct(
        private LoginService $loginService,
        private CsrfProtection $csrf,
        private string $baseUrl,
		private Template $template
    ) {}

public function showLoginForm(): string
{
    if ($this->loginService->isLoggedIn()) {
        header('Location: ' . $this->baseUrl . '/admin');
        exit;
    }

    $csrfField = $this->csrf->getTokenField();
    $error = '';

    if (isset($_GET['error'])) {
        $error = match($_GET['error']) {
            '1' => '<div class="error">Špatné přihlašovací údaje!</div>',
            'csrf' => '<div class="error">Chyba zabezpečení CSRF!</div>',
            default => '<div class="error">Neplatný požadavek!</div>'
        };
    }

    return $this->template->render('layouts/frontend.php', [
        'title' => 'Přihlášení - KRS',
        'content' => $this->template->render('pages/login.php', [
            'csrfField' => $csrfField,
            'error' => $error,
            'baseUrl' => $this->baseUrl
        ]),
        'baseUrl' => $this->baseUrl,
        'siteName' => 'Redakční systém KRS'
    ]);
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