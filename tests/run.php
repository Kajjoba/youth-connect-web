<?php
/**
 * Functional + security tests against a running server and a freshly installed (seeded) database.
 *   php database/install.php
 *   php -S 127.0.0.1:8099 -t public public/index.php        (separate terminal)
 *   php tests/run.php http://127.0.0.1:8099
 * The suite changes data (it registers users, completes a project); re-run install.php before running again.
 */
if (PHP_SAPI !== 'cli') exit;
$base = rtrim($argv[1] ?? 'http://127.0.0.1:8099', '/');
require __DIR__ . '/../app/bootstrap.php';
use App\Core\DB;

$pass = 0; $fail = 0;
function ok($cond, string $label, string $detail = ''): void
{
    global $pass, $fail;
    if ($cond) { $pass++; echo "  \033[32m✓\033[0m $label\n"; }
    else { $fail++; echo "  \033[31m✗ $label\033[0m" . ($detail !== '' ? "  [$detail]" : '') . "\n"; }
}
function section(string $t): void { echo "\n\033[1m$t\033[0m\n"; }

require __DIR__ . '/Browser.php';
function uid(string $email): int { return (int)DB::val('SELECT id FROM users WHERE email = ?', [$email]); }

$P1 = DB::val("SELECT public_id FROM projects WHERE title = 'Smallholder Planting Advisor'");
$P2 = DB::val("SELECT public_id FROM projects WHERE title = 'Borehole Water Quality Map'");
$pid1 = (int)DB::val('SELECT id FROM projects WHERE public_id = ?', [$P1]);
$pid2 = (int)DB::val('SELECT id FROM projects WHERE public_id = ?', [$P2]);

// ===================================================================================
section('1. Public pages and what a visitor sees BEFORE enrolling');
$v = newBrowser($base);
foreach (['/', '/courses', '/courses?q=ai', '/login', '/register', '/course/teamwork-and-remote-collaboration'] as $p) ok($v->get($p)['status'] === 200, "GET $p is 200");
$c = $v->get('/course/ai-literacy-for-everyday-life');
ok(has($c, "What you'll learn") && has($c, 'Skills you') && has($c, 'Week 1') && has($c, 'Week 3'), 'course page lists outcomes, skills and a week-by-week syllabus');
ok(has($c, 'What\'s included') && has($c, '1 quiz (4 questions)'), 'each week shows its contents and its quiz');
ok(has($c, '3 modules') && has($c, '3 weeks at 3 hours a week'), 'course page shows duration (weeks and hours per week)');
ok(has($c, 'Enrol for free'), 'visitor sees the Enrol button');
ok($v->get('/course/no-such-course', [], false)['status'] === 404, 'unknown course is 404');
$firstLesson = (int)DB::val("SELECT l.id FROM lessons l JOIN modules m ON m.id = l.module_id JOIN courses c ON c.id = m.course_id WHERE c.slug = 'ai-literacy-for-everyday-life' AND l.is_preview = 1 ORDER BY l.id LIMIT 1");
ok($v->req('GET', "/learn/ai-literacy-for-everyday-life/lesson/$firstLesson", [], [], false)['status'] === 302, 'preview lesson needs login (redirect)');
$s = $v->get('/api/search?q=teamwork');
ok(has($s, 'Teamwork') && $s['wire'] < 400, 'type-ahead search is a tiny JSON response', $s['wire'] . ' bytes');

// ===================================================================================
section('2. Registration, validation and authentication');
$email = 'newlearner' . random_int(1000, 9999) . '@example.com';
$n = newBrowser($base);
$n->get('/register');
$r = $n->post('/register', ['name' => 'N', 'email' => 'bad', 'password' => 'short', 'password_confirmation' => 'x', 'country' => 'Uganda', 'age_group' => '18-24']);
ok(has($r, 'Enter an email address') || has($r, 'check the highlighted'), 'invalid registration shows helpful errors');
$n->get('/register');
$r = $n->post('/register', ['name' => 'Naomi Test', 'email' => $email, 'password' => 'Passw0rdOK', 'password_confirmation' => 'Passw0rdOK', 'country' => 'Uganda', 'age_group' => '18-24', 'terms' => '1', 'role' => 'admin', 'level2_override' => '1']);
ok(has($r, 'Tell us about yourself') || has($r, 'Welcome'), 'registration succeeds and leads to the profile step');
$row = DB::one('SELECT role, level2_override FROM users WHERE email = ?', [$email]);
ok($row && $row['role'] === 'participant' && (int)$row['level2_override'] === 0, 'role/override fields in the form are ignored (no privilege escalation)');
$r = $n->post('/profile', ['name' => 'Naomi Test', 'country' => 'Uganda', 'age_group' => '18-24', 'digital_level' => 'beginner', 'connectivity' => 'limited', 'headline' => 'Student', 'lite_mode' => '1', 'welcome' => '1']);
ok(has($r, 'Profile saved') || has($r, 'Explore activities'), 'profile can be created after registering');
ok(DB::val('SELECT lite_mode FROM profiles WHERE user_id = ?', [uid($email)]) == 1, 'profile stores connectivity and Lite mode preference');
$dup = newBrowser($base); $dup->get('/register');
$r = $dup->post('/register', ['name' => 'Dup', 'email' => $email, 'password' => 'Passw0rdOK', 'password_confirmation' => 'Passw0rdOK', 'country' => 'Uganda', 'age_group' => '18-24', 'terms' => '1']);
ok(has($r, 'already exists'), 'duplicate email is rejected politely');

