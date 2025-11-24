<?php
declare(strict_types=1);

namespace App\Security;

use App\Session\SessionManager;
use App\Logger\Logger;

/**
 * Vylepšená ochrana proti CSRF útokům
 *
 * Generuje a validuje CSRF tokeny pomocí cryptograficky bezpečných metod.
 * Podporuje více tokenů současně, expiraci tokenů a další bezpečnostní vylepšení.
 *
 * @package App\Security
 * @author KRS3
 * @version 2.0
 */
class CsrfProtection
{
    private SessionManager $session;
    private array $config;
    private Logger $logger;

    /**
     * Výchozí konfigurace
     */
    private const DEFAULT_CONFIG = [
        'token_expire' => 3600,        // 1 hodina v sekundách
        'max_tokens' => 10,            // Maximální počet současných tokenů
        'validate_origin' => true,     // Validovat HTTP origin
        'validate_referer' => true,    // Validovat HTTP referer
        'token_name' => 'csrf_token'   // Název tokenu v formulářích
    ];

    /**
     * Konstruktor
     *
     * @param SessionManager $session Instance správce session
     * @param array $config Konfigurační pole
     */
    public function __construct(SessionManager $session, array $config = [])
    {
        $this->session = $session;
        $this->config = array_merge(self::DEFAULT_CONFIG, $config);
        $this->logger = Logger::getInstance();

        $this->cleanupExpiredTokens();
        $this->logInfo('CSRF Protection initialized', ['config' => $this->config]);
    }

    /**
     * Vygeneruje nový CSRF token
     *
     * @param string|null $identifier Identifikátor pro per-form tokeny
     * @return string Vygenerovaný token
     * @throws \RuntimeException Pokud generování tokenu selže
     */
    public function generateToken(?string $identifier = null): string
    {
        try {
            $token = bin2hex(random_bytes(32));
            $tokenId = $identifier ?? 'default';

            $tokens = $this->getTokens();
            $this->enforceTokenLimit($tokens);

            $tokens[$tokenId] = [
                'token' => $token,
                'created_at' => time(),
                'expires_at' => time() + $this->config['token_expire']
            ];

            $this->session->set('csrf_tokens', $tokens);

            $this->logInfo('CSRF token generated', [
                'identifier' => $tokenId,
                'token_prefix' => substr($token, 0, 8) . '...',
                'expires_at' => date('Y-m-d H:i:s', $tokens[$tokenId]['expires_at']),
                'client_ip' => $this->getClientIp(),
                'user_agent' => $this->getUserAgent(),
                'request_url' => $this->getRequestUrl()
            ]);

            return $token;
        } catch (\Exception $e) {
            $this->logError('CSRF token generation failed', [
                'error' => $e->getMessage(),
                'identifier' => $identifier,
                'client_ip' => $this->getClientIp(),
                'user_agent' => $this->getUserAgent()
            ]);
            throw new \RuntimeException('Nepodařilo se vygenerovat CSRF token');
        }
    }

    /**
     * Ověří platnost CSRF tokenu
     *
     * @param string $token Token k ověření
     * @param string|null $identifier Identifikátor tokenu
     * @return bool True pokud je token platný, jinak false
     */
    public function validateToken(string $token, ?string $identifier = null): bool
    {
        $tokenId = $identifier ?? 'default';
        $validationContext = [
            'identifier' => $tokenId,
            'token_prefix' => substr($token, 0, 8) . '...',
            'token_length' => strlen($token),
            'client_ip' => $this->getClientIp(),
            'user_agent' => $this->getUserAgent(),
            'request_url' => $this->getRequestUrl()
        ];

        // Validace origin a referer
        if (!$this->validateOrigin()) {
            $this->logWarning('CSRF origin validation failed', $validationContext);
            return false;
        }

        if (!$this->validateReferer()) {
            $this->logWarning('CSRF referer validation failed', $validationContext);
            return false;
        }

        $tokens = $this->getTokens();

        // Token neexistuje
        if (!isset($tokens[$tokenId])) {
            $this->logWarning('CSRF token not found in session', $validationContext);
            return false;
        }

        $tokenData = $tokens[$tokenId];

        // Kontrola expirace
        if (time() > $tokenData['expires_at']) {
            unset($tokens[$tokenId]);
            $this->session->set('csrf_tokens', $tokens);

            $this->logWarning('CSRF token expired', array_merge($validationContext, [
                'created_at' => date('Y-m-d H:i:s', $tokenData['created_at']),
                'expired_at' => date('Y-m-d H:i:s', $tokenData['expires_at'])
            ]));

            return false;
        }

        // Kontrola tokenu
        if (!hash_equals($tokenData['token'], $token)) {
            $this->logWarning('CSRF token validation failed - tokens do not match', $validationContext);
            return false;
        }

        // Regenerace tokenu místo mazání
        $this->regenerateToken($tokenId);

        $this->logInfo('CSRF token validation successful', $validationContext);
        return true;
    }

