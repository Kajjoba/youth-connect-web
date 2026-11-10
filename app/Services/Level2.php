<?php
namespace App\Services;

use App\Core\DB;

class Level2
{
    public static function completedCourses(int $userId): int
    {
        return (int)DB::val('SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND status = \'completed\'', [$userId]);
    }

    /** Level Two is unlocked after finishing the required Level One courses, or by an admin override. */
    public static function eligible(array $user): bool
    {
        if ($user['role'] !== 'participant') return false;
        if (!empty($user['level2_override'])) return true;
        return self::completedCourses((int)$user['id']) >= (int)config('level2.min_completed_courses');
    }

    public static function required(): int { return (int)config('level2.min_completed_courses'); }
}
