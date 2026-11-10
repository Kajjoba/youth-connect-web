<?php $u = auth_user(); ?>
<section class="hero">
  <div class="container hero-in">
    <div class="hero-text">
      <h1>Learn without limits.<br><span>Build together across borders.</span></h1>
      <p class="lead">Free digital and AI skills, employability and teamwork for young people in Uganda and across East Africa. Learn at your own pace, then team up on real projects that earn recognised certificates.</p>
      <?php if (!$u): ?>
        <p class="hero-cta"><a class="btn btn-primary btn-lg" href="<?= e(url('register')) ?>">Join for free</a>
        <a class="btn btn-outline btn-lg" href="<?= e(url('courses')) ?>">Explore activities</a></p>
      <?php else: ?>
        <p class="hero-cta"><a class="btn btn-primary btn-lg" href="<?= e(url('my-learning')) ?>">Go to my learning</a>
        <a class="btn btn-outline btn-lg" href="<?= e(url('courses')) ?>">Explore activities</a></p>
      <?php endif; ?>
      <ul class="hero-stats">
        <li><strong><?= number_format($stats['learners']) ?></strong> learners</li>
        <li><strong><?= number_format($stats['courses']) ?></strong> free courses</li>
        <li><strong><?= number_format($stats['countries']) ?></strong> countries</li>
        <li><strong><?= number_format($stats['projects']) ?></strong> team projects</li>
      </ul>
    </div>
    <?php if (!lite_mode()): ?>
    <div class="hero-art" aria-hidden="true"><div class="orb o1"></div><div class="orb o2"></div><div class="orb o3"></div>
      <div class="hero-card"><span class="chip">Level 1</span> Learn<br><span class="chip chip-warm">Level 2</span> Collaborate<br><span class="chip chip-green">Recognition</span> Certificates &amp; badges</div></div>
    <?php endif; ?>
  </div>
</section>

<?php if ($continue): ?>
<section class="section container">
  <h2>Pick up where you left off</h2>
  <div class="grid grid-3">
    <?php foreach ($continue as $c): ?>
      <a class="card continue" href="<?= e(url('learn/' . $c['slug'])) ?>"><span class="dotc" style="background:<?= e($c['color']) ?>"></span><strong><?= e($c['title']) ?></strong><span class="btn btn-sm btn-outline">Continue</span></a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="section container">
  <h2>What brings you to YouthConnect today?</h2>
  <div class="goals">
    <a class="goal" href="<?= e(url('courses?level=Beginner')) ?>"><strong>Start my career</strong><span>Employability and digital basics</span></a>
    <a class="goal" href="<?= e(url('courses?category=ai-digital-literacy')) ?>"><strong>Learn AI skills</strong><span>Use AI tools with confidence</span></a>
    <a class="goal" href="<?= e(url('teams')) ?>"><strong>Collaborate on a project</strong><span>Team up across countries</span></a>
    <a class="goal" href="<?= e(url('register')) ?>"><strong>Get recognised</strong><span>Certificates and badges</span></a>
  </div>
</section>

<section class="section container">
  <div class="between"><h2>Most popular</h2><a href="<?= e(url('courses')) ?>">See all</a></div>
  <div class="carousel" data-carousel>
    <?php foreach ($popular as $c): ?><?php include BASE_PATH . '/app/Views/partials/course_card.php'; ?><?php endforeach; ?>
  </div>
</section>

<section class="section container">
  <h2>Explore categories</h2>
  <div class="chips">
    <?php foreach ($categories as $cat): ?>
      <a class="chip chip-lg" href="<?= e(url('courses?category=' . $cat['slug'])) ?>"><?= e($cat['name']) ?> <span class="muted">(<?= (int)$cat['n'] ?>)</span></a>
    <?php endforeach; ?>
  </div>
</section>

<section class="section container">
  <div class="between"><h2>New and noteworthy</h2><a href="<?= e(url('courses?sort=newest')) ?>">See all</a></div>
  <div class="carousel" data-carousel>
    <?php foreach ($newest as $c): ?><?php include BASE_PATH . '/app/Views/partials/course_card.php'; ?><?php endforeach; ?>
  </div>
</section>

<section class="section band">
  <div class="container journey">
    <h2>How YouthConnect works</h2>
    <ol class="steps">
      <li><span class="n">1</span><strong>Join and create your profile</strong><p>Tell us your interests and connection. We adapt to your data limits.</p></li>
      <li><span class="n">2</span><strong>Learn week by week</strong><p>Short lessons, a quiz every week, and feedback from facilitators.</p></li>
      <li><span class="n">3</span><strong>Team up across borders</strong><p>Finish a course to unlock teams with youth from other countries.</p></li>
      <li><span class="n">4</span><strong>Deliver a real project</strong><p>Work with mentors, share evidence, and earn recognition.</p></li>
    </ol>
  </div>
</section>

<section class="section container narrow">
  <h2>Frequently asked questions</h2>
  <details><summary>Is YouthConnect free?</summary><p>Yes. Every course is free to join, and certificates are included when you complete a course.</p></details>
  <details><summary>Who can join?</summary><p>Young people aged 18 to 35 from Uganda and other East African countries.</p></details>
  <details><summary>Does it work on a slow phone connection?</summary><p>Yes. Pages are small, nothing downloads until you ask for it, and Lite mode removes decorative graphics and background updates. Drafts you write offline are saved on your device.</p></details>
  <details><summary>What is Level Two?</summary><p>After completing a course you can join a multi-country team, register a project, work with a mentor, and earn a project certificate and badges.</p></details>
  <details><summary>How do I know a certificate is genuine?</summary><p>Every certificate has a code. Anyone can check it on our public verification page.</p></details>
</section>
