<?php
namespace App\Services;

use App\Core\DB;
use App\Core\Notify;

class Progress
{
    /** Items in a course: lessons + one quiz per week (if it has questions) + assignments. */
    public static function summary(int $userId, int $courseId): array
    {
        $lessons = (int)DB::val('SELECT COUNT(*) FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = ?', [$courseId]);
        $quizzes = (int)DB::val('SELECT COUNT(*) FROM quizzes q JOIN modules m ON m.id = q.module_id
                                 WHERE m.course_id = ? AND EXISTS (SELECT 1 FROM questions x WHERE x.quiz_id = q.id)', [$courseId]);
        $assign = (int)DB::val('SELECT COUNT(*) FROM assignments a JOIN modules m ON m.id = a.module_id WHERE m.course_id = ?', [$courseId]);

        $lDone = (int)DB::val('SELECT COUNT(*) FROM lesson_progress lp JOIN lessons l ON l.id = lp.lesson_id JOIN modules m ON m.id = l.module_id
                               WHERE lp.user_id = ? AND m.course_id = ?', [$userId, $courseId]);
        $qDone = (int)DB::val('SELECT COUNT(DISTINCT qa.quiz_id) FROM quiz_attempts qa JOIN quizzes q ON q.id = qa.quiz_id JOIN modules m ON m.id = q.module_id
                               WHERE qa.user_id = ? AND m.course_id = ? AND qa.passed = 1', [$userId, $courseId]);
        $aDone = (int)DB::val('SELECT COUNT(*) FROM submissions s JOIN assignments a ON a.id = s.assignment_id JOIN modules m ON m.id = a.module_id
                               WHERE s.user_id = ? AND m.course_id = ?', [$userId, $courseId]);

        $total = $lessons + $quizzes + $assign;
        $done = $lDone + $qDone + $aDone;
        return ['done' => $done, 'total' => $total, 'pct' => $total > 0 ? (int)floor($done * 100 / $total) : 0];
    }

    /** Re-evaluate completion; issues the certificate exactly once (unique key makes it idempotent). */
    public static function recalc(int $userId, int $courseId): array
    {
        $s = self::summary($userId, $courseId);
        if ($s['total'] > 0 && $s['done'] >= $s['total']) {
            $changed = DB::run('UPDATE enrollments SET status = \'completed\', completed_at = NOW()
                                WHERE user_id = ? AND course_id = ? AND status = \'active\'', [$userId, $courseId]);
            if ($changed) {
                $c = DB::one('SELECT title, has_certificate FROM courses WHERE id = ?', [$courseId]);
                if ($c && $c['has_certificate']) {
                    $code = 'YCP-' . strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
                    DB::run('INSERT IGNORE INTO certificates (code, user_id, kind, course_id, project_id, title) VALUES (?,?,?,?,0,?)',
                        [$code, $userId, 'course', $courseId, 'Certificate of Completion: ' . $c['title']]);
                }
                Notify::to($userId, 'Congratulations! You completed "' . ($c['title'] ?? 'your course') . '".', 'my-learning');
            }
        }
        return $s;
    }

    public static function courseStats(int $courseId): array
    {
        $r = DB::one('SELECT COUNT(*) AS n, COALESCE(AVG(rating),0) AS avg FROM reviews WHERE course_id = ?', [$courseId]);
        $enrolled = (int)DB::val('SELECT COUNT(*) FROM enrollments WHERE course_id = ? AND status <> \'dropped\'', [$courseId]);
        return ['reviews' => (int)$r['n'], 'rating' => (float)$r['avg'], 'enrolled' => $enrolled];
    }
}
