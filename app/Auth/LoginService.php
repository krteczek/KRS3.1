<?php
// app/Auth/LoginService.php
declare(strict_types=1);

namespace App\Auth;

use App\Database\DatabaseConnection;
use App\Security\CsrfProtection;
use App\Session\SessionManager;
use App\Core\Config;
use App\Logger\Logger;

/**
 * Služba pro správu přihlašování a autentizace uživatelů
 *
 * Poskytuje kompletní funkcionalitu pro přihlašování, odhlašování,
 * správu uživatelských session a kontrolu oprávnění.
 * Zajišťuje bezpečnou autentizaci s CSRF ochranou.
 *
 * @package App\Auth
 * @author KRS3
 * @version 3.2
 */
class LoginService
{
    private Logger $logger;

    /** @var int Maximální počet neúspěšných pokusů o přihlášení */
    private const MAX_LOGIN_ATTEMPTS = 5;

    /** @var int Doba v sekundách, po kterou je účet zablokován */
    private const LOCKOUT_TIME = 900; // 15 minut

    /**
     * @param DatabaseConnection $db Databázové připojení
     * @param SessionManager $session Správce session
     * @param CsrfProtection $csrf Ochrana proti CSRF útokům
     */
    public function __construct(
        private DatabaseConnection $db,
        private SessionManager $session,
        private CsrfProtection $csrf
    ) {
        $this->logger = Logger::getInstance();
    }

