<?php
namespace App\Controllers;

use App\Core\{Controller, DB, Policy, Audit, Notify, Throttle};
use App\Services\{Courses, Progress};

class LearnController extends Controller
{
    /** Common loader: course + permission context. Returns null after sending a response. */
    private function ctx(string $slug, bool $learnerOnly = false): ?array
    {
        $course = Courses::bySlug($slug);
        if (!$course || ($course['status'] !== 'published' && !Policy::manageCourse($course))) { $this->notFound(); return null; }
        $enrolled = $this->user()['role'] === 'participant' ? Policy::enrollment((int)$course['id']) : null;
        $manage = Policy::manageCourse($course);
        if ($learnerOnly && !$enrolled) {
            flash('info', 'Enrol in this course to take part in this activity.');
            $this->redirect('course/' . $slug);
            return null;
        }
        $syllabus = Courses::syllabus((int)$course['id']);
        $items = Courses::path($syllabus, $enrolled ? $this->uid() : 0, $slug);
        return compact('course', 'enrolled', 'manage', 'syllabus', 'items');
    }

    private function neighbours(array $items, string $kind, int $id): array
    {
        $prev = $next = null;
        foreach ($items as $i => $it) {
            if ($it['kind'] === $kind && $it['id'] === $id) {
                $prev = $items[$i - 1] ?? null;
                $next = $items[$i + 1] ?? null;
                break;
            }
        }
        return [$prev, $next];
    }

    /** Resume where the learner left off: first unfinished item. */
    public function start(string $slug): void
    {
        $c = $this->ctx($slug);
        if (!$c) return;
        if (!$c['enrolled'] && !$c['manage']) { $this->redirect('course/' . $slug); return; }
        if (!$c['items']) { flash('info', 'This course has no content yet. Check back soon.'); $this->redirect('course/' . $slug); return; }
        $target = $c['items'][0];
        foreach ($c['items'] as $it) if (!$it['done']) { $target = $it; break; }
        $this->redirect($target['url']);
    }

