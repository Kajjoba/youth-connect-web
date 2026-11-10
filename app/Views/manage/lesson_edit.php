<section class="container narrow section">
  <p class="small"><a href="<?= e(url('manage/modules/' . $l['module_id'])) ?>">&larr; Week <?= (int)$l['week_no'] ?></a></p>
  <h1>Edit lesson</h1>
  <form method="post" action="<?= e(url('manage/lessons/' . $l['id'])) ?>" novalidate>
    <?= csrf_field() ?>
    <div class="field"><label for="title">Title</label><input id="title" name="title" required maxlength="160" value="<?= e(old('title', $l['title'])) ?>"><?= field_error('title') ?></div>
    <div class="field-row">
      <div class="field"><label for="type">Type</label><select id="type" name="type"><?php foreach (['reading' => 'Reading', 'video' => 'Video', 'discussion' => 'Discussion prompt'] as $k => $v): ?><option value="<?= $k ?>" <?= $l['type'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
      <div class="field"><label for="minutes">Minutes</label><input id="minutes" name="minutes" type="number" min="1" required value="<?= (int)$l['minutes'] ?>"></div>
      <div class="field"><label for="position">Order in week</label><input id="position" name="position" type="number" min="1" required value="<?= (int)$l['position'] ?>"></div>
    </div>
    <div class="field"><label for="content">Lesson text</label><textarea id="content" name="content" rows="14"><?= e(old('content', $l['content'])) ?></textarea></div>
    <div class="field"><label for="video_url">YouTube link</label><input id="video_url" name="video_url" type="url" maxlength="300" value="<?= e(old('video_url', $l['video_url'])) ?>"></div>
    <label class="check"><input type="checkbox" name="is_preview" value="1" <?= $l['is_preview'] ? 'checked' : '' ?>> Visitors can preview this lesson</label>
    <p><button class="btn btn-primary" type="submit">Save lesson</button></p>
  </form>
</section>
