<?php
// Application bootstrap: autoloading, error handling, security headers, session.
define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) return;
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) require $file;
});

require BASE_PATH . '/app/helpers.php';

date_default_timezone_set(config('app.timezone', 'UTC'));
mb_internal_encoding('UTF-8');

// Errors: log them, never show them to users in production.
ini_set('display_errors', config('app.debug') ? '1' : '0');
ini_set('log_errors', '1');
@mkdir(config('storage_path') . '/logs', 0775, true);
ini_set('error_log', config('storage_path') . '/logs/php-error.log');
error_reporting(E_ALL);

set_exception_handler(function (Throwable $e) {
    error_log('[' . date('c') . '] ' . get_class($e) . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    if (PHP_SAPI === 'cli') { fwrite(STDERR, get_class($e) . ': ' . $e->getMessage() . "\n  at " . $e->getFile() . ':' . $e->getLine() . "\n"); exit(1); }
    if (!headers_sent()) http_response_code(500);
    if (config('app.debug')) {
        echo '<pre>' . htmlspecialchars((string)$e) . '</pre>';
    } else {
        \App\Core\View::renderError(500, 'Something went wrong on our side',
            'We have recorded the problem. Please try again in a moment. Your work is safe.');
    }
});

// Optional HTTPS redirect (set APP_FORCE_HTTPS=1 on the live server).
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
if (config('app.force_https') && !$https && PHP_SAPI !== 'cli') {
    header('Location: https://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '/'), true, 301);
    exit;
}
$GLOBALS['ycp_https'] = $https;

// Security headers on every response.
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'; frame-src https://www.youtube-nocookie.com; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
if ($https) header('Strict-Transport-Security: max-age=31536000');

// Compress responses (big saving on slow mobile links).
if (PHP_SAPI !== 'cli' && extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
    ini_set('zlib.output_compression', '1');
}

\App\Core\Session::start();
