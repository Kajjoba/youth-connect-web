<?php
namespace App\Controllers;

use App\Core\{Controller, DB, Policy, Notify};
use App\Services\{Courses, Progress};

/**
 * Tiny JSON endpoints for slow connections. The pulse is ~80 bytes and supports ETag, so polling an
 * unchanged project costs a header-only 304 response, instead of re-downloading a whole page.
 */
class ApiController extends Controller
{
    private function conditional(array $data): void
    {
        $json = json_encode($data);
        $etag = '"' . md5($json) . '"';
        header('ETag: ' . $etag);
        header('Cache-Control: private, no-cache');
        if (trim($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) { http_response_code(304); return; }
        header('Content-Type: application/json; charset=utf-8');
        echo $json;
    }

    public function pulse(string $pid): void
    {
        $p = Policy::project($pid);
        if (!$p) { $this->json(['error' => 'not found'], 404); return; }
        $id = (int)$p['id'];
        $row = DB::one('SELECT version, status FROM projects WHERE id = ?', [$id]);
        $this->conditional([
            'v' => (int)$row['version'],
            's' => $row['status'],
            'm' => (int)DB::val('SELECT COALESCE(MAX(id),0) FROM project_messages WHERE project_id = ?', [$id]),
            'f' => (int)DB::val('SELECT COUNT(*) FROM feedback WHERE project_id = ?', [$id]),
            'n' => Notify::unreadCount($this->uid()),
        ]);
    }

    public function messages(string $pid): void
    {
        $p = Policy::project($pid);
        if (!$p) { $this->json(['error' => 'not found'], 404); return; }
        $after = max(0, (int)($_GET['after'] ?? 0));
        $rows = DB::all('SELECT m.id, m.body, m.created_at, m.user_id, u.name FROM project_messages m JOIN users u ON u.id = m.user_id
                         WHERE m.project_id = ? AND m.id > ? ORDER BY m.id LIMIT 50', [$p['id'], $after]);
        $me = $this->uid();
        $this->json(['messages' => array_map(fn($r) => ['id' => (int)$r['id'], 'name' => $r['name'], 'body' => $r['body'], 'when' => time_ago($r['created_at']), 'mine' => (int)$r['user_id'] === $me], $rows)]);
    }

    public function progress(string $slug): void
    {
        $c = Courses::bySlug($slug);
        if (!$c || !Policy::enrollment((int)$c['id'])) { $this->json(['error' => 'not found'], 404); return; }
        $this->conditional(Progress::summary($this->uid(), (int)$c['id']));
    }
}
