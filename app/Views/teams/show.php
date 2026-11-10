<section class="container section">
  <p class="small"><a href="<?= e(url('teams')) ?>">&larr; Collaborate</a></p>
  <div class="between"><div><h1><?= e($t['name']) ?></h1><p class="muted"><?= e($t['description']) ?></p></div>
    <form method="post" action="<?= e(url('teams/' . $t['public_id'] . '/leave')) ?>" data-confirm="Leave this team?"><?= csrf_field() ?><button class="btn btn-outline btn-sm" type="submit">Leave team</button></form></div>

  <h2>Members (<?= count($members) ?>/<?= (int)config('level2.max_team_size') ?>)</h2>
  <?php $countries = array_unique(array_filter(array_column($members, 'country'))); ?>
  <p class="muted small"><?= count($countries) ?> countr<?= count($countries) == 1 ? 'y' : 'ies' ?>: <?= e(implode(', ', $countries) ?: 'none yet') ?><?= count($countries) < 2 ? ' · Cross-border teams earn a special badge when they complete a project' : '' ?></p>
  <div class="table-wrap"><table class="table"><thead><tr><th>Member</th><th>Country</th><th>Discipline</th><th>Role</th><th></th></tr></thead><tbody>
  <?php foreach ($members as $m): ?>
    <tr><td data-label="Member"><strong><?= e($m['name']) ?></strong><br><span class="muted small"><?= e($m['headline']) ?></span></td><td data-label="Country"><?= e($m['country'] ?: '–') ?></td><td data-label="Discipline"><?= e($m['discipline'] ?: '–') ?></td><td data-label="Role"><span class="badge"><?= e(ucfirst($m['role'])) ?></span></td>
      <td>
        <?php if ($role === 'lead' && (int)$m['id'] !== (int)auth_user()['id']): ?>
          <form method="post" action="<?= e(url('teams/' . $t['public_id'] . '/member')) ?>" class="inline"><?= csrf_field() ?><input type="hidden" name="user_id" value="<?= (int)$m['id'] ?>"><input type="hidden" name="action" value="make_lead"><button class="btn btn-outline btn-sm" type="submit">Make lead</button></form>
          <form method="post" action="<?= e(url('teams/' . $t['public_id'] . '/member')) ?>" class="inline" data-confirm="Remove <?= e($m['name']) ?> from the team?"><?= csrf_field() ?><input type="hidden" name="user_id" value="<?= (int)$m['id'] ?>"><input type="hidden" name="action" value="remove"><button class="btn btn-danger btn-sm" type="submit">Remove</button></form>
        <?php endif; ?></td></tr>
  <?php endforeach; ?></tbody></table></div>

  <?php if ($role === 'lead'): ?>
    <?php if ($requests): ?>
      <h2>Requests to join</h2>
      <?php foreach ($requests as $r): ?>
        <div class="card row between"><span><strong><?= e($r['name']) ?></strong> <span class="muted small"><?= e($r['country']) ?> · <?= e($r['expertise'] ?: $r['skills']) ?></span></span>
          <span class="row"><form method="post" action="<?= e(url('teams/' . $t['public_id'] . '/respond')) ?>"><?= csrf_field() ?><input type="hidden" name="user_id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="action" value="accept"><button class="btn btn-primary btn-sm" type="submit">Accept</button></form>
          <form method="post" action="<?= e(url('teams/' . $t['public_id'] . '/respond')) ?>"><?= csrf_field() ?><input type="hidden" name="user_id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="action" value="decline"><button class="btn btn-outline btn-sm" type="submit">Decline</button></form></span></div>
      <?php endforeach; ?>
    <?php endif; ?>
    <h2>Invite someone</h2>
    <form method="post" action="<?= e(url('teams/' . $t['public_id'] . '/invite')) ?>" class="row add-row"><?= csrf_field() ?>
      <label class="sr" for="inv">Participant email</label><input id="inv" name="email" type="email" required placeholder="Email of a registered participant"><button class="btn btn-primary" type="submit">Send invitation</button></form>
    <?php if ($pending): ?><p class="muted small">Waiting for a reply: <?= e(implode(', ', array_column($pending, 'name'))) ?></p><?php endif; ?>
  <?php endif; ?>

  <h2>Projects</h2>
  <?php if (!$projects): ?><div class="empty"><p class="muted">No project yet. Register your idea below, then submit it for mentor review.</p></div><?php endif; ?>
  <div class="grid grid-2"><?php foreach ($projects as $p): ?>
    <a class="card project-card" href="<?= e(url('projects/' . $p['public_id'])) ?>"><div class="between"><strong><?= e($p['title']) ?></strong><span class="badge ps-<?= e($p['status']) ?>"><?= e(status_label($p['status'])) ?></span></div><p class="muted small">Updated <?= e(time_ago($p['updated_at'])) ?></p></a>
  <?php endforeach; ?></div>

  <details class="card" <?= $projects ? '' : 'open' ?>><summary><strong>+ Register a project</strong></summary>
    <form method="post" action="<?= e(url('teams/' . $t['public_id'] . '/projects')) ?>" novalidate><?= csrf_field() ?>
      <div class="field"><label for="p-title">Project title</label><input id="p-title" name="title" required maxlength="160" value="<?= e(old('title')) ?>"><?= field_error('title') ?></div>
      <div class="field"><label for="p-theme">Theme</label><select id="p-theme" name="theme" required><option value="">Choose…</option><?php foreach ($themes as $th): ?><option <?= old('theme') === $th ? 'selected' : '' ?>><?= e($th) ?></option><?php endforeach; ?></select><?= field_error('theme') ?></div>
      <div class="field"><label for="p-sum">Short summary</label><textarea id="p-sum" name="summary" rows="3" required maxlength="500" placeholder="e.g. A mobile-friendly tool that helps smallholder farmers plan planting using local weather data."><?= e(old('summary')) ?></textarea><?= field_error('summary') ?></div>
      <button class="btn btn-primary" type="submit">Create project</button></form>
  </details>
</section>
