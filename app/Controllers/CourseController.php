<?php
namespace App\Controllers;

use App\Core\{Controller, DB, Policy, Audit, Throttle};
use App\Services\{Courses, Progress};

class CourseController extends Controller
{
    private const SORTS = [
        'popular' => 'enrolled DESC',
        'newest'  => 'c.created_at DESC',
        'rating'  => 'rating DESC',
        'title'   => 'c.title ASC',
    ];

    public function index(): void
    {
        $q = trim((string)($_GET['q'] ?? ''));
        $cat = (string)($_GET['category'] ?? '');
        $level = (string)($_GET['level'] ?? '');
        $sort = isset(self::SORTS[$_GET['sort'] ?? '']) ? $_GET['sort'] : 'popular';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per = 12;

        $where = []; $params = [];
        if ($q !== '') {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], mb_substr($q, 0, 80)) . '%';
            $where[] = '(c.title LIKE ? OR c.summary LIKE ? OR c.skills LIKE ?)';
            array_push($params, $like, $like, $like);
        }
        if ($cat !== '') { $where[] = 'cat.slug = ?'; $params[] = $cat; }
        if (in_array($level, ['Beginner', 'Intermediate', 'Advanced'], true)) { $where[] = 'c.level = ?'; $params[] = $level; }
        $w = implode(' AND ', $where);

        $total = Courses::countPublished($w, $params);
        $courses = Courses::cards($w, $params, self::SORTS[$sort], $per, ($page - 1) * $per);
        $categories = DB::all('SELECT * FROM categories ORDER BY name');
        $this->view('courses/index', compact('courses', 'categories', 'q', 'cat', 'level', 'sort', 'page', 'total', 'per') + ['title' => 'Explore activities']);
    }

    public function show(string $slug): void
    {
        $course = Courses::bySlug($slug);
        if (!$course || ($course['status'] !== 'published' && !Policy::manageCourse($course))) { $this->notFound(); return; }

        $syllabus = Courses::syllabus((int)$course['id']);
        $stats = Progress::courseStats((int)$course['id']);
        $weeks = count($syllabus);
        $hours = array_sum(array_column($syllabus, 'hours_estimate'));
        $enrollment = $this->user() ? Policy::enrollment((int)$course['id']) : null;
        $progress = $enrollment ? Progress::summary($this->uid(), (int)$course['id']) : null;
        $dist = array_fill(1, 5, 0);
        foreach (DB::all('SELECT rating, COUNT(*) n FROM reviews WHERE course_id = ? GROUP BY rating', [$course['id']]) as $d) $dist[(int)$d['rating']] = (int)$d['n'];
        $reviews = DB::all('SELECT r.rating, r.comment, r.created_at, u.name FROM reviews r JOIN users u ON u.id = r.user_id
                            WHERE r.course_id = ? ORDER BY r.created_at DESC LIMIT 5', [$course['id']]);
        $myReview = $this->user() ? DB::one('SELECT * FROM reviews WHERE course_id = ? AND user_id = ?', [$course['id'], $this->uid()]) : null;
        $related = \App\Services\Courses::cards('c.category_id = ? AND c.id <> ?', [$course['category_id'], $course['id']], 'enrolled DESC', 3);
        $this->view('courses/show', compact('course', 'syllabus', 'stats', 'weeks', 'hours', 'enrollment', 'progress', 'dist', 'reviews', 'myReview', 'related')
            + ['title' => $course['title']]);
    }

    public function enroll(string $slug): void
    {
        $course = Courses::bySlug($slug);
        if (!$course || $course['status'] !== 'published') { $this->notFound(); return; }
        if ($this->user()['role'] !== 'participant') {
            flash('info', 'Enrolment is for participant accounts. Staff can preview courses from the management area.');
            $this->redirect('course/' . $slug);
            return;
        }
        // One enrolment per user and course (unique key) makes this safe to repeat, e.g. a double tap on a slow link.
        $new = DB::run('INSERT INTO enrollments (user_id, course_id) VALUES (?,?)
                        ON DUPLICATE KEY UPDATE status = IF(status = \'dropped\', \'active\', status)', [$this->uid(), $course['id']]);
        if ($new) Audit::log('course.enrolled', 'course', $course['id']);
        flash('success', 'You are enrolled in "' . $course['title'] . '". Start whenever you are ready.');
        $this->redirect('learn/' . $slug);
    }

    public function drop(string $slug): void
    {
        $course = Courses::bySlug($slug);
        if (!$course) { $this->notFound(); return; }
        DB::run('UPDATE enrollments SET status = \'dropped\' WHERE user_id = ? AND course_id = ? AND status = \'active\'', [$this->uid(), $course['id']]);
        Audit::log('course.dropped', 'course', $course['id']);
        flash('info', 'You have left this course. Your progress is kept if you rejoin.');
        $this->redirect('course/' . $slug);
    }

    public function review(string $slug): void
    {
        $course = Courses::bySlug($slug);
        if (!$course || !Policy::enrollment((int)$course['id'])) { $this->notFound(); return; }
        $data = $this->validate(['rating' => 'required|between:1,5', 'comment' => 'max:600'], 'course/' . $slug);
        if ($data === null) return;
        DB::run('INSERT INTO reviews (course_id, user_id, rating, comment) VALUES (?,?,?,?)
                 ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)', [$course['id'], $this->uid(), (int)$data['rating'], $data['comment']]);
        flash('success', 'Thank you for your review.');
        $this->redirect('course/' . $slug . '#reviews');
    }
}
