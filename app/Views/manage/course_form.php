<section class="container narrow section">
  <p class="small"><a href="<?= e(url('manage/courses')) ?>">&larr; All courses</a></p>
  <h1>New course</h1>
  <form method="post" action="<?= e(url('manage/courses')) ?>" novalidate>
    <?= csrf_field() ?>
    <?php include __DIR__ . '/_course_fields.php'; ?>
    <p><button class="btn btn-primary btn-lg" type="submit">Create and add weeks</button></p>
  </form>
</section>
