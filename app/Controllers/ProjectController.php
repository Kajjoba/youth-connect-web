<?php
namespace App\Controllers;

use App\Core\{Controller, DB, Policy, Audit, Notify, Upload, Throttle, Request};
use App\Services\{Level2, Lists, Recognition};

/**
 * Level Two workspace. EVERY action starts with load(): the project is fetched through Policy::project(),
 * which verifies that the signed-in user is on the team, assigned staff, or an admin. A guessed or edited
 * identifier therefore yields "not found", never another team's data.
 */
class ProjectController extends Controller
{
    private function load(string $pid): ?array
    {
        $p = Policy::project($pid);
        if (!$p) $this->notFound();
        return $p;
    }

    /** Team-only actions (staff may read and advise but not edit the team's work). */
    private function loadTeam(string $pid): ?array
    {
        $p = $this->load($pid);
        if ($p && !Policy::isTeam($p)) { $this->forbidden('Only team members can change the team\'s project work.'); return null; }
        return $p;
    }

    /** Any change visible to others bumps the version so pulse/ETag checks notice it. */
    private function touch(int $projectId): void
    {
        DB::run('UPDATE projects SET version = version + 1 WHERE id = ?', [$projectId]);
    }

    public function store(string $tpid): void
    {
        $t = preg_match('/^[a-f0-9]{16}$/', $tpid) ? DB::one('SELECT * FROM teams WHERE public_id = ?', [$tpid]) : null;
        if (!$t || !Policy::teamRole((int)$t['id'])) { $this->notFound(); return; }
        $d = $this->validate(['title' => 'required|min:5|max:160', 'theme' => 'required|in:' . Lists::csv(Lists::THEMES),
            'summary' => 'required|min:20|max:500'], "teams/$tpid", ['theme' => 'a theme', 'summary' => 'a short summary']);
        if ($d === null) return;
        $pid = public_id();
        DB::run('INSERT INTO projects (public_id, team_id, title, theme, summary) VALUES (?,?,?,?,?)', [$pid, $t['id'], $d['title'], $d['theme'], $d['summary']]);
        Audit::log('project.created', 'project', $pid);
        flash('success', 'Project created. Fill in the problem, solution and impact, then submit your proposal for mentor review.');
        $this->redirect("projects/$pid");
    }