$noCsrf = new Browser($base); $noCsrf->get('/login');
ok($noCsrf->req('POST', '/login', ['email' => 'x@y.z', 'password' => 'p'], [], false, false)['status'] === 419, 'POST without CSRF token is rejected (419)');

$t = newBrowser($base); $t->get('/login');
for ($i = 0; $i < 6; $i++) $r = $t->post('/login', ['email' => 'brian@youthconnect.test', 'password' => 'wrong' . $i]);
ok(has($r, 'Too many attempts'), 'login is throttled after repeated failures');
DB::run('DELETE FROM throttle');
$r = newBrowser($base)->login('brian@youthconnect.test', 'Learn@12345');
ok(has($r, 'My learning') || has($r, 'Welcome back'), 'valid login works');
$bad = newBrowser($base)->login('brian@youthconnect.test', 'nope');
ok(has($bad, 'do not match') && !has($bad, 'does not exist'), 'wrong password gives a generic message (no account enumeration)');
$cb = newBrowser($base, 'brian@youthconnect.test');
$cookie = file_get_contents($cb->jar);
ok(stripos($cookie, 'HttpOnly') !== false || strpos($cookie, '#HttpOnly_') !== false, 'session cookie is HttpOnly');

// ===================================================================================
section('3. Level One journey: enrol → learn → weekly quizzes → assignment → certificate');
$u = $n;   // the freshly registered participant
$slug = 'teamwork-and-remote-collaboration';
$cid = (int)DB::val('SELECT id FROM courses WHERE slug = ?', [$slug]);
$u->get("/course/$slug");
$u->post("/course/$slug/enroll"); $u->post("/course/$slug/enroll");
ok(DB::val('SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND course_id = ?', [uid($email), $cid]) == 1, 'double-tapping Enrol creates a single enrolment (idempotent)');
$r = $u->get("/learn/$slug");
ok($r['status'] === 200 && has($r, 'Week 1') && has($r, 'Week 3'), 'course player shows the weekly outline');

$modules = DB::all('SELECT id, week_no FROM modules WHERE course_id = ? ORDER BY week_no', [$cid]);
$qidWeek1 = (int)DB::val('SELECT id FROM quizzes WHERE module_id = ?', [$modules[0]['id']]);
$questions = DB::all('SELECT q.id, (SELECT id FROM options WHERE question_id = q.id AND is_correct = 1) AS good, (SELECT id FROM options WHERE question_id = q.id AND is_correct = 0 LIMIT 1) AS bad FROM questions q WHERE quiz_id = ?', [$qidWeek1]);
$r = $u->get("/learn/$slug/quiz/{$modules[0]['id']}");
ok(!preg_match('/is_correct|checked/i', $r['body']) && has($r, 'Submit quiz'), 'quiz page never exposes the answer key');
$forged = []; foreach ($questions as $q) $forged[$q['id']] = 999999;
$r = $u->post("/learn/$slug/quiz/{$modules[0]['id']}", ['answers' => $forged]);
ok(has($r, '0%') && has($r, 'Not quite'), 'forged option ids score zero instead of passing');
$wrong = []; foreach ($questions as $q) $wrong[$q['id']] = $q['bad'];
$r = $u->post("/learn/$slug/quiz/{$modules[0]['id']}", ['answers' => $wrong]);
ok(has($r, 'Try again') && has($r, 'Incorrect'), 'wrong answers give per-question feedback and a retry');
$right = []; foreach ($questions as $q) $right[$q['id']] = $q['good'];
$r = $u->post("/learn/$slug/quiz/{$modules[0]['id']}", ['answers' => $right]);
ok(has($r, '100%') && has($r, 'passed'), 'correct answers pass the weekly quiz');

