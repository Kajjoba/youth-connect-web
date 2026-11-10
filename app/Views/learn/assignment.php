<div class="container player">
  <?php include BASE_PATH . '/app/Views/partials/learn_sidebar.php'; ?>
  <article class="player-main">
    <p class="muted small">Week <?= (int)$asg['week_no'] ?> · <?= e($asg['module_title']) ?></p>
    <h1><?= e($asg['title']) ?></h1>
    <p class="muted small">Graded assignment · reviewed by your facilitator, who will send feedback</p>
    <div class="prose card"><?php foreach (preg_split('/\R{2,}/', $asg['instructions']) as $para): ?><p><?= nl2br(e(trim($para))) ?></p><?php endforeach; ?></div>

    <?php if ($sub && $sub['status'] === 'graded'): ?>
      <div class="result pass"><p class="score"><?= (int)$sub['score'] ?><small>/<?= (int)$asg['max_score'] ?></small></p>
        <div><strong>Graded</strong><p class="muted small"><?= e(time_ago($sub['graded_at'])) ?></p><p><?= nl2br(e($sub['feedback'])) ?></p></div></div>
      <h2>Your submission</h2><div class="card"><?= nl2br(e($sub['body'])) ?></div>
    <?php elseif ($enrolled): ?>
      <?php if ($sub): ?><p class="alert alert-info">Submitted <?= e(time_ago($sub['submitted_at'])) ?>. Waiting for your facilitator. You can still edit and resubmit until it is graded.</p><?php endif; ?>
      <form method="post" action="<?= e(url('learn/' . $course['slug'] . '/assignment/' . $asg['module_id'])) ?>" data-draft="asg-<?= (int)$asg['id'] ?>">
        <?= csrf_field() ?>
        <div class="field"><label for="body">Your answer</label>
          <textarea id="body" name="body" rows="10" required minlength="30" maxlength="5000"><?= e(old('body', $sub['body'] ?? '')) ?></textarea>
          <p class="hint">Saved on this device as you type. At least 30 characters.</p><?= field_error('body') ?></div>
        <button class="btn btn-primary btn-lg" type="submit"><?= $sub ? 'Resubmit' : 'Submit for feedback' ?></button>
      </form>
    <?php endif; ?>
    <div class="lesson-actions">
      <?php if ($prev): ?><a class="btn btn-outline" href="<?= e(url($prev['url'])) ?>">&larr; Previous</a><?php else: ?><span></span><?php endif; ?>
      <?php if ($next): ?><a class="btn btn-primary" href="<?= e(url($next['url'])) ?>">Next &rarr;</a><?php endif; ?>
    </div>
  </article>
</div>
