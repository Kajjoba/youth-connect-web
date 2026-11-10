<section class="container section">
  <p class="small"><a href="<?= e(url('admin')) ?>">&larr; Dashboard</a></p>
  <h1>Audit log</h1>
  <p class="muted">The last 200 security and programme events.</p>
  <div class="table-wrap"><table class="table table-sm"><thead><tr><th>When</th><th>Who</th><th>Action</th><th>Target</th><th>Detail</th><th>IP</th></tr></thead><tbody>
  <?php foreach ($rows as $r): ?>
    <tr><td data-label="When"><?= e(date('j M H:i', strtotime($r['created_at']))) ?></td><td data-label="Who"><?= e($r['name'] ?: '–') ?></td><td data-label="Action"><code><?= e($r['action']) ?></code></td>
      <td data-label="Target"><?= e(trim(($r['entity'] ?? '') . ' ' . ($r['entity_id'] ?? ''))) ?></td><td data-label="Detail"><?= e($r['detail']) ?></td><td data-label="IP"><?= e($r['ip']) ?></td></tr>
  <?php endforeach; ?></tbody></table></div>
</section>
