<?php
namespace App\Services;

use App\Core\DB;

/** Read-side queries for courses, syllabus and the learner's ordered path through a course. */
class Courses
{
    private const CARD_SQL = 'SELECT c.id, c.title, c.slug, c.summary, c.level, c.color, c.hours_per_week, c.status,
            cat.name AS category, cat.slug AS category_slug, u.name AS owner,
            (SELECT COUNT(*) FROM modules m WHERE m.course_id = c.id) AS weeks,
            (SELECT COALESCE(AVG(r.rating),0) FROM reviews r WHERE r.course_id = c.id) AS rating,
            (SELECT COUNT(*) FROM reviews r WHERE r.course_id = c.id) AS reviews,
            (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id AND e.status <> \'dropped\') AS enrolled
        FROM courses c JOIN categories cat ON cat.id = c.category_id JOIN users u ON u.id = c.owner_id ';

    /** $where must be a trusted SQL fragment using ? placeholders; $order is chosen from a whitelist by callers. */
    public static function cards(string $where, array $params, string $order = 'enrolled DESC', int $limit = 12, int $offset = 0): array
    {
        $sql = self::CARD_SQL . 'WHERE c.status = \'published\' ' . ($where ? "AND $where " : '')
             . "ORDER BY $order, c.id DESC LIMIT " . (int)$limit . ' OFFSET ' . (int)$offset;
        return DB::all($sql, $params);
    }

    public static function countPublished(string $where, array $params): int
    {
        return (int)DB::val('SELECT COUNT(*) FROM courses c JOIN categories cat ON cat.id = c.category_id WHERE c.status = \'published\' '
            . ($where ? "AND $where" : ''), $params);
    }

    public static function bySlug(string $slug): ?array
    {
        return DB::one('SELECT c.*, cat.name AS category, cat.slug AS category_slug, u.name AS owner,
                p.headline AS owner_headline, p.bio AS owner_bio
            FROM courses c JOIN categories cat ON cat.id = c.category_id JOIN users u ON u.id = c.owner_id
            LEFT JOIN profiles p ON p.user_id = u.id WHERE c.slug = ?', [$slug]);
    }

    /** Weeks with lesson list, quiz and assignment, plus item counts: what a learner sees BEFORE enrolling. */
    public static function syllabus(int $courseId): array
    {
        $mods = DB::all('SELECT * FROM modules WHERE course_id = ? ORDER BY week_no', [$courseId]);
        if (!$mods) return [];
        $ids = array_column($mods, 'id');
        $in = DB::in($ids);
        $lessons = DB::all("SELECT id, module_id, type, title, minutes, is_preview, position FROM lessons WHERE module_id IN ($in) ORDER BY module_id, position, id", $ids);
        $quizzes = DB::all("SELECT q.id, q.module_id, q.title, q.pass_mark, q.minutes,
                (SELECT COUNT(*) FROM questions x WHERE x.quiz_id = q.id) AS questions
                FROM quizzes q WHERE q.module_id IN ($in)", $ids);
        $asg = DB::all("SELECT id, module_id, title FROM assignments WHERE module_id IN ($in)", $ids);

        $byMod = [];
        foreach ($lessons as $l) $byMod[$l['module_id']]['lessons'][] = $l;
        foreach ($quizzes as $q) if ($q['questions'] > 0) $byMod[$q['module_id']]['quiz'] = $q;
        foreach ($asg as $a) $byMod[$a['module_id']]['assignment'] = $a;

        foreach ($mods as &$m) {
            $x = $byMod[$m['id']] ?? [];
            $m['lessons'] = $x['lessons'] ?? [];
            $m['quiz'] = $x['quiz'] ?? null;
            $m['assignment'] = $x['assignment'] ?? null;
            $cnt = ['reading' => 0, 'video' => 0, 'discussion' => 0];
            $mins = 0;
            foreach ($m['lessons'] as $l) { $cnt[$l['type']]++; $mins += (int)$l['minutes']; }
            $m['counts'] = $cnt;
            $m['minutes'] = $mins + ($m['quiz'] ? (int)$m['quiz']['minutes'] : 0);
        }
        return $mods;
    }

    /**
     * The learner's ordered path: for each week, its lessons, then the weekly quiz, then the assignment.
     * Each item carries a 'done' flag for the given user.
     */
    public static function path(array $syllabus, int $userId, string $slug): array
    {
        $doneLessons = $doneQuiz = $doneAsg = [];
        if ($userId && $syllabus) {
            $mIds = array_column($syllabus, 'id');
            $in = DB::in($mIds);
            $doneLessons = array_column(DB::all("SELECT lp.lesson_id FROM lesson_progress lp JOIN lessons l ON l.id = lp.lesson_id
                    WHERE lp.user_id = ? AND l.module_id IN ($in)", array_merge([$userId], $mIds)), 'lesson_id');
            $doneQuiz = array_column(DB::all("SELECT DISTINCT q.module_id FROM quiz_attempts qa JOIN quizzes q ON q.id = qa.quiz_id
                    WHERE qa.user_id = ? AND qa.passed = 1 AND q.module_id IN ($in)", array_merge([$userId], $mIds)), 'module_id');
            $doneAsg = array_column(DB::all("SELECT a.module_id FROM submissions s JOIN assignments a ON a.id = s.assignment_id
                    WHERE s.user_id = ? AND a.module_id IN ($in)", array_merge([$userId], $mIds)), 'module_id');
        }
        $items = [];
        foreach ($syllabus as $m) {
            foreach ($m['lessons'] as $l) {
                $items[] = ['kind' => 'lesson', 'type' => $l['type'], 'id' => (int)$l['id'], 'module_id' => (int)$m['id'], 'title' => $l['title'],
                    'minutes' => (int)$l['minutes'], 'done' => in_array($l['id'], $doneLessons), 'url' => "learn/$slug/lesson/{$l['id']}"];
            }
            if ($m['quiz']) {
                $items[] = ['kind' => 'quiz', 'type' => 'quiz', 'id' => (int)$m['id'], 'module_id' => (int)$m['id'], 'title' => $m['quiz']['title'],
                    'minutes' => (int)$m['quiz']['minutes'], 'done' => in_array($m['id'], $doneQuiz), 'url' => "learn/$slug/quiz/{$m['id']}"];
            }
            if ($m['assignment']) {
                $items[] = ['kind' => 'assignment', 'type' => 'assignment', 'id' => (int)$m['id'], 'module_id' => (int)$m['id'], 'title' => $m['assignment']['title'],
                    'minutes' => 30, 'done' => in_array($m['id'], $doneAsg), 'url' => "learn/$slug/assignment/{$m['id']}"];
            }
        }
        return $items;
    }
}
