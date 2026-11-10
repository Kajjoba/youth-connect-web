<?php
namespace App\Core;

/**
 * Central authorisation. Controllers never trust an ID from the URL: they obtain the resource through
 * one of these loaders, which returns null unless the CURRENT user is allowed to see it. Callers answer
 * "not found" for null, so outsiders cannot even confirm that another team's project exists.
 */
class Policy
{
    // ---- Courses --------------------------------------------------------------

    public static function manageCourse(array $course): bool
    {
        $u = Auth::user();
        if (!$u) return false;
        return $u['role'] === 'admin' || ($u['role'] === 'facilitator' && (int)$course['owner_id'] === (int)$u['id']);
    }

    public static function enrollment(int $courseId): ?array
    {
        $u = Auth::user();
        if (!$u) return null;
        return DB::one('SELECT * FROM enrollments WHERE user_id = ? AND course_id = ? AND status <> \'dropped\'', [$u['id'], $courseId]);
    }

    /** Learners see lessons of courses they are enrolled in; anyone may open lessons flagged as preview. */
    public static function canOpenLesson(array $course, array $lesson): bool
    {
        if (self::manageCourse($course)) return true;
        if (self::enrollment((int)$course['id'])) return true;
        return (int)$lesson['is_preview'] === 1 && $course['status'] === 'published';
    }

    public static function canLearn(array $course): bool
    {
        return self::manageCourse($course) || (bool)self::enrollment((int)$course['id']);
    }

    // ---- Teams ----------------------------------------------------------------

    /** Returns 'lead' | 'member' for ACTIVE members, null otherwise. */
    public static function teamRole(int $teamId): ?string
    {
        $u = Auth::user();
        if (!$u) return null;
        $r = DB::one('SELECT role FROM team_members WHERE team_id = ? AND user_id = ? AND status = \'active\'', [$teamId, $u['id']]);
        return $r['role'] ?? null;
    }

    // ---- Projects -------------------------------------------------------------

    /**
     * Load a project by its opaque public id ONLY if the current user may see it.
     * Adds $project['role'] = admin | lead | member | facilitator | mentor | stakeholder.
     */
    public static function project(string $publicId): ?array
    {
        $u = Auth::user();
        if (!$u || !preg_match('/^[a-f0-9]{16}$/', $publicId)) return null;
        $p = DB::one('SELECT p.*, t.name AS team_name, t.public_id AS team_public_id
                      FROM projects p JOIN teams t ON t.id = p.team_id WHERE p.public_id = ?', [$publicId]);
        if (!$p) return null;
        $role = self::projectRole($p, $u);
        if ($role === null) return null;
        $p['role'] = $role;
        return $p;
    }

    public static function projectRole(array $project, array $user): ?string
    {
        $tm = DB::one('SELECT role FROM team_members WHERE team_id = ? AND user_id = ? AND status = \'active\'', [$project['team_id'], $user['id']]);
        if ($tm) return $tm['role'];                       // lead | member
        $staff = DB::all('SELECT role FROM project_staff WHERE project_id = ? AND user_id = ?', [$project['id'], $user['id']]);
        $roles = array_column($staff, 'role');
        foreach (['facilitator', 'mentor', 'stakeholder'] as $r) {
            if (in_array($r, $roles, true)) return $r;
        }
        if ($user['role'] === 'admin') return 'admin';
        return null;
    }

    public static function isTeam(array $p): bool { return in_array($p['role'], ['lead', 'member'], true); }
    public static function canAdvise(array $p): bool { return in_array($p['role'], ['mentor', 'facilitator', 'admin'], true); }
    public static function canFinalize(array $p): bool { return in_array($p['role'], ['facilitator', 'admin'], true); }
    public static function canGiveFeedback(array $p): bool { return in_array($p['role'], ['mentor', 'facilitator', 'stakeholder', 'admin'], true); }
}
