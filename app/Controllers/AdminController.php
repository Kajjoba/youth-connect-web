<?php
namespace App\Controllers;

use App\Core\{Controller, DB, Audit, Upload};

class AdminController extends Controller
{
    public function dashboard(): void
    {
        $s = [
            'users'       => DB::all('SELECT role, COUNT(*) n FROM users GROUP BY role'),
            'courses'     => DB::all('SELECT status, COUNT(*) n FROM courses GROUP BY status'),
            'enrolments'  => (int)DB::val('SELECT COUNT(*) FROM enrollments WHERE status <> \'dropped\''),
            'completions' => (int)DB::val('SELECT COUNT(*) FROM enrollments WHERE status = \'completed\''),
            'active7'     => (int)DB::val('SELECT COUNT(*) FROM users WHERE last_login_at > (NOW() - INTERVAL 7 DAY)'),
            'teams'       => (int)DB::val('SELECT COUNT(*) FROM teams'),
            'projects'    => DB::all('SELECT status, COUNT(*) n FROM projects GROUP BY status'),
            'countries'   => DB::all('SELECT country, COUNT(*) n FROM profiles WHERE country IS NOT NULL GROUP BY country ORDER BY n DESC'),
            'certs'       => (int)DB::val('SELECT COUNT(*) FROM certificates'),
        ];
        $this->view('admin/dashboard', ['s' => $s, 'title' => 'Admin dashboard']);
    }

