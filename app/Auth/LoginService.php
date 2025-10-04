<?php
// app/Auth/LoginService.php
declare(strict_types=1);

namespace App\Auth;

use App\Database\DatabaseConnection;
use App\Security\CsrfProtection;
use App\Session\SessionManager;
use App\Core\Config;

/**
 * Služba pro správu přihlašování a autentizace uživatelů
 *
 * Poskytuje kompletní funkcionalitu pro přihlašování, odhlašování,
 * správu uživatelských session a kontrolu oprávnění.
 * Zajišťuje bezpečnou autentizaci s CSRF ochranou.
 *
 * @package App\Auth
 * @author KRS3
 * @version 3.1
 */
class LoginService
{
    /**
     * @param DatabaseConnection $db Databázové připojení
     * @param SessionManager $session Správce session
     * @param CsrfProtection $csrf Ochrana proti CSRF útokům
     */
    public function __construct(
        private DatabaseConnection $db,
        private SessionManager $session,
        private CsrfProtection $csrf
    ) {}

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
     *
     * @example
     * $success = $loginService->authenticate('john', 'password123', $csrfToken);
     */
    public function authenticate(string $username, string $password, string $csrfToken): bool
    {
        if (!$this->csrf->validateToken($csrfToken)) {
            throw new \RuntimeException(Config::text('messages.invalid_csrf'));
        }

        $user = $this->findUserByUsername($username);

        if ($user && password_verify($password, $user['password_hash'])) {
            $this->session->regenerate();
            $this->session->set('user', [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]);
            return true;
        }

        return false;
    }

    /**
     * Najde uživatele v databázi podle uživatelského jména
     *
     * @param string $username Uživatelské jméno k hledání
     * @return array|null Uživatelská data nebo null pokud uživatel neexistuje
     */
    private function findUserByUsername(string $username): ?array
    {
        $sql = "SELECT id, username, password_hash, email, role
                FROM users
                WHERE username = ? AND active = 1";

        $result = $this->db->query($sql, [$username]);
        return $result->fetch() ?: null;
    }

    /**
     * Odhlásí aktuálního uživatele
     *
     * Zničí kompletně uživatelskou session.
     *
     * @return void
     */
    public function logout(): void
    {
        $this->session->destroy();
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
     * Ověří, zda má přihlášený uživatel požadovanou roli
     *
     * @param string $requiredRole Požadovaná role (např. 'admin')
     * @return bool True pokud uživatel má požadovanou roli
     */
    public function hasRole(string $requiredRole): bool
    {
        $user = $this->getUser();
        return $user && $user['role'] === $requiredRole;
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
        return $user && in_array($user['role'], $allowedRoles, true);
    }
}