<?php
// app/Session/SessionManager.php
declare(strict_types=1);

namespace App\Session;

use App\Core\Config;
use App\Logger\Logger;

/**
 * Správa PHP sessions s bezpečnostními opatřeními
 *
 * Poskytuje bezpečnou správu PHP sessions včetně ochrany proti
 * session fixation, CSRF útokům a správného nastavení cookie parametrů.
 * Implementuje kompletní CRUD operace pro session data.
 *
 * @package App\Session
 * @author KRS3
 * @version 2.0
 */
class SessionManager
{
    private Logger $logger;

    /**
     * Inicializuje session s bezpečnostními nastaveními
     *
     * @throws \RuntimeException Pokud se nepodaří inicializovat session
     */
    public function __construct()
    {
        $this->logger = Logger::getInstance();

        if (session_status() === PHP_SESSION_NONE) {
            $this->configureSession();
            if (!session_start()) {
                $this->logger->error('Failed to start session', [
                    'client_ip' => $this->getClientIp(),
                    'user_agent' => $this->getUserAgent()
                ]);
                throw new \RuntimeException('Nepodařilo se inicializovat session');
            }

            $this->logger->info('Session started', [
                'session_id' => substr(session_id(), 0, 8) . '...',
                'client_ip' => $this->getClientIp(),
                'user_agent' => $this->getUserAgent()
            ]);
        }
    }

    /**
     * Nastaví bezpečnostní parametry session
     *
     * @return void
     */
    private function configureSession(): void
    {
        ini_set('session.cookie_secure', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'] ?? '',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

        $this->logger->debug('Session security parameters configured', [
            'cookie_secure' => true,
            'cookie_httponly' => true,
            'cookie_samesite' => 'Strict',
            'use_strict_mode' => true
        ]);
    }

    /**
     * Uloží hodnotu do session
     *
     * @param string $key Klíč pro uložení hodnoty
     * @param mixed $value Hodnota k uložení
     * @return void
     */
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;

        $this->logger->debug('Session value set', [
            'key' => $key,
            'value_type' => gettype($value),
            'session_id' => substr(session_id(), 0, 8) . '...'
        ]);
    }

    /**
     * Získá hodnotu z session
     *
     * @param string $key Klíč hodnoty
     * @param mixed $default Výchozí hodnota pokud klíč neexistuje
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $exists = isset($_SESSION[$key]);
        $value = $_SESSION[$key] ?? $default;

        $this->logger->debug('Session value retrieved', [
            'key' => $key,
            'exists' => $exists,
            'returned_default' => !$exists,
            'session_id' => substr(session_id(), 0, 8) . '...'
        ]);

        return $value;
    }

    /**
     * Zkontroluje zda klíč existuje v session
     *
     * @param string $key Klíč pro kontrolu
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Odstraní položku z session
     *
     * @param string $key Klíč k odstranění
     * @return void
     */
    public function remove(string $key): void
    {
        $existed = isset($_SESSION[$key]);
        unset($_SESSION[$key]);

        $this->logger->debug('Session value removed', [
            'key' => $key,
            'existed' => $existed,
            'session_id' => substr(session_id(), 0, 8) . '...'
        ]);
    }

    /**
     * Regeneruje session ID (ochrana proti session fixation)
     *
     * @return bool True pokud se regenerace povedla, jinak false
     */
    public function regenerate(): bool
    {
        $oldSessionId = session_id();
        $result = session_regenerate_id(true);

        if ($result) {
            $this->logger->info('Session ID regenerated', [
                'old_session_id' => substr($oldSessionId, 0, 8) . '...',
                'new_session_id' => substr(session_id(), 0, 8) . '...',
                'client_ip' => $this->getClientIp(),
                'user_agent' => $this->getUserAgent()
            ]);
        } else {
            $this->logger->warning('Session ID regeneration failed', [
                'session_id' => substr($oldSessionId, 0, 8) . '...',
                'client_ip' => $this->getClientIp()
            ]);
        }

        return $result;
    }

    /**
     * Vyčistí všechna data session (bez zničení session)
     *
     * @return void
     */
    public function clear(): void
    {
        $keysCount = count($_SESSION);
        $_SESSION = [];

        $this->logger->info('Session data cleared', [
            'cleared_keys_count' => $keysCount,
            'session_id' => substr(session_id(), 0, 8) . '...',
            'client_ip' => $this->getClientIp()
        ]);
    }