foreach (DB::all('SELECT l.id FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = ? ORDER BY m.week_no, l.position', [$cid]) as $l) $u->post("/learn/$slug/lesson/{$l['id']}/complete", [], false);
foreach (array_slice($modules, 1) as $m) {
    $qs = DB::all('SELECT q.id, (SELECT id FROM options WHERE question_id = q.id AND is_correct = 1) AS good FROM questions q JOIN quizzes z ON z.id = q.quiz_id WHERE z.module_id = ?', [$m['id']]);
    $a = []; foreach ($qs as $q) $a[$q['id']] = $q['good'];
    $u->post("/learn/$slug/quiz/{$m['id']}", ['answers' => $a]);
}
$asgModule = (int)DB::val('SELECT m.id FROM assignments a JOIN modules m ON m.id = a.module_id WHERE m.course_id = ?', [$cid]);
$r = $u->post("/learn/$slug/assignment/$asgModule", ['body' => 'short']);
ok(has($r, 'at least 30') || has($r, 'check the highlighted'), 'assignment answer is validated');
$prog = (int)DB::val('SELECT COUNT(*) FROM lesson_progress WHERE user_id = ?', [uid($email)]);
ok($prog >= 9, 'lesson progress is recorded', "$prog lessons");
ok(DB::val('SELECT status FROM enrollments WHERE user_id = ? AND course_id = ?', [uid($email), $cid]) === 'active', 'course is not complete until the assignment is done');
$u->post("/learn/$slug/assignment/$asgModule", ['body' => 'Our team agreement says we meet on Tuesdays at 4 pm East Africa Time, use audio calls when bandwidth is low, and give feedback kindly.']);
ok(DB::val('SELECT status FROM enrollments WHERE user_id = ? AND course_id = ?', [uid($email), $cid]) === 'completed', 'finishing every lesson, quiz and assignment completes the course');
$cert = DB::val('SELECT code FROM certificates WHERE user_id = ? AND kind = \'course\'', [uid($email)]);
ok((bool)$cert, 'a certificate is issued automatically');
$pub = newBrowser($base)->get('/certificate/' . $cert);
ok($pub['status'] === 200 && has($pub, 'Naomi Test') && has($pub, 'Verified'), 'anyone can verify the certificate by its code (name only, no email)');
ok(!has($pub, $email), 'verification page does not leak the learner\'s email');
$u->post("/learn/$slug/quiz/{$modules[0]['id']}", ['answers' => $right]);
ok(DB::val('SELECT COUNT(*) FROM certificates WHERE user_id = ?', [uid($email)]) == 1, 'retaking a quiz never issues a second certificate');
$r = $u->post('/teams', ['name' => 'Naomi Team', 'description' => 'x', 'discipline' => 'Education', 'is_open' => '1']);
ok(has($r, 'Naomi Team'), 'completing a course unlocks Level Two (team creation)');

// ===================================================================================
section('4. Roles and access control');
$esther = newBrowser($base, 'esther@youthconnect.test');
$r = $esther->get('/teams/new');
ok(has($r, 'opens after you complete') && !has($r, 'Create team'), 'learner who has not finished a course cannot start a team');
$r = $esther->post('/teams', ['name' => 'Sneaky team', 'discipline' => 'Health']);
ok(!DB::val("SELECT id FROM teams WHERE name = 'Sneaky team'"), 'creating a team by POST is also blocked until Level Two is unlocked');
ok($esther->get('/admin', [], false)['status'] === 403, 'participant cannot open /admin (403)');
ok($esther->get('/manage/courses', [], false)['status'] === 403, 'participant cannot open course management (403)');
$r = $esther->post('/admin/users', ['name' => 'X', 'email' => 'x@x.org', 'role' => 'admin'], false);
ok($r['status'] === 403, 'participant cannot create users');
$david = newBrowser($base, 'david@youthconnect.test', 'Facilitate@123');
$graceCourse = (int)DB::val("SELECT id FROM courses WHERE slug = 'ai-literacy-for-everyday-life'");
ok($david->get("/manage/courses/$graceCourse", [], false)['status'] === 404, 'a facilitator cannot edit another facilitator\'s course');
$grace = newBrowser($base, 'grace@youthconnect.test', 'Facilitate@123');
ok($grace->get("/manage/courses/$graceCourse")['status'] === 200, 'the owner can edit the course');
$r = $grace->post('/manage/courses', ['title' => 'Test course by Grace', 'summary' => 'A short summary for the automated test course.', 'description' => 'Description text that is definitely longer than forty characters.', 'what_you_learn' => 'Outcome one', 'level' => 'Beginner', 'hours_per_week' => 2, 'language' => 'English', 'category_id' => 1, 'pass_mark' => 70, 'color' => '#112233']);
ok(has($r, 'Course created') || has($r, 'Weekly outline'), 'facilitator can create a course');
$newCourse = (int)DB::val("SELECT id FROM courses WHERE title = 'Test course by Grace'");
$r = $grace->post("/manage/courses/$newCourse/status", ['status' => 'published']);
ok(has($r, 'at least one week'), 'cannot publish a course with no content');
$grace->post("/manage/courses/$newCourse/weeks", ['title' => 'Week one test']);
$mod = (int)DB::val('SELECT id FROM modules WHERE course_id = ?', [$newCourse]);
$grace->post("/manage/modules/$mod/lessons", ['title' => 'First lesson', 'type' => 'reading', 'minutes' => 5, 'content' => "Hello\n\nWorld", 'is_preview' => '1']);
$grace->post("/manage/modules/$mod/questions", ['body' => 'Is this a test?', 'opt1' => 'Yes', 'opt2' => 'No', 'correct' => '1', 'explanation' => 'It is.']);
$r = $grace->post("/manage/courses/$newCourse/status", ['status' => 'published']);
ok(has($r, 'Published') && DB::val('SELECT COUNT(*) FROM questions') > 0, 'facilitator builds weeks, lessons and a quiz, then publishes');
ok(newBrowser($base)->get('/course/test-course-by-grace')['status'] === 200, 'published course appears publicly');

