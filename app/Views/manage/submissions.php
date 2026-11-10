<section class="container section">
  <h1>Submissions to review</h1>
  <?php if (!$rows): ?><div class="empty"><p class="muted">No submissions yet.</p></div><?php endif; ?>
  <?php foreach ($rows as $s): ?>
    <article class="card">
      <div class="between"><p><strong><?= e($s['learner']) ?></strong> · <?= e($s['assignment']) ?> <span class="muted small">in <?= e($s['course']) ?> · <?= e(time_ago($s['submitted_at'])) ?></span></p>
        <span class="badge <?= $s['status'] === 'graded' ? 'badge-green' : 'badge-warn' ?>"><?= $s['status'] === 'graded' ? 'Graded ' . (int)$s['score'] . '/' . (int)$s['max_score'] : 'Needs feedback' ?></span></div>
      <div class="quote"><?= nl2br(e($s['body'])) ?></div>
      <form method="post" action="<?= e(url('manage/submissions/' . $s['id'] . '/grade')) ?>" class="grade-form"><?= csrf_field() ?>
        <div class="field"><label for="sc<?= (int)$s['id'] ?>">Score (0–<?= (int)$s['max_score'] ?>)</label><input id="sc<?= (int)$s['id'] ?>" name="score" type="number" min="0" max="<?= (int)$s['max_score'] ?>" required value="<?= e($s['score'] ?? '') ?>"></div>
        <div class="field grow"><label for="fb<?= (int)$s['id'] ?>">Feedback to the learner</label><textarea id="fb<?= (int)$s['id'] ?>" name="feedback" rows="3" required maxlength="3000"><?= e($s['feedback'] ?? '') ?></textarea></div>
        <button class="btn btn-primary" type="submit"><?= $s['status'] === 'graded' ? 'Update feedback' : 'Send feedback' ?></button>
      </form>
    </article>
  <?php endforeach; ?>
</section>