    /**
     * Kompletně zničí session a smaže cookie
     *
     * @return void
     */
    public function destroy(): void
    {
        $sessionId = session_id();
        $keysCount = count($_SESSION);

        $this->clear();

        if (session_status() === PHP_SESSION_ACTIVE) {
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }

            session_destroy();

            $this->logger->info('Session destroyed', [
                'session_id' => substr($sessionId, 0, 8) . '...',
                'cleared_keys_count' => $keysCount,
                'client_ip' => $this->getClientIp(),
                'user_agent' => $this->getUserAgent()
            ]);
        }
    }

    /**
     * Vrátí aktuální session ID
     *
     * @return string Session ID
     */
    public function getId(): string
    {
        return session_id();
    }

    /**
     * Vrátí všechna session data jako pole
     *
     * @return array
     */
    public function getAll(): array
    {
        return $_SESSION;
    }

    /**
     * Zkontroluje zda je session aktivní
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * Nastaví flash zprávu pro jednorázové zobrazení s podporou překladů
     *
     * @param string $type Typ zprávy (success, error, warning, info)
     * @param string $messageKey Klíč pro překlad z texts.php
     * @param array $params Parametry pro nahrazení v textu
     * @return void
     */
    public function setFlash(string $type, string $messageKey, array $params = []): void
    {
        $message = Config::text($messageKey, $params);
        $this->set("flash_{$type}", $message);

        $this->logger->debug('Flash message set', [
            'type' => $type,
            'message_key' => $messageKey,
            'params' => $params,
            'session_id' => substr(session_id(), 0, 8) . '...'
        ]);
    }

    /**
     * Získá a smaže flash zprávu
     *
     * @param string $type Typ zprávy
     * @param string|null $default Výchozí hodnota
     * @return string|null
     */
    public function getFlash(string $type, ?string $default = null): ?string
    {
        $message = $this->get("flash_{$type}", $default);
        $this->remove("flash_{$type}");

        $this->logger->debug('Flash message retrieved and removed', [
            'type' => $type,
            'has_message' => $message !== null,
            'session_id' => substr(session_id(), 0, 8) . '...'
        ]);

        return $message;
    }

    /**
     * Zkontroluje existenci flash zprávy
     *
     * @param string $type Typ zprávy
     * @return bool
     */
    public function hasFlash(string $type): bool
    {
        return $this->has("flash_{$type}");
    }

    /**
     * Nastaví success flash zprávu
     *
     * @param string $messageKey Klíč pro překlad
     * @param array $params Parametry pro nahrazení
     * @return void
     */
    public function setSuccess(string $messageKey, array $params = []): void
    {
        $this->setFlash('success', $messageKey, $params);
    }

    /**
     * Nastaví error flash zprávu
     *
     * @param string $messageKey Klíč pro překlad
     * @param array $params Parametry pro nahrazení
     * @return void
     */
    public function setError(string $messageKey, array $params = []): void
    {
        $this->setFlash('error', $messageKey, $params);
    }

    /**
     * Nastaví info flash zprávu
     *
     * @param string $messageKey Klíč pro překlad
     * @param array $params Parametry pro nahrazení
     * @return void
     */
    public function setInfo(string $messageKey, array $params = []): void
    {
        $this->setFlash('info', $messageKey, $params);
    }

    /**
     * Nastaví warning flash zprávu
     *
     * @param string $messageKey Klíč pro překlad
     * @param array $params Parametry pro nahrazení
     * @return void
     */
    public function setWarning(string $messageKey, array $params = []): void
    {
        $this->setFlash('warning', $messageKey, $params);
    }

    /**
     * Nastaví čas expirace session
     *
     * @param int $minutes Počet minut
     * @return void
     */
    public function setExpiration(int $minutes): void
    {
        ini_set('session.gc_maxlifetime', $minutes * 60);
        session_set_cookie_params($minutes * 60);

        $this->logger->info('Session expiration set', [
            'minutes' => $minutes,
            'seconds' => $minutes * 60,
            'session_id' => substr(session_id(), 0, 8) . '...',
            'client_ip' => $this->getClientIp()
        ]);
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