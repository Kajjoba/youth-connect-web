<?php
namespace App\Core;

/**
 * Sessions are stored in the database (not local files) so any web server node can serve any user,
 * which keeps the app horizontally scalable. Cookies are HttpOnly + SameSite=Lax (+Secure on HTTPS).
 */
class Session implements \SessionHandlerInterface
{
    private int $lifetime;

    public static function start(): void
    {
        if (PHP_SAPI === 'cli' || session_status() === PHP_SESSION_ACTIVE) return;
        $h = new self();
        $h->lifetime = (int)config('security.session_minutes') * 60;
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.gc_maxlifetime', (string)$h->lifetime);
        session_name('ycp_sid');
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => base_path() === '' ? '/' : base_path() . '/',
            'secure'   => !empty($GLOBALS['ycp_https']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_set_save_handler($h, true);
        session_start();
    }

    public static function flash(string $type, string $msg): void
    {
        $_SESSION['_flash'][] = ['type' => $type, 'msg' => $msg];
    }

    public static function pullFlash(): array
    {
        $f = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $f;
    }

    /** Remember submitted input + errors for exactly one redirect. */
    public static function withErrors(array $errors, array $old): void
    {
        $_SESSION['_errors'] = $errors;
        $_SESSION['_old'] = $old;
    }

    /** Called after a page is rendered so validation state shows exactly once. */
    public static function clearOldInput(): void
    {
        unset($_SESSION['_errors'], $_SESSION['_old']);
    }

    // --- SessionHandlerInterface -------------------------------------------------
    #[\ReturnTypeWillChange] public function open($path, $name) { return true; }
    #[\ReturnTypeWillChange] public function close() { return true; }
    #[\ReturnTypeWillChange] public function read($id)
    {
        $d = DB::val('SELECT data FROM sessions WHERE id = ? AND updated_at > ?', [$id, time() - $this->lifetime]);
        return $d === null ? '' : $d;
    }
    #[\ReturnTypeWillChange] public function write($id, $data)
    {
        DB::run('REPLACE INTO sessions (id, data, updated_at) VALUES (?,?,?)', [$id, $data, time()]);
        return true;
    }
    #[\ReturnTypeWillChange] public function destroy($id)
    {
        DB::run('DELETE FROM sessions WHERE id = ?', [$id]);
        return true;
    }
    #[\ReturnTypeWillChange] public function gc($max)
    {
        return DB::run('DELETE FROM sessions WHERE updated_at < ?', [time() - (int)$max]);
    }
}
