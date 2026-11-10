<?php
namespace App\Core;

class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        return $_SESSION['_csrf'];
    }

    /** Accepts the token from a form field or an X-CSRF-Token header (AJAX). */
    public static function verify(): bool
    {
        $sent = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        $real = $_SESSION['_csrf'] ?? '';
        return $real !== '' && is_string($sent) && hash_equals($real, $sent);
    }
}
