<?php $active = array_filter($rows, fn($r) => $r['status'] === 'active'); $done = array_filter($rows, fn($r) => $r['status'] === 'completed'); ?>
<section class="container section">
  <h1>My learning</h1>

  <?php if ($level2['eligible']): ?>
    <div class="card callout l2 ok">
      <div><strong>Level Two is unlocked</strong><p class="muted">Team up with youth from other countries, register a project and work with a mentor.</p></div>
      <a class="btn btn-primary" href="<?= e(url('teams')) ?>">Collaborate on a project</a>
    </div>
  <?php else: ?>
    <div class="card callout l2">
      <div><strong>Unlock Level Two: Collaborative Exchange</strong>
        <p class="muted">Complete <?= (int)$level2['need'] ?> course<?= $level2['need'] == 1 ? '' : 's' ?> to join cross-border project teams. You have completed <?= (int)$level2['done'] ?>.</p></div>
      <a class="btn btn-outline" href="<?= e(url('courses')) ?>">Find a course</a>
    </div>
  <?php endif; ?>
  <?php if ($invites): ?><p class="alert alert-info">You have <?= (int)$invites ?> team invitation<?= $invites == 1 ? '' : 's' ?>. <a href="<?= e(url('teams')) ?>">View invitations</a></p><?php endif; ?>

  <h2>In progress</h2>
  <?php if (!$active): ?>
    <div class="empty"><h3><?= $done ? 'Nothing in progress right now' : 'You have not started a course yet' ?></h3><p class="muted">Pick a free course, see the weekly plan first, then enrol when you are ready.</p><a class="btn btn-primary" href="<?= e(url($done ? 'courses?sort=newest' : 'courses')) ?>"><?= $done ? 'Find your next course' : 'Explore courses' ?></a></div>
  <?php else: ?>
    <div class="grid grid-3">
      <?php foreach ($active as $r): ?>
        <article class="card enrol-card">
          <div class="thumb thumb-sm" style="--c:<?= e($r['color']) ?>"><span class="thumb-cat"><?= e($r['category']) ?></span></div>
          <h3><a href="<?= e(url('course/' . $r['slug'])) ?>"><?= e($r['title']) ?></a></h3>
          <div class="progress" role="progressbar" aria-valuenow="<?= (int)$r['progress']['pct'] ?>" aria-valuemin="0" aria-valuemax="100"><span style="width:<?= (int)$r['progress']['pct'] ?>%"></span></div>
          <p class="small muted"><?= (int)$r['progress']['pct'] ?>% · <?= (int)$r['progress']['done'] ?> of <?= (int)$r['progress']['total'] ?> items</p>
          <a class="btn btn-primary btn-block" href="<?= e(url('learn/' . $r['slug'])) ?>"><?= $r['progress']['pct'] > 0 ? 'Continue' : 'Start' ?></a>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($done): ?>
    <h2>Completed</h2>
    <ul class="plain list-cards"><?php foreach ($done as $r): ?><li class="card row between"><span>✅ <a href="<?= e(url('course/' . $r['slug'])) ?>"><?= e($r['title']) ?></a></span><span class="muted small">Completed <?= e(time_ago($r['completed_at'])) ?></span></li><?php endforeach; ?></ul>
  <?php endif; ?>

  <?php if ($certs): ?>
    <h2>Certificates</h2>
    <div class="grid grid-2"><?php foreach ($certs as $c): ?>
      <a class="card cert-mini" href="<?= e(url('certificate/' . $c['code'])) ?>"><strong><?= e($c['title']) ?></strong><span class="muted small"><?= e(date('j M Y', strtotime($c['issued_at']))) ?> · Code <?= e($c['code']) ?></span></a>
    <?php endforeach; ?></div>
  <?php endif; ?>

  <?php if ($badges): ?>
    <h2>Badges</h2>
    <div class="badges"><?php foreach ($badges as $b): ?><div class="badge-card" title="<?= e($b['description']) ?>"><span class="bi"><?= e($b['icon']) ?></span><strong><?= e($b['name']) ?></strong><span class="muted small"><?= e($b['description']) ?></span></div><?php endforeach; ?></div>
  <?php endif; ?>

  <?php if ($myTeams): ?>
    <h2>My teams</h2>
    <ul class="plain"><?php foreach ($myTeams as $t): ?><li><a href="<?= e(url('teams/' . $t['public_id'])) ?>"><?= e($t['name']) ?></a></li><?php endforeach; ?></ul>
  <?php endif; ?>

  <?php if ($suggested): ?>
    <h2>Recommended for you</h2>
    <div class="grid grid-4"><?php foreach ($suggested as $c): ?><?php include BASE_PATH . '/app/Views/partials/course_card.php'; ?><?php endforeach; ?></div>
  <?php endif; ?>
</section>
