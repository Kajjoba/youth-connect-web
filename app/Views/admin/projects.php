<section class="container section">
  <p class="small"><a href="<?= e(url('admin')) ?>">&larr; Dashboard</a></p>
  <h1>All projects</h1>
  <p class="muted">A project needs a mentor (or reviewer) before anyone can review its proposal. Open a project and use <strong>Mentors &amp; reviewers → Assign someone</strong>.</p>
  <?php if (!$rows): ?><div class="empty"><p class="muted">No projects yet.</p></div><?php endif; ?>
  <div class="table-wrap"><table class="table"><thead><tr><th>Project</th><th>Team</th><th>Stage</th><th>Staff</th></tr></thead><tbody>
  <?php foreach ($rows as $r): ?>
    <tr><td data-label="Project"><a href="<?= e(url('projects/' . $r['public_id'])) ?>"><strong><?= e($r['title']) ?></strong></a></td><td data-label="Team"><?= e($r['team']) ?></td>
      <td data-label="Stage"><span class="badge ps-<?= e($r['status']) ?>"><?= e(status_label($r['status'])) ?></span></td>
      <td data-label="Staff"><?php if ($r['staff']): ?><?= e($r['staff']) ?> <a class="small" href="<?= e(url('projects/' . $r['public_id'] . '#mentors')) ?>">Change</a>
        <?php else: ?><span class="badge badge-warn"><?= $r['needs_mentor'] ? 'Needs a mentor' : 'None assigned' ?></span> <a class="btn btn-outline btn-sm" href="<?= e(url('projects/' . $r['public_id'] . '#mentors')) ?>">Assign a mentor</a><?php endif; ?></td></tr>
  <?php endforeach; ?></tbody></table></div>
</section>