    public function show(string $pid): void
    {
        $p = $this->load($pid);
        if (!$p) return;
        $tab = $_GET['tab'] ?? 'overview';
        if (!in_array($tab, ['overview', 'work', 'evidence', 'feedback', 'chat', 'assessment'], true)) $tab = 'overview';
        $id = (int)$p['id'];
        $d = ['p' => $p, 'tab' => $tab, 'title' => $p['title']];

        $d['members'] = DB::all('SELECT u.id, u.name, tm.role, tm.discipline, pr.country FROM team_members tm JOIN users u ON u.id = tm.user_id
                                 LEFT JOIN profiles pr ON pr.user_id = u.id WHERE tm.team_id = ? AND tm.status = \'active\' ORDER BY tm.role = \'lead\' DESC', [$p['team_id']]);
        $d['staff'] = DB::all('SELECT u.name, ps.role FROM project_staff ps JOIN users u ON u.id = ps.user_id WHERE ps.project_id = ? ORDER BY ps.role', [$id]);
        $d['counts'] = [
            'feedback' => (int)DB::val('SELECT COUNT(*) FROM feedback WHERE project_id = ?', [$id]),
            'evidence' => (int)DB::val('SELECT COUNT(*) FROM evidence WHERE project_id = ?', [$id]),
            'tasks'    => (int)DB::val('SELECT COUNT(*) FROM tasks WHERE project_id = ?', [$id]),
            'tasks_done' => (int)DB::val('SELECT COUNT(*) FROM tasks WHERE project_id = ? AND status = \'done\'', [$id]),
        ];
        $d['countries'] = array_values(array_unique(array_filter(array_column($d['members'], 'country'))));

        if ($tab === 'work') {
            $d['milestones'] = DB::all('SELECT * FROM milestones WHERE project_id = ? ORDER BY position, id', [$id]);
            $d['tasks'] = DB::all('SELECT t.*, u.name AS assignee FROM tasks t LEFT JOIN users u ON u.id = t.assignee_id WHERE t.project_id = ? ORDER BY t.status = \'done\', t.due_date IS NULL, t.due_date, t.id', [$id]);
        } elseif ($tab === 'evidence') {
            $d['evidence'] = DB::all('SELECT e.*, u.name AS by_name, m.title AS milestone FROM evidence e JOIN users u ON u.id = e.uploaded_by
                                      LEFT JOIN milestones m ON m.id = e.milestone_id WHERE e.project_id = ? ORDER BY e.id DESC', [$id]);
            $d['milestones'] = DB::all('SELECT id, title FROM milestones WHERE project_id = ? ORDER BY position, id', [$id]);
        } elseif ($tab === 'feedback') {
            $d['feedback'] = DB::all('SELECT f.*, u.name FROM feedback f JOIN users u ON u.id = f.author_id WHERE f.project_id = ? ORDER BY f.id DESC', [$id]);
        } elseif ($tab === 'chat') {
            $d['messages'] = array_reverse(DB::all('SELECT m.id, m.body, m.created_at, m.user_id, u.name, u.role FROM project_messages m JOIN users u ON u.id = m.user_id
                                                    WHERE m.project_id = ? ORDER BY m.id DESC LIMIT 50', [$id]));
        } elseif ($tab === 'assessment') {
            $d['assessment'] = DB::one('SELECT a.*, u.name AS assessor FROM assessments a JOIN users u ON u.id = a.assessor_id WHERE a.project_id = ?', [$id]);
            $d['validations'] = DB::all('SELECT v.*, u.name FROM validations v JOIN users u ON u.id = v.stakeholder_id WHERE v.project_id = ?', [$id]);
            $d['my_validation'] = DB::one('SELECT * FROM validations WHERE project_id = ? AND stakeholder_id = ?', [$id, $this->uid()]);
        } else {
            if ($p['role'] === 'admin' || $p['role'] === 'facilitator') {
                $d['assignable'] = DB::all('SELECT id, name, email, role FROM users WHERE role IN (\'mentor\',\'facilitator\',\'stakeholder\') AND status = \'active\' ORDER BY role, name');
            }
        }
        $d['themes'] = Lists::THEMES;
        $this->view('projects/show', $d);
    }

    public function update(string $pid): void
    {
        $p = $this->loadTeam($pid);
        if (!$p) return;
        if (!in_array($p['status'], ['draft', 'changes_requested', 'approved'], true)) {
            flash('info', 'The project is under review, so its description is locked for now.'); $this->redirect("projects/$pid"); return;
        }
        $d = $this->validate(['title' => 'required|min:5|max:160', 'theme' => 'required|in:' . Lists::csv(Lists::THEMES),
            'summary' => 'required|min:20|max:500', 'problem' => 'max:3000', 'solution' => 'max:3000', 'impact' => 'max:3000'], "projects/$pid");
        if ($d === null) return;
        DB::run('UPDATE projects SET title=?, theme=?, summary=?, problem=?, solution=?, impact=?, version = version + 1 WHERE id=?',
            [$d['title'], $d['theme'], $d['summary'], $d['problem'], $d['solution'], $d['impact'], $p['id']]);
        flash('success', 'Project saved.');
        $this->redirect("projects/$pid");
    }

    // ---- lifecycle -----------------------------------------------------------

    /** Status changes are atomic: UPDATE ... WHERE status = expected, so two clicks can never double-apply. */
    private function move(array $p, string $from, string $to, string $note): bool
    {
        $n = DB::run('UPDATE projects SET status = ?, version = version + 1 WHERE id = ? AND status = ?', [$to, $p['id'], $from]);
        if ($n) {
            Audit::log('project.' . $to, 'project', $p['public_id'], $note);
            Notify::project((int)$p['id'], $note, 'projects/' . $p['public_id'], $this->uid(), true);
        }
        return (bool)$n;
    }

    public function transition(string $pid): void
    {
        $p = $this->load($pid);
        if (!$p) return;
        $act = $_POST['action'] ?? '';
        $back = "projects/$pid";
        $ok = false; $err = null;
        $id = (int)$p['id'];

        switch ($act) {
            case 'submit_proposal':
                if ($p['role'] !== 'lead') { $this->forbidden('Only the team lead can submit the proposal.'); return; }
                $members = (int)DB::val('SELECT COUNT(*) FROM team_members WHERE team_id = ? AND status = \'active\'', [$p['team_id']]);
                if ($members < 2) $err = 'A collaborative project needs at least 2 team members.';
                elseif (mb_strlen((string)$p['problem']) < 30 || mb_strlen((string)$p['solution']) < 30 || mb_strlen((string)$p['impact']) < 30) $err = 'Describe the problem, your solution and the expected impact (at least a couple of sentences each) before submitting.';
                elseif (!in_array($p['status'], ['draft', 'changes_requested'], true)) $err = 'The proposal was already submitted.';
                else $ok = $this->move($p, $p['status'], 'proposed', 'Proposal "' . $p['title'] . '" was submitted for review.');
                break;

            case 'approve':
            case 'request_changes':
                if (!Policy::canAdvise($p)) { $this->forbidden(); return; }
                $comment = trim((string)($_POST['comment'] ?? ''));
                if ($act === 'request_changes' && mb_strlen($comment) < 10) { $err = 'Please explain what should change (at least 10 characters).'; break; }
                $ok = DB::tx(function () use ($p, $act, $comment, $id) {
                    $moved = $this->move($p, 'proposed', $act === 'approve' ? 'approved' : 'changes_requested',
                        $act === 'approve' ? 'Your proposal "' . $p['title'] . '" was approved. You can start work.' : 'Changes were requested on "' . $p['title'] . '".');
                    if ($moved && $comment !== '') {
                        DB::run('INSERT INTO feedback (project_id, author_id, kind, body) VALUES (?,?,?,?)', [$id, $this->uid(), $this->feedbackKind($p), $comment]);
                    }
                    return $moved;
                });
                if (!$ok) $err = 'This proposal is not waiting for review.';
                break;

            case 'submit_final':
                if ($p['role'] !== 'lead') { $this->forbidden('Only the team lead can submit the final project.'); return; }
                if (!DB::val('SELECT COUNT(*) FROM evidence WHERE project_id = ?', [$id])) $err = 'Add at least one piece of evidence (file, link or note) before final submission.';
                else $ok = $this->move($p, 'approved', 'submitted', 'The team submitted "' . $p['title'] . '" for assessment.');
                if (!$ok && !$err) $err = 'The project must be approved before final submission.';
                break;

            case 'complete':
                if (!Policy::canFinalize($p)) { $this->forbidden(); return; }
                $needsStake = (int)DB::val('SELECT COUNT(*) FROM project_staff WHERE project_id = ? AND role = \'stakeholder\'', [$id]);
                $from = $p['status'] === 'validated' ? 'validated' : ($p['status'] === 'assessed' && !$needsStake ? 'assessed' : null);
                if (!$from) { $err = 'The project must be assessed' . ($needsStake ? ' and validated by a stakeholder' : '') . ' before it can be completed.'; break; }
                $ok = $this->move($p, $from, 'completed', 'Project "' . $p['title'] . '" is complete. Well done!');
                if ($ok) Recognition::award($p);
                break;

            default:
                $this->notFound();
                return;
        }
        if ($ok) flash('success', 'Done. The team has been notified.');
        elseif ($err) flash('error', $err);
        else flash('error', 'That step is not available right now.');
        $this->redirect($back);
    }

    private function feedbackKind(array $p): string
    {
        return in_array($p['role'], ['mentor', 'stakeholder'], true) ? $p['role'] : 'facilitator';
    }

    // ---- milestones & tasks -------------------------------------------------

    public function addMilestone(string $pid): void
    {
        $p = $this->loadTeam($pid);
        if (!$p) return;
        $d = $this->validate(['title' => 'required|min:3|max:160', 'due_date' => 'date'], "projects/$pid?tab=work");
        if ($d === null) return;
        $pos = (int)DB::val('SELECT COALESCE(MAX(position),0)+1 FROM milestones WHERE project_id = ?', [$p['id']]);
        DB::run('INSERT INTO milestones (project_id, title, due_date, position) VALUES (?,?,?,?)', [$p['id'], $d['title'], $d['due_date'], $pos]);
        $this->touch((int)$p['id']);
        $this->redirect("projects/$pid?tab=work");
    }

    public function toggleMilestone(string $pid, string $id): void
    {
        $p = $this->loadTeam($pid);
        if (!$p) return;
        // project_id in the WHERE clause: a milestone id from another project matches nothing.
        $n = DB::run('UPDATE milestones SET status = IF(status = \'done\', \'open\', \'done\') WHERE id = ? AND project_id = ?', [(int)$id, $p['id']]);
        if (!$n) { $this->notFound(); return; }
        $this->touch((int)$p['id']);
        $this->redirect("projects/$pid?tab=work");
    }

    private function validAssignee(array $p, $uid): bool
    {
        return !$uid || (bool)DB::val('SELECT 1 FROM team_members WHERE team_id = ? AND user_id = ? AND status = \'active\'', [$p['team_id'], (int)$uid]);
    }

    private function validMilestone(array $p, $mid): bool
    {
        return !$mid || (bool)DB::val('SELECT 1 FROM milestones WHERE id = ? AND project_id = ?', [(int)$mid, $p['id']]);
    }

    public function addTask(string $pid): void
    {
        $p = $this->loadTeam($pid);
        if (!$p) return;
        $d = $this->validate(['title' => 'required|min:3|max:200', 'assignee_id' => 'int', 'milestone_id' => 'int', 'due_date' => 'date'], "projects/$pid?tab=work");
        if ($d === null) return;
        if (!$this->validAssignee($p, $d['assignee_id']) || !$this->validMilestone($p, $d['milestone_id'])) { $this->notFound(); return; }
        DB::run('INSERT INTO tasks (project_id, milestone_id, title, assignee_id, due_date) VALUES (?,?,?,?,?)',
            [$p['id'], $d['milestone_id'] ?: null, $d['title'], $d['assignee_id'] ?: null, $d['due_date']]);
        if ($d['assignee_id'] && (int)$d['assignee_id'] !== $this->uid()) Notify::to((int)$d['assignee_id'], 'New task for you: ' . $d['title'], "projects/$pid?tab=work");
        $this->touch((int)$p['id']);
        $this->redirect("projects/$pid?tab=work");
    }

    public function updateTask(string $pid, string $id): void
    {
        $p = $this->loadTeam($pid);
        if (!$p) return;
        $t = DB::one('SELECT * FROM tasks WHERE id = ? AND project_id = ?', [(int)$id, $p['id']]);
        if (!$t) { $this->notFound(); return; }
        $status = $_POST['status'] ?? $t['status'];
        if (!in_array($status, ['todo', 'doing', 'done'], true)) { $this->notFound(); return; }
        $assignee = array_key_exists('assignee_id', $_POST) ? ((int)$_POST['assignee_id'] ?: null) : $t['assignee_id'];
        if (!$this->validAssignee($p, $assignee)) { $this->notFound(); return; }
        DB::run('UPDATE tasks SET status = ?, assignee_id = ? WHERE id = ?', [$status, $assignee, $t['id']]);
        $this->touch((int)$p['id']);
        if (Request::isAjax()) { $this->json(['ok' => true]); return; }
        $this->redirect("projects/$pid?tab=work");
    }

    // ---- evidence ------------------------------------------------------------

    public function addEvidence(string $pid): void
    {
        $p = $this->loadTeam($pid);
        if (!$p) return;
        if ($p['status'] === 'completed') { flash('info', 'This project is complete.'); $this->redirect("projects/$pid?tab=evidence"); return; }
        $back = "projects/$pid?tab=evidence";
        $kind = $_POST['kind'] ?? '';
        $rules = ['title' => 'required|min:3|max:160', 'kind' => 'required|in:file,link,note', 'milestone_id' => 'int'];
        if ($kind === 'link') $rules['url'] = 'required|url|max:400';
        if ($kind === 'note') $rules['note'] = 'required|min:10|max:5000';
        $d = $this->validate($rules, $back);
        if ($d === null) return;
        if (!$this->validMilestone($p, $d['milestone_id'])) { $this->notFound(); return; }
        $file = null;
        if ($d['kind'] === 'file') {
            $file = Upload::store($_FILES['file'] ?? []);
            if (is_string($file)) { flash('error', $file); $this->redirect($back); return; }
        }
        DB::run('INSERT INTO evidence (public_id, project_id, milestone_id, uploaded_by, title, kind, file_path, original_name, mime, size_bytes, url, note) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
            [public_id(), $p['id'], $d['milestone_id'] ?: null, $this->uid(), $d['title'], $d['kind'],
             $file['path'] ?? null, $file['name'] ?? null, $file['mime'] ?? null, $file['size'] ?? null, $d['url'] ?? null, $d['note'] ?? null]);
        $this->touch((int)$p['id']);
        Notify::project((int)$p['id'], $this->user()['name'] . ' added evidence: ' . $d['title'], "projects/$pid?tab=evidence", $this->uid(), true);
        flash('success', 'Evidence added.');
        $this->redirect($back);
    }

    public function downloadEvidence(string $pid, string $eid): void
    {
        $p = $this->load($pid);                       // authorisation first
        if (!$p) return;
        if (!preg_match('/^[a-f0-9]{16}$/', $eid)) { $this->notFound(); return; }
        // The evidence must belong to THIS project: an id from another project cannot be smuggled in.
        $e = DB::one('SELECT * FROM evidence WHERE public_id = ? AND project_id = ? AND kind = \'file\'', [$eid, $p['id']]);
        $path = $e ? Upload::fullPath($e['file_path']) : null;
        if (!$path) { $this->notFound(); return; }
        header('Content-Type: ' . $e['mime']);
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: attachment; filename="' . preg_replace('/[^A-Za-z0-9._ -]/', '_', $e['original_name']) . '"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=3600');
        readfile($path);
    }

    // ---- feedback, chat, assessment, validation --------------------------------

    public function addFeedback(string $pid): void
    {
        $p = $this->load($pid);
        if (!$p) return;
        if (!Policy::canGiveFeedback($p)) { $this->forbidden('Feedback comes from mentors, facilitators and stakeholders.'); return; }
        $d = $this->validate(['body' => 'required|min:5|max:4000'], "projects/$pid?tab=feedback", ['body' => 'your feedback']);
        if ($d === null) return;
        DB::tx(function () use ($p, $d) {
            // Stored ONCE against the project (not per recipient): every active member reads the same row,
            // so no member can be left looking at an older copy.
            DB::run('INSERT INTO feedback (project_id, author_id, kind, body) VALUES (?,?,?,?)', [$p['id'], $this->uid(), $this->feedbackKind($p), $d['body']]);
            $this->touch((int)$p['id']);
            Notify::project((int)$p['id'], 'New feedback on "' . $p['title'] . '" from ' . $this->user()['name'] . '.', 'projects/' . $p['public_id'] . '?tab=feedback', $this->uid());
        });
        flash('success', 'Feedback shared with the whole team.');
        $this->redirect("projects/$pid?tab=feedback");
    }

    public function postMessage(string $pid): void
    {
        $p = $this->load($pid);
        if (!$p) return;
        $key = 'msg:' . $this->uid();
        if (Throttle::tooMany($key, 30, 1)) {
            if (Request::isAjax()) { $this->json(['error' => 'Slow down a little.'], 429); return; }
            flash('error', 'You are sending messages very quickly. Please wait a moment.'); $this->redirect("projects/$pid?tab=chat"); return;
        }
        $body = trim((string)($_POST['body'] ?? ''));
        if ($body === '' || mb_strlen($body) > 2000) {
            if (Request::isAjax()) { $this->json(['error' => 'Write a message of up to 2000 characters.'], 422); return; }
            flash('error', 'Write a message of up to 2000 characters.'); $this->redirect("projects/$pid?tab=chat"); return;
        }
        $ck = preg_match('/^[a-f0-9]{32}$/', $_POST['client_key'] ?? '') ? $_POST['client_key'] : null;
        Throttle::hit($key);
        // client_key makes retries idempotent: a message queued offline and re-sent twice is stored once.
        DB::run('INSERT IGNORE INTO project_messages (project_id, user_id, body, client_key) VALUES (?,?,?,?)', [$p['id'], $this->uid(), $body, $ck]);
        if (Request::isAjax()) { $this->json(['ok' => true]); return; }
        $this->redirect("projects/$pid?tab=chat");
    }

    public function assess(string $pid): void
    {
        $p = $this->load($pid);
        if (!$p) return;
        if (!Policy::canAdvise($p)) { $this->forbidden(); return; }
        $rules = ['comment' => 'max:3000'];
        foreach (['impact', 'collaboration', 'technical', 'presentation', 'sustainability'] as $c) $rules[$c] = 'required|between:1,5';
        $d = $this->validate($rules, "projects/$pid?tab=assessment", ['impact' => 'Impact score', 'collaboration' => 'Collaboration score', 'technical' => 'Technical score', 'presentation' => 'Presentation score', 'sustainability' => 'Sustainability score']);
        if ($d === null) return;
        $sum = $d['impact'] + $d['collaboration'] + $d['technical'] + $d['presentation'] + $d['sustainability'];
        $total = (int)round($sum / 25 * 100);
        $ok = DB::tx(function () use ($p, $d, $total) {
            if (!$this->move($p, 'submitted', 'assessed', 'Your project "' . $p['title'] . '" was assessed: ' . $total . '%.')) return false;
            DB::run('INSERT INTO assessments (project_id, assessor_id, impact, collaboration, technical, presentation, sustainability, total, comment) VALUES (?,?,?,?,?,?,?,?,?)',
                [$p['id'], $this->uid(), $d['impact'], $d['collaboration'], $d['technical'], $d['presentation'], $d['sustainability'], $total, $d['comment']]);
            return true;
        });
        flash($ok ? 'success' : 'error', $ok ? 'Assessment recorded.' : 'The project must be submitted by the team before it can be assessed.');
        $this->redirect("projects/$pid?tab=assessment");
    }

    public function validateProject(string $pid): void
    {
        $p = $this->load($pid);
        if (!$p) return;
        if ($p['role'] !== 'stakeholder') { $this->forbidden('Only the assigned stakeholder can validate this project.'); return; }
        if (!in_array($p['status'], ['assessed', 'validated'], true)) { flash('error', 'The project is not ready for validation yet.'); $this->redirect("projects/$pid"); return; }
        $d = $this->validate(['decision' => 'required|in:endorsed,not_endorsed', 'comment' => 'required|min:5|max:2000'], "projects/$pid?tab=assessment", ['comment' => 'a comment']);
        if ($d === null) return;
        DB::run('INSERT INTO validations (project_id, stakeholder_id, decision, comment) VALUES (?,?,?,?)
                 ON DUPLICATE KEY UPDATE decision = VALUES(decision), comment = VALUES(comment)', [$p['id'], $this->uid(), $d['decision'], $d['comment']]);
        if ($d['decision'] === 'endorsed' && $p['status'] === 'assessed') $this->move($p, 'assessed', 'validated', 'A stakeholder endorsed "' . $p['title'] . '".');
        else { $this->touch((int)$p['id']); Notify::project((int)$p['id'], 'A stakeholder responded on "' . $p['title'] . '".', 'projects/' . $pid . '?tab=assessment', $this->uid(), true); }
        flash('success', 'Your validation was recorded.');
        $this->redirect("projects/$pid?tab=assessment");
    }

    public function assignStaff(string $pid): void
    {
        $p = $this->load($pid);
        if (!$p) return;
        if (!in_array($p['role'], ['admin', 'facilitator'], true)) { $this->forbidden(); return; }
        $d = $this->validate(['user_id' => 'required|int'], "projects/$pid", ['user_id' => 'a person']);
        if ($d === null) return;
        // The project role comes from the person's own account type, never from the form.
        $u = DB::one('SELECT id, name, role FROM users WHERE id = ? AND role IN (\'mentor\',\'facilitator\',\'stakeholder\') AND status = \'active\'', [(int)$d['user_id']]);
        if (!$u) { flash('error', 'Choose an active mentor, facilitator or stakeholder account.'); $this->redirect("projects/$pid"); return; }
        if ($p['role'] === 'facilitator' && $u['role'] === 'facilitator') { $this->forbidden('Only an administrator can assign facilitators.'); return; }
        $n = DB::run('INSERT IGNORE INTO project_staff (project_id, user_id, role) VALUES (?,?,?)', [$p['id'], $u['id'], $u['role']]);
        if ($n) {
            Notify::to((int)$u['id'], 'You were assigned as ' . $u['role'] . ' on "' . $p['title'] . '".', 'projects/' . $pid);
            Notify::project((int)$p['id'], $u['name'] . ' joined your project as ' . $u['role'] . '.', 'projects/' . $pid, $this->uid());
            $this->touch((int)$p['id']);
        }
        Audit::log('project.staff', 'project', $pid, $u['role'] . ':' . $u['id']);
        flash('success', $u['name'] . ' is now a ' . $u['role'] . ' on this project.');
        $this->redirect("projects/$pid");
    }
}
