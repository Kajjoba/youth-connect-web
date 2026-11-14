<?php
$learn = array_filter(array_map('trim', preg_split('/\R/', $course['what_you_learn'])));
$skills = array_filter(array_map('trim', explode(',', (string)$course['skills'])));
$quizCount = count(array_filter($syllabus, fn($m) => $m['quiz']));
$asgCount = count(array_filter($syllabus, fn($m) => $m['assignment']));
$lessonCount = array_sum(array_map(fn($m) => count($m['lessons']), $syllabus));
$totalReviews = max(1, array_sum($dist));
?>
<?php $heroPhoto = course_cover_url($course); ?>
<section class="course-hero art-<?= e(course_art($course['category_slug'] ?? null)) ?><?= $heroPhoto ? ' has-photo' : '' ?>" style="--c:<?= e($course['color']) ?><?= $heroPhoto ? ';--photo:url(' . e($heroPhoto) . ')' : '' ?>">
  <div class="container course-hero-in">
    <div class="course-hero-main">
      <p class="crumbs small"><a href="<?= e(url('courses')) ?>">Explore</a> / <a href="<?= e(url('courses?category=' . $course['category_slug'])) ?>"><?= e($course['category']) ?></a></p>
      <?php if ($course['status'] !== 'published'): ?><p class="alert alert-info">Draft preview: learners cannot see this course until you publish it.</p><?php endif; ?>
      <h1><?= e($course['title']) ?></h1>
      <p class="lead"><?= e($course['summary']) ?></p>
      <p class="byline">Instructor: <strong><?= e($course['owner']) ?></strong><?= $course['owner_headline'] ? ' · ' . e($course['owner_headline']) : '' ?></p>
      <div class="hero-enrol desktop-only"><?php include BASE_PATH . '/app/Views/partials/enrol_box.php'; ?></div>
      <p class="small"><strong><?= number_format($stats['enrolled']) ?></strong> already enrolled</p>
    </div>
    <aside class="facts" aria-label="Course facts">
      <dl>
        <div><dt><?= $weeks ?> module<?= $weeks == 1 ? '' : 's' ?></dt><dd>One per week</dd></div>
        <div><dt><?= $stats['reviews'] ? e(stars($stats['rating'])) . ' ★' : 'New' ?></dt><dd><?= $stats['reviews'] ? number_format($stats['reviews']) . ' reviews' : 'No reviews yet' ?></dd></div>
        <div><dt><?= e($course['level']) ?> level</dt><dd>No prior experience needed<?= $course['level'] === 'Beginner' ? '' : ' beyond the basics' ?></dd></div>
        <div><dt>Flexible schedule</dt><dd><?= $weeks ?> week<?= $weeks == 1 ? '' : 's' ?> at <?= (int)$course['hours_per_week'] ?> hours a week · learn at your own pace</dd></div>
      </dl>
    </aside>
  </div>
</section>

<nav class="subnav" aria-label="On this page"><div class="container">
  <a href="#about">About</a><a href="#outcomes">Outcomes</a><a href="#modules">Modules</a><a href="#instructor">Instructor</a><a href="#reviews">Reviews</a><a href="#faq">FAQ</a>
</div></nav>

