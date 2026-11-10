<section class="container section">
  <p class="small"><a href="<?= e(url('admin')) ?>">&larr; Dashboard</a></p>
  <h1>All projects</h1>
  <?php if (!$rows): ?><div class="empty"><p class="muted">No projects yet.</p></div><?php endif; ?>
  <div class="table-wrap"><table class="table"><thead><tr><th>Project</th><th>Team</th><th>Stage</th><th>Staff</th></tr></thead><tbody>
  <?php foreach ($rows as $r): ?>
    <tr><td data-label="Project"><a href="<?= e(url('projects/' . $r['public_id'])) ?>"><strong><?= e($r['title']) ?></strong></a></td><td data-label="Team"><?= e($r['team']) ?></td>
      <td data-label="Stage"><span class="badge ps-<?= e($r['status']) ?>"><?= e(status_label($r['status'])) ?></span></td><td data-label="Staff"><?= e($r['staff'] ?: 'None assigned') ?></td></tr>
  <?php endforeach; ?></tbody></table></div>
</section>