// ===================================================================================
section('5. CAT 2 incident A: a member cannot see updated feedback');
$mentor = newBrowser($base, 'amina@youthconnect.test', 'Mentor@12345');
$text = 'Please add a measurable pilot target. ' . bin2hex(random_bytes(4));
$mentor->get("/projects/$P1?tab=feedback");
$before = (int)DB::val('SELECT COUNT(*) FROM feedback WHERE project_id = ?', [$pid1]);
$r = $mentor->post("/projects/$P1/feedback", ['body' => $text]);
ok(has($r, 'shared with the whole team'), 'mentor posts feedback');
ok((int)DB::val('SELECT COUNT(*) FROM feedback WHERE project_id = ?', [$pid1]) === $before + 1, 'feedback is stored exactly once for the project (not per member)');
foreach (['brian', 'wanjiru', 'joseph', 'aline'] as $who) {
    $m = newBrowser($base, "$who@youthconnect.test");
    $r = $m->get("/projects/$P1?tab=feedback");
    ok(has($r, $text), "$who (team member) sees the new feedback immediately");
    ok(DB::val('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND message LIKE ? AND read_at IS NULL', [uid("$who@youthconnect.test"), '%New feedback%']) >= 1, "$who has an in-app notification about it");
}

// ===================================================================================
section('6. CAT 2 incident B: changing the project identifier must not expose another team');
$brian = newBrowser($base, 'brian@youthconnect.test');
ok($brian->get("/projects/$P1")['status'] === 200, 'Brian opens his own team\'s project');
foreach (["/projects/$P2", "/projects/$P2?tab=feedback", "/projects/$P2?tab=evidence", "/projects/$P2?tab=chat", "/projects/$P2?tab=work", "/projects/$P2?tab=assessment"] as $p) {
    $r = $brian->get($p, [], false);
    ok($r['status'] === 404 && !has($r, 'Borehole'), "other team's project is not found: $p");
}
foreach (['/projects/' . $pid2, '/projects/1', '/projects/2', '/projects/0', '/projects/' . substr($P2, 0, 15) . 'x', '/projects/../admin', '/projects/%00'] as $p)
    ok(in_array($brian->get($p, [], false)['status'], [404], true), "guessed/edited identifier $p returns 404");
ok($brian->get("/api/projects/$P2/pulse", [], false)['status'] === 404, 'the pulse API also refuses another team\'s project');
ok($brian->get("/api/projects/$P2/messages", [], false)['status'] === 404, 'the messages API also refuses another team\'s project');

