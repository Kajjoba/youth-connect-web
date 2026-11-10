<div class="container player">
  <?php include BASE_PATH . '/app/Views/partials/learn_sidebar.php'; ?>
  <article class="player-main">
    <p class="muted small">Week <?= (int)$quiz['week_no'] ?> · <?= e($quiz['module_title']) ?></p>
    <h1><?= e($quiz['title']) ?></h1>
    <p class="muted small">Quiz · <?= count($quiz['questions']) ?> questions · Pass mark <?= (int)$quiz['pass_mark'] ?>% · You can retake it as many times as you like.</p>

    <?php if ($review): $att = $review['attempt']; ?>
      <div class="result <?= $att['passed'] ? 'pass' : 'fail' ?>" role="status">
        <p class="score"><?= (int)$att['score'] ?>%</p>
        <p><strong><?= $att['passed'] ? 'Well done, you passed!' : 'Not quite there yet.' ?></strong>
          <?= $att['passed'] ? 'This week is complete.' : 'You need ' . (int)$quiz['pass_mark'] . '% to pass. Review the notes below and try again, there is no penalty.' ?></p>
      </div>
      <ol class="questions review">
        <?php foreach ($quiz['questions'] as $q): $given = (int)($review['given'][$q['id']] ?? 0); $right = (int)($review['correct'][$q['id']] ?? 0); $ok = $given && $given === $right; ?>
          <li class="q <?= $ok ? 'ok' : 'bad' ?>">
            <p class="qtext"><?= e($q['body']) ?> <span class="verdict"><?= $ok ? '✓ Correct' : ($given ? '✗ Incorrect' : '✗ Not answered') ?></span></p>
            <ul class="opts">
              <?php foreach ($q['options'] as $o): $mine = (int)$o['id'] === $given; ?>
                <li class="<?= $mine ? ($ok ? 'sel-ok' : 'sel-bad') : '' ?> <?= ($att['passed'] && (int)$o['id'] === $right) ? 'is-right' : '' ?>">
                  <?= $mine ? '● ' : '○ ' ?><?= e($o['body']) ?><?= ($att['passed'] && (int)$o['id'] === $right && !$mine) ? ' <span class="muted small">(correct answer)</span>' : '' ?></li>
              <?php endforeach; ?>
            </ul>
            <?php if (!$ok && $q['explanation']): ?><p class="explain">💡 <?= e($q['explanation']) ?></p><?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ol>
      <div class="lesson-actions">
        <a class="btn btn-outline" href="<?= e(url('learn/' . $course['slug'] . '/quiz/' . $quiz['module_id'])) ?>"><?= $att['passed'] ? 'Retake for practice' : 'Try again' ?></a>
        <?php if ($next): ?><a class="btn btn-primary" href="<?= e(url($next['url'])) ?>">Continue &rarr;</a>
        <?php else: ?><a class="btn btn-primary" href="<?= e(url('course/' . $course['slug'])) ?>">Back to course</a><?php endif; ?>
      </div>
    <?php elseif ($enrolled): ?>
      <form method="post" action="<?= e(url('learn/' . $course['slug'] . '/quiz/' . $quiz['module_id'])) ?>" data-draft="quiz-<?= (int)$quiz['id'] ?>">
        <?= csrf_field() ?>
        <ol class="questions">
          <?php foreach ($quiz['questions'] as $q): ?>
            <li class="q">
              <fieldset><legend class="qtext"><?= e($q['body']) ?></legend>
                <?php foreach ($q['options'] as $o): ?>
                  <label class="opt"><input type="radio" name="answers[<?= (int)$q['id'] ?>]" value="<?= (int)$o['id'] ?>" required> <span><?= e($o['body']) ?></span></label>
                <?php endforeach; ?>
              </fieldset>
            </li>
          <?php endforeach; ?>
        </ol>
        <p class="small muted">Your choices are saved on this device while you work, so a dropped connection will not lose them.</p>
        <button class="btn btn-primary btn-lg" type="submit">Submit quiz</button>
      </form>
      <?php if ($attempts): ?>
        <h2>Your attempts</h2>
        <ul class="plain"><?php foreach ($attempts as $a): ?>
          <li><a href="<?= e(url('learn/' . $course['slug'] . '/quiz/' . $quiz['module_id'] . '?attempt=' . $a['id'])) ?>"><?= (int)$a['score'] ?>% · <?= $a['passed'] ? 'Passed' : 'Not passed' ?></a> <span class="muted small"><?= e(time_ago($a['created_at'])) ?></span></li>
        <?php endforeach; ?></ul>
      <?php endif; ?>
    <?php else: ?>
      <p class="card">You are previewing this quiz as course staff. Learners answer these <?= count($quiz['questions']) ?> questions and see feedback straight away.</p>
      <ol class="questions"><?php foreach ($quiz['questions'] as $q): ?><li class="q"><p class="qtext"><?= e($q['body']) ?></p></li><?php endforeach; ?></ol>
    <?php endif; ?>

    <?php if (!$review && $prev): ?><p><a href="<?= e(url($prev['url'])) ?>">&larr; Back to <?= e($prev['title']) ?></a></p><?php endif; ?>
  </article>
</div>
