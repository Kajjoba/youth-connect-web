<section class="container section">
  <p class="small"><a href="<?= e(url('manage/courses/' . $course['id'])) ?>">&larr; <?= e($course['title']) ?></a></p>
  <h1>Learners (<?= count($rows) ?>)</h1>
  <?php if (!$rows): ?><div class="empty"><p class="muted">No one has enrolled yet.</p></div><?php else: ?>
  <div class="table-wrap"><table class="table"><thead><tr><th>Name</th><th>Country</th><th>Progress</th><th>Status</th><th>Enrolled</th></tr></thead><tbody>
    <?php foreach ($rows as $r): ?>
      <tr><td data-label="Name"><?= e($r['name']) ?><br><span class="muted small"><?= e($r['email']) ?></span></td><td data-label="Country"><?= e($r['country'] ?: '–') ?></td>
        <td data-label="Progress"><div class="progress sm"><span style="width:<?= (int)$r['progress']['pct'] ?>%"></span></div><span class="small"><?= (int)$r['progress']['pct'] ?>%</span></td>
        <td data-label="Status"><span class="badge"><?= e(ucfirst($r['status'])) ?></span></td><td data-label="Enrolled"><?= e(time_ago($r['enrolled_at'])) ?></td></tr>
    <?php endforeach; ?></tbody></table></div><?php endif; ?>
</section>