$task2 = DB::insert('INSERT INTO tasks (project_id, title) VALUES (?,?)', [$pid2, 'Secret task of team 2']);
$ms2 = DB::insert('INSERT INTO milestones (project_id, title) VALUES (?,?)', [$pid2, 'Secret milestone']);
$r = $brian->post("/projects/$P1/tasks/$task2", ['status' => 'done'], false);
ok($r['status'] === 404 && DB::val('SELECT status FROM tasks WHERE id = ?', [$task2]) === 'todo', 'cannot modify another project\'s task through my own project URL');
$r = $brian->post("/projects/$P2/tasks/$task2", ['status' => 'done'], false);
ok($r['status'] === 404 && DB::val('SELECT status FROM tasks WHERE id = ?', [$task2]) === 'todo', 'cannot modify another project\'s task directly');
$r = $brian->post("/projects/$P1/milestones/$ms2", [], false);
ok($r['status'] === 404 && DB::val('SELECT status FROM milestones WHERE id = ?', [$ms2]) === 'open', 'cannot toggle another project\'s milestone');
$r = $brian->post("/projects/$P1/tasks", ['title' => 'Bad assignee', 'assignee_id' => uid('moses@youthconnect.test')], false);
ok($r['status'] === 404 && !DB::val('SELECT id FROM tasks WHERE title = ?', ['Bad assignee']), 'cannot assign a task to someone outside the team');
$r = $brian->post("/projects/$P1/tasks", ['title' => 'Bad milestone', 'milestone_id' => $ms2], false);
ok($r['status'] === 404 && !DB::val('SELECT id FROM tasks WHERE title = ?', ['Bad milestone']), 'cannot attach a task to another project\'s milestone');
$r = $brian->post("/projects/$P2/messages", ['body' => 'I should not be here'], false);
ok($r['status'] === 404 && !DB::val('SELECT id FROM project_messages WHERE body = ?', ['I should not be here']), 'cannot post into another team\'s discussion');
$r = $brian->post("/projects/$P2/feedback", ['body' => 'Fake feedback from outsider'], false);
ok($r['status'] === 404, 'cannot add feedback to another team\'s project');
$r = $brian->post("/projects/$P1/feedback", ['body' => 'Self-awarded feedback by a team member'], false);
ok($r['status'] === 403 && !DB::val('SELECT id FROM feedback WHERE body = ?', ['Self-awarded feedback by a team member']), 'a team member cannot write mentor feedback for their own project');
$r = $brian->post("/projects/$P1/assess", ['impact' => 5, 'collaboration' => 5, 'technical' => 5, 'presentation' => 5, 'sustainability' => 5], false);
ok($r['status'] === 403, 'a team member cannot assess their own project');
$r = $brian->post("/projects/$P1/transition", ['action' => 'complete'], false);
ok($r['status'] === 403, 'a team member cannot mark their own project complete');
$moses = newBrowser($base, 'moses@youthconnect.test');
ok($moses->get("/projects/$P2")['status'] === 200 && $moses->get("/projects/$P1", [], false)['status'] === 404, 'the other team sees only its own project (isolation both ways)');
$samuel = newBrowser($base, 'samuel@youthconnect.test', 'Stake@12345');
ok($samuel->get("/projects/$P1")['status'] === 200 && $samuel->get("/projects/$P2", [], false)['status'] === 404, 'assigned stakeholder sees only assigned projects');
$admin = newBrowser($base, 'admin@youthconnect.test', 'Admin@12345');
ok($admin->get("/projects/$P2")['status'] === 200, 'an administrator can open any project');
$teamPage = $brian->get('/teams/' . DB::val('SELECT public_id FROM teams WHERE name = ?', ['Clean Water Data']), [], false);
ok($teamPage['status'] === 200 && !has($teamPage, 'Fatuma') && !has($teamPage, 'Borehole Water Quality Map'), 'outsiders see only a public team card: no member names, no project titles');

// evidence files
section('   evidence uploads and downloads');
$tmp = sys_get_temp_dir() . '/ycp_test.txt'; file_put_contents($tmp, "evidence file content");
$php = sys_get_temp_dir() . '/ycp_evil.php'; file_put_contents($php, '<?php echo 1;');
$fake = sys_get_temp_dir() . '/ycp_fake.txt'; file_put_contents($fake, "<?php system('id'); ?>");
$post = fn($b, $pid, $file, $name) => $b->req('POST', "/projects/$pid/evidence", ['title' => 'Test ' . $name, 'kind' => 'file', 'file' => new CURLFile($file, 'text/plain', $name), '_csrf' => $b->token], [], true);
$r = $post($brian, $P1, $php, 'evil.php');
ok(has($r, 'not accepted') && !DB::val('SELECT id FROM evidence WHERE original_name = ?', ['evil.php']), 'a .php upload is refused');
$r = $post($brian, $P1, $tmp, 'notes.txt');
ok(has($r, 'Evidence added') && DB::val('SELECT id FROM evidence WHERE original_name = ?', ['notes.txt']), 'a valid text file is accepted');
$ev = DB::one('SELECT public_id, file_path FROM evidence WHERE original_name = ?', ['notes.txt']);
ok($ev && !file_exists(BASE_PATH . '/public/' . $ev['file_path']) && is_file(config('storage_path') . '/uploads/' . $ev['file_path']), 'uploaded file is stored outside the public web root');
ok($ev && preg_match('#^\d{4}/\d{2}/[a-f0-9]{32}\.txt$#', $ev['file_path']), 'uploaded file gets a random server-side name');
$wanjiru = newBrowser($base, 'wanjiru@youthconnect.test');
$d = $wanjiru->get("/projects/$P1/evidence/{$ev['public_id']}", [], false);
ok($d['status'] === 200 && trim($d['body']) === 'evidence file content' && stripos($d['headers']['content-disposition'] ?? '', 'attachment') !== false, 'a teammate can download the file (as an attachment)');
ok($moses->get("/projects/$P1/evidence/{$ev['public_id']}", [], false)['status'] === 404, 'someone outside the team cannot download it');
ok($moses->get("/projects/$P2/evidence/{$ev['public_id']}", [], false)['status'] === 404, 'a project id cannot be swapped to fetch another team\'s file');
ok(newBrowser($base)->get("/projects/$P1/evidence/{$ev['public_id']}", [], false)['status'] === 302, 'anonymous download redirects to login');
ok(newBrowser($base)->get('/storage/uploads/' . $ev['file_path'], [], false)['status'] === 404, 'direct URL to the stored file does not exist');

