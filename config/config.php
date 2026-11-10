<?php
// Configuration. Values come from environment variables first, then config/local.php (git-ignored),
// then the defaults below. Never commit real credentials.
$local = is_file(__DIR__ . '/local.php') ? require __DIR__ . '/local.php' : [];
$env = function (string $key, $default = null) use ($local) {
    $v = getenv($key);
    if ($v !== false && $v !== '') return $v;
    return $local[$key] ?? $default;
};

return [
    'app' => [
        'name'        => 'YouthConnect',
        'env'         => $env('APP_ENV', 'production'),   // production | local
        'debug'       => filter_var($env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN),
        'base_path'   => $env('APP_BASE_PATH', null),     // optional, auto-detected otherwise
        'timezone'    => $env('APP_TZ', 'Africa/Kampala'),
        'force_https' => filter_var($env('APP_FORCE_HTTPS', false), FILTER_VALIDATE_BOOLEAN),
    ],
    'db' => [
        'host' => $env('DB_HOST', '127.0.0.1'),
        'port' => (int)$env('DB_PORT', 3306),
        'name' => $env('DB_NAME', 'youthconnect'),
        'user' => $env('DB_USER', 'root'),
        'pass' => $env('DB_PASS', ''),
    ],
    'security' => [
        'login_max_attempts'   => 5,     // per email per window
        'login_ip_max'         => 30,    // per IP per window
        'login_window_minutes' => 15,
        'session_minutes'      => 120,
        'password_min'         => 8,
        'upload_max_bytes'     => 5 * 1024 * 1024,
    ],
    // "Continue with Google". Leave empty to hide the button. See docs/GOOGLE_SIGNIN.md.
    'google' => [
        'client_id'     => $env('GOOGLE_CLIENT_ID', ''),
        'client_secret' => $env('GOOGLE_CLIENT_SECRET', ''),
        'redirect_uri'  => $env('GOOGLE_REDIRECT_URI', ''),   // optional; e.g. https://yoursite/auth/google/callback
    ],
    'level2' => [
        'min_completed_courses' => 1,    // courses a participant must complete to unlock Level Two
        'max_team_size'         => 6,
    ],
    'storage_path' => $env('STORAGE_PATH', dirname(__DIR__) . '/storage'),
];
