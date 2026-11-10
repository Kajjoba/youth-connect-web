<?php
namespace App\Controllers;

use App\Core\{Controller, DB, Policy, Audit, Notify};
use App\Services\{Level2, Lists};

class TeamController extends Controller
{
    private function team(string $pid): ?array
    {
        $t = preg_match('/^[a-f0-9]{16}$/', $pid) ? DB::one('SELECT * FROM teams WHERE public_id = ?', [$pid]) : null;
        if (!$t) $this->notFound();
        return $t;
    }

    private function gate(): bool
    {
        if (!Level2::eligible($this->user())) {
            flash('info', 'Level Two (Collaborative Exchange) opens after you complete ' . Level2::required() . ' Level One course(s). Keep learning, you are on your way.');
            $this->redirect('my-learning');
            return false;
        }
        return true;
    }

    public function index(): void
    {
        $u = $this->user();
        $eligible = Level2::eligible($u);
        $mine = DB::all('SELECT t.public_id, t.name, t.description, tm.role,
                (SELECT COUNT(*) FROM team_members x WHERE x.team_id = t.id AND x.status = \'active\') AS members,
                (SELECT COUNT(*) FROM projects p WHERE p.team_id = t.id) AS projects
            FROM team_members tm JOIN teams t ON t.id = tm.team_id WHERE tm.user_id = ? AND tm.status = \'active\'', [$u['id']]);
        $invites = DB::all('SELECT t.public_id, t.name, t.description FROM team_members tm JOIN teams t ON t.id = tm.team_id WHERE tm.user_id = ? AND tm.status = \'invited\'', [$u['id']]);
        $q = trim((string)($_GET['q'] ?? ''));
        $params = [$u['id']]; $w = '';
        if ($q !== '') { $w = ' AND (t.name LIKE ? OR t.looking_for LIKE ? OR t.description LIKE ?)'; $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%'; array_push($params, $like, $like, $like); }
        $open = DB::all("SELECT t.public_id, t.name, t.description, t.looking_for,
                (SELECT COUNT(*) FROM team_members x WHERE x.team_id = t.id AND x.status = 'active') AS members,
                (SELECT GROUP_CONCAT(DISTINCT pr.country SEPARATOR ', ') FROM team_members x JOIN profiles pr ON pr.user_id = x.user_id WHERE x.team_id = t.id AND x.status = 'active') AS countries
            FROM teams t WHERE t.is_open = 1 AND t.id NOT IN (SELECT team_id FROM team_members WHERE user_id = ?) $w ORDER BY t.id DESC LIMIT 30", $params);
        $done = Level2::completedCourses((int)$u['id']);
        $this->view('teams/index', compact('mine', 'invites', 'open', 'eligible', 'q', 'done') + ['need' => Level2::required(), 'title' => 'Collaborate']);
    }

    public function create(): void
    {
        if (!$this->gate()) return;
        $this->view('teams/new', ['disciplines' => Lists::DISCIPLINES, 'title' => 'Start a team']);
    }

    public function store(): void
    {
        if (!$this->gate()) return;
        $d = $this->validate(['name' => 'required|min:3|max:100', 'description' => 'max:500', 'looking_for' => 'max:255',
            'discipline' => 'required|in:' . Lists::csv(Lists::DISCIPLINES)], 'teams/new', ['discipline' => 'your discipline']);
        if ($d === null) return;
        $pid = public_id();
        DB::tx(function () use ($d, $pid) {
            $id = DB::insert('INSERT INTO teams (public_id, name, description, looking_for, is_open, created_by) VALUES (?,?,?,?,?,?)',
                [$pid, $d['name'], $d['description'], $d['looking_for'], isset($_POST['is_open']) ? 1 : 0, $this->uid()]);
            DB::run('INSERT INTO team_members (team_id, user_id, role, discipline, status) VALUES (?,?,\'lead\',?,\'active\')', [$id, $this->uid(), $d['discipline']]);
        });
        Audit::log('team.created', 'team', $pid);
        flash('success', 'Team created. Invite people from other countries and disciplines to join you.');
        $this->redirect("teams/$pid");
    }

    public function show(string $pid): void
    {
        $t = $this->team($pid);
        if (!$t) return;
        $role = Policy::teamRole((int)$t['id']);
        $u = $this->user();
        if ($role) {
            $members = DB::all('SELECT u.id, u.name, tm.role, tm.discipline, tm.joined_at, pr.country, pr.headline FROM team_members tm
                                JOIN users u ON u.id = tm.user_id LEFT JOIN profiles pr ON pr.user_id = u.id
                                WHERE tm.team_id = ? AND tm.status = \'active\' ORDER BY tm.role = \'lead\' DESC, tm.joined_at', [$t['id']]);
            $requests = $role === 'lead' ? DB::all('SELECT u.id, u.name, pr.country, pr.expertise, pr.skills FROM team_members tm JOIN users u ON u.id = tm.user_id
                                LEFT JOIN profiles pr ON pr.user_id = u.id WHERE tm.team_id = ? AND tm.status = \'requested\'', [$t['id']]) : [];
            $pending = $role === 'lead' ? DB::all('SELECT u.name FROM team_members tm JOIN users u ON u.id = tm.user_id WHERE tm.team_id = ? AND tm.status = \'invited\'', [$t['id']]) : [];
            $projects = DB::all('SELECT public_id, title, status, updated_at FROM projects WHERE team_id = ? ORDER BY id DESC', [$t['id']]);
            $this->view('teams/show', compact('t', 'role', 'members', 'requests', 'pending', 'projects') + ['disciplines' => Lists::DISCIPLINES, 'themes' => Lists::THEMES, 'title' => $t['name']]);
            return;
        }
        // Outsiders see only a public card: no member names, no projects.
        $my = DB::one('SELECT status FROM team_members WHERE team_id = ? AND user_id = ?', [$t['id'], $u['id']]);
        $card = DB::one('SELECT (SELECT COUNT(*) FROM team_members WHERE team_id = ? AND status = \'active\') AS members,
                (SELECT GROUP_CONCAT(DISTINCT pr.country SEPARATOR \', \') FROM team_members x JOIN profiles pr ON pr.user_id = x.user_id WHERE x.team_id = ? AND x.status = \'active\') AS countries', [$t['id'], $t['id']]);
        $this->view('teams/public', compact('t', 'my', 'card') + ['eligible' => Level2::eligible($u), 'disciplines' => Lists::DISCIPLINES, 'title' => $t['name']]);
    }

    private function size(int $teamId): int
    {
        return (int)DB::val('SELECT COUNT(*) FROM team_members WHERE team_id = ? AND status IN (\'active\',\'invited\')', [$teamId]);
    }

    public function invite(string $pid): void
    {
        $t = $this->team($pid);
        if (!$t) return;
        if (Policy::teamRole((int)$t['id']) !== 'lead') { $this->notFound(); return; }
        $d = $this->validate(['email' => 'required|email'], "teams/$pid");
        if ($d === null) return;
        $target = DB::one('SELECT id, name, role, level2_override FROM users WHERE email = ? AND status = \'active\'', [strtolower($d['email'])]);
        if (!$target || $target['role'] !== 'participant') { flash('error', 'We could not find a participant with that email. Ask them to register first.'); $this->redirect("teams/$pid"); return; }
        if (!Level2::eligible($target)) { flash('error', $target['name'] . ' has not yet unlocked Level Two.'); $this->redirect("teams/$pid"); return; }
        if ($this->size((int)$t['id']) >= (int)config('level2.max_team_size')) { flash('error', 'This team is full (' . config('level2.max_team_size') . ' members).'); $this->redirect("teams/$pid"); return; }
        $n = DB::run('INSERT IGNORE INTO team_members (team_id, user_id, role, status) VALUES (?,?,\'member\',\'invited\')', [$t['id'], $target['id']]);
        if ($n) Notify::to((int)$target['id'], $this->user()['name'] . ' invited you to join the team "' . $t['name'] . '".', 'teams/' . $pid);
        flash($n ? 'success' : 'info', $n ? 'Invitation sent to ' . $target['name'] . '.' : $target['name'] . ' is already linked to this team.');
        $this->redirect("teams/$pid");
    }

    public function requestJoin(string $pid): void
    {
        $t = $this->team($pid);
        if (!$t) return;
        if (!$this->gate()) return;
        if (!$t['is_open']) { flash('error', 'This team accepts members by invitation only.'); $this->redirect('teams'); return; }
        $d = $this->validate(['discipline' => 'required|in:' . Lists::csv(Lists::DISCIPLINES)], "teams/$pid", ['discipline' => 'your discipline']);
        if ($d === null) return;
        $n = DB::run('INSERT IGNORE INTO team_members (team_id, user_id, role, discipline, status) VALUES (?,?,\'member\',?,\'requested\')', [$t['id'], $this->uid(), $d['discipline']]);
        if ($n) {
            $lead = DB::val('SELECT user_id FROM team_members WHERE team_id = ? AND role = \'lead\' AND status = \'active\' LIMIT 1', [$t['id']]);
            if ($lead) Notify::to((int)$lead, $this->user()['name'] . ' asked to join "' . $t['name'] . '".', 'teams/' . $pid);
        }
        flash('success', 'Request sent. The team lead will respond.');
        $this->redirect("teams/$pid");
    }

    public function respond(string $pid): void
    {
        $t = $this->team($pid);
        if (!$t) return;
        $accept = ($_POST['action'] ?? '') === 'accept';
        $target = (int)($_POST['user_id'] ?? 0) ?: $this->uid();
        $isSelf = $target === $this->uid();
        $row = DB::one('SELECT status FROM team_members WHERE team_id = ? AND user_id = ?', [$t['id'], $target]);
        if (!$row) { $this->notFound(); return; }
        if ($isSelf && $row['status'] === 'invited') {
            // I am answering my own invitation
        } elseif (!$isSelf && $row['status'] === 'requested' && Policy::teamRole((int)$t['id']) === 'lead') {
            // The lead is answering someone's request
        } else { $this->notFound(); return; }

        if ($accept) {
            $active = (int)DB::val('SELECT COUNT(*) FROM team_members WHERE team_id = ? AND status = \'active\'', [$t['id']]);
            if ($active >= (int)config('level2.max_team_size')) { flash('error', 'This team is full.'); $this->redirect("teams/$pid"); return; }
            DB::run('UPDATE team_members SET status = \'active\', joined_at = NOW() WHERE team_id = ? AND user_id = ?', [$t['id'], $target]);
            Notify::to($isSelf ? (int)$t['created_by'] : $target, $isSelf ? $this->user()['name'] . ' joined "' . $t['name'] . '".' : 'You are now a member of "' . $t['name'] . '".', 'teams/' . $pid);
            flash('success', $isSelf ? 'Welcome to the team!' : 'Member added.');
        } else {
            DB::run('DELETE FROM team_members WHERE team_id = ? AND user_id = ?', [$t['id'], $target]);
            flash('info', 'Declined.');
        }
        $this->redirect($isSelf && !$accept ? 'teams' : "teams/$pid");
    }

    public function updateMember(string $pid): void
    {
        $t = $this->team($pid);
        if (!$t) return;
        $role = Policy::teamRole((int)$t['id']);
        if (!$role) { $this->notFound(); return; }
        $target = (int)($_POST['user_id'] ?? 0);
        $action = $_POST['action'] ?? '';
        $m = DB::one('SELECT * FROM team_members WHERE team_id = ? AND user_id = ? AND status = \'active\'', [$t['id'], $target]);
        if (!$m) { $this->notFound(); return; }
        if ($action === 'discipline') {
            if ($target !== $this->uid() && $role !== 'lead') { $this->notFound(); return; }
            $d = $_POST['discipline'] ?? '';
            if (in_array($d, Lists::DISCIPLINES, true)) DB::run('UPDATE team_members SET discipline = ? WHERE team_id = ? AND user_id = ?', [$d, $t['id'], $target]);
            flash('success', 'Discipline updated.');
        } elseif ($role === 'lead' && $action === 'remove' && $target !== $this->uid()) {
            DB::run('DELETE FROM team_members WHERE team_id = ? AND user_id = ?', [$t['id'], $target]);
            Notify::to($target, 'You were removed from the team "' . $t['name'] . '".');
            flash('success', 'Member removed.');
        } elseif ($role === 'lead' && $action === 'make_lead' && $target !== $this->uid()) {
            DB::tx(function () use ($t, $target) {
                DB::run('UPDATE team_members SET role = \'member\' WHERE team_id = ? AND user_id = ?', [$t['id'], $this->uid()]);
                DB::run('UPDATE team_members SET role = \'lead\' WHERE team_id = ? AND user_id = ?', [$t['id'], $target]);
            });
            flash('success', 'Leadership transferred.');
        } else { $this->notFound(); return; }
        $this->redirect("teams/$pid");
    }

    public function leave(string $pid): void
    {
        $t = $this->team($pid);
        if (!$t) return;
        $role = Policy::teamRole((int)$t['id']);
        if (!$role) { $this->notFound(); return; }
        $others = (int)DB::val('SELECT COUNT(*) FROM team_members WHERE team_id = ? AND status = \'active\' AND user_id <> ?', [$t['id'], $this->uid()]);
        if ($role === 'lead' && $others > 0) { flash('error', 'Transfer leadership to another member before you leave.'); $this->redirect("teams/$pid"); return; }
        DB::run('DELETE FROM team_members WHERE team_id = ? AND user_id = ?', [$t['id'], $this->uid()]);
        flash('info', 'You left the team.');
        $this->redirect('teams');
    }
}
