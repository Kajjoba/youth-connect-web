<section class="container narrow section">
  <div class="between"><h1>Notifications</h1>
    <?php if (array_filter($items, fn($i) => !$i['read_at'])): ?><form method="post" action="<?= e(url('notifications/read')) ?>"><?= csrf_field() ?><button class="btn btn-outline btn-sm" type="submit">Mark all as read</button></form><?php endif; ?></div>
  <?php if (!$items): ?><div class="empty"><p class="muted">Nothing new yet. Updates about your courses, teams and projects appear here.</p></div><?php endif; ?>
  <ul class="plain notif">
    <?php foreach ($items as $n): ?>
      <li class="card <?= $n['read_at'] ? '' : 'unread' ?>">
        <?php if ($n['link']): ?><a href="<?= e(url($n['link'])) ?>"><?= e($n['message']) ?></a><?php else: ?><?= e($n['message']) ?><?php endif; ?>
        <span class="muted small"><?= e(time_ago($n['created_at'])) ?></span>
      </li>
    <?php endforeach; ?>
  </ul>
</section>