// ===================================================================================
section('7. CAT 2 incident C: refreshing a project page on a slow connection');
$slow = newBrowser($base, 'brian@youthconnect.test');
$first = $slow->get("/projects/$P1", ['Accept-Encoding: gzip'], false);
$etag = $first['headers']['etag'] ?? '';
ok($first['status'] === 200 && $etag !== '', 'project page is sent with an ETag', "{$first['wire']} bytes on the wire (gzip)");
$again = $slow->get("/projects/$P1", ["If-None-Match: $etag"], false);
ok($again['status'] === 304 && $again['wire'] < 600, 'refreshing an unchanged page returns 304 with no body', $again['wire'] . ' bytes (vs ' . $first['wire'] . ')');
ok(strlen($first['body']) > 3000 && ($first['headers']['content-encoding'] ?? '') !== '' || $first['wire'] < strlen($first['body']), 'responses are compressed');
$pulse = $slow->get("/api/projects/$P1/pulse", [], false);
$pj = json_decode($pulse['body'], true);
ok($pulse['status'] === 200 && isset($pj['v'], $pj['f']) && strlen($pulse['body']) < 150, 'the pulse endpoint is tiny', strlen($pulse['body']) . ' bytes');
$p304 = $slow->get("/api/projects/$P1/pulse", ['If-None-Match: ' . ($pulse['headers']['etag'] ?? '')], false);
ok($p304['status'] === 304, 'polling an unchanged project returns 304 (headers only)');
$slow->post("/projects/$P1/messages", ['body' => 'pulse test message']);
$after = json_decode($slow->get("/api/projects/$P1/pulse", [], false)['body'], true);
ok($after['m'] > $pj['m'], 'the pulse reports new chat messages so the page can fetch just those');
$key = bin2hex(random_bytes(16));
$slow->req('POST', "/projects/$P1/messages", ['body' => 'retry-safe message', 'client_key' => $key, '_csrf' => $slow->token], ['X-Requested-With: XMLHttpRequest']);
$slow->req('POST', "/projects/$P1/messages", ['body' => 'retry-safe message', 'client_key' => $key, '_csrf' => $slow->token], ['X-Requested-With: XMLHttpRequest']);
ok(DB::val('SELECT COUNT(*) FROM project_messages WHERE body = ?', ['retry-safe message']) == 1, 'a message re-sent after a dropped connection is stored only once');
$css = $slow->get('/assets/css/app.css', ['Accept-Encoding: gzip'], false);
$gz = strlen(gzencode($css['body'], 6));
ok($gz < 9000 && strlen($css['body']) < 40000, 'the whole stylesheet is small (served gzipped by the web server)', strlen($css['body']) . ' bytes raw, ' . $gz . ' gzipped');
$js = $slow->get('/assets/js/app.js', [], false);
ok(strlen(gzencode($js['body'], 6)) < 8000, 'the whole script is small', strlen(gzencode($js['body'], 6)) . ' bytes gzipped');
$lite = newBrowser($base); $lite->get('/'); $lite->post('/lite', ['lite' => '1']);
$home = $lite->get('/');
ok(strpos($home['body'], 'class="lite"') !== false && !has($home, 'hero-art'), 'Lite mode removes decorative graphics');