<div class="container course-body">
  <section id="outcomes" class="block">
    <h2>What you'll learn</h2>
    <ul class="checks"><?php foreach ($learn as $l): ?><li><?= e(ltrim($l, "-•* ")) ?></li><?php endforeach; ?></ul>
  </section>

  <?php if ($skills): ?>
  <section class="block">
    <h2>Skills you'll gain</h2>
    <div class="chips"><?php foreach ($skills as $s): ?><a class="chip" href="<?= e(url('courses?q=' . urlencode($s))) ?>"><?= e($s) ?></a><?php endforeach; ?></div>
  </section>
  <?php endif; ?>

  <section class="block">
    <h2>Details to know</h2>
    <div class="details-grid">
      <div><strong>Shareable certificate</strong><span><?= $course['has_certificate'] ? 'Included when you complete the course. Verify online with a code.' : 'Not offered for this course' ?></span></div>
      <div><strong>Assessments</strong><span><?= $quizCount ?> weekly quiz<?= $quizCount == 1 ? '' : 'zes' ?><?= $asgCount ? ' and ' . $asgCount . ' graded assignment' . ($asgCount == 1 ? '' : 's') : '' ?></span></div>
      <div><strong>Taught in <?= e($course['language']) ?></strong><span>Plain language, mobile-friendly pages</span></div>
      <div><strong>Cost</strong><span>Free for programme participants</span></div>
      <div><strong>Time</strong><span>About <?= (int)$hours ?> hours in total</span></div>
      <div><strong>Content</strong><span><?= $lessonCount ?> lesson<?= $lessonCount == 1 ? '' : 's' ?> across <?= $weeks ?> week<?= $weeks == 1 ? '' : 's' ?></span></div>
    </div>
  </section>

  <section id="about" class="block">
    <h2>About this course</h2>
    <?php foreach (preg_split('/\R{2,}/', $course['description']) as $para): ?><p><?= nl2br(e($para)) ?></p><?php endforeach; ?>
  </section>

  <section id="modules" class="block">
    <h2>There <?= $weeks == 1 ? 'is 1 module' : 'are ' . $weeks . ' modules' ?> in this course</h2>
    <p class="muted">Each week ends with a quiz. Open a week to see what is included.</p>
    <?php if (!$syllabus): ?><p class="muted">The weekly outline is being prepared.</p><?php endif; ?>
    <?php foreach ($syllabus as $i => $m): ?>
      <details class="week" <?= $i === 0 ? 'open' : '' ?>>
        <summary>
          <span class="week-no">Week <?= (int)$m['week_no'] ?></span>
          <span class="week-title"><?= e($m['title']) ?></span>
          <span class="muted small"><?= (int)$m['hours_estimate'] ?> hour<?= $m['hours_estimate'] == 1 ? '' : 's' ?> to complete</span>
        </summary>
        <div class="week-body">
          <?php if ($m['description']): ?><p><?= e($m['description']) ?></p><?php endif; ?>
          <p class="whats"><strong>What's included:</strong>
            <?php $bits = [];
              if ($m['counts']['reading']) $bits[] = $m['counts']['reading'] . ' reading' . ($m['counts']['reading'] == 1 ? '' : 's');
              if ($m['counts']['video']) $bits[] = $m['counts']['video'] . ' video' . ($m['counts']['video'] == 1 ? '' : 's');
              if ($m['counts']['audio']) $bits[] = $m['counts']['audio'] . ' audio lesson' . ($m['counts']['audio'] == 1 ? '' : 's');
              if ($m['counts']['discussion']) $bits[] = $m['counts']['discussion'] . ' discussion' . ($m['counts']['discussion'] == 1 ? '' : 's');
              if ($m['quiz']) $bits[] = '1 quiz (' . (int)$m['quiz']['questions'] . ' questions)';
              if ($m['assignment']) $bits[] = '1 graded assignment';
              echo e(implode(' · ', $bits) ?: 'Coming soon'); ?></p>
          <ul class="items">
            <?php foreach ($m['lessons'] as $l): ?>
              <li><span class="ico ico-<?= e($l['type']) ?>"><?= $l['type'] === 'video' ? '▶' : ($l['type'] === 'audio' ? '🎧' : ($l['type'] === 'discussion' ? '💬' : '📄')) ?></span>
                <?php if ($l['is_preview'] || $enrollment): ?><a href="<?= e(url('learn/' . $course['slug'] . '/lesson/' . $l['id'])) ?>"><?= e($l['title']) ?></a><?php else: ?><?= e($l['title']) ?><?php endif; ?>
                <span class="muted small"><?= (int)$l['minutes'] ?> min</span><?= $l['is_preview'] ? ' <span class="badge badge-green">Preview</span>' : '' ?></li>
            <?php endforeach; ?>
            <?php if ($m['quiz']): ?><li><span class="ico">✅</span> <strong><?= e($m['quiz']['title']) ?></strong> <span class="muted small">Pass mark <?= (int)$m['quiz']['pass_mark'] ?>% · <?= (int)$m['quiz']['minutes'] ?> min</span></li><?php endif; ?>
            <?php if ($m['assignment']): ?><li><span class="ico">✍️</span> <strong><?= e($m['assignment']['title']) ?></strong> <span class="muted small">Graded with feedback</span></li><?php endif; ?>
          </ul>
        </div>
      </details>
    <?php endforeach; ?>
  </section>

  <section class="block">
    <h2>Earn a certificate</h2>
    <p>Complete every lesson, pass every weekly quiz and submit the assignments. You will receive a certificate with a unique code that employers can verify online. You can then unlock <strong>Level Two</strong>: team projects with youth from other countries.</p>
  </section>

  <section id="instructor" class="block">
    <h2>Instructor</h2>
    <div class="person">
      <span class="avatar avatar-lg"><?= e(initials($course['owner'])) ?></span>
      <div><strong><?= e($course['owner']) ?></strong><p class="muted"><?= e($course['owner_headline'] ?: 'Programme facilitator') ?></p><?php if ($course['owner_bio']): ?><p><?= e($course['owner_bio']) ?></p><?php endif; ?></div>
    </div>
  </section>

  <section id="reviews" class="block">
    <h2>Learner reviews</h2>
    <?php if ($stats['reviews']): ?>
      <div class="reviews-head">
        <div class="big"><?= e(stars($stats['rating'])) ?><span class="star"> ★</span><br><span class="muted small"><?= number_format($stats['reviews']) ?> reviews</span></div>
        <ul class="dist">
          <?php for ($s = 5; $s >= 1; $s--): $pct = round($dist[$s] * 100 / $totalReviews); ?>
            <li><span><?= $s ?> star<?= $s == 1 ? '' : 's' ?></span><span class="bar"><i style="width:<?= $pct ?>%"></i></span><span class="muted small"><?= $pct ?>%</span></li>
          <?php endfor; ?>
        </ul>
      </div>
      <?php foreach ($reviews as $r): ?>
        <article class="review"><p><span class="star"><?= str_repeat('★', (int)$r['rating']) ?></span> <strong><?= e($r['name']) ?></strong> <span class="muted small">· <?= e(date('j M Y', strtotime($r['created_at']))) ?></span></p>
        <?php if ($r['comment']): ?><p><?= e($r['comment']) ?></p><?php endif; ?></article>
      <?php endforeach; ?>
    <?php else: ?><p class="muted">No reviews yet. Be the first after you start learning.</p><?php endif; ?>

    <?php if ($enrollment): ?>
      <form method="post" action="<?= e(url('course/' . $course['slug'] . '/review')) ?>" class="review-form card">
        <?= csrf_field() ?>
        <h3><?= $myReview ? 'Update your review' : 'Rate this course' ?></h3>
        <div class="field"><label for="rating">Your rating</label>
          <select id="rating" name="rating" required><option value="">Choose…</option>
            <?php for ($s = 5; $s >= 1; $s--): ?><option value="<?= $s ?>" <?= ($myReview['rating'] ?? 0) == $s ? 'selected' : '' ?>><?= $s ?> – <?= ['', 'Poor', 'Fair', 'Good', 'Very good', 'Excellent'][$s] ?></option><?php endfor; ?></select><?= field_error('rating') ?></div>
        <div class="field"><label for="comment">Comment (optional)</label><textarea id="comment" name="comment" rows="3" maxlength="600"><?= e($myReview['comment'] ?? '') ?></textarea></div>
        <button class="btn btn-outline" type="submit">Save review</button>
      </form>
    <?php endif; ?>
  </section>

  <?php if ($related): ?>
  <section class="block">
    <h2>You may also like</h2>
    <div class="grid grid-3"><?php foreach ($related as $c): ?><?php include BASE_PATH . '/app/Views/partials/course_card.php'; ?><?php endforeach; ?></div>
  </section>
  <?php endif; ?>

  <section id="faq" class="block narrow-left">
    <h2>Frequently asked questions</h2>
    <details><summary>Do I pay anything?</summary><p>No. Courses on YouthConnect are free for programme participants, including the certificate.</p></details>
    <details><summary>How long do I have?</summary><p>Learn at your own pace. The suggested pace is <?= (int)$course['hours_per_week'] ?> hours a week for <?= $weeks ?> week<?= $weeks == 1 ? '' : 's' ?>, but there are no deadlines.</p></details>
    <details><summary>What if my connection is slow?</summary><p>Lessons are light text pages. Videos only download when you tap to play, and Lite mode removes extras. Answers you type are saved on your phone if the signal drops.</p></details>
    <details><summary>What do I get at the end?</summary><p>A verifiable certificate, and eligibility to join Level Two collaborative projects.</p></details>
  </section>
</div>

<div class="sticky-enrol mobile-only"><?php include BASE_PATH . '/app/Views/partials/enrol_box.php'; ?></div>
