<?php
declare(strict_types=1);

namespace App\Security;

class CsrfProtection {
    private \App\Session\SessionManager $session;

    public function __construct(\App\Session\SessionManager $session) {
        $this->session = $session;
    }

    public function generateToken(): string {
        $token = bin2hex(random_bytes(32));
        $this->session->set('csrf_token', $token);
        return $token;
    }

    public function validateToken(string $token): bool {
        $storedToken = $this->session->get('csrf_token');
        $this->session->remove('csrf_token');
        return hash_equals($storedToken ?? '', $token);
    }

    public function getTokenField(): string {
        return '<input type="hidden" name="csrf_token" value="' . $this->generateToken() . '">';
    }
}