<section class="container narrow section">
  <p class="small"><a href="<?= e(url('teams')) ?>">&larr; Collaborate</a></p>
  <h1><?= e($t['name']) ?></h1>
  <p class="lead"><?= e($t['description']) ?></p>
  <p class="muted"><?= (int)$card['members'] ?> member<?= $card['members'] == 1 ? '' : 's' ?><?= $card['countries'] ? ' · ' . e($card['countries']) : '' ?></p>
  <?php if ($t['looking_for']): ?><p><strong>Looking for:</strong> <?= e($t['looking_for']) ?></p><?php endif; ?>
  <p class="muted small">Member names and project work are visible to team members only.</p>

  <?php if ($my && $my['status'] === 'invited'): ?>
    <div class="card callout"><strong>You are invited to join this team</strong>
      <span class="row"><form method="post" action="<?= e(url('teams/' . $t['public_id'] . '/respond')) ?>"><?= csrf_field() ?><input type="hidden" name="action" value="accept"><button class="btn btn-primary" type="submit">Accept</button></form>
      <form method="post" action="<?= e(url('teams/' . $t['public_id'] . '/respond')) ?>"><?= csrf_field() ?><input type="hidden" name="action" value="decline"><button class="btn btn-outline" type="submit">Decline</button></form></span></div>
  <?php elseif ($my && $my['status'] === 'requested'): ?>
    <p class="alert alert-info">Your request is waiting for the team lead.</p>
  <?php elseif (!$t['is_open']): ?>
    <p class="alert alert-info">This team accepts members by invitation only.</p>
  <?php elseif (!$eligible): ?>
    <p class="alert alert-info">Complete a Level One course to unlock Level Two and join teams.</p>
  <?php else: ?>
    <form method="post" action="<?= e(url('teams/' . $t['public_id'] . '/join')) ?>" class="card"><?= csrf_field() ?>
      <div class="field"><label for="discipline">What do you bring to this team?</label><select id="discipline" name="discipline" required><option value="">Choose your discipline…</option><?php foreach ($disciplines as $d): ?><option><?= e($d) ?></option><?php endforeach; ?></select><?= field_error('discipline') ?></div>
      <button class="btn btn-primary" type="submit">Ask to join</button></form>
  <?php endif; ?>
</section>