    /**
     * Ověří přihlašovací údaje a vytvoří uživatelskou session
     *
     * Provádí kompletní autentizační proces včetně validace CSRF tokenu,
     * ověření hesla a nastavení uživatelských dat do session.
     *
     * @param string $username Uživatelské jméno
     * @param string $password Heslo
     * @param string $csrfToken CSRF token z formuláře
     * @return bool True pokud přihlášení proběhlo úspěšně
     * @throws \RuntimeException Pokud je neplatný CSRF token
     * @throws \Exception Pokud dojde k databázové chybě
     *
     * @example
     * $success = $loginService->authenticate('john', 'password123', $csrfToken);
     */
    public function authenticate(string $username, string $password, string $csrfToken): bool
    {
        $clientIp = $this->getClientIp();
        $userAgent = $this->getUserAgent();

        // Sanitizace username
        $username = trim($username);

        $this->logger->debug('Authentication attempt started', [
            'username' => $username,
            'client_ip' => $clientIp,
            'user_agent' => $userAgent
        ]);

        // Validace CSRF tokenu
        if (!$this->csrf->validateToken($csrfToken)) {
            $this->logger->warning('Authentication failed - invalid CSRF token', [
                'username' => $username,
                'client_ip' => $clientIp,
                'user_agent' => $userAgent
            ]);
            throw new \RuntimeException(Config::text('messages.invalid_csrf'));
        }

        // Kontrola rate limiting
        if ($this->isAccountLocked($username)) {
            $this->logger->warning('Authentication blocked - account locked due to too many failed attempts', [
                'username' => $username,
                'client_ip' => $clientIp,
                'user_agent' => $userAgent
            ]);
            return false;
        }

        try {
            $user = $this->findUserByUsername($username);

            if (!$user) {
                $this->logger->warning('Authentication failed - user not found', [
                    'username' => $username,
                    'client_ip' => $clientIp,
                    'user_agent' => $userAgent
                ]);
                $this->recordFailedAttempt($username, $clientIp);
                return false;
            }

            // Ověření hesla
            if (!password_verify($password, $user['password_hash'])) {
                $this->logger->warning('Authentication failed - invalid password', [
                    'username' => $username,
                    'user_id' => $user['id'],
                    'client_ip' => $clientIp,
                    'user_agent' => $userAgent
                ]);
                $this->recordFailedAttempt($username, $clientIp);
                return false;
            }

            // Kontrola, zda není potřeba rehash hesla (např. po změně algoritmu)
            if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                $this->logger->info('Password rehash needed', [
                    'user_id' => $user['id'],
                    'username' => $username
                ]);
                $this->updatePasswordHash($user['id'], $password);
            }

            // Úspěšné přihlášení
            $this->session->regenerate();
            $this->session->set('user', [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]);

            // Zaznamenat poslední přihlášení
            $this->updateLastLogin($user['id'], $clientIp);

            // Vymazat failed attempts
            $this->clearFailedAttempts($username);

            $this->logger->info('Authentication successful', [
                'user_id' => $user['id'],
                'username' => $username,
                'role' => $user['role'],
                'client_ip' => $clientIp,
                'user_agent' => $userAgent
            ]);

            return true;
        } catch (\PDOException $e) {
            $this->logger->error('Authentication failed - database error', [
                'username' => $username,
                'error' => $e->getMessage(),
                'client_ip' => $clientIp
            ]);
            throw new \Exception('Chyba při přihlašování. Zkuste to prosím znovu.');
        } catch (\Exception $e) {
            $this->logger->error('Authentication failed - unexpected error', [
                'username' => $username,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'client_ip' => $clientIp
            ]);
            throw $e;
        }
    }

    /**
     * Najde uživatele v databázi podle uživatelského jména
     *
     * @param string $username Uživatelské jméno k hledání
     * @return array|null Uživatelská data nebo null pokud uživatel neexistuje
     * @throws \Exception Pokud dojde k databázové chybě
     */
    private function findUserByUsername(string $username): ?array
    {
        try {
            $sql = "SELECT id, username, password_hash, email, role
                    FROM users
                    WHERE username = ? AND active = 1
                    LIMIT 1";

            $result = $this->db->query($sql, [$username]);
            $user = $result->fetch();

            $this->logger->debug('User lookup in database', [
                'username' => $username,
                'found' => (bool)$user
            ]);

            return $user ?: null;
        } catch (\PDOException $e) {
            $this->logger->error('Database error during user lookup', [
                'username' => $username,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Chyba při hledání uživatele.');
        }
    }

    /**
     * Zkontroluje, zda je účet zablokován kvůli příliš mnoha neúspěšným pokusům
     *
     * @param string $username Uživatelské jméno
     * @return bool True pokud je účet zablokován
     */
    private function isAccountLocked(string $username): bool
    {
        try {
            $sql = "SELECT COUNT(*) as attempts, MAX(attempted_at) as last_attempt
                    FROM login_attempts
                    WHERE username = ?
                    AND attempted_at > DATE_SUB(NOW(), INTERVAL ? SECOND)";

            $result = $this->db->query($sql, [$username, self::LOCKOUT_TIME]);
            $data = $result->fetch();

            $isLocked = $data && $data['attempts'] >= self::MAX_LOGIN_ATTEMPTS;

            if ($isLocked) {
                $this->logger->warning('Account is locked', [
                    'username' => $username,
                    'attempts' => $data['attempts'],
                    'last_attempt' => $data['last_attempt']
                ]);
            }

            return $isLocked;
        } catch (\PDOException $e) {
            $this->logger->error('Error checking account lock status', [
                'username' => $username,
                'error' => $e->getMessage()
            ]);
            // V případě chyby raději neblokujeme
            return false;
        }
    }

    /**
     * Zaznamenává neúspěšný pokus o přihlášení
     *
     * @param string $username Uživatelské jméno
     * @param string $ipAddress IP adresa
     * @return void
     */
    private function recordFailedAttempt(string $username, string $ipAddress): void
    {
        try {
            $sql = "INSERT INTO login_attempts (username, ip_address, attempted_at)
                    VALUES (?, ?, NOW())";

            $this->db->execute($sql, [$username, $ipAddress]);

            $this->logger->debug('Failed login attempt recorded', [
                'username' => $username,
                'ip_address' => $ipAddress
            ]);
        } catch (\PDOException $e) {
            $this->logger->error('Failed to record login attempt', [
                'username' => $username,
                'error' => $e->getMessage()
            ]);
            // Nekritická chyba - pokračujeme
        }
    }

    /**
     * Vymaže záznamy o neúspěšných pokusech pro daného uživatele
     *
     * @param string $username Uživatelské jméno
     * @return void
     */
    private function clearFailedAttempts(string $username): void
    {
        try {
            $sql = "DELETE FROM login_attempts WHERE username = ?";
            $this->db->execute($sql, [$username]);

            $this->logger->debug('Failed login attempts cleared', [
                'username' => $username
            ]);
        } catch (\PDOException $e) {
            $this->logger->error('Failed to clear login attempts', [
                'username' => $username,
                'error' => $e->getMessage()
            ]);
            // Nekritická chyba
        }
    }

    /**
     * Aktualizuje hash hesla v databázi
     *
     * @param int $userId ID uživatele
     * @param string $password Nové heslo v plain textu
     * @return void
     */
    private function updatePasswordHash(int $userId, string $password): void
    {
        try {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
            $this->db->execute($sql, [$newHash, $userId]);

            $this->logger->info('Password hash updated', [
                'user_id' => $userId
            ]);
        } catch (\PDOException $e) {
            $this->logger->error('Failed to update password hash', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            // Nekritická chyba - přihlášení může pokračovat
        }
    }

    /**
     * Aktualizuje čas posledního přihlášení
     *
     * @param int $userId ID uživatele
     * @param string $ipAddress IP adresa
     * @return void
     */
    private function updateLastLogin(int $userId, string $ipAddress): void
    {
        try {
            $sql = "UPDATE users
                    SET last_login_at = NOW(),
                        last_login_ip = ?
                    WHERE id = ?";
            $this->db->execute($sql, [$ipAddress, $userId]);

            $this->logger->debug('Last login timestamp updated', [
                'user_id' => $userId,
                'ip_address' => $ipAddress
            ]);
        } catch (\PDOException $e) {
            $this->logger->error('Failed to update last login', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            // Nekritická chyba
        }
    }

    /**
     * Odhlásí aktuálního uživatele
     *
     * @return void
     */
    public function logout(): void
    {
        $user = $this->getUser();
        $clientIp = $this->getClientIp();

        if ($user) {
            $this->logger->info('User logout initiated', [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'client_ip' => $clientIp
            ]);
        }

        $this->session->destroy();

        if ($user) {
            $this->logger->info('User logout completed', [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'client_ip' => $clientIp
            ]);
        }
    }

    /**
     * Zjistí, zda je uživatel přihlášen
     *
     * @return bool True pokud je uživatel přihlášen
     */
    public function isLoggedIn(): bool
    {
        return $this->session->has('user');
    }

    /**
     * Získá data aktuálně přihlášeného uživatele
     *
     * @return array|null Uživatelská data nebo null pokud není přihlášen
     */
    public function getUser(): ?array
    {
        return $this->session->get('user');
    }

    /**
     * Získá ID aktuálně přihlášeného uživatele
     *
     * @return int|null ID uživatele nebo null pokud není přihlášen
     */
    public function getUserId(): ?int
    {
        $user = $this->getUser();
        return $user['id'] ?? null;
    }

    /**
     * Získá username aktuálně přihlášeného uživatele
     *
     * @return string|null Username nebo null pokud není přihlášen
     */
    public function getUsername(): ?string
    {
        $user = $this->getUser();
        return $user['username'] ?? null;
    }

    /**
     * Ověří, zda má přihlášený uživatel požadovanou roli
     *
     * @param string $requiredRole Požadovaná role (např. 'admin')
     * @return bool True pokud uživatel má požadovanou roli
     */
    public function hasRole(string $requiredRole): bool
    {
        $user = $this->getUser();
        $hasRole = $user && $user['role'] === $requiredRole;

        $this->logger->debug('Role check performed', [
            'user_id' => $user['id'] ?? null,
            'required_role' => $requiredRole,
            'user_role' => $user['role'] ?? null,
            'has_role' => $hasRole
        ]);

        return $hasRole;
    }

    /**
     * Ověří, zda má přihlášený uživatel některou z požadovaných rolí
     *
     * @param array $allowedRoles Seznam povolených rolí
     * @return bool True pokud uživatel má některou z povolených rolí
     */
    public function hasAnyRole(array $allowedRoles): bool
    {
        $user = $this->getUser();
        $hasAnyRole = $user && in_array($user['role'], $allowedRoles, true);

        $this->logger->debug('Multiple roles check performed', [
            'user_id' => $user['id'] ?? null,
            'allowed_roles' => $allowedRoles,
            'user_role' => $user['role'] ?? null,
            'has_any_role' => $hasAnyRole
        ]);

        return $hasAnyRole;
    }

    /**
     * Vyžaduje přihlášení - pokud uživatel není přihlášen, přesměruje na login
     *
     * @param string $redirectUrl URL pro přesměrování při nepřihlášení
     * @return void
     */
    public function requireLogin(string $redirectUrl = '/login'): void
    {
        if (!$this->isLoggedIn()) {
            $this->logger->warning('Unauthorized access attempt - redirecting to login', [
                'requested_url' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'client_ip' => $this->getClientIp(),
                'user_agent' => $this->getUserAgent()
            ]);

            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    /**
     * Vyžaduje specifickou roli - pokud uživatel nemá požadovanou roli, přesměruje
     *
     * @param string $requiredRole Požadovaná role
     * @param string $redirectUrl URL pro přesměrování při nedostatečném oprávnění
     * @return void
     */
    public function requireRole(string $requiredRole, string $redirectUrl = '/'): void
    {
        $this->requireLogin();

        if (!$this->hasRole($requiredRole)) {
            $user = $this->getUser();

            $this->logger->warning('Insufficient permissions - access denied', [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'user_role' => $user['role'],
                'required_role' => $requiredRole,
                'requested_url' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'client_ip' => $this->getClientIp()
            ]);

            header('Location: ' . $redirectUrl);
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