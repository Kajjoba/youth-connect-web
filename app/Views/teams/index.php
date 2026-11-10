<section class="container section">
  <div class="between"><div><h1>Collaborate</h1><p class="muted">Level Two: build a project with youth from other countries and disciplines.</p></div>
    <?php if ($eligible): ?><a class="btn btn-primary" href="<?= e(url('teams/new')) ?>">+ Start a team</a><?php endif; ?></div>

  <div class="flow small" aria-label="How Level Two works">
    <span>Team</span>&rarr;<span>Project</span>&rarr;<span>Tasks &amp; milestones</span>&rarr;<span>Evidence</span>&rarr;<span>Mentoring</span>&rarr;<span>Assessment</span>&rarr;<span>Validation</span>&rarr;<span>Recognition</span>
  </div>

  <?php if (!$eligible): ?>
    <div class="card callout"><div><strong>Level Two is not unlocked yet</strong>
      <p class="muted">Complete <?= (int)$need ?> Level One course<?= $need == 1 ? '' : 's' ?> first. You have completed <?= (int)$done ?>. You can still look at teams below.</p></div>
      <a class="btn btn-primary" href="<?= e(url('my-learning')) ?>">Go to my learning</a></div>
  <?php endif; ?>

  <?php if ($invites): ?>
    <h2>Invitations</h2>
    <?php foreach ($invites as $i): ?>
      <div class="card row between"><span><strong><?= e($i['name']) ?></strong><br><span class="muted small"><?= e($i['description']) ?></span></span>
        <span class="row">
          <form method="post" action="<?= e(url('teams/' . $i['public_id'] . '/respond')) ?>"><?= csrf_field() ?><input type="hidden" name="action" value="accept"><button class="btn btn-primary btn-sm" type="submit">Accept</button></form>
          <form method="post" action="<?= e(url('teams/' . $i['public_id'] . '/respond')) ?>"><?= csrf_field() ?><input type="hidden" name="action" value="decline"><button class="btn btn-outline btn-sm" type="submit">Decline</button></form></span></div>
    <?php endforeach; ?>
  <?php endif; ?>

  <h2>My teams</h2>
  <?php if (!$mine): ?><div class="empty"><p class="muted">You are not in a team yet. Start one or ask to join an open team below.</p></div><?php endif; ?>
  <div class="grid grid-2">
    <?php foreach ($mine as $t): ?>
      <a class="card project-card" href="<?= e(url('teams/' . $t['public_id'])) ?>"><div class="between"><strong><?= e($t['name']) ?></strong><span class="badge"><?= e(ucfirst($t['role'])) ?></span></div>
        <p class="muted small"><?= e($t['description']) ?></p><p class="small"><?= (int)$t['members'] ?> member<?= $t['members'] == 1 ? '' : 's' ?> · <?= (int)$t['projects'] ?> project<?= $t['projects'] == 1 ? '' : 's' ?></p></a>
    <?php endforeach; ?>
  </div>

  <h2>Teams looking for members</h2>
  <form method="get" class="row filter-row"><label class="sr" for="q">Search teams</label><input id="q" name="q" type="search" placeholder="Search by name or skill, e.g. data analysis" value="<?= e($q) ?>"><button class="btn btn-outline" type="submit">Search</button></form>
  <?php if (!$open): ?><div class="empty"><p class="muted">No open teams match right now.</p></div><?php endif; ?>
  <div class="grid grid-2">
    <?php foreach ($open as $t): ?>
      <a class="card project-card" href="<?= e(url('teams/' . $t['public_id'])) ?>"><strong><?= e($t['name']) ?></strong>
        <p class="muted small"><?= e($t['description']) ?></p>
        <?php if ($t['looking_for']): ?><p class="small"><strong>Looking for:</strong> <?= e($t['looking_for']) ?></p><?php endif; ?>
        <p class="small muted"><?= (int)$t['members'] ?> member<?= $t['members'] == 1 ? '' : 's' ?><?= $t['countries'] ? ' · ' . e($t['countries']) : '' ?></p></a>
    <?php endforeach; ?>
  </div>
</section>
