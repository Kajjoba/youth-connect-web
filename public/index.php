<?php
// Front controller: every request enters here.
// With PHP's built-in server (php -S) this file also acts as the router script for static assets.
if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($path !== '/' && is_file(__DIR__ . $path)) return false;
}

require __DIR__ . '/../app/bootstrap.php';

$router = new App\Core\Router();
require BASE_PATH . '/app/routes.php';
$router->dispatch();
