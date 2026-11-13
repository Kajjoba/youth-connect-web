<?php
namespace App\Controllers;

use App\Core\{Controller, DB, Policy, Audit, Notify, Upload};
use App\Services\Progress;

/** Facilitator (and admin) tools: build courses week by week, quizzes, assignments; grade work. */
class ManageController extends Controller
{
    // ---- loaders: every one verifies the CURRENT user manages the owning course ----

    private function course(string $id): ?array
    {
        $c = DB::one('SELECT * FROM courses WHERE id = ?', [(int)$id]);
        if (!$c || !Policy::manageCourse($c)) { $this->notFound(); return null; }
        return $c;
    }

    private function module(string $id): ?array
    {
        $m = DB::one('SELECT m.*, c.owner_id, c.slug AS course_slug, c.title AS course_title, c.pass_mark FROM modules m JOIN courses c ON c.id = m.course_id WHERE m.id = ?', [(int)$id]);
        if (!$m || !Policy::manageCourse(['owner_id' => $m['owner_id']])) { $this->notFound(); return null; }
        return $m;
    }

    private function lesson(string $id): ?array
    {
        $l = DB::one('SELECT l.*, m.course_id, m.week_no, c.owner_id, c.slug AS course_slug FROM lessons l JOIN modules m ON m.id = l.module_id
                      JOIN courses c ON c.id = m.course_id WHERE l.id = ?', [(int)$id]);
        if (!$l || !Policy::manageCourse(['owner_id' => $l['owner_id']])) { $this->notFound(); return null; }
        return $l;
    }

    // ---- courses --------------------------------------------------------------

    public function courses(): void
    {
        $u = $this->user();
        $where = $u['role'] === 'admin' ? '' : 'WHERE c.owner_id = ' . (int)$u['id'];
        $rows = DB::all("SELECT c.id, c.title, c.slug, c.status, c.color, cat.name AS category, u.name AS owner,
                (SELECT COUNT(*) FROM modules m WHERE m.course_id = c.id) AS weeks,
                (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id AND e.status <> 'dropped') AS learners
            FROM courses c JOIN categories cat ON cat.id = c.category_id JOIN users u ON u.id = c.owner_id $where ORDER BY c.updated_at DESC");
        $pending = (int)DB::val('SELECT COUNT(*) FROM submissions s JOIN assignments a ON a.id = s.assignment_id JOIN modules m ON m.id = a.module_id
                                 JOIN courses c ON c.id = m.course_id WHERE s.status = \'submitted\' AND (? = \'admin\' OR c.owner_id = ?)', [$u['role'], $u['id']]);
        $this->view('manage/courses', compact('rows', 'pending') + ['title' => 'Manage courses']);
    }

    public function newCourse(): void
    {
        $this->view('manage/course_form', ['course' => null, 'categories' => DB::all('SELECT * FROM categories ORDER BY name'), 'title' => 'New course']);
    }

    private function courseRules(): array
    {
        return [
            'title' => 'required|min:5|max:160', 'summary' => 'required|min:20|max:300', 'description' => 'required|min:40|max:5000',
            'what_you_learn' => 'required|min:10|max:2000', 'skills' => 'max:300',
            'level' => 'required|in:Beginner,Intermediate,Advanced', 'hours_per_week' => 'required|between:1,40',
            'language' => 'required|max:40', 'category_id' => 'required|int', 'pass_mark' => 'required|between:30,100',
        ];
    }

    public function createCourse(): void
    {
        $d = $this->validate($this->courseRules(), 'manage/courses/new', ['what_you_learn' => 'what learners will learn', 'category_id' => 'a category']);
        if ($d === null) return;
        if (!DB::val('SELECT id FROM categories WHERE id = ?', [$d['category_id']])) { $this->notFound(); return; }
        $slug = slugify($d['title']);
        if (DB::val('SELECT id FROM courses WHERE slug = ?', [$slug])) $slug .= '-' . substr(bin2hex(random_bytes(3)), 0, 5);
        $color = preg_match('/^#[0-9a-fA-F]{6}$/', $_POST['color'] ?? '') ? $_POST['color'] : '#0b5cd5';
        $id = DB::insert('INSERT INTO courses (category_id, owner_id, title, slug, summary, description, what_you_learn, skills, level, hours_per_week, language, color, pass_mark, has_certificate)
                          VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [$d['category_id'], $this->uid(), $d['title'], $slug, $d['summary'], $d['description'], $d['what_you_learn'], $d['skills'], $d['level'],
             $d['hours_per_week'], $d['language'], $color, $d['pass_mark'], isset($_POST['has_certificate']) ? 1 : 0]);
        Audit::log('course.created', 'course', $id);
        flash('success', 'Course created. Now add its weeks, lessons and quizzes.');
        $this->redirect("manage/courses/$id");
    }

    public function editCourse(string $id): void
    {
        $course = $this->course($id);
        if (!$course) return;
        $modules = DB::all('SELECT m.*, (SELECT COUNT(*) FROM lessons l WHERE l.module_id = m.id) AS lessons,
                (SELECT COUNT(*) FROM questions q JOIN quizzes z ON z.id = q.quiz_id WHERE z.module_id = m.id) AS questions,
                (SELECT COUNT(*) FROM assignments a WHERE a.module_id = m.id) AS has_assignment
            FROM modules m WHERE m.course_id = ? ORDER BY week_no', [$course['id']]);
        $discussion = DB::one('SELECT COUNT(*) AS posts, COALESCE(SUM(reported),0) AS reported FROM discussion_posts WHERE course_id = ?', [$course['id']]);
        $this->view('manage/course_edit', ['course' => $course, 'modules' => $modules, 'discussion' => $discussion, 'categories' => DB::all('SELECT * FROM categories ORDER BY name'), 'title' => 'Edit ' . $course['title']]);
    }

    public function updateCourse(string $id): void
    {
        $course = $this->course($id);
        if (!$course) return;
        $d = $this->validate($this->courseRules(), "manage/courses/{$course['id']}", ['what_you_learn' => 'what learners will learn', 'category_id' => 'a category']);
        if ($d === null) return;
        $color = preg_match('/^#[0-9a-fA-F]{6}$/', $_POST['color'] ?? '') ? $_POST['color'] : $course['color'];
        DB::run('UPDATE courses SET category_id=?, title=?, summary=?, description=?, what_you_learn=?, skills=?, level=?, hours_per_week=?, language=?, color=?, pass_mark=?, has_certificate=? WHERE id=?',
            [$d['category_id'], $d['title'], $d['summary'], $d['description'], $d['what_you_learn'], $d['skills'], $d['level'], $d['hours_per_week'], $d['language'], $color, $d['pass_mark'], isset($_POST['has_certificate']) ? 1 : 0, $course['id']]);
        flash('success', 'Course details saved.');
        $this->redirect("manage/courses/{$course['id']}");
    }

    public function setStatus(string $id): void
    {
        $course = $this->course($id);
        if (!$course) return;
        $to = $_POST['status'] ?? '';
        if (!in_array($to, ['draft', 'published', 'archived'], true)) { $this->notFound(); return; }
        if ($to === 'published') {
            $ok = DB::val('SELECT COUNT(*) FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = ?', [$course['id']]);
            if (!$ok) { flash('error', 'Add at least one week with a lesson before publishing.'); $this->redirect("manage/courses/{$course['id']}"); return; }
        }
        DB::run('UPDATE courses SET status = ? WHERE id = ?', [$to, $course['id']]);
        Audit::log('course.status', 'course', $course['id'], $to);
        flash('success', $to === 'published' ? 'Published. Learners can now find and enrol in this course.' : 'Status changed to ' . $to . '.');
        $this->redirect("manage/courses/{$course['id']}");
    }

    public function addWeek(string $id): void
    {
        $course = $this->course($id);
        if (!$course) return;
        $d = $this->validate(['title' => 'required|min:3|max:160'], "manage/courses/{$course['id']}");
        if ($d === null) return;
        $next = (int)DB::val('SELECT COALESCE(MAX(week_no),0)+1 FROM modules WHERE course_id = ?', [$course['id']]);
        $mid = DB::insert('INSERT INTO modules (course_id, week_no, title) VALUES (?,?,?)', [$course['id'], $next, $d['title']]);
        $this->redirect("manage/modules/$mid");
    }

    public function learners(string $id): void
    {
        $course = $this->course($id);
        if (!$course) return;
        $rows = DB::all('SELECT u.id, u.name, u.email, p.country, e.status, e.enrolled_at FROM enrollments e JOIN users u ON u.id = e.user_id
                         LEFT JOIN profiles p ON p.user_id = u.id WHERE e.course_id = ? AND e.status <> \'dropped\' ORDER BY e.enrolled_at DESC LIMIT 500', [$course['id']]);
        foreach ($rows as &$r) $r['progress'] = Progress::summary((int)$r['id'], (int)$course['id']);
        unset($r);
        $this->view('manage/learners', compact('course', 'rows') + ['title' => 'Learners – ' . $course['title']]);
    }

    // ---- weeks (modules) -----------------------------------------------------

    public function editModule(string $id): void
    {
        $m = $this->module($id);
        if (!$m) return;
        $lessons = DB::all('SELECT * FROM lessons WHERE module_id = ? ORDER BY position, id', [$m['id']]);
        $quiz = DB::one('SELECT * FROM quizzes WHERE module_id = ?', [$m['id']]);
        $questions = [];
        if ($quiz) {
            $questions = DB::all('SELECT * FROM questions WHERE quiz_id = ? ORDER BY position, id', [$quiz['id']]);
            foreach ($questions as &$q) $q['options'] = DB::all('SELECT * FROM options WHERE question_id = ? ORDER BY id', [$q['id']]);
            unset($q);
        }
        $assignment = DB::one('SELECT * FROM assignments WHERE module_id = ?', [$m['id']]);
        $this->view('manage/module_edit', compact('m', 'lessons', 'quiz', 'questions', 'assignment') + ['title' => "Week {$m['week_no']}"]);
    }

    public function updateModule(string $id): void
    {
        $m = $this->module($id);
        if (!$m) return;
        $d = $this->validate(['title' => 'required|min:3|max:160', 'description' => 'max:1000', 'hours_estimate' => 'required|between:1,40'], "manage/modules/{$m['id']}");
        if ($d === null) return;
        DB::run('UPDATE modules SET title=?, description=?, hours_estimate=? WHERE id=?', [$d['title'], $d['description'], $d['hours_estimate'], $m['id']]);
        flash('success', 'Week saved.');
        $this->redirect("manage/modules/{$m['id']}");
    }

    public function deleteModule(string $id): void
    {
        $m = $this->module($id);
        if (!$m) return;
        foreach (DB::all('SELECT audio_path FROM lessons WHERE module_id = ? AND audio_path IS NOT NULL', [$m['id']]) as $a) Upload::delete($a['audio_path']);
        DB::run('DELETE FROM modules WHERE id = ?', [$m['id']]);
        flash('success', 'Week removed.');
        $this->redirect("manage/courses/{$m['course_id']}");
    }

    // ---- lessons -------------------------------------------------------------

    private function lessonRules(): array
    {
        return ['type' => 'required|in:reading,video,audio,discussion', 'title' => 'required|min:3|max:160', 'content' => 'max:20000',
                'video_url' => 'url|max:300', 'minutes' => 'required|between:1,600'];
    }

    private function validVideo(?string $u): bool
    {
        if ($u === null || $u === '') return true;
        $host = strtolower((string)parse_url($u, PHP_URL_HOST));
        return in_array($host, ['www.youtube.com', 'youtube.com', 'youtu.be', 'www.youtube-nocookie.com'], true);
    }

    public function addLesson(string $id): void
    {
        $m = $this->module($id);
        if (!$m) return;
        $d = $this->validate($this->lessonRules(), "manage/modules/{$m['id']}");
        if ($d === null) return;
        if (!$this->validVideo($d['video_url'])) { flash('error', 'Videos must be YouTube links.'); $this->redirect("manage/modules/{$m['id']}"); return; }
        $audio = null;
        if ($d['type'] === 'audio') {
            $audio = $this->audioUpload(true);
            if (is_string($audio)) { flash('error', $audio); $this->redirect("manage/modules/{$m['id']}"); return; }
        }
        $pos = (int)DB::val('SELECT COALESCE(MAX(position),0)+1 FROM lessons WHERE module_id = ?', [$m['id']]);
        DB::run('INSERT INTO lessons (module_id, position, type, title, content, video_url, audio_path, audio_name, minutes, is_preview) VALUES (?,?,?,?,?,?,?,?,?,?)',
            [$m['id'], $pos, $d['type'], $d['title'], $d['content'], $d['video_url'], $audio['path'] ?? null, $audio['name'] ?? null, $d['minutes'], isset($_POST['is_preview']) ? 1 : 0]);
        flash('success', 'Lesson added.');
        $this->redirect("manage/modules/{$m['id']}");
    }

    /** Audio file from the lesson form: null if none was chosen (and none required), error string, or the stored-file info. */
    private function audioUpload(bool $required)
    {
        $f = $_FILES['audio'] ?? null;
        if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return $required ? 'Choose an audio file (MP3, M4A, OGG or WAV) for an audio lesson.' : null;
        if (in_array($f['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) return 'That audio file is larger than this server allows (' . upload_limit_mb() . ' MB). Compress it or use a shorter recording.';
        return Upload::store($f, 'audio');
    }

    /** Move a week up or down: renumbers all weeks 1..n so the order is always clean. */
    public function moveModule(string $id): void
    {
        $m = $this->module($id);
        if (!$m) return;
        $ids = array_map('intval', array_column(DB::all('SELECT id FROM modules WHERE course_id = ? ORDER BY week_no, id', [$m['course_id']]), 'id'));
        $i = array_search((int)$m['id'], $ids, true);
        $j = $i === false ? false : $i + (($_POST['dir'] ?? '') === 'up' ? -1 : 1);
        if ($j !== false && isset($ids[$j])) {
            [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
            DB::tx(function () use ($ids) {
                foreach ($ids as $k => $mid) DB::run('UPDATE modules SET week_no = ? WHERE id = ?', [100 + $k + 1, $mid]);   // two passes: (course, week) is unique
                foreach ($ids as $k => $mid) DB::run('UPDATE modules SET week_no = ? WHERE id = ?', [$k + 1, $mid]);
            });
        }
        $this->redirect("manage/courses/{$m['course_id']}");
    }

    /** Move a lesson up or down within its week. */
    public function moveLesson(string $id): void
    {
        $l = $this->lesson($id);
        if (!$l) return;
        $ids = array_map('intval', array_column(DB::all('SELECT id FROM lessons WHERE module_id = ? ORDER BY position, id', [$l['module_id']]), 'id'));
        $i = array_search((int)$l['id'], $ids, true);
        $j = $i === false ? false : $i + (($_POST['dir'] ?? '') === 'up' ? -1 : 1);
        if ($j !== false && isset($ids[$j])) {
            [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
            DB::tx(function () use ($ids) { foreach ($ids as $k => $lid) DB::run('UPDATE lessons SET position = ? WHERE id = ?', [$k + 1, $lid]); });
        }
        $this->redirect("manage/modules/{$l['module_id']}");
    }
    public function editLesson(string $id): void
    {
        $l = $this->lesson($id);
        if ($l) $this->view('manage/lesson_edit', ['l' => $l, 'title' => 'Edit lesson']);
    }

    public function updateLesson(string $id): void
    {
        $l = $this->lesson($id);
        if (!$l) return;
        $d = $this->validate($this->lessonRules() + ['position' => 'required|between:1,200'], "manage/lessons/{$l['id']}");
        if ($d === null) return;
        if (!$this->validVideo($d['video_url'])) { flash('error', 'Videos must be YouTube links.'); $this->redirect("manage/lessons/{$l['id']}"); return; }
        $path = $l['audio_path']; $name = $l['audio_name'];
        if ($d['type'] === 'audio') {
            $new = $this->audioUpload(!$l['audio_path']);
            if (is_string($new)) { flash('error', $new); $this->redirect("manage/lessons/{$l['id']}"); return; }
            if ($new) { Upload::delete($l['audio_path']); $path = $new['path']; $name = $new['name']; }
        } elseif ($l['audio_path']) {   // no longer an audio lesson: remove the stored file
            Upload::delete($l['audio_path']); $path = null; $name = null;
        }
        DB::run('UPDATE lessons SET type=?, title=?, content=?, video_url=?, audio_path=?, audio_name=?, minutes=?, position=?, is_preview=? WHERE id=?',
            [$d['type'], $d['title'], $d['content'], $d['video_url'], $path, $name, $d['minutes'], $d['position'], isset($_POST['is_preview']) ? 1 : 0, $l['id']]);
        flash('success', 'Lesson saved.');
        $this->redirect("manage/modules/{$l['module_id']}");
    }

    public function deleteLesson(string $id): void
    {
        $l = $this->lesson($id);
        if (!$l) return;
        Upload::delete($l['audio_path']);
        DB::run('DELETE FROM lessons WHERE id = ?', [$l['id']]);
        flash('success', 'Lesson removed.');
        $this->redirect("manage/modules/{$l['module_id']}");
    }

    // ---- weekly quiz ---------------------------------------------------------

    public function addQuestion(string $id): void
    {
        $m = $this->module($id);
        if (!$m) return;
        $d = $this->validate([
            'body' => 'required|min:5|max:500', 'explanation' => 'max:500',
            'opt1' => 'required|max:300', 'opt2' => 'required|max:300', 'opt3' => 'max:300', 'opt4' => 'max:300',
            'correct' => 'required|between:1,4',
        ], "manage/modules/{$m['id']}", ['body' => 'the question', 'opt1' => 'option 1', 'opt2' => 'option 2']);
        if ($d === null) return;
        $opts = array_values(array_filter([1 => $d['opt1'], 2 => $d['opt2'], 3 => $d['opt3'], 4 => $d['opt4']], fn($x) => $x !== null && $x !== ''));
        $correctIdx = (int)$d['correct'];
        if ($correctIdx > count($opts) || [1 => $d['opt1'], 2 => $d['opt2'], 3 => $d['opt3'], 4 => $d['opt4']][$correctIdx] === null) {
            flash('error', 'The correct answer must be one of the options you filled in.'); $this->redirect("manage/modules/{$m['id']}"); return;
        }
        $all = [1 => $d['opt1'], 2 => $d['opt2'], 3 => $d['opt3'], 4 => $d['opt4']];
        DB::tx(function () use ($m, $d, $all, $correctIdx) {
            $qz = DB::one('SELECT id FROM quizzes WHERE module_id = ?', [$m['id']]);
            $qid = $qz ? (int)$qz['id'] : DB::insert('INSERT INTO quizzes (module_id, title, pass_mark) VALUES (?,?,?)', [$m['id'], "Week {$m['week_no']} Quiz", $m['pass_mark']]);
            $pos = (int)DB::val('SELECT COALESCE(MAX(position),0)+1 FROM questions WHERE quiz_id = ?', [$qid]);
            $q = DB::insert('INSERT INTO questions (quiz_id, position, body, explanation) VALUES (?,?,?,?)', [$qid, $pos, $d['body'], $d['explanation']]);
            foreach ($all as $i => $text) {
                if ($text === null || $text === '') continue;
                DB::run('INSERT INTO options (question_id, body, is_correct) VALUES (?,?,?)', [$q, $text, $i === $correctIdx ? 1 : 0]);
            }
        });
        flash('success', 'Question added to the Week ' . $m['week_no'] . ' quiz.');
        $this->redirect("manage/modules/{$m['id']}#quiz");
    }

    public function deleteQuestion(string $id): void
    {
        $q = DB::one('SELECT q.id, z.module_id, c.owner_id FROM questions q JOIN quizzes z ON z.id = q.quiz_id JOIN modules m ON m.id = z.module_id
                      JOIN courses c ON c.id = m.course_id WHERE q.id = ?', [(int)$id]);
        if (!$q || !Policy::manageCourse(['owner_id' => $q['owner_id']])) { $this->notFound(); return; }
        DB::run('DELETE FROM questions WHERE id = ?', [$q['id']]);
        flash('success', 'Question removed.');
        $this->redirect("manage/modules/{$q['module_id']}#quiz");
    }

    // ---- assignment ----------------------------------------------------------

    public function saveAssignment(string $id): void
    {
        $m = $this->module($id);
        if (!$m) return;
        if (isset($_POST['remove'])) {
            DB::run('DELETE FROM assignments WHERE module_id = ?', [$m['id']]);
            flash('success', 'Assignment removed.');
            $this->redirect("manage/modules/{$m['id']}");
            return;
        }
        $d = $this->validate(['title' => 'required|min:3|max:160', 'instructions' => 'required|min:20|max:4000'], "manage/modules/{$m['id']}");
        if ($d === null) return;
        DB::run('INSERT INTO assignments (module_id, title, instructions) VALUES (?,?,?) ON DUPLICATE KEY UPDATE title = VALUES(title), instructions = VALUES(instructions)',
            [$m['id'], $d['title'], $d['instructions']]);
        flash('success', 'Assignment saved.');
        $this->redirect("manage/modules/{$m['id']}#assignment");
    }

    // ---- grading -------------------------------------------------------------

    public function submissions(): void
    {
        $u = $this->user();
        $rows = DB::all('SELECT s.*, a.title AS assignment, a.max_score, u.name AS learner, c.title AS course, c.slug
                         FROM submissions s JOIN assignments a ON a.id = s.assignment_id JOIN modules m ON m.id = a.module_id
                         JOIN courses c ON c.id = m.course_id JOIN users u ON u.id = s.user_id
                         WHERE (? = \'admin\' OR c.owner_id = ?) ORDER BY s.status = \'submitted\' DESC, s.submitted_at DESC LIMIT 100', [$u['role'], $u['id']]);
        $this->view('manage/submissions', ['rows' => $rows, 'title' => 'Submissions to review']);
    }

    public function grade(string $id): void
    {
        $s = DB::one('SELECT s.*, a.max_score, a.title, c.owner_id, c.slug FROM submissions s JOIN assignments a ON a.id = s.assignment_id
                      JOIN modules m ON m.id = a.module_id JOIN courses c ON c.id = m.course_id WHERE s.id = ?', [(int)$id]);
        if (!$s || !Policy::manageCourse(['owner_id' => $s['owner_id']])) { $this->notFound(); return; }
        $d = $this->validate(['score' => 'required|between:0,' . $s['max_score'], 'feedback' => 'required|min:5|max:3000'], 'manage/submissions', ['score' => 'a score']);
        if ($d === null) return;
        DB::run('UPDATE submissions SET status=\'graded\', score=?, feedback=?, graded_by=?, graded_at=NOW() WHERE id=?', [$d['score'], $d['feedback'], $this->uid(), $s['id']]);
        Notify::to((int)$s['user_id'], 'Your work "' . $s['title'] . '" has been graded with feedback.', 'learn/' . $s['slug'] . '/assignment/' . DB::val('SELECT module_id FROM assignments WHERE id = ?', [$s['assignment_id']]));
        Audit::log('submission.graded', 'submission', $s['id']);
        flash('success', 'Feedback sent to the learner.');
        $this->redirect('manage/submissions');
    }
}
