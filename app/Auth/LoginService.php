<?php

declare(strict_types=1);

namespace App\Auth;

use App\Database\DatabaseConnection;
use App\Security\CsrfProtection;
use App\Session\SessionManager;

class LoginService
{
    public function __construct(
        private DatabaseConnection $db,
        private SessionManager $session,
        private CsrfProtection $csrf
    ) {}

    public function authenticate(string $username, string $password, string $csrfToken): bool
    {
        if (!$this->csrf->validateToken($csrfToken)) {
            throw new \RuntimeException('Neplatný CSRF token');
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

    private function findUserByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, username, password_hash, email, role
            FROM users
            WHERE username = :username AND active = 1
        ");

        $stmt->execute([':username' => $username]);
        return $stmt->fetch() ?: null;
    }

    public function logout(): void
    {
        $this->session->destroy();
    }

    public function isLoggedIn(): bool
    {
        return $this->session->has('user');
    }

    public function getUser(): ?array
    {
        return $this->session->get('user');
    }

    public function requiresRole(int $requiredRole): bool
    {
        $user = $this->getUser();
        return $user && ($user['role'] >= $requiredRole);
    }
}