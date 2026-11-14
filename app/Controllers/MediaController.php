<?php
namespace App\Controllers;

use App\Core\{Controller, DB, Policy, Upload};

/**
 * Streams lesson audio to people who may open that lesson (enrolled learners, the course's staff, or anyone for a preview
 * lesson of a published course). Files live outside the web root. Supports HTTP Range so the player can seek and resume,
 * which also saves data on weak connections.
 */
class MediaController extends Controller
{
    private const TYPES = ['mp3' => 'audio/mpeg', 'm4a' => 'audio/mp4', 'ogg' => 'audio/ogg', 'wav' => 'audio/wav'];

    public function lessonAudio(string $id): void
    {
        $l = DB::one('SELECT l.id, l.audio_path, l.is_preview, c.id AS course_id, c.owner_id, c.status
                      FROM lessons l JOIN modules m ON m.id = l.module_id JOIN courses c ON c.id = m.course_id WHERE l.id = ?', [(int)$id]);
        if (!$l || !$l['audio_path'] || !Policy::canOpenLesson(['id' => $l['course_id'], 'owner_id' => $l['owner_id'], 'status' => $l['status']], $l)) {
            $this->notFound();
            return;
        }
        $path = Upload::fullPath($l['audio_path']);
        if (!$path) { $this->notFound(); return; }

        $size = filesize($path);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $start = 0; $end = $size - 1; $status = 200;
        if (isset($_SERVER['HTTP_RANGE']) && preg_match('/^bytes=(\d*)-(\d*)$/', trim($_SERVER['HTTP_RANGE']), $m) && ($m[1] !== '' || $m[2] !== '')) {
            if ($m[1] === '') { $start = max(0, $size - (int)$m[2]); }          // "last N bytes"
            else { $start = (int)$m[1]; if ($m[2] !== '') $end = min($end, (int)$m[2]); }
            if ($start > $end || $start >= $size) {
                http_response_code(416);
                header("Content-Range: bytes */$size");
                return;
            }
            $status = 206;
        }
        http_response_code($status);
        header('Content-Type: ' . (self::TYPES[$ext] ?? 'application/octet-stream'));
        header('Accept-Ranges: bytes');
        header('Cache-Control: private, max-age=86400');
        header('Content-Length: ' . ($end - $start + 1));
        if ($status === 206) header("Content-Range: bytes $start-$end/$size");
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD') return;

        $fh = fopen($path, 'rb');
        fseek($fh, $start);
        $left = $end - $start + 1;
        while ($left > 0 && !feof($fh)) {
            $chunk = fread($fh, min(8192, $left));
            if ($chunk === false) break;
            echo $chunk;
            $left -= strlen($chunk);
        }
        fclose($fh);
    }

    /** Course cover photo: public for published courses (it appears on cards), staff-only for drafts. Cached by the browser. */
    public function courseCover(string $id): void
    {
        $c = DB::one('SELECT id, owner_id, status, cover_path FROM courses WHERE id = ?', [(int)$id]);
        if (!$c || !$c['cover_path'] || ($c['status'] !== 'published' && !Policy::manageCourse($c))) { $this->notFound(); return; }
        $path = Upload::coverPath($c['cover_path']);
        if (!$path) { $this->notFound(); return; }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $etag = '"' . md5($c['cover_path']) . '"';
        header('ETag: ' . $etag);
        header('Cache-Control: ' . ($c['status'] === 'published' ? 'public' : 'private') . ', max-age=604800');
        if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) { http_response_code(304); return; }
        header('Content-Type: ' . ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'][$ext]);
        header('Content-Length: ' . filesize($path));
        readfile($path);
    }
}
