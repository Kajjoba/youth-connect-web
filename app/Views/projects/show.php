<?php
use App\Core\Policy;
$pid = $p['public_id'];
$isTeam = Policy::isTeam($p);
$editable = $isTeam && in_array($p['status'], ['draft', 'changes_requested', 'approved'], true);
$steps = ['draft' => 'Draft', 'proposed' => 'Proposed', 'approved' => 'In progress', 'submitted' => 'Submitted', 'assessed' => 'Assessed', 'validated' => 'Validated', 'completed' => 'Completed'];
$order = array_keys($steps);
$cur = $p['status'] === 'changes_requested' ? 'draft' : $p['status'];
$curIdx = array_search($cur, $order, true);
$tabUrl = fn($t) => url("projects/$pid" . ($t === 'overview' ? '' : "?tab=$t"));
$lastMsg = (int)\App\Core\DB::val('SELECT COALESCE(MAX(id),0) FROM project_messages WHERE project_id = ?', [$p['id']]);
?>
<section class="container section project" data-pulse="<?= e(url('api/projects/' . $pid . '/pulse')) ?>" data-version="<?= (int)$p['version'] ?>" data-feedback="<?= (int)$counts['feedback'] ?>" data-lastmsg="<?= $lastMsg ?>" data-tab="<?= e($tab) ?>">
  <p class="small"><?php if ($isTeam): ?><a href="<?= e(url('teams/' . $p['team_public_id'])) ?>">&larr; <?= e($p['team_name']) ?></a><?php else: ?><a href="<?= e(url($p['role'] === 'admin' ? 'admin/projects' : 'staff/projects')) ?>">&larr; Projects</a><?php endif; ?></p>
  <div class="between"><div>
      <h1><?= e($p['title']) ?></h1>
      <p class="muted"><?= e($p['theme']) ?> · Team <?= e($p['team_name']) ?> · You are: <strong><?= e($p['role']) ?></strong></p>
    </div>
    <span class="badge badge-lg ps-<?= e($p['status']) ?>"><?= e(status_label($p['status'])) ?></span></div>

  <div id="update-banner" class="alert alert-info" hidden role="status">
    <span id="update-text">There are new updates on this project.</span> <a href="<?= e($tabUrl($tab)) ?>" id="update-link">Refresh to see them</a></div>

  <ol class="tracker" aria-label="Project stage">
    <?php foreach ($steps as $k => $label): $i = array_search($k, $order, true); ?>
      <li class="<?= $i < $curIdx ? 'done' : ($i === $curIdx ? 'now' : '') ?>"><span><?= $i < $curIdx ? '✓' : $i + 1 ?></span><?= e($label) ?></li>
    <?php endforeach; ?>
  </ol>
  <?php if ($p['status'] === 'changes_requested'): ?><p class="alert alert-warning">Your mentor asked for changes. Read the <a href="<?= e($tabUrl('feedback')) ?>">feedback</a>, update the description, then resubmit.</p><?php endif; ?>

  <nav class="tabs" aria-label="Project sections">
    <?php foreach (['overview' => 'Overview', 'work' => 'Tasks (' . $counts['tasks_done'] . '/' . $counts['tasks'] . ')', 'evidence' => 'Evidence (' . $counts['evidence'] . ')', 'feedback' => 'Feedback (' . $counts['feedback'] . ')', 'chat' => 'Discussion', 'assessment' => 'Assessment'] as $k => $label): ?>
      <a href="<?= e($tabUrl($k)) ?>" class="<?= $tab === $k ? 'on' : '' ?>" <?= $tab === $k ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
    <?php endforeach; ?>
  </nav>