    public function lesson(string $slug, string $id): void
    {
        $c = $this->ctx($slug);
        if (!$c) return;
        $lesson = DB::one('SELECT l.*, m.course_id, m.week_no, m.title AS module_title FROM lessons l
                           JOIN modules m ON m.id = l.module_id WHERE l.id = ? AND m.course_id = ?', [(int)$id, $c['course']['id']]);
        if (!$lesson) { $this->notFound(); return; }
        if (!Policy::canOpenLesson($c['course'], $lesson)) {
            flash('info', 'Enrol for free to open this lesson. You can preview the lessons marked "Preview".');
            $this->redirect('course/' . $slug);
            return;
        }
        [$prev, $next] = $this->neighbours($c['items'], 'lesson', (int)$id);
        $done = $c['enrolled'] ? (bool)DB::val('SELECT 1 FROM lesson_progress WHERE user_id = ? AND lesson_id = ?', [$this->uid(), $id]) : false;
        if ($c['enrolled']) DB::run('UPDATE enrollments SET last_lesson_id = ? WHERE id = ?', [$id, $c['enrolled']['id']]);
        $progress = $c['enrolled'] ? Progress::summary($this->uid(), (int)$c['course']['id']) : null;
        $this->view('learn/lesson', $c + compact('lesson', 'prev', 'next', 'done', 'progress') + ['title' => $lesson['title'], 'active' => ['lesson', (int)$id]]);
    }

    public function complete(string $slug, string $id): void
    {
        $c = $this->ctx($slug, true);
        if (!$c) return;
        $lesson = DB::one('SELECT l.id FROM lessons l JOIN modules m ON m.id = l.module_id WHERE l.id = ? AND m.course_id = ?', [(int)$id, $c['course']['id']]);
        if (!$lesson) { $this->notFound(); return; }
        DB::run('INSERT IGNORE INTO lesson_progress (user_id, lesson_id) VALUES (?,?)', [$this->uid(), $lesson['id']]);
        $p = Progress::recalc($this->uid(), (int)$c['course']['id']);
        [, $next] = $this->neighbours($c['items'], 'lesson', (int)$id);
        if (\App\Core\Request::isAjax()) { $this->json(['ok' => true, 'pct' => $p['pct'], 'next' => $next ? url($next['url']) : null]); return; }
        $this->redirect($next ? $next['url'] : 'course/' . $slug);
    }

    // ---- Weekly quiz ----------------------------------------------------------

    private function loadQuiz(string $slug, string $moduleId, array $c): ?array
    {
        $quiz = DB::one('SELECT q.*, m.title AS module_title, m.week_no FROM quizzes q JOIN modules m ON m.id = q.module_id
                         WHERE m.id = ? AND m.course_id = ?', [(int)$moduleId, $c['course']['id']]);
        if (!$quiz) { $this->notFound(); return null; }
        $quiz['questions'] = DB::all('SELECT id, body, explanation FROM questions WHERE quiz_id = ? ORDER BY position, id', [$quiz['id']]);
        if (!$quiz['questions']) { $this->notFound(); return null; }
        $qIds = array_column($quiz['questions'], 'id');
        $opts = DB::all('SELECT id, question_id, body FROM options WHERE question_id IN (' . DB::in($qIds) . ') ORDER BY id', $qIds);   // NOTE: is_correct is never sent to the browser
        foreach ($quiz['questions'] as &$q) $q['options'] = array_values(array_filter($opts, fn($o) => $o['question_id'] == $q['id']));
        unset($q);
        return $quiz;
    }

    public function quiz(string $slug, string $id): void
    {
        $c = $this->ctx($slug, !$this->isManager($slug));
        if (!$c) return;
        $quiz = $this->loadQuiz($slug, $id, $c);
        if (!$quiz) return;
        [$prev, $next] = $this->neighbours($c['items'], 'quiz', (int)$id);

        $attempts = $c['enrolled'] ? DB::all('SELECT id, score, passed, created_at FROM quiz_attempts WHERE user_id = ? AND quiz_id = ? ORDER BY id DESC LIMIT 10', [$this->uid(), $quiz['id']]) : [];
        $review = null;
        if (isset($_GET['attempt'])) {
            $att = DB::one('SELECT * FROM quiz_attempts WHERE id = ? AND user_id = ? AND quiz_id = ?', [(int)$_GET['attempt'], $this->uid(), $quiz['id']]);
            if ($att) {
                $given = json_decode($att['answers'], true) ?: [];
                $correct = array_column(DB::all('SELECT question_id, id FROM options WHERE is_correct = 1 AND question_id IN (' . DB::in(array_column($quiz['questions'], 'id')) . ')', array_column($quiz['questions'], 'id')), 'id', 'question_id');
                $review = ['attempt' => $att, 'given' => $given, 'correct' => $correct];
            }
        }
        $this->view('learn/quiz', $c + compact('quiz', 'prev', 'next', 'attempts', 'review') + ['title' => $quiz['title'], 'active' => ['quiz', (int)$id]]);
    }

    private function isManager(string $slug): bool
    {
        $course = Courses::bySlug($slug);
        return $course && Policy::manageCourse($course);
    }

    public function submitQuiz(string $slug, string $id): void
    {
        $c = $this->ctx($slug, true);
        if (!$c) return;
        $quiz = $this->loadQuiz($slug, $id, $c);
        if (!$quiz) return;
        $answers = $_POST['answers'] ?? [];
        if (!is_array($answers)) $answers = [];
        $qIds = array_column($quiz['questions'], 'id');
        $correct = array_column(DB::all('SELECT question_id, id FROM options WHERE is_correct = 1 AND question_id IN (' . DB::in($qIds) . ')', $qIds), 'id', 'question_id');
        $validOpts = [];
        foreach ($quiz['questions'] as $q) foreach ($q['options'] as $o) $validOpts[$q['id']][] = (int)$o['id'];

        $given = []; $right = 0;
        foreach ($quiz['questions'] as $q) {
            $a = (int)($answers[$q['id']] ?? 0);
            if (!in_array($a, $validOpts[$q['id']] ?? [], true)) $a = 0;     // ignore forged option ids
            $given[$q['id']] = $a;
            if ($a && (int)($correct[$q['id']] ?? -1) === $a) $right++;
        }
        $score = (int)round($right * 100 / count($quiz['questions']));
        $passed = $score >= (int)$quiz['pass_mark'] ? 1 : 0;
        $aid = DB::insert('INSERT INTO quiz_attempts (user_id, quiz_id, score, passed, answers) VALUES (?,?,?,?,?)',
            [$this->uid(), $quiz['id'], $score, $passed, json_encode($given)]);
        Progress::recalc($this->uid(), (int)$c['course']['id']);
        Audit::log('quiz.attempt', 'quiz', $quiz['id'], "score=$score");
        $this->redirect("learn/$slug/quiz/$id?attempt=$aid");   // Post/Redirect/Get: refreshing never resubmits
    }

    // ---- Assignment -----------------------------------------------------------

    private function loadAssignment(string $id, array $c): ?array
    {
        $a = DB::one('SELECT a.*, m.week_no, m.title AS module_title FROM assignments a JOIN modules m ON m.id = a.module_id
                      WHERE m.id = ? AND m.course_id = ?', [(int)$id, $c['course']['id']]);
        if (!$a) $this->notFound();
        return $a;
    }

    public function assignment(string $slug, string $id): void
    {
        $c = $this->ctx($slug, !$this->isManager($slug));
        if (!$c) return;
        $asg = $this->loadAssignment($id, $c);
        if (!$asg) return;
        [$prev, $next] = $this->neighbours($c['items'], 'assignment', (int)$id);
        $sub = $c['enrolled'] ? DB::one('SELECT * FROM submissions WHERE assignment_id = ? AND user_id = ?', [$asg['id'], $this->uid()]) : null;
        $this->view('learn/assignment', $c + compact('asg', 'sub', 'prev', 'next') + ['title' => $asg['title'], 'active' => ['assignment', (int)$id]]);
    }

    public function submitAssignment(string $slug, string $id): void
    {
        $c = $this->ctx($slug, true);
        if (!$c) return;
        $asg = $this->loadAssignment($id, $c);
        if (!$asg) return;
        $existing = DB::one('SELECT status FROM submissions WHERE assignment_id = ? AND user_id = ?', [$asg['id'], $this->uid()]);
        if ($existing && $existing['status'] === 'graded') {
            flash('info', 'This work has already been graded, so it can no longer be changed.');
            $this->redirect("learn/$slug/assignment/$id");
            return;
        }
        $data = $this->validate(['body' => 'required|min:30|max:5000'], "learn/$slug/assignment/$id", ['body' => 'your answer']);
        if ($data === null) return;
        DB::run('INSERT INTO submissions (assignment_id, user_id, body) VALUES (?,?,?)
                 ON DUPLICATE KEY UPDATE body = VALUES(body), submitted_at = NOW()', [$asg['id'], $this->uid(), $data['body']]);
        // Tell the facilitator there is work to review.
        \App\Core\Notify::to((int)$c['course']['owner_id'], $this->user()['name'] . ' submitted "' . $asg['title'] . '".', 'manage/submissions');
        Progress::recalc($this->uid(), (int)$c['course']['id']);
        flash('success', 'Submitted. Your facilitator will send feedback here.');
        $this->redirect("learn/$slug/assignment/$id");
    }

    // ---- Discussion -----------------------------------------------------------

    public function discussion(string $slug): void
    {
        $c = $this->ctx($slug, !$this->isManager($slug));
        if (!$c) return;
        $posts = DB::all('SELECT d.*, u.name, u.role FROM discussion_posts d JOIN users u ON u.id = d.user_id
                          WHERE d.course_id = ? ORDER BY d.id DESC LIMIT 100', [$c['course']['id']]);
        $top = []; $replies = [];
        foreach ($posts as $p) { if ($p['parent_id']) $replies[$p['parent_id']][] = $p; else $top[] = $p; }
        usort($top, fn($a, $b) => [(int)$b['pinned'], (int)$b['id']] <=> [(int)$a['pinned'], (int)$a['id']]);   // pinned first, then newest
        foreach ($replies as &$r) $r = array_reverse($r);
        unset($r);
        $this->view('learn/discussion', $c + compact('top', 'replies') + ['title' => 'Discussion – ' . $c['course']['title'], 'active' => ['discussion', 0]]);
    }

    public function post(string $slug): void
    {
        $c = $this->ctx($slug, !$this->isManager($slug));
        if (!$c) return;
        $key = 'post:' . $this->uid();
        if (Throttle::tooMany($key, 10, 1)) { flash('error', 'You are posting very quickly. Please wait a minute.'); $this->redirect("learn/$slug/discussion"); return; }
        $data = $this->validate(['body' => 'required|min:2|max:2000', 'parent_id' => 'int'], "learn/$slug/discussion", ['body' => 'your message']);
        if ($data === null) return;
        $parent = null;
        if ($data['parent_id']) {
            $parent = DB::one('SELECT id, parent_id FROM discussion_posts WHERE id = ? AND course_id = ?', [(int)$data['parent_id'], $c['course']['id']]);
            if (!$parent) { $this->notFound(); return; }
        }
        Throttle::hit($key);
        DB::run('INSERT INTO discussion_posts (course_id, user_id, parent_id, body) VALUES (?,?,?,?)',
            [$c['course']['id'], $this->uid(), $parent ? ($parent['parent_id'] ?: $parent['id']) : null, $data['body']]);
        flash('success', 'Posted.');
        $this->redirect("learn/$slug/discussion");
    }

    // ---- discussion moderation ---------------------------------------------------

    private function loadPost(string $slug, string $id, bool $staffOnly): ?array
    {
        $c = $this->ctx($slug, !$this->isManager($slug));
        if (!$c) return null;
        $p = DB::one('SELECT * FROM discussion_posts WHERE id = ? AND course_id = ?', [(int)$id, $c['course']['id']]);
        if (!$p) { $this->notFound(); return null; }
        if ($staffOnly && !$c['manage']) { $this->forbidden('Only the course facilitator or an admin can do this.'); return null; }
        return $p + ['_course' => $c['course'], '_manage' => $c['manage']];
    }

    /** Staff can delete any post; learners can delete their own. A deleted post takes its replies with it. */
    public function deletePost(string $slug, string $id): void
    {
        $p = $this->loadPost($slug, $id, false);
        if (!$p) return;
        if (!$p['_manage'] && (int)$p['user_id'] !== $this->uid()) { $this->forbidden('You can only delete your own posts.'); return; }
        DB::run('DELETE FROM discussion_posts WHERE id = ? OR parent_id = ?', [$p['id'], $p['id']]);
        if ($p['_manage'] && (int)$p['user_id'] !== $this->uid()) Audit::log('discussion.removed', 'course', (int)$p['course_id'], "post {$p['id']} by user {$p['user_id']}");
        flash('success', 'Post removed.');
        $this->redirect("learn/$slug/discussion");
    }

    public function pinPost(string $slug, string $id): void
    {
        $p = $this->loadPost($slug, $id, true);
        if (!$p) return;
        if ($p['parent_id']) { $this->notFound(); return; }
        DB::run('UPDATE discussion_posts SET pinned = ? WHERE id = ?', [$p['pinned'] ? 0 : 1, $p['id']]);
        flash('success', $p['pinned'] ? 'Post unpinned.' : 'Post pinned to the top.');
        $this->redirect("learn/$slug/discussion");
    }

    /** Any learner can flag a post for the facilitator; the course owner is notified. */
    public function reportPost(string $slug, string $id): void
    {
        $p = $this->loadPost($slug, $id, false);
        if (!$p) return;
        if ((int)$p['user_id'] === $this->uid()) { flash('info', 'That is your own post.'); $this->redirect("learn/$slug/discussion"); return; }
        $key = 'report:' . $this->uid();
        if (Throttle::tooMany($key, 10, 10)) { flash('error', 'Too many reports in a short time. Please wait a few minutes.'); $this->redirect("learn/$slug/discussion"); return; }
        Throttle::hit($key);
        if (!$p['reported']) {
            DB::run('UPDATE discussion_posts SET reported = 1 WHERE id = ?', [$p['id']]);
            Notify::to((int)$p['_course']['owner_id'], 'A discussion post was reported in "' . $p['_course']['title'] . '". Please review it.', "learn/$slug/discussion");
        }
        flash('success', 'Thank you. The facilitator has been told and will take a look.');
        $this->redirect("learn/$slug/discussion");
    }

    public function dismissReport(string $slug, string $id): void
    {
        $p = $this->loadPost($slug, $id, true);
        if (!$p) return;
        DB::run('UPDATE discussion_posts SET reported = 0 WHERE id = ?', [$p['id']]);
        flash('success', 'Report cleared. The post stays.');
        $this->redirect("learn/$slug/discussion");
    }
}
