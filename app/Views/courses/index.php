<?php
$qs = function (array $over = []) use ($q, $cat, $level, $sort) {
    $p = array_filter(['q' => $q, 'category' => $cat, 'level' => $level, 'sort' => $sort === 'popular' ? '' : $sort], 'strlen');
    foreach ($over as $k => $v) { if ($v === '' || $v === null) unset($p[$k]); else $p[$k] = $v; }
    return url('courses') . ($p ? '?' . http_build_query($p) : '');
};
$pages = (int)ceil($total / $per);
?>
<section class="container section">
  <h1><?= $q !== '' ? 'Results for "' . e($q) . '"' : 'Explore activities' ?></h1>
  <p class="muted"><?= number_format($total) ?> free course<?= $total == 1 ? '' : 's' ?> found</p>

  <div class="layout-filter">
    <aside class="filters" aria-label="Filters">
      <details open>
        <summary>Category</summary>
        <ul class="plain">
          <li><a class="<?= $cat === '' ? 'on' : '' ?>" href="<?= e($qs(['category' => '', 'page' => ''])) ?>">All categories</a></li>
          <?php foreach ($categories as $c): ?><li><a class="<?= $cat === $c['slug'] ? 'on' : '' ?>" href="<?= e($qs(['category' => $c['slug']])) ?>"><?= e($c['name']) ?></a></li><?php endforeach; ?>
        </ul>
      </details>
      <details open>
        <summary>Level</summary>
        <ul class="plain">
          <li><a class="<?= $level === '' ? 'on' : '' ?>" href="<?= e($qs(['level' => ''])) ?>">Any level</a></li>
          <?php foreach (['Beginner', 'Intermediate', 'Advanced'] as $l): ?><li><a class="<?= $level === $l ? 'on' : '' ?>" href="<?= e($qs(['level' => $l])) ?>"><?= e($l) ?></a></li><?php endforeach; ?>
        </ul>
      </details>
      <details open>
        <summary>Sort by</summary>
        <ul class="plain">
          <?php foreach (['popular' => 'Most popular', 'rating' => 'Highest rated', 'newest' => 'Newest', 'title' => 'Title A–Z'] as $k => $label): ?>
            <li><a class="<?= $sort === $k ? 'on' : '' ?>" href="<?= e($qs(['sort' => $k === 'popular' ? '' : $k])) ?>"><?= e($label) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </details>
    </aside>

    <div>
      <?php if (!$courses): ?>
        <div class="empty">
          <h2>No courses match your search</h2>
          <p class="muted">Try a shorter search word, or clear the filters to see everything.</p>
          <a class="btn btn-primary" href="<?= e(url('courses')) ?>">Clear filters</a>
        </div>
      <?php else: ?>
        <div class="grid grid-3">
          <?php foreach ($courses as $c): ?><?php include BASE_PATH . '/app/Views/partials/course_card.php'; ?><?php endforeach; ?>
        </div>
        <?php if ($pages > 1): ?>
        <nav class="pager" aria-label="Pages">
          <?php if ($page > 1): ?><a class="btn btn-outline btn-sm" href="<?= e($qs(['page' => $page - 1])) ?>">&larr; Previous</a><?php endif; ?>
          <span class="muted small">Page <?= $page ?> of <?= $pages ?></span>
          <?php if ($page < $pages): ?><a class="btn btn-outline btn-sm" href="<?= e($qs(['page' => $page + 1])) ?>">Next &rarr;</a><?php endif; ?>
        </nav>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</section>
