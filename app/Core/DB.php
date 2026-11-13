<?php
namespace App\Core;

use PDO;

/** Thin PDO wrapper. All queries use prepared statements with bound parameters. */
class DB
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $c = config('db');
            $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['name']};charset=utf8mb4";
            $opts = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            // Cloud MySQL (TiDB, Aiven, PlanetScale...) requires an encrypted connection: set DB_SSL=1.
            // 1009 = the MySQL SSL CA option; defaults to the system certificate bundle.
            if (!empty($c['ssl'])) {
                $ca = $c['ssl_ca'];
                if (!$ca) foreach (['/etc/ssl/certs/ca-certificates.crt', (string)ini_get('openssl.cafile'), (string)ini_get('curl.cainfo')] as $cand) { if ($cand !== '' && is_file($cand)) { $ca = $cand; break; } }
                if ($ca) $opts[1009] = $ca;
            }
            self::$pdo = new PDO($dsn, $c['user'], $c['pass'], $opts);
            // Keep DB timestamps in the same timezone as PHP so time_ago() is right.
            self::$pdo->exec("SET time_zone = '" . date('P') . "'");
        }
        return self::$pdo;
    }

    public static function all(string $sql, array $p = []): array
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($p);
        return $st->fetchAll();
    }

    public static function one(string $sql, array $p = []): ?array
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($p);
        $r = $st->fetch();
        return $r === false ? null : $r;
    }

    public static function val(string $sql, array $p = [])
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($p);
        $r = $st->fetchColumn();
        return $r === false ? null : $r;
    }

    /** Executes a write; returns affected rows. */
    public static function run(string $sql, array $p = []): int
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($p);
        return $st->rowCount();
    }

    public static function insert(string $sql, array $p = []): int
    {
        self::run($sql, $p);
        return (int)self::pdo()->lastInsertId();
    }

    /** Run $fn inside a transaction; rolls back on any exception. */
    public static function tx(callable $fn)
    {
        $pdo = self::pdo();
        if ($pdo->inTransaction()) return $fn();
        $pdo->beginTransaction();
        try {
            $r = $fn();
            $pdo->commit();
            return $r;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    /** Builds "?,?,?" for IN() lists. */
    public static function in(array $values): string
    {
        return implode(',', array_fill(0, max(1, count($values)), '?'));
    }
}
