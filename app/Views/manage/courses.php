<section class="container section">
  <div class="between"><h1>Manage courses</h1><a class="btn btn-primary" href="<?= e(url('manage/courses/new')) ?>">+ New course</a></div>
  <?php if ($pending): ?><p class="alert alert-info"><?= (int)$pending ?> submission<?= $pending == 1 ? '' : 's' ?> waiting for feedback. <a href="<?= e(url('manage/submissions')) ?>">Review now</a></p><?php endif; ?>
  <?php if (!$rows): ?><div class="empty"><h3>No courses yet</h3><p class="muted">Create your first course: add weekly modules, lessons and a quiz for each week.</p></div><?php endif; ?>
  <div class="table-wrap"><table class="table">
    <thead><tr><th>Course</th><th>Category</th><th>Weeks</th><th>Learners</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td data-label="Course"><span class="dotc" style="background:<?= e($r['color']) ?>"></span> <a href="<?= e(url('manage/courses/' . $r['id'])) ?>"><strong><?= e($r['title']) ?></strong></a><?php if (auth_user()['role'] === 'admin'): ?><br><span class="muted small">by <?= e($r['owner']) ?></span><?php endif; ?></td>
        <td data-label="Category"><?= e($r['category']) ?></td>
        <td data-label="Weeks"><?= (int)$r['weeks'] ?></td>
        <td data-label="Learners"><?= (int)$r['learners'] ?></td>
        <td data-label="Status"><span class="badge st-<?= e($r['status']) ?>"><?= e(ucfirst($r['status'])) ?></span></td>
        <td><a class="btn btn-outline btn-sm" href="<?= e(url('manage/courses/' . $r['id'])) ?>">Edit</a> <a class="btn btn-outline btn-sm" href="<?= e(url('manage/courses/' . $r['id'] . '/learners')) ?>">Learners</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody></table></div>
</section>
