<?php
/**
 * Creates the schema and (optionally) the demo data.
 *   php database/install.php            -> schema + demo data
 *   php database/install.php --no-seed  -> schema only (empty platform)
 * WARNING: drops and recreates all YouthConnect tables.
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../app/bootstrap.php';

use App\Core\DB;

$c = config('db');
try {   // create the database if the account is allowed to (not possible on some shared hosts)
    $p = new PDO("mysql:host={$c['host']};port={$c['port']};charset=utf8mb4", $c['user'], $c['pass']);
    $p->exec("CREATE DATABASE IF NOT EXISTS `{$c['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
} catch (Throwable $e) {
    echo "Note: could not auto-create the database ({$e->getMessage()}). Continuing with the existing one.\n";
}

$sql = file_get_contents(__DIR__ . '/schema.sql');
$sql = preg_replace('/^--.*$/m', '', $sql);
$n = 0;
foreach (preg_split('/;\s*[\r\n]+/', $sql) as $stmt) {
    $stmt = trim($stmt);
    if ($stmt === '') continue;
    DB::pdo()->exec($stmt);
    $n++;
}
echo "Schema installed ($n statements).\n";

if (!in_array('--no-seed', $argv, true)) {
    require __DIR__ . '/seed.php';
}
echo "Done.\n";
