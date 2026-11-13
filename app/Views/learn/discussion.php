<?php
$me = (int)(auth_user()['id'] ?? 0);
$base = 'learn/' . $course['slug'] . '/discussion';
$isStaffRole = fn($role) => in_array($role, ['facilitator', 'admin', 'mentor'], true);
/** Small POST button for a moderation action on one post. */
$act = function (array $p, string $action, string $label, string $confirm = '') use ($base) {
    return '<form method="post" action="' . e(url($base . '/' . (int)$p['id'] . '/' . $action)) . '" class="inline"'
         . ($confirm !== '' ? ' data-confirm="' . e($confirm) . '"' : '') . '>' . csrf_field()
         . '<button class="linklike small" type="submit">' . e($label) . '</button></form>';
};
$actions = function (array $p, bool $top) use ($manage, $me, $act) {
    $out = [];
    if ($manage && $top) $out[] = $act($p, 'pin', $p['pinned'] ? 'Unpin' : '📌 Pin to top');
    if ($manage && $p['reported']) $out[] = $act($p, 'dismiss', 'Clear report');
    if (!$manage && (int)$p['user_id'] !== $me) $out[] = $act($p, 'report', '⚑ Report', 'Report this post to the facilitator?');
    if ($manage || (int)$p['user_id'] === $me) $out[] = $act($p, 'delete', 'Delete', 'Delete this post' . ($top ? ' and its replies' : '') . '? This cannot be undone.');
    return $out ? '<div class="post-actions">' . implode('', $out) . '</div>' : '';
};
?>
<div class="container player">
  <?php include BASE_PATH . '/app/Views/partials/learn_sidebar.php'; ?>
  <article class="player-main">
    <h1>Course discussion</h1>
    <p class="muted">Ask questions, share what you learned and help each other. Be kind and specific.<?= $manage ? ' As a facilitator you can pin, remove and review reported posts here.' : '' ?></p>
    <form method="post" action="<?= e(url('learn/' . $course['slug'] . '/discussion')) ?>" class="card" data-draft="disc-<?= (int)$course['id'] ?>">
      <?= csrf_field() ?>
      <div class="field"><label for="body"><?= $manage ? 'Post an announcement or question' : 'Start a conversation' ?></label><textarea id="body" name="body" rows="3" maxlength="2000" required></textarea><?= field_error('body') ?></div>
      <button class="btn btn-primary" type="submit">Post</button>
    </form>
    <?php if (!$top): ?><div class="empty"><p class="muted">No posts yet. Be the first to say hello.</p></div><?php endif; ?>
    <?php foreach ($top as $p): ?>
      <div class="post<?= $p['pinned'] ? ' pinned' : '' ?>">
        <?php if ($p['pinned']): ?><p class="pin-tag">📌 Pinned by the facilitator</p><?php endif; ?>
        <p><span class="avatar"><?= e(initials($p['name'])) ?></span> <strong><?= e($p['name']) ?></strong>
          <?php if ($isStaffRole($p['role'])): ?><span class="badge">Facilitator</span><?php endif; ?>
          <?php if ($manage && $p['reported']): ?><span class="badge badge-warn-solid">Reported</span><?php endif; ?>
          <span class="muted small"><?= e(time_ago($p['created_at'])) ?></span></p>
        <p><?= nl2br(e($p['body'])) ?></p>
        <?= $actions($p, true) ?>
        <?php foreach ($replies[$p['id']] ?? [] as $r): ?>
          <div class="reply"><p><strong><?= e($r['name']) ?></strong>
            <?php if ($isStaffRole($r['role'])): ?><span class="badge">Facilitator</span><?php endif; ?>
            <?php if ($manage && $r['reported']): ?><span class="badge badge-warn-solid">Reported</span><?php endif; ?>
            <span class="muted small"><?= e(time_ago($r['created_at'])) ?></span></p><p><?= nl2br(e($r['body'])) ?></p>
            <?= $actions($r, false) ?></div>
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