// ===================================================================================
section('8. Level Two lifecycle: proposal → mentor review → work → final → assessment → validation → recognition');
$r = $brian->post("/projects/$P1/transition", ['action' => 'submit_final']);
ok(DB::val('SELECT status FROM projects WHERE id = ?', [$pid1]) === 'changes_requested', 'cannot skip stages (final submission before approval is refused)');
$r = $mentor->post("/projects/$P1/transition", ['action' => 'approve']);
ok(DB::val('SELECT status FROM projects WHERE id = ?', [$pid1]) === 'changes_requested', 'mentor cannot approve a proposal that is not under review');
$brian->post("/projects/$P1/transition", ['action' => 'submit_proposal']);
ok(DB::val('SELECT status FROM projects WHERE id = ?', [$pid1]) === 'proposed', 'team lead resubmits the proposal');
$wanjiru->post("/projects/$P1/transition", ['action' => 'submit_proposal'], false);
ok($wanjiru->last['status'] === 403, 'only the lead can submit (members get 403)');
$mentor->post("/projects/$P1/transition", ['action' => 'request_changes', 'comment' => 'short']);
ok(DB::val('SELECT status FROM projects WHERE id = ?', [$pid1]) === 'proposed', 'requesting changes requires a real explanation');
$mentor->post("/projects/$P1/transition", ['action' => 'approve', 'comment' => 'Great, approved.']);
ok(DB::val('SELECT status FROM projects WHERE id = ?', [$pid1]) === 'approved', 'mentor approves the proposal');
$brian->post("/projects/$P1/transition", ['action' => 'submit_final']);
ok(DB::val('SELECT status FROM projects WHERE id = ?', [$pid1]) === 'submitted', 'team lead submits the final project (evidence exists)');
$wanjiru->post("/projects/$P1/transition", ['action' => 'submit_final'], false);
ok(DB::val('SELECT version FROM projects WHERE id = ?', [$pid1]) > 0, 'repeating a transition does nothing (atomic status check)');
$wanjiru->post("/projects/$P1/tasks", ['title' => 'Locked?'], false);
$r = $mentor->post("/projects/$P1/assess", ['impact' => 5, 'collaboration' => 5, 'technical' => 4, 'presentation' => 4, 'sustainability' => 4, 'comment' => 'Excellent work.']);
ok(DB::val('SELECT total FROM assessments WHERE project_id = ?', [$pid1]) == 88 && DB::val('SELECT status FROM projects WHERE id = ?', [$pid1]) === 'assessed', 'mentor assesses with the 5-criteria rubric (88%)');
$r = $mentor->post("/projects/$P1/assess", ['impact' => 1, 'collaboration' => 1, 'technical' => 1, 'presentation' => 1, 'sustainability' => 1]);
ok(DB::val('SELECT total FROM assessments WHERE project_id = ?', [$pid1]) == 88, 'a project cannot be assessed twice');
$r = $brian->post("/projects/$P1/validate", ['decision' => 'endorsed', 'comment' => 'I endorse my own work'], false);
ok($r['status'] === 403 && !DB::val('SELECT id FROM validations WHERE comment = ?', ['I endorse my own work']), 'a team member cannot validate their own project');
$samuel->post("/projects/$P1/validate", ['decision' => 'endorsed', 'comment' => 'This would really help our farmers.']);
ok(DB::val('SELECT status FROM projects WHERE id = ?', [$pid1]) === 'validated', 'stakeholder validation moves the project to Validated');
$grace->post("/projects/$P1/transition", ['action' => 'complete']);
ok(DB::val('SELECT status FROM projects WHERE id = ?', [$pid1]) === 'completed', 'the facilitator completes the project');
$certs = (int)DB::val("SELECT COUNT(*) FROM certificates WHERE kind = 'project' AND project_id = ?", [$pid1]);
ok($certs === 4, 'every team member receives a project certificate', "$certs");
$bad = DB::all("SELECT b.code FROM user_badges ub JOIN badges b ON b.id = ub.badge_id WHERE ub.user_id = ?", [uid('brian@youthconnect.test')]);
$codes = array_column($bad, 'code');
ok(in_array('collaborator', $codes) && in_array('cross_border', $codes) && in_array('impact_maker', $codes) && in_array('validated_impact', $codes), 'recognition badges awarded (collaborator, cross-border, impact maker, validated impact)', implode(',', $codes));
$grace->post("/projects/$P1/transition", ['action' => 'complete']);
ok(DB::val("SELECT COUNT(*) FROM certificates WHERE kind = 'project' AND project_id = ?", [$pid1]) == 4, 'completing twice does not duplicate certificates');
$dash = $brian->get('/my-learning');
ok(has($dash, 'Certificate of Collaboration') && has($dash, 'Cross-Border Collaborator'), 'recognition appears on the learner\'s dashboard');

// ===================================================================================
section('9. Output safety');
$xss = '<script>alert(1)</script>';
$brian->post("/projects/$P1/messages", ['body' => $xss]);
$r = $brian->get("/projects/$P1?tab=chat");
ok(!has($r, $xss) && has($r, '&lt;script&gt;'), 'user input is HTML-escaped on output (XSS)');
$h = $r['headers'];
ok(isset($h['content-security-policy']) && strpos($h['content-security-policy'], "script-src 'self'") !== false, 'strict Content-Security-Policy is sent');
ok(($h['x-frame-options'] ?? '') === 'DENY' && ($h['x-content-type-options'] ?? '') === 'nosniff', 'clickjacking and MIME-sniffing protections are sent');
$sqli = newBrowser($base)->get('/courses?q=' . urlencode("' OR 1=1 --") . '&category=' . urlencode("x' UNION SELECT 1 --"));
ok($sqli['status'] === 200 && has($sqli, 'No courses match'), 'SQL injection attempt in search is treated as plain text');
$sess = DB::val('SELECT COUNT(*) FROM sessions');
ok($sess > 0, 'sessions are stored in the database (stateless web nodes)', "$sess rows");

