<?php
$yt = null;
if ($lesson['video_url'] && preg_match('~(?:youtu\.be/|v=|embed/)([A-Za-z0-9_-]{11})~', $lesson['video_url'], $mm)) $yt = $mm[1];
?>
<div class="container player">
  <?php include BASE_PATH . '/app/Views/partials/learn_sidebar.php'; ?>
  <article class="player-main">
    <p class="muted small">Week <?= (int)$lesson['week_no'] ?> · <?= e($lesson['module_title']) ?></p>
    <h1><?= e($lesson['title']) ?></h1>
    <p class="muted small"><?= ucfirst(e($lesson['type'])) ?> · <?= (int)$lesson['minutes'] ?> min<?= (!$enrolled && !$manage) ? ' · <span class="badge badge-green">Preview</span>' : '' ?></p>

    <?php if ($lesson['type'] === 'video' && $yt): ?>
      <div class="video" data-video="<?= e($yt) ?>">
        <button type="button" class="btn btn-outline" data-load-video>▶ Load video (uses mobile data)</button>
        <p class="muted small">Prefer to save data? Read the lesson notes below instead.</p>
      </div>
    <?php endif; ?>

    <?php if ($lesson['type'] === 'audio' && !empty($lesson['audio_path'])): ?>
      <div class="audio-box"><p class="audio-title">🎧 <?= e($lesson['audio_name'] ?: $lesson['title']) ?></p>
        <audio controls preload="none" src="<?= e(url('media/lesson/' . (int)$lesson['id'] . '/audio')) ?>">Your browser cannot play audio. Read the notes below instead.</audio>
        <p class="muted small">Nothing downloads until you press play. Prefer to save data? Read the notes below.</p></div>
    <?php endif; ?>
    <div class="prose"><?= render_prose((string)$lesson['content']) ?></div>

    <?php if ($lesson['type'] === 'discussion'): ?>
      <p><a class="btn btn-outline" href="<?= e(url('learn/' . $course['slug'] . '/discussion')) ?>">Join the discussion</a></p>
    <?php endif; ?>

    <div class="lesson-actions">
      <?php if ($prev): ?><a class="btn btn-outline" href="<?= e(url($prev['url'])) ?>">&larr; Previous</a><?php else: ?><span></span><?php endif; ?>
      <?php if ($enrolled): ?>
        <form method="post" action="<?= e(url('learn/' . $course['slug'] . '/lesson/' . $lesson['id'] . '/complete')) ?>" data-complete><?= csrf_field() ?>
          <button class="btn btn-primary" type="submit"><?= $done ? ($next ? 'Next &rarr;' : 'Finish') : ($next ? 'Mark complete &amp; continue' : 'Mark complete') ?></button>
        </form>
      <?php elseif ($next): ?>
        <a class="btn btn-primary" href="<?= e(url($next['url'])) ?>">Next &rarr;</a>
      <?php endif; ?>
    </div>
    <?php if (!$enrolled && !$manage): ?>
      <div class="card callout"><strong>Enjoying the preview?</strong> Enrol for free to track progress, take quizzes and earn a certificate.
        <form method="post" action="<?= e(url('course/' . $course['slug'] . '/enroll')) ?>"><?= csrf_field() ?><button class="btn btn-primary" type="submit">Enrol for free</button></form></div>
    <?php endif; ?>
  </article>
</div>
