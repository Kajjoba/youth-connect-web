<?php
namespace App\Core;

class Notify
{
    public static function to(int $userId, string $message, ?string $link = null): void
    {
        DB::run('INSERT INTO notifications (user_id, message, link) VALUES (?,?,?)', [$userId, mb_substr($message, 0, 250), $link]);
    }

    /** Notify every active member of a project's team (and optionally staff), except one user. */
    public static function project(int $projectId, string $message, ?string $link = null, ?int $exceptUserId = null, bool $includeStaff = false): void
    {
        $ids = DB::all('SELECT tm.user_id FROM team_members tm JOIN projects p ON p.team_id = tm.team_id
                        WHERE p.id = ? AND tm.status = \'active\'', [$projectId]);
        $users = array_column($ids, 'user_id');
        if ($includeStaff) {
            $users = array_merge($users, array_column(DB::all('SELECT DISTINCT user_id FROM project_staff WHERE project_id = ?', [$projectId]), 'user_id'));
        }
        foreach (array_unique($users) as $uid) {
            if ($exceptUserId !== null && (int)$uid === $exceptUserId) continue;
            self::to((int)$uid, $message, $link);
        }
    }

    public static function unreadCount(int $userId): int
    {
        return (int)DB::val('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL', [$userId]);
    }
}
