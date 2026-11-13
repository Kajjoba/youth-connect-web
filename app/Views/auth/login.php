<link rel="stylesheet" href="<?= e(asset('css/auth.css')) ?>">
<section class="auth-stage">
<?php include BASE_PATH . '/app/Views/partials/auth_art.php'; ?>
<div class="container auth-wrap">
  <div class="auth-pitch">
    <h2>Learn. Grow. Build together.</h2>
    <p class="lead">Pick up your courses, quizzes and team projects right where you left off.</p>
    <ul class="pitch-chips"><li>🌱 Agriculture &amp; food</li><li>🤖 AI &amp; digital skills</li><li>🤝 Teamwork across borders</li><li>🎓 Certificates you can prove</li></ul>
  </div>
  <div class="auth-card">
    <h1>Welcome back</h1>
    <p class="muted">Log in to continue learning.</p>
    <?php if (\App\Controllers\GoogleAuthController::enabled()): ?>
      <?php include BASE_PATH . '/app/Views/partials/google_button.php'; ?>
      <div class="or"><span>or log in with email</span></div>
    <?php endif; ?>
    <form method="post" action="<?= e(url('login')) ?>" novalidate>
      <?= csrf_field() ?>
      <div class="field">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" autocomplete="username" required value="<?= e(old('email')) ?>" autofocus>
        <?= field_error('email') ?>
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        <?= field_error('password') ?>
      </div>
      <button class="btn btn-primary btn-block btn-lg" type="submit">Log in</button>
    </form>
    <p class="center small">New to YouthConnect? <a href="<?= e(url('register')) ?>">Join for free</a></p>
    <p class="center muted small">Forgot your password? Ask a programme administrator to issue a temporary one.</p>
  </div>
</div>
</section>
