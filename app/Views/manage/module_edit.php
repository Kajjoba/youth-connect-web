<section class="container section">
  <p class="small"><a href="<?= e(url('manage/courses/' . $m['course_id'])) ?>">&larr; <?= e($m['course_title']) ?></a></p>
  <div class="between"><h1>Week <?= (int)$m['week_no'] ?>: <?= e($m['title']) ?></h1>
    <form method="post" action="<?= e(url('manage/modules/' . $m['id'] . '/delete')) ?>" data-confirm="Delete Week <?= (int)$m['week_no'] ?> and everything in it (lessons, quiz, assignment)? Learner progress for it will be lost."><?= csrf_field() ?>
      <button class="btn btn-danger btn-sm" type="submit">Delete week</button></form></div>

  <h2>Week details</h2>
  <form method="post" action="<?= e(url('manage/modules/' . $m['id'])) ?>" novalidate>
    <?= csrf_field() ?>
    <div class="field"><label for="title">Week title</label><input id="title" name="title" required maxlength="160" value="<?= e(old('title', $m['title'])) ?>"><?= field_error('title') ?></div>
    <div class="field"><label for="description">What this week covers</label><textarea id="description" name="description" rows="3" maxlength="1000"><?= e(old('description', $m['description'])) ?></textarea></div>
    <div class="field"><label for="hours_estimate">Hours to complete</label><input id="hours_estimate" name="hours_estimate" type="number" min="1" max="40" required value="<?= e(old('hours_estimate', $m['hours_estimate'])) ?>"></div>
    <button class="btn btn-primary" type="submit">Save week</button>
  </form>

  <h2>Lessons (<?= count($lessons) ?>)</h2>
  <ul class="plain list-cards">
    <?php foreach ($lessons as $l): ?>
      <li class="card row between"><span><?= e($l['position']) ?>. <strong><?= e($l['title']) ?></strong> <span class="muted small"><?= e($l['type']) ?> · <?= (int)$l['minutes'] ?> min<?= $l['is_preview'] ? ' · preview' : '' ?></span></span>
        <span class="row"><a class="btn btn-outline btn-sm" href="<?= e(url('manage/lessons/' . $l['id'])) ?>">Edit</a>
        <form method="post" action="<?= e(url('manage/lessons/' . $l['id'] . '/delete')) ?>" data-confirm="Delete this lesson?"><?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Delete</button></form></span></li>
    <?php endforeach; ?>
  </ul>
  <details class="card" <?= $lessons ? '' : 'open' ?>><summary><strong>+ Add a lesson</strong></summary>
    <form method="post" action="<?= e(url('manage/modules/' . $m['id'] . '/lessons')) ?>" novalidate>
      <?= csrf_field() ?>
      <div class="field-row">
        <div class="field"><label for="l-title">Lesson title</label><input id="l-title" name="title" required maxlength="160"><?= field_error('title') ?></div>
        <div class="field"><label for="l-type">Type</label><select id="l-type" name="type"><option value="reading">Reading</option><option value="video">Video (YouTube link)</option><option value="discussion">Discussion prompt</option></select></div>
        <div class="field"><label for="l-min">Minutes</label><input id="l-min" name="minutes" type="number" min="1" value="10" required></div>
      </div>
      <div class="field"><label for="l-content">Lesson text</label><textarea id="l-content" name="content" rows="8"></textarea>
        <p class="hint">Blank line = new paragraph. Start a line with "## " for a heading, and lines with "- " for bullets.</p></div>
      <div class="field"><label for="l-video">YouTube link (optional)</label><input id="l-video" name="video_url" type="url" maxlength="300" placeholder="https://www.youtube.com/watch?v=…"></div>
      <label class="check"><input type="checkbox" name="is_preview" value="1"> Let visitors preview this lesson before enrolling</label>
      <p><button class="btn btn-primary" type="submit">Add lesson</button></p>
    </form>
  </details>

  <h2 id="quiz">Week <?= (int)$m['week_no'] ?> quiz (<?= count($questions) ?> question<?= count($questions) == 1 ? '' : 's' ?>)</h2>
  <p class="muted">Learners must pass this quiz (<?= (int)($quiz['pass_mark'] ?? $m['pass_mark']) ?>%) to complete the week.</p>
  <?php foreach ($questions as $i => $q): ?>
    <div class="card qedit"><div class="between"><p><strong><?= $i + 1 ?>. <?= e($q['body']) ?></strong></p>
      <form method="post" action="<?= e(url('manage/questions/' . $q['id'] . '/delete')) ?>" data-confirm="Delete this question?"><?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Delete</button></form></div>
      <ul class="plain"><?php foreach ($q['options'] as $o): ?><li><?= $o['is_correct'] ? '✅' : '▫️' ?> <?= e($o['body']) ?></li><?php endforeach; ?></ul>
      <?php if ($q['explanation']): ?><p class="muted small">Feedback: <?= e($q['explanation']) ?></p><?php endif; ?></div>
  <?php endforeach; ?>
  <details class="card" <?= $questions ? '' : 'open' ?>><summary><strong>+ Add a question</strong></summary>
    <form method="post" action="<?= e(url('manage/modules/' . $m['id'] . '/questions')) ?>" novalidate>
      <?= csrf_field() ?>
      <div class="field"><label for="q-body">Question</label><input id="q-body" name="body" required maxlength="500"><?= field_error('body') ?></div>
      <?php for ($i = 1; $i <= 4; $i++): ?>
        <div class="field opt-row"><label for="o<?= $i ?>">Option <?= $i ?><?= $i > 2 ? ' (optional)' : '' ?></label><input id="o<?= $i ?>" name="opt<?= $i ?>" maxlength="300" <?= $i <= 2 ? 'required' : '' ?>>
          <label class="check"><input type="radio" name="correct" value="<?= $i ?>" <?= $i === 1 ? 'checked' : '' ?>> Correct answer</label></div>
      <?php endfor; ?>
      <div class="field"><label for="q-exp">Feedback shown when answered wrongly (optional)</label><input id="q-exp" name="explanation" maxlength="500"></div>
      <button class="btn btn-primary" type="submit">Add question</button>
    </form>
  </details>

  <h2 id="assignment">Graded assignment (optional)</h2>
  <form method="post" action="<?= e(url('manage/modules/' . $m['id'] . '/assignment')) ?>" novalidate>
    <?= csrf_field() ?>
    <div class="field"><label for="a-title">Assignment title</label><input id="a-title" name="title" maxlength="160" required value="<?= e(old('title', $assignment['title'] ?? '')) ?>"></div>
    <div class="field"><label for="a-ins">Instructions</label><textarea id="a-ins" name="instructions" rows="5" required maxlength="4000"><?= e($assignment['instructions'] ?? '') ?></textarea></div>
    <button class="btn btn-primary" type="submit"><?= $assignment ? 'Save assignment' : 'Add assignment' ?></button>
    <?php if ($assignment): ?><button class="btn btn-danger" type="submit" name="remove" value="1" data-confirm-btn="Remove this assignment and its submissions?">Remove</button><?php endif; ?>
  </form>
</section>
