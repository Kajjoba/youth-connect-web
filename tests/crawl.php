<?php
/**
 * Visits every important page as each role and fails on HTTP 5xx, PHP warnings/notices, or unrendered template fragments.
 *   php tests/crawl.php http://127.0.0.1:8099        (run after tests/run.php so there is rich data, or on fresh seed data)
 */
if (PHP_SAPI !== 'cli') exit;
$base = rtrim($argv[1] ?? 'http://127.0.0.1:8099', '/');
require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/Browser.php';
use App\Core\DB;

$bad = 0; $count = 0;
$check = function (Browser $b, string $who, string $path, int $expect = 200) use (&$bad, &$count) {
    $r = $b->get($path, [], true); $count++;
    $problems = [];
    if ($r['status'] !== $expect) $problems[] = "status {$r['status']} (expected $expect)";
    if (preg_match('/(Warning|Notice|Fatal error|Parse error|Deprecated)<\/b>:|<b>(Warning|Notice|Deprecated)|Undefined (variable|array key|index)|Stack trace/i', $r['body'], $m)) $problems[] = 'PHP message: ' . $m[0];
    if (preg_match('/<\?php|<\?=/', $r['body'])) $problems[] = 'raw PHP tag in output';
    if ($problems) { $bad++; echo "  ✗ [$who] $path → " . implode('; ', $problems) . "\n"; }
};

$P1 = DB::val("SELECT public_id FROM projects WHERE title = 'Smallholder Planting Advisor'");
$P2 = DB::val("SELECT public_id FROM projects WHERE title = 'Borehole Water Quality Map'");
$T1 = DB::val("SELECT public_id FROM teams WHERE name = 'AgriTech Bridge'");
$T2 = DB::val("SELECT public_id FROM teams WHERE name = 'Clean Water Data'");
$courses = DB::all("SELECT id, slug FROM courses");
$ai = DB::val("SELECT id FROM courses WHERE slug = 'ai-literacy-for-everyday-life'");
$lesson = DB::val('SELECT l.id FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = ? ORDER BY l.id LIMIT 1', [$ai]);
$disc = DB::val('SELECT l.id FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = ? AND l.type = \'discussion\' ORDER BY l.id LIMIT 1', [$ai]);
$mod1 = DB::val('SELECT id FROM modules WHERE course_id = ? ORDER BY week_no LIMIT 1', [$ai]);
$modAsg = DB::val('SELECT m.id FROM modules m JOIN assignments a ON a.module_id = m.id WHERE m.course_id = ? LIMIT 1', [$ai]);
$cert = DB::val('SELECT code FROM certificates LIMIT 1');

echo "Visitor\n"; $v = newBrowser($base);
foreach (['/', '/courses', '/courses?category=ai-digital-literacy&level=Beginner&sort=rating', '/courses?q=zzzz', '/courses?page=99', '/login', '/register', '/offline', "/certificate/$cert"] as $p) $check($v, 'visitor', $p);
foreach ($courses as $c) $check($v, 'visitor', '/course/' . $c['slug']);
$check($v, 'visitor', '/nope', 404);

echo "Participant (Level Two unlocked)\n"; $b = newBrowser($base, 'brian@youthconnect.test');
foreach (['/my-learning', '/profile', '/notifications', '/teams', '/teams/new', "/teams/$T1", "/teams/$T2", '/teams?q=data',
    "/projects/$P1", "/projects/$P1?tab=work", "/projects/$P1?tab=evidence", "/projects/$P1?tab=feedback", "/projects/$P1?tab=chat", "/projects/$P1?tab=assessment",
    "/course/ai-literacy-for-everyday-life", "/learn/ai-literacy-for-everyday-life", "/learn/ai-literacy-for-everyday-life/lesson/$lesson", "/learn/ai-literacy-for-everyday-life/lesson/$disc",
    "/learn/ai-literacy-for-everyday-life/quiz/$mod1", "/learn/ai-literacy-for-everyday-life/assignment/$modAsg", '/learn/ai-literacy-for-everyday-life/discussion',
    '/api/progress/ai-literacy-for-everyday-life'] as $p) $check($b, 'brian', $p);
$check($b, 'brian', "/teams/$T2");

echo "Participant (in progress / not eligible)\n";
foreach (['peter', 'esther'] as $who) {
    $x = newBrowser($base, "$who@youthconnect.test");
    foreach (['/my-learning', '/profile', '/teams', "/teams/$T1", "/learn/ai-literacy-for-everyday-life/lesson/$lesson"] as $p) $check($x, $who, $p);
}
echo "Facilitator\n"; $g = newBrowser($base, 'grace@youthconnect.test', 'Facilitate@123');
$mid = DB::val('SELECT id FROM modules WHERE course_id = ? ORDER BY week_no DESC LIMIT 1', [$ai]);
foreach (['/manage/courses', '/manage/courses/new', "/manage/courses/$ai", "/manage/courses/$ai/learners", "/manage/modules/$mid", "/manage/lessons/$lesson", '/manage/submissions', '/staff/projects',
    "/projects/$P1", "/projects/$P1?tab=feedback", "/projects/$P1?tab=assessment", "/learn/ai-literacy-for-everyday-life/quiz/$mod1"] as $p) $check($g, 'grace', $p);
echo "Mentor / stakeholder\n";
foreach ([['amina', 'Mentor@12345'], ['samuel', 'Stake@12345']] as [$who, $pw]) {
    $x = newBrowser($base, "$who@youthconnect.test", $pw);
    foreach (['/staff/projects', '/notifications', "/projects/$P1", "/projects/$P1?tab=assessment", "/projects/$P1?tab=chat"] as $p) $check($x, $who, $p);
}
echo "Admin\n"; $a = newBrowser($base, 'admin@youthconnect.test', 'Admin@12345');
foreach (['/admin', '/admin/users', '/admin/users?q=brian&role=participant', '/admin/categories', '/admin/projects', '/admin/audit', '/manage/courses', "/projects/$P2", "/projects/$P2?tab=work"] as $p) $check($a, 'admin', $p);

$log = config('storage_path') . '/logs/php-error.log';
$logLines = is_file($log) ? array_filter(file($log), fn($l) => stripos($l, 'warning') !== false || stripos($l, 'notice') !== false || stripos($l, 'deprecated') !== false || stripos($l, 'fatal') !== false) : [];
foreach (array_slice($logLines, -10) as $l) echo "  log: " . trim($l) . "\n";
echo "\n$count pages checked, $bad problem(s), " . count($logLines) . " PHP log warning line(s)\n";
exit($bad ? 1 : 0);
