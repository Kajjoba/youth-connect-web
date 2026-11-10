<section class="container section">
  <p class="small"><a href="<?= e(url('manage/courses')) ?>">&larr; All courses</a></p>
  <div class="between">
    <h1><?= e($course['title']) ?> <span class="badge st-<?= e($course['status']) ?>"><?= e(ucfirst($course['status'])) ?></span></h1>
    <div class="row">
      <a class="btn btn-outline" href="<?= e(url('course/' . $course['slug'])) ?>">View page</a>
      <a class="btn btn-outline" href="<?= e(url('manage/courses/' . $course['id'] . '/learners')) ?>">Learners</a>
      <form method="post" action="<?= e(url('manage/courses/' . $course['id'] . '/status')) ?>"><?= csrf_field() ?>
        <?php if ($course['status'] === 'published'): ?><input type="hidden" name="status" value="draft"><button class="btn btn-outline" type="submit">Unpublish</button>
        <?php else: ?><input type="hidden" name="status" value="published"><button class="btn btn-primary" type="submit">Publish</button><?php endif; ?></form>
    </div>
  </div>

  <h2>Weekly outline</h2>
  <p class="muted">Each week is a module with lessons, a quiz and an optional graded assignment.</p>
  <div class="table-wrap"><table class="table">
    <thead><tr><th>Week</th><th>Title</th><th>Lessons</th><th>Quiz questions</th><th>Assignment</th><th></th></tr></thead><tbody>
    <?php foreach ($modules as $m): ?>
      <tr><td data-label="Week"><?= (int)$m['week_no'] ?></td><td data-label="Title"><a href="<?= e(url('manage/modules/' . $m['id'])) ?>"><strong><?= e($m['title']) ?></strong></a></td>
        <td data-label="Lessons"><?= (int)$m['lessons'] ?></td>
        <td data-label="Quiz"><?= (int)$m['questions'] ?><?= $m['questions'] == 0 ? ' <span class="badge badge-warn">no quiz yet</span>' : '' ?></td>
        <td data-label="Assignment"><?= $m['has_assignment'] ? 'Yes' : '–' ?></td>
        <td><a class="btn btn-outline btn-sm" href="<?= e(url('manage/modules/' . $m['id'])) ?>">Edit week</a></td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
  <form method="post" action="<?= e(url('manage/courses/' . $course['id'] . '/weeks')) ?>" class="row add-row"><?= csrf_field() ?>
    <label class="sr" for="wk">New week title</label><input id="wk" name="title" required maxlength="160" placeholder="Title for the next week, e.g. Spotting misinformation">
    <button class="btn btn-primary" type="submit">+ Add week</button></form>

  <h2>Course details</h2>
  <form method="post" action="<?= e(url('manage/courses/' . $course['id'])) ?>" novalidate>
    <?= csrf_field() ?>
    <?php include __DIR__ . '/_course_fields.php'; ?>
    <p><button class="btn btn-primary" type="submit">Save details</button></p>
  </form>
</section>