    /**
     * Vrátí HTML hidden input s CSRF tokenem
     *
     * @param string|null $identifier Identifikátor tokenu
     * @return string HTML kód hidden inputu
     */
    public function getTokenField(?string $identifier = null): string
    {
        $token = $this->getToken($identifier);
        $tokenName = $this->config['token_name'];

        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            htmlspecialchars($tokenName),
            htmlspecialchars($token)
        );
    }

    /**
     * Vrátí CSRF token jako řetězec
     *
     * @param string|null $identifier Identifikátor tokenu
     * @return string Token
     */
    public function getToken(?string $identifier = null): string
    {
        $tokenId = $identifier ?? 'default';
        $tokens = $this->getTokens();

        if (isset($tokens[$tokenId]) && time() <= $tokens[$tokenId]['expires_at']) {
            return $tokens[$tokenId]['token'];
        }

        return $this->generateToken($identifier);
    }

    /**
     * Obnoví token (změní ho, ale zachová platnost)
     *
     * @param string|null $identifier Identifikátor tokenu
     * @return string Nový token
     */
    public function regenerateToken(?string $identifier = null): string
    {
        $tokenId = $identifier ?? 'default';
        $tokens = $this->getTokens();

        if (isset($tokens[$tokenId])) {
            try {
                $oldToken = $tokens[$tokenId]['token'];
                $tokens[$tokenId]['token'] = bin2hex(random_bytes(32));
                $tokens[$tokenId]['created_at'] = time();
                $this->session->set('csrf_tokens', $tokens);

                $this->logInfo('CSRF token regenerated', [
                    'identifier' => $tokenId,
                    'old_token_prefix' => substr($oldToken, 0, 8) . '...',
                    'new_token_prefix' => substr($tokens[$tokenId]['token'], 0, 8) . '...',
                    'client_ip' => $this->getClientIp(),
                    'user_agent' => $this->getUserAgent()
                ]);
            } catch (\Exception $e) {
                $this->logError('CSRF token regeneration failed', [
                    'error' => $e->getMessage(),
                    'identifier' => $identifier,
                    'client_ip' => $this->getClientIp(),
                    'user_agent' => $this->getUserAgent()
                ]);
                throw new \RuntimeException('Nepodařilo se obnovit CSRF token');
            }
        }

        return $tokens[$tokenId]['token'] ?? $this->generateToken($identifier);
    }

    /**
     * Získá všechny platné tokeny
     *
     * @return array Pole tokenů
     */
    private function getTokens(): array
    {
        return $this->session->get('csrf_tokens') ?? [];
    }

    /**
     * Ověří HTTP origin hlavičku
     *
     * @return bool True pokud je origin platný
     */
    private function validateOrigin(): bool
    {
        if (!$this->config['validate_origin']) {
            return true;
        }

        $origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? null;

        if (!$origin) {
            $this->logDebug('Origin validation failed - no origin header found', [
                'client_ip' => $this->getClientIp(),
                'request_url' => $this->getRequestUrl()
            ]);
            return false;
        }

        $allowedOrigin = $_SERVER['HTTP_HOST'] ?? null;
        if (!$allowedOrigin) {
            return true; // Pokud nemáme co porovnávat, povolíme
        }

        $isValid = parse_url($origin, PHP_URL_HOST) === $allowedOrigin;

        if (!$isValid) {
            $this->logDebug('Origin validation failed - host mismatch', [
                'origin' => $origin,
                'allowed_origin' => $allowedOrigin,
                'client_ip' => $this->getClientIp(),
                'request_url' => $this->getRequestUrl()
            ]);
        }

        return $isValid;
    }

    /**
     * Ověří HTTP referer hlavičku
     *
     * @return bool True pokud je referer platný
     */
    private function validateReferer(): bool
    {
        if (!$this->config['validate_referer']) {
            return true;
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? null;
        if (!$referer) {
            $this->logDebug('Referer validation failed - no referer header found', [
                'client_ip' => $this->getClientIp(),
                'request_url' => $this->getRequestUrl()
            ]);
            return false;
        }

        $allowedHost = $_SERVER['HTTP_HOST'] ?? null;
        if (!$allowedHost) {
            return true;
        }

        $isValid = parse_url($referer, PHP_URL_HOST) === $allowedHost;

        if (!$isValid) {
            $this->logDebug('Referer validation failed - host mismatch', [
                'referer' => $referer,
                'allowed_host' => $allowedHost,
                'client_ip' => $this->getClientIp(),
                'request_url' => $this->getRequestUrl()
            ]);
        }

        return $isValid;
    }

    /**
     * Vynutí maximální počet tokenů
     *
     * @param array $tokens Pole tokenů
     */
    private function enforceTokenLimit(array &$tokens): void
    {
        if (count($tokens) >= $this->config['max_tokens']) {
            $removedCount = count($tokens) - ($this->config['max_tokens'] - 1);

            // Seřadí tokeny podle data vytvoření a odstraní nejstarší
            uasort($tokens, function ($a, $b) {
                return $b['created_at'] <=> $a['created_at'];
            });

            $tokens = array_slice($tokens, 0, $this->config['max_tokens'] - 1, true);

            $this->logDebug('CSRF token limit enforced - old tokens removed', [
                'max_tokens' => $this->config['max_tokens'],
                'removed_count' => $removedCount,
                'remaining_count' => count($tokens),
                'client_ip' => $this->getClientIp()
            ]);
        }
    }

    /**
     * Vyčistí prošlé tokeny
     */
    private function cleanupExpiredTokens(): void
    {
        $tokens = $this->getTokens();
        $now = time();
        $removedCount = 0;

        foreach ($tokens as $id => $tokenData) {
            if ($now > $tokenData['expires_at']) {
                unset($tokens[$id]);
                $removedCount++;
            }
        }

        if ($removedCount > 0) {
            $this->session->set('csrf_tokens', $tokens);
            $this->logDebug('Expired CSRF tokens cleaned up', [
                'removed_count' => $removedCount,
                'remaining_count' => count($tokens),
                'client_ip' => $this->getClientIp()
            ]);
        }
    }

    /**
     * Metoda pro AJAX požadavky - vrátí token v hlavičce
     *
     * @param string|null $identifier Identifikátor tokenu
     */
    public function setAjaxTokenHeader(?string $identifier = null): void
    {
        if (!headers_sent()) {
            $token = $this->getToken($identifier);
            header("X-CSRF-Token: {$token}");

            $this->logDebug('CSRF token set in AJAX header', [
                'identifier' => $identifier ?? 'default',
                'token_prefix' => substr($token, 0, 8) . '...',
                'client_ip' => $this->getClientIp(),
                'request_url' => $this->getRequestUrl()
            ]);
        }
    }

    /**
     * Validuje token z AJAX požadavku
     *
     * @param string|null $identifier Identifikátor tokenu
     * @return bool True pokud je token platný
     */
    public function validateAjaxToken(?string $identifier = null): bool
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ??
                $_POST[$this->config['token_name']] ??
                $_GET[$this->config['token_name']] ?? null;

        if (!$token) {
            $this->logWarning('AJAX CSRF token not found in request', [
                'identifier' => $identifier,
                'sources_checked' => ['HTTP_X_CSRF_TOKEN', 'POST', 'GET'],
                'client_ip' => $this->getClientIp(),
                'user_agent' => $this->getUserAgent(),
                'request_url' => $this->getRequestUrl()
            ]);
            return false;
        }

        $this->logDebug('AJAX CSRF token validation attempt', [
            'identifier' => $identifier,
            'token_prefix' => substr($token, 0, 8) . '...',
            'token_length' => strlen($token),
            'client_ip' => $this->getClientIp(),
            'user_agent' => $this->getUserAgent(),
            'request_url' => $this->getRequestUrl()
        ]);

        return $this->validateToken($token, $identifier);
    }

    /**
     * Změní konfiguraci za běhu
     *
     * @param array $config Nová konfigurace
     */
    public function setConfig(array $config): void
    {
        $oldConfig = $this->config;
        $this->config = array_merge($this->config, $config);

        $this->logInfo('CSRF configuration updated', [
            'old_config' => array_diff_assoc($oldConfig, $this->config),
            'new_config' => array_diff_assoc($this->config, $oldConfig),
            'client_ip' => $this->getClientIp()
        ]);
    }

    /**
     * Vrátí aktuální konfiguraci
     *
     * @return array Konfigurace
     */
    public function getConfig(): array
    {
        return $this->config;
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

    /**
     * Získá URL požadavku
     *
     * @return string URL
     */
    private function getRequestUrl(): string
    {
        return ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' .
               ($_SERVER['HTTP_HOST'] ?? 'unknown') .
               ($_SERVER['REQUEST_URI'] ?? '');
    }

    /**
     * Loguje zprávu na úrovni DEBUG
     */
    private function logDebug(string $message, array $context = []): void
    {
        $this->logger->debug($this->formatLogMessage($message, $context));
    }

    /**
     * Loguje zprávu na úrovni INFO
     */
    private function logInfo(string $message, array $context = []): void
    {
        $this->logger->info($this->formatLogMessage($message, $context));
    }

    /**
     * Loguje zprávu na úrovni WARNING
     */
    private function logWarning(string $message, array $context = []): void
    {
        $this->logger->warning($this->formatLogMessage($message, $context));
    }

    /**
     * Loguje zprávu na úrovni ERROR
     */
    private function logError(string $message, array $context = []): void
    {
        $this->logger->error($this->formatLogMessage($message, $context));
    }

    /**
     * Formátuje log zprávu s kontextem
     */
    private function formatLogMessage(string $message, array $context): string
    {
        if (empty($context)) {
            return $message;
        }

        return $message . ' | ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
    
    /**
 * Vrátí název tokenu pro formuláře
 *
 * @return string Název tokenu
 */
public function getTokenName(): string
{
    return $this->config['token_name'];
}
}