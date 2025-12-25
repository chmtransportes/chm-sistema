<?php
/**
 * CHM Sistema - Gerenciador de Sessões
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 * @version 1.0.0
 */

namespace CHM\Core;

class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started) return;

        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_httponly', SESSION_HTTPONLY ? 1 : 0);
        ini_set('session.cookie_secure', SESSION_SECURE ? 1 : 0);
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

        session_name(SESSION_NAME);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['_created'])) {
            $_SESSION['_created'] = time();
        } elseif (time() - $_SESSION['_created'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['_created'] = time();
        }
        self::$started = true;
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function clear(): void
    {
        self::start();
        $_SESSION = [];
    }

    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
        self::$started = false;
    }

    public static function flash(string $key, mixed $value): void
    {
        self::set('_flash_' . $key, $value);
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = self::get('_flash_' . $key, $default);
        self::remove('_flash_' . $key);
        return $value;
    }

    public static function isAuthenticated(): bool
    {
        return self::has('user_id') && self::get('user_id') > 0;
    }

    public static function getUserId(): ?int
    {
        return self::get('user_id');
    }

    public static function getUserProfile(): ?int
    {
        return self::get('user_profile');
    }

    public static function isAdmin(): bool
    {
        return self::getUserProfile() === PROFILE_ADMIN;
    }

    public static function isDriver(): bool
    {
        return self::getUserProfile() === PROFILE_DRIVER;
    }

    public static function isClient(): bool
    {
        return self::getUserProfile() === PROFILE_CLIENT;
    }

    public static function setCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        self::set('csrf_token', $token);
        return $token;
    }

    public static function validateCsrfToken(string $token): bool
    {
        $storedToken = self::get('csrf_token');
        return $storedToken && hash_equals($storedToken, $token);
    }

    public static function getCsrfToken(): string
    {
        if (!self::has('csrf_token')) {
            return self::setCsrfToken();
        }
        return self::get('csrf_token');
    }
}
