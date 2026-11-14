<?php /** @var ?array $course @var array $categories */
$v = fn($k, $d = '') => old($k, $course[$k] ?? $d); ?>
<div class="field"><label for="title">Course title</label><input id="title" name="title" required maxlength="160" value="<?= e($v('title')) ?>"><?= field_error('title') ?></div>
<div class="field"><label for="summary">One-line summary (shown on cards)</label><input id="summary" name="summary" required maxlength="300" value="<?= e($v('summary')) ?>"><?= field_error('summary') ?></div>
<div class="field"><label for="description">About this course</label><textarea id="description" name="description" rows="6" required maxlength="5000"><?= e($v('description')) ?></textarea><p class="hint">Separate paragraphs with a blank line.</p><?= field_error('description') ?></div>
<div class="field"><label for="what_you_learn">What learners will learn (one outcome per line)</label><textarea id="what_you_learn" name="what_you_learn" rows="5" required><?= e($v('what_you_learn')) ?></textarea><?= field_error('what_you_learn') ?></div>
<div class="field"><label for="skills">Skills gained (comma separated)</label><input id="skills" name="skills" maxlength="300" value="<?= e($v('skills')) ?>"></div>
<div class="field cover-field"><label for="cover">Cover photo (shown on the course card and page)</label>
  <?php if (!empty($course['cover_path'])): ?>
    <div class="cover-current"><img src="<?= e(url('media/course/' . (int)$course['id'] . '/cover') . '?v=' . substr(md5($course['cover_path']), 0, 8)) ?>" alt="Current cover" width="240" height="135" loading="lazy">
      <label class="check"><input type="checkbox" name="remove_cover" value="1"> Remove this photo and use the automatic sector artwork</label></div>
  <?php endif; ?>
  <input id="cover" name="cover" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
  <p class="hint">JPG, PNG or WebP. A wide photo (16:9) works best. It is shrunk automatically so pages stay light on mobile data. Leave empty to use the artwork for this course's category.</p></div>
<div class="field-row">
  <div class="field"><label for="category_id">Category</label><select id="category_id" name="category_id" required>
    <?php foreach ($categories as $c): ?><option value="<?= (int)$c['id'] ?>" <?= (int)$v('category_id') === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select><?= field_error('category_id') ?></div>
  <div class="field"><label for="level">Level</label><select id="level" name="level"><?php foreach (['Beginner', 'Intermediate', 'Advanced'] as $l): ?><option <?= $v('level', 'Beginner') === $l ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
</div>
<div class="field-row">
  <div class="field"><label for="hours_per_week">Hours per week</label><input id="hours_per_week" name="hours_per_week" type="number" min="1" max="40" required value="<?= e($v('hours_per_week', 3)) ?>"><?= field_error('hours_per_week') ?></div>
  <div class="field"><label for="language">Language</label><input id="language" name="language" required maxlength="40" value="<?= e($v('language', 'English')) ?>"></div>
  <div class="field"><label for="pass_mark">Quiz pass mark (%)</label><input id="pass_mark" name="pass_mark" type="number" min="30" max="100" required value="<?= e($v('pass_mark', 70)) ?>"><?= field_error('pass_mark') ?></div>
  <div class="field"><label for="color">Card colour</label><input id="color" name="color" type="color" value="<?= e($v('color', '#0b5cd5')) ?>"></div>
</div>
<label class="check"><input type="checkbox" name="has_certificate" value="1" <?= ($course['has_certificate'] ?? 1) ? 'checked' : '' ?>> Issue a certificate on completion</label>
