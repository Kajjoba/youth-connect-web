<?php
/** @var array $course @var ?array $enrollment @var ?array $progress */
$u = auth_user();
?>
<?php if (!$u): ?>
  <a class="btn btn-primary btn-lg btn-block" href="<?= e(url('register')) ?>">Enrol for free</a>
  <p class="small muted center">Free to join. <a href="<?= e(url('login')) ?>">Log in</a></p>
<?php elseif ($u['role'] !== 'participant'): ?>
  <?php if (\App\Core\Policy::manageCourse($course)): ?>
    <a class="btn btn-primary btn-lg btn-block" href="<?= e(url('manage/courses/' . $course['id'])) ?>">Edit this course</a>
    <a class="btn btn-outline btn-block" href="<?= e(url('learn/' . $course['slug'])) ?>">Preview as learner</a>
  <?php else: ?><p class="small muted">Enrolment is for participant accounts.</p><?php endif; ?>
<?php elseif ($enrollment): ?>
  <?php $myCert = $enrollment['status'] === 'completed' ? \App\Core\DB::val('SELECT code FROM certificates WHERE user_id = ? AND course_id = ? AND kind = ?', [$u['id'], $course['id'], 'course']) : null; ?>
  <?php if ($myCert): ?><a class="btn btn-primary btn-lg btn-block" href="<?= e(url('certificate/' . $myCert . '?download=1')) ?>">🎓 Download my certificate</a><?php endif; ?>
  <a class="btn <?= $myCert ? 'btn-outline' : 'btn-primary' ?> btn-lg btn-block" href="<?= e(url('learn/' . $course['slug'])) ?>"><?= ($progress['pct'] ?? 0) > 0 ? 'Continue learning' : 'Go to course' ?></a>
  <div class="progress" role="progressbar" aria-valuenow="<?= (int)($progress['pct'] ?? 0) ?>" aria-valuemin="0" aria-valuemax="100"><span style="width:<?= (int)($progress['pct'] ?? 0) ?>%"></span></div>
  <p class="small muted center"><?= (int)($progress['pct'] ?? 0) ?>% complete<?= $enrollment['status'] === 'completed' ? ' · Completed' : '' ?></p>
<?php else: ?>
  <form method="post" action="<?= e(url('course/' . $course['slug'] . '/enroll')) ?>"><?= csrf_field() ?>
    <button class="btn btn-primary btn-lg btn-block" type="submit">Enrol for free</button></form>
  <p class="small muted center">Starts today · learn at your own pace</p>
<?php endif; ?>
