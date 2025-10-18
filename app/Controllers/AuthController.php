<?php
// app/Controllers/AuthController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth\LoginService;
use App\Security\CsrfProtection;
use App\Core\Template;
use App\Core\Config;
use App\Logger\Logger;

/**
 * Controller pro správu přihlašování a odhlašování uživatelů
 *
 * Zpracovává zobrazení přihlašovacího formuláře, autentizaci uživatelů
 * a správu uživatelských session. Zajišťuje bezpečné přihlašování
 * s CSRF ochranou.
 *
 * @package App\Controllers
 * @author KRS3
 * @version 3.1
 */
class AuthController
{
    private Logger $logger;

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
    ) {
        $this->logger = Logger::getInstance();
    }

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
        $clientIp = $this->getClientIp();

        // Pokud je již přihlášen, redirect
        if ($this->loginService->isLoggedIn()) {
            $this->logger->debug('Already logged in user redirected from login page', [
                'client_ip' => $clientIp,
                'redirect_to' => 'admin'
            ]);

            header('Location: ' . $this->baseUrl . 'admin');
            exit;
        }

        $csrfField = $this->csrf->getTokenField();
        $error = $this->getLoginError();

        $this->logger->debug('Login form displayed', [
            'has_error' => !empty($error),
            'error_param' => $_GET['error'] ?? null,
            'client_ip' => $clientIp,
            'user_agent' => $this->getUserAgent()
        ]);

        try {
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
                'isLoginPage' => true,
                'user' => null  // Přidáno - na login stránce není přihlášený uživatel
            ]);

            return $form;
        } catch (\Exception $e) {
            $this->logger->error('Failed to render login form', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'client_ip' => $clientIp
            ]);
            throw $e;
        }
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
            'invalid' => Config::text('errors.invalid_request'),
            'default' => Config::text('errors.invalid_request')
        ];

        $errorKey = $_GET['error'];
        $errorMessage = $errorMessages[$errorKey] ?? $errorMessages['default'];

        $this->logger->debug('Login error message prepared', [
            'error_key' => $errorKey,
            'client_ip' => $this->getClientIp()
        ]);

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
     */
    public function processLogin(): void
    {
        $clientIp = $this->getClientIp();
        $userAgent = $this->getUserAgent();

        // Kontrola HTTP metody
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->logger->warning('Invalid HTTP method for login', [
                'method' => $_SERVER['REQUEST_METHOD'],
                'client_ip' => $clientIp,
                'user_agent' => $userAgent
            ]);

            header('Location: ' . $this->baseUrl . 'login?error=invalid');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';

        // Validace prázdných polí
        if (empty($username) || empty($password)) {
            $this->logger->warning('Login attempt with empty credentials', [
                'empty_username' => empty($username),
                'empty_password' => empty($password),
                'client_ip' => $clientIp,
                'user_agent' => $userAgent
            ]);

            header('Location: ' . $this->baseUrl . 'login?error=1');
            exit;
        }

        $this->logger->info('Login attempt started', [
            'username' => $username,
            'client_ip' => $clientIp,
            'user_agent' => $userAgent
        ]);

        try {
            if ($this->loginService->authenticate($username, $password, $csrfToken)) {
                $this->logger->info('Login successful', [
                    'username' => $username,
                    'client_ip' => $clientIp,
                    'user_agent' => $userAgent,
                    'redirect_to' => 'admin'
                ]);

                header('Location: ' . $this->baseUrl . 'admin');
                exit;
            } else {
                $this->logger->warning('Login failed - invalid credentials', [
                    'username' => $username,
                    'client_ip' => $clientIp,
                    'user_agent' => $userAgent
                ]);

                header('Location: ' . $this->baseUrl . 'login?error=1');
                exit;
            }
        } catch (\RuntimeException $e) {
            $this->logger->error('Login failed - CSRF token validation error', [
                'username' => $username,
                'error' => $e->getMessage(),
                'client_ip' => $clientIp,
                'user_agent' => $userAgent
            ]);

            header('Location: ' . $this->baseUrl . 'login?error=csrf');
            exit;
        } catch (\Exception $e) {
            $this->logger->error('Login failed - unexpected error', [
                'username' => $username,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'client_ip' => $clientIp,
                'user_agent' => $userAgent
            ]);

            header('Location: ' . $this->baseUrl . 'login?error=1');
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
        $clientIp = $this->getClientIp();
        $userAgent = $this->getUserAgent();

        // Získáme username před odhlášením (pokud je v session)
        $username = $_SESSION['user']['username'] ?? 'unknown';

        $this->logger->info('Logout initiated', [
            'username' => $username,
            'client_ip' => $clientIp,
            'user_agent' => $userAgent
        ]);

        try {
            $this->loginService->logout();

            $this->logger->info('Logout successful', [
                'username' => $username,
                'client_ip' => $clientIp,
                'user_agent' => $userAgent
            ]);

            header('Location: ' . $this->baseUrl . 'login?logout=1');
            exit;
        } catch (\Exception $e) {
            $this->logger->error('Logout failed', [
                'username' => $username,
                'error' => $e->getMessage(),
                'client_ip' => $clientIp,
                'user_agent' => $userAgent
            ]);

            // I přes chybu se pokusíme přesměrovat
            header('Location: ' . $this->baseUrl . 'login');
            exit;
        }
    }

    /**
     * Získá IP adresu klienta
     *
     * @return string IP adresa
     */
    private function getClientIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Získá User Agent
     *
     * @return string User Agent
     */
    private function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    }
}