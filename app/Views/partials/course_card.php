<?php /** @var array $c */ ?>
<article class="course-card">
  <a class="card-link" href="<?= e(url('course/' . $c['slug'])) ?>">
    <?php $small = false; include BASE_PATH . '/app/Views/partials/thumb.php'; ?>
    <div class="card-body">
      <p class="provider"><?= e($c['owner']) ?> · YouthConnect</p>
      <h3><?= e($c['title']) ?></h3>
      <p class="muted small"><?= e($c['level']) ?> · <?= (int)$c['weeks'] ?> week<?= $c['weeks'] == 1 ? '' : 's' ?> · <?= (int)$c['hours_per_week'] ?> h/week</p>
      <p class="rating small">
        <?php if ($c['reviews'] > 0): ?><span class="star">&#9733;</span> <strong><?= e(stars((float)$c['rating'])) ?></strong> <span class="muted">(<?= (int)$c['reviews'] ?>)</span> ·<?php endif; ?>
        <span class="muted"><?= number_format((int)$c['enrolled']) ?> enrolled</span>
      </p>
    </div>
  </a>
</article>