<?php if ($tab === 'overview'): ?>
  <!-- ACTIONS BY ROLE AND STAGE -->
  <?php if ($p['role'] === 'lead' && in_array($p['status'], ['draft', 'changes_requested'], true)): ?>
    <div class="card callout"><div><strong>Ready for mentor review?</strong><p class="muted">You need at least 2 team members and a written problem, solution and impact.</p></div>
      <form method="post" action="<?= e(url("projects/$pid/transition")) ?>"><?= csrf_field() ?><input type="hidden" name="action" value="submit_proposal"><button class="btn btn-primary" type="submit">Submit proposal</button></form></div>
  <?php elseif (Policy::canAdvise($p) && $p['status'] === 'proposed'): ?>
    <div class="card"><h2>Review this proposal</h2>
      <form method="post" action="<?= e(url("projects/$pid/transition")) ?>"><?= csrf_field() ?>
        <div class="field"><label for="cm">Comment to the team (required if you request changes)</label><textarea id="cm" name="comment" rows="3" maxlength="2000"></textarea></div>
        <button class="btn btn-primary" type="submit" name="action" value="approve">Approve</button>
        <button class="btn btn-outline" type="submit" name="action" value="request_changes">Request changes</button></form></div>
  <?php elseif ($p['role'] === 'lead' && $p['status'] === 'approved'): ?>
    <div class="card callout"><div><strong>Finished the work?</strong><p class="muted">Add your evidence first, then submit the final project for assessment.</p></div>
      <form method="post" action="<?= e(url("projects/$pid/transition")) ?>" data-confirm="Submit the final project for assessment? The description will be locked."><?= csrf_field() ?><input type="hidden" name="action" value="submit_final"><button class="btn btn-primary" type="submit">Submit final project</button></form></div>
  <?php elseif (Policy::canAdvise($p) && $p['status'] === 'submitted'): ?>
    <div class="card callout"><div><strong>Final project submitted</strong><p class="muted">Review the evidence, then record your assessment.</p></div><a class="btn btn-primary" href="<?= e($tabUrl('assessment')) ?>">Assess project</a></div>
  <?php elseif ($p['role'] === 'stakeholder' && $p['status'] === 'assessed'): ?>
    <div class="card callout"><div><strong>Your validation is needed</strong><p class="muted">Say whether this project is useful and credible for the community it serves.</p></div><a class="btn btn-primary" href="<?= e($tabUrl('assessment')) ?>">Validate</a></div>
  <?php endif; ?>
  <?php if (Policy::canFinalize($p) && (in_array($p['status'], ['validated', 'assessed'], true))): ?>
    <div class="card callout"><div><strong>Complete and recognise</strong><p class="muted">Issues a project certificate and badges to every team member.</p></div>
      <form method="post" action="<?= e(url("projects/$pid/transition")) ?>" data-confirm="Complete this project and issue certificates?"><?= csrf_field() ?><input type="hidden" name="action" value="complete"><button class="btn btn-primary" type="submit">Complete project</button></form></div>
  <?php endif; ?>
  <?php if ($p['status'] === 'completed' && $isTeam): ?><p class="alert alert-success">🎉 Project complete. Find your certificate and badges in <a href="<?= e(url('my-learning')) ?>">My learning</a>.</p><?php endif; ?>

  <div class="grid grid-main">
    <div>
      <?php if ($editable): ?>
        <form method="post" action="<?= e(url("projects/$pid")) ?>" novalidate data-draft="proj-<?= e($pid) ?>"><?= csrf_field() ?>
          <div class="field"><label for="title">Title</label><input id="title" name="title" required maxlength="160" value="<?= e(old('title', $p['title'])) ?>"><?= field_error('title') ?></div>
          <div class="field"><label for="theme">Theme</label><select id="theme" name="theme"><?php foreach ($themes as $th): ?><option <?= old('theme', $p['theme']) === $th ? 'selected' : '' ?>><?= e($th) ?></option><?php endforeach; ?></select></div>
          <div class="field"><label for="summary">Summary</label><textarea id="summary" name="summary" rows="3" required maxlength="500"><?= e(old('summary', $p['summary'])) ?></textarea><?= field_error('summary') ?></div>
          <div class="field"><label for="problem">The problem</label><textarea id="problem" name="problem" rows="5" maxlength="3000" placeholder="Who has this problem? Why does it matter?"><?= e(old('problem', $p['problem'])) ?></textarea></div>
          <div class="field"><label for="solution">Your solution</label><textarea id="solution" name="solution" rows="5" maxlength="3000" placeholder="What will you build or do? How do the team's disciplines fit together?"><?= e(old('solution', $p['solution'])) ?></textarea></div>
          <div class="field"><label for="impact">Expected impact</label><textarea id="impact" name="impact" rows="4" maxlength="3000" placeholder="How will you know it worked? Who benefits?"><?= e(old('impact', $p['impact'])) ?></textarea></div>
          <button class="btn btn-primary" type="submit">Save changes</button>
        </form>
      <?php else: ?>
        <h2>Summary</h2><p><?= nl2br(e($p['summary'])) ?></p>
        <?php foreach (['problem' => 'The problem', 'solution' => 'Our solution', 'impact' => 'Expected impact'] as $k => $label): if (!$p[$k]) continue; ?>
          <h2><?= e($label) ?></h2><p><?= nl2br(e($p[$k])) ?></p>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <aside>
      <div class="card"><h3>Team</h3>
        <ul class="plain"><?php foreach ($members as $m): ?><li><strong><?= e($m['name']) ?></strong><?= $m['role'] === 'lead' ? ' <span class="badge">Lead</span>' : '' ?><br><span class="muted small"><?= e($m['country'] ?: '') ?><?= $m['discipline'] ? ' · ' . e($m['discipline']) : '' ?></span></li><?php endforeach; ?></ul>
        <p class="small muted"><?= count($countries) ?> countr<?= count($countries) == 1 ? 'y' : 'ies' ?>: <?= e(implode(', ', $countries)) ?></p></div>
      <div class="card"><h3>Mentors &amp; reviewers</h3>
        <?php if (!$staff): ?><p class="muted small">No mentor assigned yet.</p><?php endif; ?>
        <ul class="plain"><?php foreach ($staff as $s): ?><li><?= e($s['name']) ?> <span class="badge"><?= e(ucfirst($s['role'])) ?></span></li><?php endforeach; ?></ul>
        <?php if (!empty($assignable)): ?>
          <form method="post" action="<?= e(url("projects/$pid/staff")) ?>"><?= csrf_field() ?>
            <div class="field"><label for="st-u">Assign someone</label><select id="st-u" name="user_id" required><option value="">Choose a person…</option>
              <?php foreach ($assignable as $a): ?><option value="<?= (int)$a['id'] ?>" data-role="<?= e($a['role']) ?>"><?= e($a['name']) ?> (<?= e($a['role']) ?>)</option><?php endforeach; ?></select></div>
            <button class="btn btn-outline btn-sm" type="submit">Assign</button></form>
        <?php endif; ?></div>
    </aside>
  </div>

