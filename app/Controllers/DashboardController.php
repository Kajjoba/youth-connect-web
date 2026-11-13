<?php
namespace App\Controllers;

use App\Core\{Controller, DB, Audit, Auth, Session, Throttle, Validator};
use App\Services\{Progress, Level2, Lists};

class DashboardController extends Controller
{
    public function index(): void
    {
        $u = $this->user();
        if ($u['role'] !== 'participant') { $this->redirect(Lists::homeFor($u['role'])); return; }
        $rows = DB::all('SELECT e.*, c.title, c.slug, c.color, c.hours_per_week, cat.name AS category
                         FROM enrollments e JOIN courses c ON c.id = e.course_id JOIN categories cat ON cat.id = c.category_id
                         WHERE e.user_id = ? AND e.status <> \'dropped\' ORDER BY e.status, e.enrolled_at DESC', [$u['id']]);
        foreach ($rows as &$r) $r['progress'] = Progress::summary((int)$u['id'], (int)$r['course_id']);
        unset($r);
        $certs = DB::all('SELECT code, title, kind, issued_at, course_id FROM certificates WHERE user_id = ? ORDER BY issued_at DESC', [$u['id']]);
        $certByCourse = [];
        foreach ($certs as $ct) if ($ct['kind'] === 'course') $certByCourse[(int)$ct['course_id']] = $ct['code'];
        $badges = DB::all('SELECT b.name, b.description, b.icon, ub.awarded_at FROM user_badges ub JOIN badges b ON b.id = ub.badge_id WHERE ub.user_id = ?', [$u['id']]);
        $level2 = ['eligible' => Level2::eligible($u), 'done' => Level2::completedCourses((int)$u['id']), 'need' => Level2::required()];
        $myTeams = DB::all('SELECT t.public_id, t.name FROM team_members tm JOIN teams t ON t.id = tm.team_id WHERE tm.user_id = ? AND tm.status = \'active\'', [$u['id']]);
        $invites = (int)DB::val('SELECT COUNT(*) FROM team_members WHERE user_id = ? AND status = \'invited\'', [$u['id']]);
        $suggested = \App\Services\Courses::cards('c.id NOT IN (SELECT course_id FROM enrollments WHERE user_id = ?)', [$u['id']], 'enrolled DESC', 4);
        $this->view('dashboard/index', compact('rows', 'certs', 'certByCourse', 'badges', 'level2', 'myTeams', 'invites', 'suggested') + ['title' => 'My learning']);
    }

    public function profile(): void
    {
        $p = DB::one('SELECT u.name, u.email, p.* FROM users u LEFT JOIN profiles p ON p.user_id = u.id WHERE u.id = ?', [$this->uid()]);
        $this->view('dashboard/profile', ['p' => $p, 'welcome' => isset($_GET['welcome']), 'title' => 'My profile', 'countries' => Lists::COUNTRIES]);
    }

    /** Change my own password: needs the current password, is rate-limited, and the session id is renewed. */
    public function changePassword(): void
    {
        $uid = $this->uid();
        $key = 'pwchange:' . $uid;
        $v = Validator::make($_POST, [
            'current_password' => 'required',
            'new_password' => 'required|min:' . config('security.password_min') . '|max:72|strongpass|confirmed:new_password_confirmation',
        ], ['current_password' => 'your current password', 'new_password' => 'the new password']);
        if ($v->fails()) {
            Session::withErrors($v->errors(), []);   // never keep passwords in the session
            flash('error', 'Please check the password fields and try again.');
            $this->redirect('profile#password');
            return;
        }
        if (Throttle::tooMany($key, 5, (int)config('security.login_window_minutes'))) {
            flash('error', 'Too many attempts. For your security, please wait a few minutes before trying again.');
            $this->redirect('profile#password');
            return;
        }
        $hash = (string)DB::val('SELECT password_hash FROM users WHERE id = ?', [$uid]);
        if (!password_verify((string)$_POST['current_password'], $hash)) {
            Throttle::hit($key);
            Audit::log('password.change_failed', 'user', $uid);
            Session::withErrors(['current_password' => 'That is not your current password.'], []);
            flash('error', 'Your password was not changed.');
            $this->redirect('profile#password');
            return;
        }
        if (password_verify((string)$_POST['new_password'], $hash)) {
            Session::withErrors(['new_password' => 'Choose a password you have not used just now.'], []);
            flash('error', 'Your password was not changed.');
            $this->redirect('profile#password');
            return;
        }
        DB::run('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash((string)$_POST['new_password'], PASSWORD_DEFAULT), $uid]);
        Throttle::clear($key);
        Auth::login($uid);   // fresh session id after a credential change
        Audit::log('password.changed', 'user', $uid, null, $uid);
        flash('success', 'Your password has been changed.');
        $this->redirect('profile#password');
    }
    public function saveProfile(): void
    {
        $data = $this->validate([
            'name' => 'required|min:2|max:120',
            'country' => 'required|in:' . Lists::csv(Lists::COUNTRIES),
            'age_group' => 'in:' . Lists::csv(Lists::AGE_GROUPS),
            'headline' => 'max:160', 'bio' => 'max:1000', 'expertise' => 'max:160',
            'skills' => 'max:255', 'interests' => 'max:255', 'organisation' => 'max:160',
            'digital_level' => 'required|in:beginner,intermediate,advanced',
            'connectivity' => 'required|in:good,limited,very_limited',
        ], 'profile');
        if ($data === null) return;
        $lite = isset($_POST['lite_mode']) ? 1 : 0;
        DB::tx(function () use ($data, $lite) {
            DB::run('UPDATE users SET name = ? WHERE id = ?', [$data['name'], $this->uid()]);
            DB::run('INSERT INTO profiles (user_id, country, age_group, headline, bio, expertise, skills, interests, organisation, digital_level, connectivity, lite_mode)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
                     ON DUPLICATE KEY UPDATE country=VALUES(country), age_group=VALUES(age_group), headline=VALUES(headline), bio=VALUES(bio),
                       expertise=VALUES(expertise), skills=VALUES(skills), interests=VALUES(interests), organisation=VALUES(organisation),
                       digital_level=VALUES(digital_level), connectivity=VALUES(connectivity), lite_mode=VALUES(lite_mode)',
                [$this->uid(), $data['country'], $data['age_group'], $data['headline'], $data['bio'], $data['expertise'], $data['skills'],
                 $data['interests'], $data['organisation'], $data['digital_level'], $data['connectivity'], $lite]);
        });
        setcookie('ycp_lite', $lite ? '1' : '0', ['expires' => time() + 86400 * 365, 'path' => '/', 'samesite' => 'Lax', 'httponly' => true]);
        Audit::log('profile.updated', 'user', $this->uid());
        flash('success', 'Profile saved.');
        $this->redirect(isset($_POST['welcome']) ? 'courses' : 'profile');
    }

    public function notifications(): void
    {
        $items = DB::all('SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT 50', [$this->uid()]);
        $this->view('dashboard/notifications', ['items' => $items, 'title' => 'Notifications']);
    }

    public function markRead(): void
    {
        DB::run('UPDATE notifications SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL', [$this->uid()]);
        $this->redirect('notifications');
    }
}