    public function users(): void
    {
        $q = trim((string)($_GET['q'] ?? ''));
        $role = (string)($_GET['role'] ?? '');
        $where = []; $p = [];
        if ($q !== '') { $where[] = '(u.name LIKE ? OR u.email LIKE ?)'; $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%'; array_push($p, $like, $like); }
        if (in_array($role, ['participant', 'facilitator', 'mentor', 'stakeholder', 'admin'], true)) { $where[] = 'u.role = ?'; $p[] = $role; }
        $users = DB::all('SELECT u.id, u.name, u.email, u.role, u.status, u.level2_override, u.created_at, pr.country FROM users u LEFT JOIN profiles pr ON pr.user_id = u.id '
            . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY u.id DESC LIMIT 200', $p);
        $this->view('admin/users', compact('users', 'q', 'role') + ['title' => 'Users']);
    }

    private function tempPassword(): string
    {
        return substr(str_replace(['+', '/', '='], ['k', 'm', 'x'], base64_encode(random_bytes(9))), 0, 10) . '7a';
    }

    public function createUser(): void
    {
        $d = $this->validate(['name' => 'required|min:2|max:120', 'email' => 'required|email|max:190',
            'role' => 'required|in:participant,facilitator,mentor,stakeholder,admin'], 'admin/users');
        if ($d === null) return;
        if (DB::val('SELECT id FROM users WHERE email = ?', [strtolower($d['email'])])) { flash('error', 'An account with that email already exists.'); $this->redirect('admin/users'); return; }
        $pw = $this->tempPassword();
        $id = DB::tx(function () use ($d, $pw) {
            $id = DB::insert('INSERT INTO users (name, email, password_hash, role) VALUES (?,?,?,?)', [$d['name'], strtolower($d['email']), password_hash($pw, PASSWORD_DEFAULT), $d['role']]);
            DB::run('INSERT INTO profiles (user_id) VALUES (?)', [$id]);
            return $id;
        });
        Audit::log('user.created', 'user', $id, $d['role']);
        flash('success', "Account created for {$d['email']}. Temporary password (shown once): $pw");
        $this->redirect('admin/users');
    }

    public function updateUser(string $id): void
    {
        $u = DB::one('SELECT * FROM users WHERE id = ?', [(int)$id]);
        if (!$u) { $this->notFound(); return; }
        $d = $this->validate(['role' => 'required|in:participant,facilitator,mentor,stakeholder,admin', 'status' => 'required|in:active,suspended'], 'admin/users');
        if ($d === null) return;
        if ((int)$u['id'] === $this->uid() && ($d['role'] !== 'admin' || $d['status'] !== 'active')) {
            flash('error', 'You cannot remove your own admin access or suspend yourself.');
            $this->redirect('admin/users');
            return;
        }
        DB::run('UPDATE users SET role=?, status=?, level2_override=? WHERE id=?', [$d['role'], $d['status'], isset($_POST['level2_override']) ? 1 : 0, $u['id']]);
        if ($d['status'] === 'suspended') DB::run('DELETE FROM sessions WHERE data LIKE ?', ['%uid|i:' . (int)$u['id'] . ';%']);   // end their sessions now
        Audit::log('user.updated', 'user', $u['id'], "role={$d['role']} status={$d['status']}");
        flash('success', 'User updated.');
        $this->redirect('admin/users');
    }

    /**
     * Permanently delete a person. Their personal data goes with them; shared work is kept.
     * Refused for: yourself, the last admin, and anyone who owns courses or wrote feedback (suspend them instead).
     */
    public function deleteUser(string $id): void
    {
        $u = DB::one('SELECT id, name, email, role FROM users WHERE id = ?', [(int)$id]);
        if (!$u) { $this->notFound(); return; }
        $uid = (int)$u['id'];
        $back = 'admin/users';
        if ($uid === $this->uid()) { flash('error', 'You cannot delete your own account.'); $this->redirect($back); return; }
        if ($u['role'] === 'admin' && (int)DB::val('SELECT COUNT(*) FROM users WHERE role = \'admin\' AND status = \'active\' AND id <> ?', [$uid]) === 0) {
            flash('error', 'You cannot delete the last active admin.'); $this->redirect($back); return;
        }
        $courses = (int)DB::val('SELECT COUNT(*) FROM courses WHERE owner_id = ?', [$uid]);
        $feedback = (int)DB::val('SELECT COUNT(*) FROM feedback WHERE author_id = ?', [$uid]);
        if ($courses > 0 || $feedback > 0) {
            flash('error', "{$u['name']} owns $courses course(s) and wrote $feedback piece(s) of project feedback that other people rely on, so they cannot be deleted. Suspend them instead (set Status to suspended and Save).");
            $this->redirect($back);
            return;
        }
        // Teams they created: hand over to another active member; refuse if they are the only member.
        $teams = DB::all('SELECT id, name FROM teams WHERE created_by = ?', [$uid]);
        $handover = [];
        foreach ($teams as $t) {
            $next = DB::val('SELECT user_id FROM team_members WHERE team_id = ? AND user_id <> ? AND status = \'active\' ORDER BY (role = \'lead\') DESC, joined_at LIMIT 1', [$t['id'], $uid]);
            if (!$next) {
                flash('error', "{$u['name']} is the only member of the team \"{$t['name']}\". Remove or hand over that team first, or suspend them instead.");
                $this->redirect($back);
                return;
            }
            $handover[(int)$t['id']] = (int)$next;
        }
        $files = array_column(DB::all('SELECT file_path FROM evidence WHERE uploaded_by = ? AND file_path IS NOT NULL', [$uid]), 'file_path');
        DB::tx(function () use ($uid, $handover) {
            foreach ($handover as $teamId => $newOwner) DB::run('UPDATE teams SET created_by = ? WHERE id = ?', [$newOwner, $teamId]);
            DB::run('UPDATE tasks SET assignee_id = NULL WHERE assignee_id = ?', [$uid]);
            DB::run('UPDATE submissions SET graded_by = NULL WHERE graded_by = ?', [$uid]);
            DB::run('DELETE FROM project_messages WHERE user_id = ?', [$uid]);
            DB::run('DELETE FROM evidence WHERE uploaded_by = ?', [$uid]);
            DB::run('DELETE FROM users WHERE id = ?', [$uid]);    // cascades: profile, enrolments, progress, attempts, certificates, memberships, notifications...
        });
        foreach ($files as $rel) { $p = Upload::fullPath($rel); if ($p) @unlink($p); }
        DB::run('DELETE FROM sessions WHERE data LIKE ?', ['%uid|i:' . $uid . ';%']);   // end any open session
        Audit::log('user.deleted', 'user', $uid, "{$u['email']} ({$u['role']})");
        flash('success', "{$u['name']} ({$u['email']}) was deleted.");
        $this->redirect($back);
    }
    public function resetPassword(string $id): void
    {
        $u = DB::one('SELECT id, email FROM users WHERE id = ?', [(int)$id]);
        if (!$u) { $this->notFound(); return; }
        $pw = $this->tempPassword();
        DB::run('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($pw, PASSWORD_DEFAULT), $u['id']]);
        Audit::log('user.password_reset', 'user', $u['id']);
        flash('success', "New temporary password for {$u['email']} (shown once): $pw");
        $this->redirect('admin/users');
    }

    public function categories(): void
    {
        $rows = DB::all('SELECT cat.*, (SELECT COUNT(*) FROM courses c WHERE c.category_id = cat.id) AS courses FROM categories cat ORDER BY name');
        $this->view('admin/categories', ['rows' => $rows, 'title' => 'Categories']);
    }

    public function saveCategory(): void
    {
        $d = $this->validate(['name' => 'required|min:2|max:80'], 'admin/categories');
        if ($d === null) return;
        DB::run('INSERT IGNORE INTO categories (name, slug) VALUES (?,?)', [$d['name'], slugify($d['name'])]);
        flash('success', 'Category saved.');
        $this->redirect('admin/categories');
    }

    public function projects(): void
    {
        $rows = DB::all('SELECT p.public_id, p.title, p.status, t.name AS team,
                (SELECT GROUP_CONCAT(CONCAT(u.name, " (", ps.role, ")") SEPARATOR ", ") FROM project_staff ps JOIN users u ON u.id = ps.user_id WHERE ps.project_id = p.id) AS staff
            FROM projects p JOIN teams t ON t.id = p.team_id ORDER BY p.updated_at DESC LIMIT 200');
        $this->view('admin/projects', ['rows' => $rows, 'title' => 'All projects']);
    }

    public function audit(): void
    {
        $rows = DB::all('SELECT a.*, u.name FROM audit_log a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.id DESC LIMIT 200');
        $this->view('admin/audit', ['rows' => $rows, 'title' => 'Audit log']);
    }
}
