<?php
// app/Controllers/AuthController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth\LoginService;
use App\Security\CsrfProtection;
use App\Core\Template;
use App\Core\Config;

/**
 * Controller pro správu přihlašování a odhlašování uživatelů
 *
 * Zpracovává zobrazení přihlašovacího formuláře, autentizaci uživatelů
 * a správu uživatelských session. Zajišťuje bezpečné přihlašování
 * s CSRF ochranou.
 *
 * @package App\Controllers
 * @author KRS3
 * @version 3.0
 */
class AuthController
{
    /**
     * @param LoginService $loginService Služba pro přihlašování
     * @param CsrfProtection $csrf Ochrana proti CSRF útokům
     * @param string $baseUrl Základní URL aplikace
     * @param Template $template Šablonovací systém
     */
    public function __construct(
        private LoginService $loginService,
        private CsrfProtection $csrf,
        private string $baseUrl,
        private Template $template
    ) {}

    /**
     * Zobrazí přihlašovací formulář
     *
     * Pokud je uživatel již přihlášen, přesměruje ho do administrace.
     * Zobrazí přihlašovací formulář s CSRF ochranou a případné chybové hlášky.
     *
     * @return string HTML obsah přihlašovacího formuláře
     *
     * @uses LoginService::isLoggedIn()
     * @uses CsrfProtection::getTokenField()
     */
    public function showLoginForm(): string
    {

        if ($this->loginService->isLoggedIn()) {
            header('Location: ' . $this->baseUrl . '/admin');
            exit;
        }
        $csrfField = $this->csrf->getTokenField();
        $error = $this->getLoginError();

		$form = $this->template->render('layouts/frontend.php', [
            'title' => Config::text('pages.login', ['site_name' => Config::site('name')]),
            'content' => $this->template->render('pages/login.php', [
                'csrfField' => $csrfField,
                'error' => $error,
                'baseUrl' => $this->baseUrl,
                'siteName' => Config::site('name'),
                'loginTitle' => Config::text('ui.login'),
                'usernameLabel' => Config::text('ui.username'),
                'passwordLabel' => Config::text('ui.password'),
                'submitText' => Config::text('ui.login'),
                'backLinkText' => Config::text('ui.back_to_home')
            ]),
            'baseUrl' => $this->baseUrl,
            'siteName' => Config::site('name'),
            'isLoginPage' => true // ← TOHLE JE DŮLEŽITÉ!
        ]);
		//print_r($form);
		return $form;
    }

    /**
     * Získá chybovou hlášku pro přihlašování na základě URL parametru
     *
     * @return string HTML chybové hlášky nebo prázdný řetězec
     */
    private function getLoginError(): string
    {
        if (!isset($_GET['error'])) {
            return '';
        }

        $errorMessages = [
            '1' => Config::text('errors.login_failed'),
            'csrf' => Config::text('errors.csrf'),
            'default' => Config::text('errors.invalid_request')
        ];

        $errorKey = $_GET['error'];
        $errorMessage = $errorMessages[$errorKey] ?? $errorMessages['default'];

        return '<div class="error">' . htmlspecialchars($errorMessage) . '</div>';
    }

    /**
     * Zpracuje odeslání přihlašovacího formuláře
     *
     * Ověří přihlašovací údaje, provede autentizaci a v případě úspěchu
     * přesměruje do administrace. Při neúspěchu vrátí na přihlašovací
     * stránku s chybovou hláškou.
     *
     * @return void
     * @throws \RuntimeException Pokud dojde k CSRF chybě
     */
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

    /**
     * Odhlásí aktuálního uživatele
     *
     * Zničí uživatelskou session a přesměruje na přihlašovací stránku
     * s potvrzovací hláškou.
     *
     * @return void
     */
    public function logout(): void
    {
        $this->loginService->logout();
        header('Location: ' . $this->baseUrl . 'login?logout=1');
        exit;
    }
}