<?php
/** Course picture: the course's own photo if it has one, otherwise artwork for its sector, on the course colour.
 *  @var array $c course row (color, category, category_slug, cover_path, id or course_id)  @var bool $small optional */
$cover = course_cover_url($c);
?>
<div class="thumb<?= !empty($small) ? ' thumb-sm' : '' ?> art-<?= e(course_art($c['category_slug'] ?? null)) ?><?= $cover ? ' has-cover' : '' ?>" style="--c:<?= e($c['color']) ?>">
  <?php if ($cover): ?><img class="thumb-img" src="<?= e($cover) ?>" alt="" loading="lazy" decoding="async" width="480" height="270"><?php endif; ?>
  <span class="thumb-cat"><?= e($c['category']) ?></span>
</div>