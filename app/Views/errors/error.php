<section class="container narrow center section">
  <p class="muted"><?= (int)$code ?></p>
  <h1><?= e($title) ?></h1>
  <p class="lead"><?= e($message) ?></p>
  <p><a class="btn btn-primary" href="<?= e(url('')) ?>">Go to the home page</a>
     <a class="btn btn-outline" href="<?= e(url('courses')) ?>">Explore activities</a></p>
</section>
