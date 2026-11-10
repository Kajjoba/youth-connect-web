<section class="container section">
  <h1>My assigned projects</h1>
  <p class="muted">Projects where you are the mentor, facilitator or stakeholder. Those waiting for you appear first.</p>
  <?php if (!$rows): ?><div class="empty"><h3>No projects assigned yet</h3><p class="muted">An administrator or facilitator will assign you to a team project.</p></div><?php endif; ?>
  <div class="grid grid-2">
  <?php foreach ($rows as $r):
    $needs = ($r['status'] === 'proposed' && $r['my_role'] !== 'stakeholder') || ($r['status'] === 'submitted' && $r['my_role'] !== 'stakeholder') || ($r['status'] === 'assessed' && $r['my_role'] === 'stakeholder'); ?>
    <a class="card project-card" href="<?= e(url('projects/' . $r['public_id'])) ?>">
      <div class="between"><strong><?= e($r['title']) ?></strong><span class="badge ps-<?= e($r['status']) ?>"><?= e(status_label($r['status'])) ?></span></div>
      <p class="muted small">Team <?= e($r['team']) ?> · <?= (int)$r['members'] ?> members · you are <?= e($r['my_role']) ?></p>
      <?php if ($needs): ?><p class="small"><span class="badge badge-warn">Needs your action</span></p><?php endif; ?>
    </a>
  <?php endforeach; ?></div>
</section>
