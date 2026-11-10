<?php
/** Demo data: staff, learners, six courses, a finished Level One journey and two Level Two teams. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

use App\Core\DB;
use App\Services\Progress;

$hash = fn(string $p) => password_hash($p, PASSWORD_DEFAULT);

// ---- Badges ---------------------------------------------------------------------
foreach ([
    ['collaborator', 'Collaborator', 'Completed a team project on YouthConnect', '🤝'],
    ['cross_border', 'Cross-Border Collaborator', 'Worked in a team from three or more countries', '🌍'],
    ['impact_maker', 'Impact Maker', 'Project assessed at 80% or above', '🚀'],
    ['validated_impact', 'Validated Impact', 'Project endorsed by a community stakeholder', '✅'],
] as $b) DB::run('INSERT INTO badges (code, name, description, icon) VALUES (?,?,?,?)', $b);

// ---- Users ----------------------------------------------------------------------
$people = [
    // name, email, role, country, age, headline, expertise, skills, org, password
    ['Programme Admin', 'admin@youthconnect.test', 'admin', 'Uganda', '31-35', 'YouthConnect programme administrator', 'Programme management', null, 'YouthConnect Steering Committee', 'Admin@12345'],
    ['Grace Nakato', 'grace@youthconnect.test', 'facilitator', 'Uganda', '31-35', 'Digital literacy trainer, Kampala', 'Digital skills training', 'Training, AI literacy', 'YouthConnect', 'Facilitate@123'],
    ['David Otieno', 'david@youthconnect.test', 'facilitator', 'Kenya', '31-35', 'Employability and communication coach, Nairobi', 'Career coaching', 'Coaching, communication', 'YouthConnect', 'Facilitate@123'],
    ['Amina Mwangi', 'amina@youthconnect.test', 'mentor', 'Tanzania', '31-35', 'Agricultural technology mentor, Arusha', 'Agritech', 'Agribusiness, mentoring', 'AgriHub Tanzania', 'Mentor@12345'],
    ['Samuel Byaruhanga', 'samuel@youthconnect.test', 'stakeholder', 'Uganda', '31-35', 'Manager, Kabale Farmers\' Cooperative', 'Farmer cooperatives', null, 'Kabale Farmers\' Cooperative', 'Stake@12345'],
    ['Brian Ssemakula', 'brian@youthconnect.test', 'participant', 'Uganda', '18-24', 'Agribusiness student, Makerere University', 'Agriculture', 'Farm planning, extension', 'Makerere University', 'Learn@12345'],
    ['Wanjiru Kamau', 'wanjiru@youthconnect.test', 'participant', 'Kenya', '25-30', 'Junior software developer, Nairobi', 'Software development', 'PHP, JavaScript, mobile web', 'Nairobi tech hub', 'Learn@12345'],
    ['Joseph Mushi', 'joseph@youthconnect.test', 'participant', 'Tanzania', '25-30', 'Data analyst, Dar es Salaam', 'Data analysis', 'Excel, SQL, data cleaning', null, 'Learn@12345'],
    ['Aline Uwase', 'aline@youthconnect.test', 'participant', 'Rwanda', '18-24', 'Environmental science graduate, Kigali', 'Environmental studies', 'GIS basics, field research', 'University of Rwanda', 'Learn@12345'],
    ['Peter Okello', 'peter@youthconnect.test', 'participant', 'Uganda', '18-24', 'Recent graduate looking for work', 'Business', null, null, 'Learn@12345'],
    ['Esther Achieng', 'esther@youthconnect.test', 'participant', 'Kenya', '25-30', 'Community health volunteer', 'Health', null, null, 'Learn@12345'],
    ['Moses Kato', 'moses@youthconnect.test', 'participant', 'Uganda', '25-30', 'Water and sanitation technician', 'Environmental studies', 'Water testing', null, 'Learn@12345'],
    ['Fatuma Hassan', 'fatuma@youthconnect.test', 'participant', 'Tanzania', '25-30', 'GIS and data officer', 'Data analysis', 'Mapping, data', null, 'Learn@12345'],
];
$U = [];
foreach ($people as [$name, $email, $role, $country, $age, $headline, $exp, $skills, $org, $pw]) {
    $id = DB::insert('INSERT INTO users (name, email, password_hash, role) VALUES (?,?,?,?)', [$name, $email, $hash($pw), $role]);
    DB::run('INSERT INTO profiles (user_id, country, age_group, headline, expertise, skills, organisation, digital_level, connectivity) VALUES (?,?,?,?,?,?,?,?,?)',
        [$id, $country, $age, $headline, $exp, $skills, $org, $role === 'participant' ? 'intermediate' : 'advanced', in_array($email, ['joseph@youthconnect.test', 'peter@youthconnect.test'], true) ? 'limited' : 'good']);
    $U[$email] = $id;
}

// ---- Categories & courses -----------------------------------------------------------
$content = require __DIR__ . '/seed_content.php';
$catIds = [];
foreach (['AI & Digital Literacy', 'Critical Thinking', 'Intercultural Communication', 'Collaboration & Teamwork', 'Employability & Career Skills', 'Entrepreneurship', 'Data Skills'] as $cn) {
    $catIds[$cn] = DB::insert('INSERT INTO categories (name, slug) VALUES (?,?)', [$cn, slugify($cn)]);
}
$C = [];   // slug => course id
foreach ($content as $c) {
    $slug = slugify($c['title']);
    $cid = DB::insert('INSERT INTO courses (category_id, owner_id, title, slug, summary, description, what_you_learn, skills, level, hours_per_week, language, color, status, pass_mark)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,\'published\',70)',
        [$catIds[$c['category']], $U[$c['owner']], $c['title'], $slug, $c['summary'], $c['description'], $c['learn'], $c['skills'], $c['level'], $c['hours'], 'English', $c['color']]);
    $C[$slug] = $cid;
    foreach ($c['weeks'] as $i => $w) {
        $wk = $i + 1;
        $mid = DB::insert('INSERT INTO modules (course_id, week_no, title, description, hours_estimate) VALUES (?,?,?,?,?)', [$cid, $wk, $w['title'], $w['desc'], $w['hours']]);
        foreach ($w['lessons'] as $pos => [$type, $title, $mins, $body, $preview]) {
            DB::run('INSERT INTO lessons (module_id, position, type, title, content, minutes, is_preview) VALUES (?,?,?,?,?,?,?)', [$mid, $pos + 1, $type, $title, $body, $mins, $preview ? 1 : 0]);
        }
        $qid = DB::insert('INSERT INTO quizzes (module_id, title, pass_mark, minutes) VALUES (?,?,70,10)', [$mid, "Week $wk Quiz"]);
        foreach ($w['quiz'] as $qp => [$qtext, $opts, $correct, $why]) {
            $q = DB::insert('INSERT INTO questions (quiz_id, position, body, explanation) VALUES (?,?,?,?)', [$qid, $qp + 1, $qtext, $why]);
            foreach ($opts as $oi => $ot) DB::run('INSERT INTO options (question_id, body, is_correct) VALUES (?,?,?)', [$q, $ot, $oi === $correct ? 1 : 0]);
        }
        if (!empty($w['assignment'])) {
            DB::run('INSERT INTO assignments (module_id, title, instructions) VALUES (?,?,?)', [$mid, $w['assignment'][0], $w['assignment'][1]]);
        }
    }
}
$slugAi = 'ai-literacy-for-everyday-life';
$slugTeam = 'teamwork-and-remote-collaboration';
$slugCrit = 'critical-thinking-and-spotting-misinformation';

// ---- Learner progress ---------------------------------------------------------------------
function finishCourse(int $userId, int $courseId, int $graderId): void
{
    DB::run('INSERT IGNORE INTO enrollments (user_id, course_id) VALUES (?,?)', [$userId, $courseId]);
    DB::run('INSERT IGNORE INTO lesson_progress (user_id, lesson_id) SELECT ?, l.id FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = ?', [$userId, $courseId]);
    foreach (DB::all('SELECT q.id FROM quizzes q JOIN modules m ON m.id = q.module_id WHERE m.course_id = ?', [$courseId]) as $q) {
        DB::run('INSERT INTO quiz_attempts (user_id, quiz_id, score, passed, answers) VALUES (?,?,?,?,?)', [$userId, $q['id'], 100, 1, '{}']);
    }
    foreach (DB::all('SELECT a.id FROM assignments a JOIN modules m ON m.id = a.module_id WHERE m.course_id = ?', [$courseId]) as $a) {
        DB::run('INSERT IGNORE INTO submissions (assignment_id, user_id, body, status, score, feedback, graded_by, graded_at) VALUES (?,?,?,\'graded\',88,?,?,NOW())',
            [$a['id'], $userId, 'Demo submission written for the seeded example learner.', 'Clear and practical. Good use of a real example. Next time, add one measurable result.', $graderId]);
    }
    Progress::recalc($userId, $courseId);
}
foreach (['brian', 'wanjiru', 'joseph', 'aline', 'moses', 'fatuma'] as $n) finishCourse($U["$n@youthconnect.test"], $C[$slugTeam], $U['grace@youthconnect.test']);
foreach (['brian', 'wanjiru'] as $n) finishCourse($U["$n@youthconnect.test"], $C[$slugAi], $U['grace@youthconnect.test']);

// Peter: part-way through the AI course (3 lessons done); Esther: just enrolled.
DB::run('INSERT INTO enrollments (user_id, course_id) VALUES (?,?)', [$U['peter@youthconnect.test'], $C[$slugAi]]);
DB::run('INSERT INTO lesson_progress (user_id, lesson_id) SELECT ?, l.id FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = ? ORDER BY l.id LIMIT 3', [$U['peter@youthconnect.test'], $C[$slugAi]]);
DB::run('INSERT INTO enrollments (user_id, course_id) VALUES (?,?)', [$U['esther@youthconnect.test'], $C[$slugCrit]]);

foreach ([
    ['brian', $slugTeam, 5, 'Practical and clear. The team agreement exercise helped our group a lot.'],
    ['wanjiru', $slugTeam, 5, 'Short lessons that work well on my phone.'],
    ['joseph', $slugTeam, 4, 'Good content. I would like more examples about remote work with weak internet.'],
    ['aline', $slugTeam, 5, 'The quizzes at the end of every week kept me focused.'],
    ['brian', $slugAi, 5, 'I finally understand what AI is, without any jargon.'],
    ['wanjiru', $slugAi, 4, 'Very useful tips on checking AI answers.'],
] as [$who, $slug, $r, $txt]) DB::run('INSERT INTO reviews (course_id, user_id, rating, comment) VALUES (?,?,?,?)', [$C[$slug], $U["$who@youthconnect.test"], $r, $txt]);

$firstWeekLesson = DB::val('SELECT l.id FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = ? ORDER BY m.week_no, l.position LIMIT 1', [$C[$slugAi]]);
DB::run('INSERT INTO discussion_posts (course_id, user_id, body) VALUES (?,?,?)', [$C[$slugAi], $U['brian@youthconnect.test'], 'My bank sends a warning when a payment looks unusual. I guess that is AI spotting a pattern. What did you find?']);
$post = DB::insert('INSERT INTO discussion_posts (course_id, user_id, body) VALUES (?,?,?)', [$C[$slugAi], $U['wanjiru@youthconnect.test'], 'My phone keyboard suggests Swahili and English words depending on what I type. Great example of AI learning my habits.']);
DB::run('INSERT INTO discussion_posts (course_id, user_id, parent_id, body) VALUES (?,?,?,?)', [$C[$slugAi], $U['grace@youthconnect.test'], $post, 'Exactly, Wanjiru. The keyboard learns from patterns in your typing. Remember that this is also why privacy settings matter.']);

// ---- Level Two: teams & projects ---------------------------------------------------------------
$T1 = public_id(); $T2 = public_id();
$t1 = DB::insert('INSERT INTO teams (public_id, name, description, looking_for, is_open, created_by) VALUES (?,?,?,?,1,?)',
    [$T1, 'AgriTech Bridge', 'A cross-border team building a mobile-friendly planting advisor for smallholder farmers.', 'An environmental scientist from Burundi or Ethiopia', $U['brian@youthconnect.test']]);
foreach ([['brian', 'lead', 'Agriculture'], ['wanjiru', 'member', 'Software development'], ['joseph', 'member', 'Data analysis'], ['aline', 'member', 'Environmental studies']] as [$n, $r, $d]) {
    DB::run('INSERT INTO team_members (team_id, user_id, role, discipline, status) VALUES (?,?,?,?,\'active\')', [$t1, $U["$n@youthconnect.test"], $r, $d]);
}
$t2 = DB::insert('INSERT INTO teams (public_id, name, description, looking_for, is_open, created_by) VALUES (?,?,?,?,1,?)',
    [$T2, 'Clean Water Data', 'Mapping borehole water quality in rural districts to help communities choose safe water sources.', 'A mobile developer', $U['moses@youthconnect.test']]);
foreach ([['moses', 'lead', 'Environmental studies'], ['fatuma', 'member', 'Data analysis']] as [$n, $r, $d]) {
    DB::run('INSERT INTO team_members (team_id, user_id, role, discipline, status) VALUES (?,?,?,?,\'active\')', [$t2, $U["$n@youthconnect.test"], $r, $d]);
}

$P1 = public_id(); $P2 = public_id();
$p1 = DB::insert('INSERT INTO projects (public_id, team_id, title, theme, summary, problem, solution, impact, status, version) VALUES (?,?,?,?,?,?,?,?,\'changes_requested\',4)',
    [$P1, $t1, 'Smallholder Planting Advisor', 'Agriculture & food security',
     'A low-data mobile web tool that tells smallholder farmers the best time to plant, using local weather and soil information.',
     'Smallholder farmers in Uganda, Kenya, Tanzania and Rwanda often plant too early or too late because rainfall has become less predictable, and advice from extension officers is hard to reach.',
     'We will combine farmer interviews (agriculture), a lightweight web app that works on basic smartphones (software), seasonal rainfall data analysis (data) and local environmental conditions (environment) into a simple planting calendar delivered in English and Swahili.',
     'We aim to reach 200 farmers in a pilot, reduce failed plantings, and measure the change in yield reported at the end of the season.']);
foreach ([['amina', 'mentor'], ['grace', 'facilitator'], ['samuel', 'stakeholder']] as [$n, $r]) DB::run('INSERT INTO project_staff (project_id, user_id, role) VALUES (?,?,?)', [$p1, $U["$n@youthconnect.test"], $r]);
DB::run('INSERT INTO feedback (project_id, author_id, kind, body) VALUES (?,?,\'mentor\',?)', [$p1, $U['amina@youthconnect.test'],
    'Strong idea and a well balanced team. Before approval please (1) say how you will reach farmers who do not have smartphones, (2) name the weather data source you will use, and (3) add a measurable target for the pilot. Then resubmit.']);
$m1 = DB::insert('INSERT INTO milestones (project_id, title, due_date, status, position) VALUES (?,?,?,?,1)', [$p1, 'Farmer interviews completed', date('Y-m-d', strtotime('+14 days')), 'done']);
$m2 = DB::insert('INSERT INTO milestones (project_id, title, due_date, status, position) VALUES (?,?,?,?,2)', [$p1, 'Prototype ready for testing', date('Y-m-d', strtotime('+45 days')), 'open']);
foreach ([
    ['Interview 10 farmers in Kabale and Mbarara', 'brian', $m1, 'done'], ['Draft the planting calendar logic', 'joseph', $m2, 'doing'],
    ['Build the mobile web prototype', 'wanjiru', $m2, 'todo'], ['Collect soil and rainfall notes', 'aline', $m2, 'todo'],
] as [$title, $who, $ms, $st]) DB::run('INSERT INTO tasks (project_id, milestone_id, title, assignee_id, status, due_date) VALUES (?,?,?,?,?,?)', [$p1, $ms, $title, $U["$who@youthconnect.test"], $st, date('Y-m-d', strtotime('+21 days'))]);
DB::run('INSERT INTO evidence (public_id, project_id, milestone_id, uploaded_by, title, kind, note) VALUES (?,?,?,?,?,\'note\',?)', [public_id(), $p1, $m1, $U['brian@youthconnect.test'], 'Summary of farmer interviews',
    'We interviewed 10 farmers. 8 of 10 said they plant by observing the first rains, and 6 lost a planting in the last two seasons because rain stopped early. All 10 have a basic phone; 4 have a smartphone.']);
foreach ([['wanjiru', 'Hi team, I started a simple prototype layout. It loads in under 100 KB so it works on slow phones.'], ['joseph', 'Great. I will share the rainfall dataset on Friday.'], ['aline', 'I can add notes about soil types for the highland districts.']] as [$who, $body])
    DB::run('INSERT INTO project_messages (project_id, user_id, body) VALUES (?,?,?)', [$p1, $U["$who@youthconnect.test"], $body]);
foreach (['brian', 'wanjiru', 'joseph', 'aline'] as $who) DB::run('INSERT INTO notifications (user_id, message, link) VALUES (?,?,?)',
    [$U["$who@youthconnect.test"], 'New feedback on "Smallholder Planting Advisor" from Amina Mwangi.', "projects/$P1?tab=feedback"]);

DB::insert('INSERT INTO projects (public_id, team_id, title, theme, summary, status) VALUES (?,?,?,?,?,\'draft\')',
    [$P2, $t2, 'Borehole Water Quality Map', 'Water & sanitation', 'A shared map showing which community boreholes have been tested and whether the water is safe to drink.']);

echo "Demo data loaded.\n";
echo "  admin@youthconnect.test / Admin@12345\n  grace@youthconnect.test, david@youthconnect.test / Facilitate@123\n";
echo "  amina@youthconnect.test / Mentor@12345\n  samuel@youthconnect.test / Stake@12345\n";
echo "  brian@, wanjiru@, joseph@, aline@, peter@, esther@, moses@, fatuma@youthconnect.test / Learn@12345\n";
echo "  CHANGE OR DELETE THESE ACCOUNTS before using the platform for real people.\n";
