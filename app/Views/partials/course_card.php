<?php /** @var array $c */ ?>
<article class="course-card">
  <a class="card-link" href="<?= e(url('course/' . $c['slug'])) ?>">
    <div class="thumb" style="--c:<?= e($c['color']) ?>"<?= lite_mode() ? ' data-lite' : '' ?>>
      <span class="thumb-cat"><?= e($c['category']) ?></span>
      <span class="thumb-letter" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($c['title'], 0, 1))) ?></span>
    </div>
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
