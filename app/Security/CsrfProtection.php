<?php
declare(strict_types=1);

namespace App\Security;

use App\Session\SessionManager;

/**
 * Ochrana proti CSRF útokům
 *
 * Generuje a validuje CSRF tokeny pomocí cryptograficky bezpečných metod.
 * Tokeny jsou ukládány v session a automaticky obnovovány po validaci.
 *
 * @package App\Security
 * @author KRS3
 * @version 1.0
 */
class CsrfProtection
{
    /**
     * @var SessionManager Správce session pro ukládání tokenů
     */
    private SessionManager $session;

    /**
     * Konstruktor
     *
     * @param SessionManager $session Instance správce session
     */
    public function __construct(SessionManager $session)
    {
        $this->session = $session;
    }

    /**
     * Vygeneruje nový CSRF token
     *
     * Generuje cryptograficky bezpečný token pomocí random_bytes()
     * a ukládá jej do session pro pozdější validaci.
     *
     * @return string Vygenerovaný token
     * @throws \RuntimeException Pokud generování tokenu selže
     */
    public function generateToken(): string
    {
        try {
            $token = bin2hex(random_bytes(32));
            $this->session->set('csrf_token', $token);
            return $token;
        } catch (\Exception $e) {
            throw new \RuntimeException('Nepodařilo se vygenerovat CSRF token');
        }
    }

    /**
     * Ověří platnost CSRF tokenu
     *
     * Porovná poskytnutý token s tokenem uloženým v session
     * pomocí časově konstantní funkce hash_equals().
     * Po validaci je token ze session odstraněn.
     *
     * @param string $token Token k ověření
     * @return bool True pokud je token platný, jinak false
     */
    public function validateToken(string $token): bool
    {
        $storedToken = $this->session->get('csrf_token');
        $this->session->remove('csrf_token');

        if ($storedToken === null) {
            return false;
        }

        return hash_equals($storedToken, $token);
    }

    /**
     * Vrátí HTML hidden input s CSRF tokenem
     *
     * @return string HTML kód hidden inputu
     */
    public function getTokenField(): string
    {
        $token = $this->generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Vrátí CSRF token jako řetězec (pro API atd.)
     *
     * @return string Vygenerovaný token
     */
    public function getToken(): string
    {
        return $this->generateToken();
    }
}