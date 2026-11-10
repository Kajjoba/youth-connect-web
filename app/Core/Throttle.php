<?php
namespace App\Core;

/** Simple DB-backed rate limiter (works across multiple web nodes). */
class Throttle
{
    public static function tooMany(string $key, int $max, int $minutes): bool
    {
        $n = (int)DB::val('SELECT COUNT(*) FROM throttle WHERE k = ? AND attempted_at > (UTC_TIMESTAMP() - INTERVAL ? MINUTE)', [$key, $minutes]);
        return $n >= $max;
    }

    public static function hit(string $key): void
    {
        DB::run('INSERT INTO throttle (k, attempted_at) VALUES (?, UTC_TIMESTAMP())', [$key]);
        if (random_int(1, 50) === 1) DB::run('DELETE FROM throttle WHERE attempted_at < (UTC_TIMESTAMP() - INTERVAL 1 DAY)');
    }

    public static function clear(string $key): void
    {
        DB::run('DELETE FROM throttle WHERE k = ?', [$key]);
    }
}
