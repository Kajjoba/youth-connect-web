<?php
namespace App\Controllers;

use App\Core\{Controller, DB};

/** Certificate page: public verification (shows the name and course only) and, for the holder, download/share tools. */
class CertificateController extends Controller
{
    public function show(string $code): void
    {
        $c = DB::one('SELECT c.*, u.name, co.skills, co.level, co.hours_per_week,
                             (SELECT COUNT(*) FROM modules m WHERE m.course_id = co.id) AS weeks, o.name AS facilitator
                      FROM certificates c JOIN users u ON u.id = c.user_id
                      LEFT JOIN courses co ON co.id = c.course_id LEFT JOIN users o ON o.id = co.owner_id
                      WHERE c.code = ?', [strtoupper($code)]);
        if (!$c) { $this->notFound(); return; }
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
        $abs = ($https ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . url('certificate/' . $c['code']);
        $mine = $this->user() && (int)$this->user()['id'] === (int)$c['user_id'];
        $this->view('certificates/show', ['c' => $c, 'abs' => $abs, 'mine' => $mine, 'title' => 'Certificate ' . $c['code']]);
    }
}
