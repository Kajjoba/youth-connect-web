<?php
namespace App\Controllers;

use App\Core\{Controller, DB, Audit};

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
