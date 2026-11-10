<section class="container auth">
  <div class="auth-card">
    <h1>Join YouthConnect for free</h1>
    <p class="muted">Takes about a minute. You can finish your profile after.</p>
    <?php if (\App\Controllers\GoogleAuthController::enabled()): ?>
      <?php include BASE_PATH . '/app/Views/partials/google_button.php'; ?>
      <div class="or"><span>or sign up with email</span></div>
    <?php endif; ?>
    <form method="post" action="<?= e(url('register')) ?>" novalidate data-validate>
      <?= csrf_field() ?>
      <div class="field">
        <label for="name">Full name</label>
        <input id="name" name="name" autocomplete="name" required maxlength="120" value="<?= e(old('name')) ?>">
        <?= field_error('name') ?>
      </div>
      <div class="field">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" autocomplete="email" required maxlength="190" value="<?= e(old('email')) ?>">
        <?= field_error('email') ?>
      </div>
      <div class="field-row">
        <div class="field">
          <label for="country">Country</label>
          <select id="country" name="country" required>
            <option value="">Choose…</option>
            <?php foreach ($countries as $c): ?><option <?= old('country') === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?>
          </select>
          <?= field_error('country') ?>
        </div>
        <div class="field">
          <label for="age_group">Age group</label>
          <select id="age_group" name="age_group" required>
            <option value="">Choose…</option>
            <?php foreach (\App\Services\Lists::AGE_GROUPS as $a): ?><option <?= old('age_group') === $a ? 'selected' : '' ?>><?= e($a) ?></option><?php endforeach; ?>
          </select>
          <?= field_error('age_group') ?>
        </div>
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="new-password" required minlength="8" aria-describedby="pw-hint">
        <p id="pw-hint" class="hint">At least 8 characters, with a letter and a number.</p>
        <?= field_error('password') ?>
      </div>
      <div class="field">
        <label for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
      </div>
      <label class="check"><input type="checkbox" name="terms" value="1" required> I agree to use YouthConnect respectfully and to the storage of my learning records.</label>
      <?= field_error('terms') ?>
      <button class="btn btn-primary btn-block btn-lg" type="submit">Create my account</button>
    </form>
    <p class="center small">Already have an account? <a href="<?= e(url('login')) ?>">Log in</a></p>
  </div>
</section>
