<?php
// Copy to config/local.php (git-ignored) and edit. Environment variables of the same name take precedence.
return [
    'APP_ENV'   => 'local',      // use 'production' on the live server
    'APP_DEBUG' => false,
    'DB_HOST'   => '127.0.0.1',
    'DB_PORT'   => 3306,
    'DB_NAME'   => 'youthconnect',
    'DB_USER'   => 'root',
    'DB_PASS'   => '',
    // 'GOOGLE_CLIENT_ID'     => '',   // from Google Cloud Console (docs/GOOGLE_SIGNIN.md)
    // 'GOOGLE_CLIENT_SECRET' => '',
    // 'GOOGLE_REDIRECT_URI'  => '',   // e.g. https://yoursite.example/auth/google/callback
    // 'APP_FORCE_HTTPS' => true,   // enable on the live server (redirects http -> https, sends HSTS)
    // 'APP_BASE_PATH'   => '',     // only if auto-detection is wrong (e.g. '/youthconnect')
];