<?php elseif ($tab === 'work'): ?>
  <div class="grid grid-main">
    <div>
      <h2>Tasks</h2>
      <?php if (!$tasks): ?><div class="empty"><p class="muted">No tasks yet. Break the work into small tasks and assign each one.</p></div><?php endif; ?>
      <ul class="plain tasks">
        <?php foreach ($tasks as $t): ?>
          <li class="card task st-<?= e($t['status']) ?>">
            <div class="between"><strong><?= e($t['title']) ?></strong><span class="muted small"><?= $t['due_date'] ? 'Due ' . e(date('j M', strtotime($t['due_date']))) : '' ?></span></div>
            <p class="muted small"><?= $t['assignee'] ? 'Assigned to ' . e($t['assignee']) : 'Unassigned' ?></p>
            <?php if ($isTeam && $p['status'] !== 'completed'): ?>
              <form method="post" action="<?= e(url("projects/$pid/tasks/{$t['id']}")) ?>" class="row"><?= csrf_field() ?>
                <label class="sr" for="ts<?= (int)$t['id'] ?>">Status</label>
                <select id="ts<?= (int)$t['id'] ?>" name="status"><?php foreach (['todo' => 'To do', 'doing' => 'In progress', 'done' => 'Done'] as $k => $v): ?><option value="<?= $k ?>" <?= $t['status'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select>
                <label class="sr" for="ta<?= (int)$t['id'] ?>">Assignee</label>
                <select id="ta<?= (int)$t['id'] ?>" name="assignee_id"><option value="">Unassigned</option><?php foreach ($members as $m): ?><option value="<?= (int)$m['id'] ?>" <?= (int)$t['assignee_id'] === (int)$m['id'] ? 'selected' : '' ?>><?= e($m['name']) ?></option><?php endforeach; ?></select>
                <button class="btn btn-outline btn-sm" type="submit">Update</button></form>
            <?php else: ?><span class="badge"><?= e(status_label($t['status'])) ?></span><?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
      <?php if ($isTeam && $p['status'] !== 'completed'): ?>
      <details class="card" <?= $tasks ? '' : 'open' ?>><summary><strong>+ Add a task</strong></summary>
        <form method="post" action="<?= e(url("projects/$pid/tasks")) ?>" novalidate data-draft="task-<?= e($pid) ?>"><?= csrf_field() ?>
          <div class="field"><label for="tt">Task</label><input id="tt" name="title" required maxlength="200"><?= field_error('title') ?></div>
          <div class="field-row">
            <div class="field"><label for="tas">Assign to</label><select id="tas" name="assignee_id"><option value="">Unassigned</option><?php foreach ($members as $m): ?><option value="<?= (int)$m['id'] ?>"><?= e($m['name']) ?></option><?php endforeach; ?></select></div>
            <div class="field"><label for="tms">Milestone</label><select id="tms" name="milestone_id"><option value="">None</option><?php foreach ($milestones as $ms): ?><option value="<?= (int)$ms['id'] ?>"><?= e($ms['title']) ?></option><?php endforeach; ?></select></div>
            <div class="field"><label for="tdue">Due date</label><input id="tdue" name="due_date" type="date"></div>
          </div>
          <button class="btn btn-primary" type="submit">Add task</button></form></details>
      <?php endif; ?>
    </div>
    <aside>
      <h2>Milestones</h2>
      <?php if (!$milestones): ?><p class="muted small">Milestones mark big steps, like "Prototype ready".</p><?php endif; ?>
      <ul class="plain">
        <?php foreach ($milestones as $ms): ?>
          <li class="card row between"><span class="<?= $ms['status'] === 'done' ? 'strike' : '' ?>"><?= $ms['status'] === 'done' ? '✅' : '⬜' ?> <?= e($ms['title']) ?><?= $ms['due_date'] ? '<br><span class="muted small">' . e(date('j M Y', strtotime($ms['due_date']))) . '</span>' : '' ?></span>
            <?php if ($isTeam && $p['status'] !== 'completed'): ?><form method="post" action="<?= e(url("projects/$pid/milestones/{$ms['id']}")) ?>"><?= csrf_field() ?><button class="btn btn-outline btn-sm" type="submit"><?= $ms['status'] === 'done' ? 'Reopen' : 'Done' ?></button></form><?php endif; ?></li>
        <?php endforeach; ?>
      </ul>
      <?php if ($isTeam && $p['status'] !== 'completed'): ?>
      <form method="post" action="<?= e(url("projects/$pid/milestones")) ?>" class="card" novalidate><?= csrf_field() ?>
        <div class="field"><label for="mt">New milestone</label><input id="mt" name="title" required maxlength="160"></div>
        <div class="field"><label for="md">Target date</label><input id="md" name="due_date" type="date"></div>
        <button class="btn btn-outline btn-sm" type="submit">Add milestone</button></form>
      <?php endif; ?>
    </aside>
  </div>

<?php elseif ($tab === 'evidence'): ?>
  <h2>Project evidence</h2>
  <p class="muted">Show what you did: documents, photos, links to a prototype or dataset, or a short written note. Mentors and assessors read this.</p>
  <?php if (!$evidence): ?><div class="empty"><p class="muted">No evidence yet.</p></div><?php endif; ?>
  <ul class="plain list-cards">
    <?php foreach ($evidence as $ev): ?>
      <li class="card"><div class="between"><strong><?= e($ev['title']) ?></strong><span class="badge"><?= e(ucfirst($ev['kind'])) ?></span></div>
        <p class="muted small">Added by <?= e($ev['by_name']) ?> · <?= e(time_ago($ev['created_at'])) ?><?= $ev['milestone'] ? ' · for "' . e($ev['milestone']) . '"' : '' ?></p>
        <?php if ($ev['kind'] === 'file'): ?><a class="btn btn-outline btn-sm" href="<?= e(url("projects/$pid/evidence/{$ev['public_id']}")) ?>">Download <?= e($ev['original_name']) ?> (<?= e(round($ev['size_bytes'] / 1024)) ?> KB)</a>
        <?php elseif ($ev['kind'] === 'link'): ?><a href="<?= e($ev['url']) ?>" rel="noopener noreferrer nofollow" target="_blank"><?= e($ev['url']) ?></a>
        <?php else: ?><div class="quote"><?= nl2br(e($ev['note'])) ?></div><?php endif; ?></li>
    <?php endforeach; ?>
  </ul>
  <?php if ($isTeam && $p['status'] !== 'completed'): ?>
  <details class="card" open><summary><strong>+ Add evidence</strong></summary>
    <form method="post" action="<?= e(url("projects/$pid/evidence")) ?>" enctype="multipart/form-data" novalidate data-draft="ev-<?= e($pid) ?>"><?= csrf_field() ?>
      <div class="field-row">
        <div class="field"><label for="et">Title</label><input id="et" name="title" required maxlength="160"><?= field_error('title') ?></div>
        <div class="field"><label for="ek">Type</label><select id="ek" name="kind" data-evidence-kind><option value="file">File upload</option><option value="link">Web link</option><option value="note">Written note</option></select></div>
        <div class="field"><label for="em">For milestone</label><select id="em" name="milestone_id"><option value="">None</option><?php foreach ($milestones as $ms): ?><option value="<?= (int)$ms['id'] ?>"><?= e($ms['title']) ?></option><?php endforeach; ?></select></div>
      </div>
      <div class="field" data-kind="file"><label for="ef">File (PDF, image, Word, Excel, PowerPoint, TXT, CSV · max <?= round(config('security.upload_max_bytes') / 1048576) ?> MB)</label><input id="ef" name="file" type="file"></div>
      <div class="field" data-kind="link" hidden><label for="eu">Web address</label><input id="eu" name="url" type="url" maxlength="400" placeholder="https://"><?= field_error('url') ?></div>
      <div class="field" data-kind="note" hidden><label for="en">Note</label><textarea id="en" name="note" rows="4" maxlength="5000"></textarea><?= field_error('note') ?></div>
      <button class="btn btn-primary" type="submit">Add evidence</button></form></details>
  <?php endif; ?>

<?php elseif ($tab === 'feedback'): ?>
  <h2>Mentor &amp; facilitator feedback</h2>
  <p class="muted">Everyone on the team sees the same feedback, in one place.</p>
  <?php if (!$feedback): ?><div class="empty"><p class="muted">No feedback yet.</p></div><?php endif; ?>
  <?php foreach ($feedback as $f): ?>
    <article class="card feedback"><p><strong><?= e($f['name']) ?></strong> <span class="badge"><?= e(ucfirst($f['kind'])) ?></span> <span class="muted small"><?= e(time_ago($f['created_at'])) ?></span></p><p><?= nl2br(e($f['body'])) ?></p></article>
  <?php endforeach; ?>
  <?php if (Policy::canGiveFeedback($p)): ?>
    <form method="post" action="<?= e(url("projects/$pid/feedback")) ?>" class="card" data-draft="fb-<?= e($pid) ?>"><?= csrf_field() ?>
      <div class="field"><label for="fb">Add feedback for the team</label><textarea id="fb" name="body" rows="4" required maxlength="4000"></textarea><?= field_error('body') ?></div>
      <button class="btn btn-primary" type="submit">Share with the team</button></form>
  <?php endif; ?>

<?php elseif ($tab === 'chat'): ?>
  <h2>Team discussion</h2>
  <p class="muted small">Visible to the team and its assigned mentor, facilitator and stakeholder.</p>
  <div id="chat" class="chat" data-url="<?= e(url('api/projects/' . $pid . '/messages')) ?>" data-post="<?= e(url("projects/$pid/messages")) ?>" aria-live="polite">
    <?php if (!$messages): ?><p class="muted" id="chat-empty">No messages yet. Say hello!</p><?php endif; ?>
    <?php foreach ($messages as $m): ?>
      <div class="msg <?= (int)$m['user_id'] === (int)auth_user()['id'] ? 'mine' : '' ?>" data-id="<?= (int)$m['id'] ?>"><p class="meta"><strong><?= e($m['name']) ?></strong> <span class="muted small"><?= e(time_ago($m['created_at'])) ?></span></p><p><?= nl2br(e($m['body'])) ?></p></div>
    <?php endforeach; ?>
  </div>
  <form method="post" action="<?= e(url("projects/$pid/messages")) ?>" class="chat-form" id="chat-form" data-draft="chat-<?= e($pid) ?>"><?= csrf_field() ?>
    <label class="sr" for="cb">Message</label><textarea id="cb" name="body" rows="2" maxlength="2000" required placeholder="Write a message…"></textarea>
    <input type="hidden" name="client_key" value="">
    <button class="btn btn-primary" type="submit">Send</button></form>
  <p class="small muted" id="chat-status" hidden></p>

<?php elseif ($tab === 'assessment'): ?>
  <h2>Assessment</h2>
  <?php if ($assessment): ?>
    <div class="card"><div class="between"><strong>Total score</strong><span class="score-big"><?= (int)$assessment['total'] ?>%</span></div>
      <ul class="dist"><?php foreach (['impact' => 'Impact', 'collaboration' => 'Collaboration', 'technical' => 'Technical quality', 'presentation' => 'Presentation', 'sustainability' => 'Sustainability'] as $k => $label): ?>
        <li><span><?= e($label) ?></span><span class="bar"><i style="width:<?= (int)$assessment[$k] * 20 ?>%"></i></span><span class="small"><?= (int)$assessment[$k] ?>/5</span></li><?php endforeach; ?></ul>
      <?php if ($assessment['comment']): ?><p><?= nl2br(e($assessment['comment'])) ?></p><?php endif; ?>
      <p class="muted small">Assessed by <?= e($assessment['assessor']) ?>, <?= e(time_ago($assessment['created_at'])) ?></p></div>
  <?php elseif (Policy::canAdvise($p) && $p['status'] === 'submitted'): ?>
    <form method="post" action="<?= e(url("projects/$pid/assess")) ?>" class="card" novalidate><?= csrf_field() ?>
      <p class="muted">Score each criterion from 1 (weak) to 5 (excellent).</p>
      <?php foreach (['impact' => 'Impact: real value for the people served', 'collaboration' => 'Collaboration: cross-border, multidisciplinary teamwork', 'technical' => 'Technical quality of the solution', 'presentation' => 'Presentation of evidence', 'sustainability' => 'Sustainability: can it continue?'] as $k => $label): ?>
        <div class="field"><label for="a-<?= $k ?>"><?= e($label) ?></label><select id="a-<?= $k ?>" name="<?= $k ?>" required><option value="">Choose…</option><?php for ($i = 5; $i >= 1; $i--): ?><option><?= $i ?></option><?php endfor; ?></select><?= field_error($k) ?></div>
      <?php endforeach; ?>
      <div class="field"><label for="a-c">Overall comment</label><textarea id="a-c" name="comment" rows="3" maxlength="3000"></textarea></div>
      <button class="btn btn-primary" type="submit">Record assessment</button></form>
  <?php else: ?>
    <div class="empty"><p class="muted">Assessment happens after the team submits the final project.</p></div>
  <?php endif; ?>

  <h2>Stakeholder validation</h2>
  <?php if (!$validations): ?><p class="muted">No stakeholder responses yet.</p><?php endif; ?>
  <?php foreach ($validations as $v): ?>
    <article class="card"><p><strong><?= e($v['name']) ?></strong> <span class="badge <?= $v['decision'] === 'endorsed' ? 'badge-green' : 'badge-warn' ?>"><?= $v['decision'] === 'endorsed' ? 'Endorsed' : 'Not endorsed' ?></span></p><p><?= nl2br(e($v['comment'])) ?></p></article>
  <?php endforeach; ?>
  <?php if ($p['role'] === 'stakeholder' && in_array($p['status'], ['assessed', 'validated'], true)): ?>
    <form method="post" action="<?= e(url("projects/$pid/validate")) ?>" class="card" novalidate><?= csrf_field() ?>
      <div class="field"><label for="vd">Your decision</label><select id="vd" name="decision" required><option value="endorsed" <?= ($my_validation['decision'] ?? '') === 'endorsed' ? 'selected' : '' ?>>Endorse: useful and credible</option><option value="not_endorsed" <?= ($my_validation['decision'] ?? '') === 'not_endorsed' ? 'selected' : '' ?>>Do not endorse yet</option></select></div>
      <div class="field"><label for="vc">Why?</label><textarea id="vc" name="comment" rows="3" required maxlength="2000"><?= e($my_validation['comment'] ?? '') ?></textarea><?= field_error('comment') ?></div>
      <button class="btn btn-primary" type="submit">Submit validation</button></form>
  <?php endif; ?>
<?php endif; ?>
</section>
