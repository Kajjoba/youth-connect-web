<section class="container narrow section">
  <p class="small"><a href="<?= e(url('admin')) ?>">&larr; Dashboard</a></p>
  <h1>Categories</h1>
  <ul class="plain list-cards"><?php foreach ($rows as $r): ?><li class="card row between"><strong><?= e($r['name']) ?></strong><span class="muted small"><?= (int)$r['courses'] ?> course<?= $r['courses'] == 1 ? '' : 's' ?></span></li><?php endforeach; ?></ul>
  <form method="post" action="<?= e(url('admin/categories')) ?>" class="row add-row"><?= csrf_field() ?>
    <label class="sr" for="cn">Category name</label><input id="cn" name="name" required maxlength="80" placeholder="New category name"><button class="btn btn-primary" type="submit">Add category</button></form>
</section>