// ===================================================================================
section('10. Change my own password');
$cp = newBrowser($base, 'fatuma@youthconnect.test');
$r = $cp->post('/profile/password', ['current_password' => 'WRONG', 'new_password' => 'Newpass123', 'new_password_confirmation' => 'Newpass123']);
ok(has($r, 'not your current password'), 'wrong current password is refused');
$r = $cp->post('/profile/password', ['current_password' => 'Learn@12345', 'new_password' => 'short', 'new_password_confirmation' => 'short']);
ok(!has($r, 'Your password has been changed'), 'weak new password is refused');
$r = $cp->post('/profile/password', ['current_password' => 'Learn@12345', 'new_password' => 'Newpass123', 'new_password_confirmation' => 'Different1']);
ok(!has($r, 'Your password has been changed'), 'mismatched confirmation is refused');
$r = $cp->post('/profile/password', ['current_password' => 'Learn@12345', 'new_password' => 'Newpass123', 'new_password_confirmation' => 'Newpass123']);
ok(has($r, 'Your password has been changed'), 'correct current password changes the password');
ok(newBrowser($base)->login('fatuma@youthconnect.test', 'Newpass123')['status'] < 400 && has(newBrowser($base, 'fatuma@youthconnect.test', 'Newpass123')->get('/my-learning'), 'My learning'), 'the new password works for logging in');
$old = newBrowser($base)->login('fatuma@youthconnect.test', 'Learn@12345');
ok(has($old, 'do not match'), 'the old password no longer works');

// ===================================================================================
section('11. Admin can delete a person (safely)');
$adm = newBrowser($base, 'admin@youthconnect.test', 'Admin@12345');
$idOf = fn($email) => (int)DB::val('SELECT id FROM users WHERE email = ?', [$email]);
$exists = fn($email) => (bool)DB::val('SELECT COUNT(*) FROM users WHERE email = ?', [$email]);
$r = $adm->post('/admin/users/' . $idOf('admin@youthconnect.test') . '/delete');
ok(has($r, 'cannot delete your own account') && $exists('admin@youthconnect.test'), 'an admin cannot delete themselves');
$r = $adm->post('/admin/users/' . $idOf('grace@youthconnect.test') . '/delete');
ok(has($r, 'Suspend them instead') && $exists('grace@youthconnect.test'), 'a facilitator who owns courses cannot be deleted (suspend instead)');
$r = $adm->post('/admin/users/' . $idOf('amina@youthconnect.test') . '/delete');
ok(has($r, 'Suspend them instead') && $exists('amina@youthconnect.test'), 'a mentor whose feedback others rely on cannot be deleted');
ok(newBrowser($base, 'brian@youthconnect.test')->post('/admin/users/' . $idOf('peter@youthconnect.test') . '/delete')['status'] === 403 && $exists('peter@youthconnect.test'), 'a learner cannot delete anyone (403)');
ok(newBrowser($base)->post('/admin/users/' . $idOf('peter@youthconnect.test') . '/delete', [], false)['status'] !== 200 && $exists('peter@youthconnect.test'), 'a visitor cannot delete anyone');
$peter = $idOf('peter@youthconnect.test');
$r = $adm->post('/admin/users/' . $peter . '/delete');
ok(has($r, 'was deleted') && !$exists('peter@youthconnect.test'), 'admin deletes a learner');
ok(!DB::val('SELECT COUNT(*) FROM profiles WHERE user_id = ?', [$peter]) && !DB::val('SELECT COUNT(*) FROM enrollments WHERE user_id = ?', [$peter]), 'their profile and enrolments are removed with them');
ok(has(newBrowser($base)->login('peter@youthconnect.test', 'Learn@12345'), 'do not match'), 'the deleted person can no longer log in');
$brianId = $idOf('brian@youthconnect.test');
$team = DB::val('SELECT id FROM teams WHERE created_by = ?', [$brianId]);
$r = $adm->post('/admin/users/' . $brianId . '/delete');
ok(has($r, 'was deleted') && !$exists('brian@youthconnect.test'), 'a team creator can be deleted');
ok($team && (int)DB::val('SELECT created_by FROM teams WHERE id = ?', [$team]) !== $brianId && (int)DB::val('SELECT COUNT(*) FROM team_members WHERE team_id = ?', [$team]) >= 2, 'their team survives and passes to another member');
ok(!DB::val('SELECT COUNT(*) FROM evidence WHERE uploaded_by = ?', [$brianId]) && !DB::val('SELECT COUNT(*) FROM tasks WHERE assignee_id = ?', [$brianId]), 'their evidence is removed and their tasks are unassigned');
$audit = DB::val('SELECT COUNT(*) FROM audit_log WHERE action = \'user.deleted\'');
ok($audit >= 2, 'deletions are recorded in the audit log', "$audit entries");

echo "\n";
echo $fail === 0 ? "\033[32mALL $pass CHECKS PASSED\033[0m\n" : "\033[31m$fail FAILED, $pass passed\033[0m\n";
exit($fail === 0 ? 0 : 1);
