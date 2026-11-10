<div class="container player">
  <?php include BASE_PATH . '/app/Views/partials/learn_sidebar.php'; ?>
  <article class="player-main">
    <h1>Course discussion</h1>
    <p class="muted">Ask questions, share what you learned and help each other. Be kind and specific.</p>
    <form method="post" action="<?= e(url('learn/' . $course['slug'] . '/discussion')) ?>" class="card" data-draft="disc-<?= (int)$course['id'] ?>">
      <?= csrf_field() ?>
      <div class="field"><label for="body">Start a conversation</label><textarea id="body" name="body" rows="3" maxlength="2000" required></textarea><?= field_error('body') ?></div>
      <button class="btn btn-primary" type="submit">Post</button>
    </form>
    <?php if (!$top): ?><div class="empty"><p class="muted">No posts yet. Be the first to say hello.</p></div><?php endif; ?>
    <?php foreach ($top as $p): ?>
      <div class="post">
        <p><span class="avatar"><?= e(initials($p['name'])) ?></span> <strong><?= e($p['name']) ?></strong>
          <?php if (in_array($p['role'], ['facilitator', 'admin', 'mentor'], true)): ?><span class="badge">Facilitator</span><?php endif; ?>
          <span class="muted small"><?= e(time_ago($p['created_at'])) ?></span></p>
        <p><?= nl2br(e($p['body'])) ?></p>
        <?php foreach ($replies[$p['id']] ?? [] as $r): ?>
          <div class="reply"><p><strong><?= e($r['name']) ?></strong> <span class="muted small"><?= e(time_ago($r['created_at'])) ?></span></p><p><?= nl2br(e($r['body'])) ?></p></div>
        <?php endforeach; ?>
        <details><summary class="small">Reply</summary>
          <form method="post" action="<?= e(url('learn/' . $course['slug'] . '/discussion')) ?>"><?= csrf_field() ?>
            <input type="hidden" name="parent_id" value="<?= (int)$p['id'] ?>">
            <label class="sr" for="r<?= (int)$p['id'] ?>">Your reply</label>
            <textarea id="r<?= (int)$p['id'] ?>" name="body" rows="2" maxlength="2000" required></textarea>
            <button class="btn btn-outline btn-sm" type="submit">Reply</button></form>
        </details>
      </div>
    <?php endforeach; ?>
  </article>
</div>
