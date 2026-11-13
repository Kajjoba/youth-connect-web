<?php
namespace App\Controllers;

use App\Core\{Controller, DB, Auth};
use App\Services\Courses;

class HomeController extends Controller
{
    public function index(): void
    {
        $categories = DB::all('SELECT cat.*, (SELECT COUNT(*) FROM courses c WHERE c.category_id = cat.id AND c.status = \'published\') AS n
                               FROM categories cat ORDER BY cat.name');
        $popular = Courses::cards('', [], 'enrolled DESC', 8);
        $newest = Courses::cards('', [], 'c.created_at DESC', 8);
        $agri = Courses::cards('cat.slug = ?', ['agriculture-food'], 'enrolled DESC', 8);
        $stats = [
            'learners' => (int)DB::val('SELECT COUNT(*) FROM users WHERE role = \'participant\''),
            'courses'  => (int)DB::val('SELECT COUNT(*) FROM courses WHERE status = \'published\''),
            'countries' => (int)DB::val('SELECT COUNT(DISTINCT country) FROM profiles WHERE country IS NOT NULL'),
            'projects' => (int)DB::val('SELECT COUNT(*) FROM projects WHERE status <> \'draft\''),
        ];
        $continue = [];
        if ($this->user()) {
            $continue = DB::all('SELECT c.title, c.slug, c.color FROM enrollments e JOIN courses c ON c.id = e.course_id
                                 WHERE e.user_id = ? AND e.status = \'active\' ORDER BY e.enrolled_at DESC LIMIT 3', [$this->uid()]);
        }
        $this->view('home/index', compact('categories', 'popular', 'newest', 'agri', 'stats', 'continue') + ['title' => 'YouthConnect – Learn, collaborate and be recognised']);
    }

    /** Tiny JSON search used for type-ahead (about 300 bytes) instead of loading a full results page per keystroke. */
    public function search(): void
    {
        $q = trim((string)($_GET['q'] ?? ''));
        if (mb_strlen($q) < 2) { $this->json(['results' => []]); return; }
        $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], mb_substr($q, 0, 60)) . '%';
        $rows = DB::all('SELECT title, slug FROM courses WHERE status = \'published\' AND (title LIKE ? OR skills LIKE ?) ORDER BY title LIMIT 6', [$like, $like]);
        $this->json(['results' => array_map(fn($r) => ['t' => $r['title'], 'u' => url('course/' . $r['slug'])], $rows)]);
    }

    public function lite(): void
    {
        $on = ($_POST['lite'] ?? '0') === '1';
        setcookie('ycp_lite', $on ? '1' : '0', ['expires' => time() + 86400 * 365, 'path' => '/', 'samesite' => 'Lax', 'httponly' => true]);
        if ($this->user()) DB::run('UPDATE profiles SET lite_mode = ? WHERE user_id = ?', [$on ? 1 : 0, $this->uid()]);
        $back = $_SERVER['HTTP_REFERER'] ?? '';
        $host = parse_url($back, PHP_URL_HOST);
        $this->redirect($host && $host === ($_SERVER['HTTP_HOST'] ?? '') ? $back : '');
    }

    public function offline(): void
    {
        $this->view('home/offline', ['title' => 'You are offline']);
    }
}
