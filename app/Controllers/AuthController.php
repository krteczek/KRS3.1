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
	    $error = $this->getLoginError();

	    return $this->template->render('layouts/frontend.php', [
	        'title' => \App\Core\Config::text('pages.login', ['site_name' => \App\Core\Config::site('name')]),
	        'content' => $this->template->render('pages/login.php', [
	            'csrfField' => $csrfField,
	            'error' => $error,
	            'baseUrl' => $this->baseUrl,
	            'siteName' => \App\Core\Config::site('name'),
	            'loginTitle' => \App\Core\Config::text('ui.login'),
	            'usernameLabel' => \App\Core\Config::text('ui.username'),
	            'passwordLabel' => \App\Core\Config::text('ui.password'),
	            'submitText' => \App\Core\Config::text('ui.login'),
	            'backLinkText' => \App\Core\Config::text('ui.back_to_home')
	        ]),
	        'baseUrl' => $this->baseUrl,
	        'siteName' => \App\Core\Config::site('name'),
	        'isLoginPage' => true // ← TOHLE JE DŮLEŽITÉ!
	    ]);
	}

	private function getLoginError(): string
	{
	    if (!isset($_GET['error'])) {
	        return '';
	    }

	    $errorMessages = [
	        '1' => \App\Core\Config::text('errors.login_failed'),
	        'csrf' => \App\Core\Config::text('errors.csrf'),
	        'default' => \App\Core\Config::text('errors.invalid_request')
	    ];

	    $errorKey = $_GET['error'];
	    $errorMessage = $errorMessages[$errorKey] ?? $errorMessages['default'];

	    return '<div class="error">' . htmlspecialchars($errorMessage) . '</div>';
	}


    public function processLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->baseUrl . 'login?error=invalid');
            exit;
        }

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';

        try {
            if ($this->loginService->authenticate($username, $password, $csrfToken)) {
                header('Location: ' . $this->baseUrl . 'admin');
                exit;
            } else {
                header('Location: ' . $this->baseUrl . 'login?error=1');
                exit;
            }
        } catch (\RuntimeException $e) {
            header('Location: ' . $this->baseUrl . 'login?error=csrf');
            exit;
        }
    }

    public function logout(): void
    {
        $this->loginService->logout();
        header('Location: ' . $this->baseUrl . 'login?logout=1');
        exit;
    }
}