<?php
namespace App\Core;

class Audit
{
    public static function log(string $action, ?string $entity = null, $entityId = null, ?string $detail = null, ?int $userId = null): void
    {
        try {
            $uid = $userId ?? (Auth::user()['id'] ?? null);
            DB::run('INSERT INTO audit_log (user_id, action, entity, entity_id, detail, ip) VALUES (?,?,?,?,?,?)',
                [$uid, $action, $entity, $entityId === null ? null : (string)$entityId,
                 $detail === null ? null : mb_substr($detail, 0, 250), Request::ip()]);
        } catch (\Throwable $e) {
            error_log('audit failed: ' . $e->getMessage());   // auditing must never break the request
        }
    }
}
