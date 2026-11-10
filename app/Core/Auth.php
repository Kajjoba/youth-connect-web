<?php
namespace App\Core;

class Auth
{
    private static ?array $user = null;
    private static bool $loaded = false;

    public static function check(): bool { return self::user() !== null; }

    public static function user(): ?array
    {
        if (!self::$loaded) {
            self::$loaded = true;
            $id = $_SESSION['uid'] ?? null;
            if ($id) {
                $u = DB::one('SELECT u.id, u.name, u.email, u.role, u.status, u.level2_override,
                                     p.country, p.headline, p.lite_mode, p.connectivity
                              FROM users u LEFT JOIN profiles p ON p.user_id = u.id WHERE u.id = ?', [$id]);
                if ($u && $u['status'] === 'active') self::$user = $u;
                else { unset($_SESSION['uid']); }
            }
        }
        return self::$user;
    }

    public static function hasRole(string ...$roles): bool
    {
        $u = self::user();
        return $u && in_array($u['role'], $roles, true);
    }

    public static function login(int $userId): void
    {
        session_regenerate_id(true);               // prevents session fixation
        $_SESSION['uid'] = $userId;
        unset($_SESSION['_csrf']);                 // fresh CSRF token for the new session
        DB::run('UPDATE users SET last_login_at = NOW() WHERE id = ?', [$userId]);
        self::$loaded = false;
        self::$user = null;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) session_destroy();
        self::$user = null;
        self::$loaded = true;
    }

    /**
     * Verify credentials with rate limiting. Returns user id, or a string error key:
     * 'throttled' | 'invalid' | 'suspended'.
     */
    public static function attempt(string $email, string $password)
    {
        $email = strtolower(trim($email));
        $win = (int)config('security.login_window_minutes');
        $kE = 'login:e:' . $email;
        $kI = 'login:i:' . Request::ip();
        if (Throttle::tooMany($kE, (int)config('security.login_max_attempts'), $win)
            || Throttle::tooMany($kI, (int)config('security.login_ip_max'), $win)) {
            return 'throttled';
        }
        $u = DB::one('SELECT id, password_hash, status FROM users WHERE email = ?', [$email]);
        // Always run a hash comparison so response time does not reveal whether the email exists.
        $hash = $u['password_hash'] ?? '$2y$10$abcdefghijklmnopqrstuuYQ5s6Vq0m8o0Yk0m2k3c1p5q7r9s1u2';
        $ok = password_verify($password, $hash);
        if (!$u || !$ok) {
            Throttle::hit($kE);
            Throttle::hit($kI);
            Audit::log('login.failed', 'user', null, $email, null);
            return 'invalid';
        }
        if ($u['status'] !== 'active') return 'suspended';
        Throttle::clear($kE);
        if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) {
            DB::run('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $u['id']]);
        }
        return (int)$u['id'];
    }
}
