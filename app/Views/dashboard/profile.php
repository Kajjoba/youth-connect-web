<section class="container narrow section">
  <h1><?= $welcome ? 'Tell us about yourself' : 'Profile & settings' ?></h1>
  <?php if ($welcome): ?><p class="lead">This helps us recommend activities and helps teammates find you. You can skip anything and come back later.</p><?php endif; ?>
  <form method="post" action="<?= e(url('profile')) ?>" novalidate>
    <?= csrf_field() ?>
    <?php if ($welcome): ?><input type="hidden" name="welcome" value="1"><?php endif; ?>
    <div class="field"><label for="name">Full name</label><input id="name" name="name" required value="<?= e(old('name', $p['name'])) ?>"><?= field_error('name') ?></div>
    <p class="muted small">Email: <?= e($p['email']) ?></p>
    <div class="field-row">
      <div class="field"><label for="country">Country</label>
        <select id="country" name="country" required><?php foreach ($countries as $c): ?><option <?= old('country', $p['country']) === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select><?= field_error('country') ?></div>
      <div class="field"><label for="age_group">Age group</label>
        <select id="age_group" name="age_group"><?php foreach (\App\Services\Lists::AGE_GROUPS as $a): ?><option <?= old('age_group', $p['age_group']) === $a ? 'selected' : '' ?>><?= e($a) ?></option><?php endforeach; ?></select></div>
    </div>
    <div class="field"><label for="headline">Headline</label><input id="headline" name="headline" maxlength="160" placeholder="e.g. Agribusiness student interested in data" value="<?= e(old('headline', $p['headline'])) ?>"><?= field_error('headline') ?></div>
    <div class="field"><label for="bio">About you</label><textarea id="bio" name="bio" rows="4" maxlength="1000"><?= e(old('bio', $p['bio'])) ?></textarea></div>
    <div class="field-row">
      <div class="field"><label for="expertise">Main field of study or work</label><input id="expertise" name="expertise" maxlength="160" value="<?= e(old('expertise', $p['expertise'])) ?>"></div>
      <div class="field"><label for="organisation">School or organisation</label><input id="organisation" name="organisation" maxlength="160" value="<?= e(old('organisation', $p['organisation'])) ?>"></div>
    </div>
    <div class="field"><label for="skills">Skills (comma separated)</label><input id="skills" name="skills" maxlength="255" value="<?= e(old('skills', $p['skills'])) ?>"></div>
    <div class="field"><label for="interests">Interests (comma separated)</label><input id="interests" name="interests" maxlength="255" value="<?= e(old('interests', $p['interests'])) ?>"></div>
    <div class="field-row">
      <div class="field"><label for="digital_level">Comfort with digital tools</label>
        <select id="digital_level" name="digital_level"><?php foreach (['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced'] as $k => $v): ?><option value="<?= $k ?>" <?= old('digital_level', $p['digital_level']) === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
      <div class="field"><label for="connectivity">Your internet connection</label>
        <select id="connectivity" name="connectivity"><?php foreach (['good' => 'Good', 'limited' => 'Limited / sometimes slow', 'very_limited' => 'Very limited'] as $k => $v): ?><option value="<?= $k ?>" <?= old('connectivity', $p['connectivity']) === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
    </div>
    <label class="check"><input type="checkbox" name="lite_mode" value="1" <?= $p['lite_mode'] ? 'checked' : '' ?>> Lite mode: hide decorative graphics and background updates to save mobile data</label>
    <p><button class="btn btn-primary btn-lg" type="submit"><?= $welcome ? 'Save and find courses' : 'Save changes' ?></button></p>
  </form>
  <form method="post" action="<?= e(url('profile/password')) ?>" id="password" class="card" novalidate autocomplete="off">
    <?= csrf_field() ?>
    <h2>Change password</h2>
    <div class="field"><label for="current_password">Current password</label>
      <input id="current_password" name="current_password" type="password" autocomplete="current-password" required><?= field_error('current_password') ?></div>
    <div class="field"><label for="new_password">New password</label>
      <input id="new_password" name="new_password" type="password" autocomplete="new-password" required minlength="8" aria-describedby="np-hint">
      <p id="np-hint" class="hint">At least 8 characters, with a letter and a number.</p><?= field_error('new_password') ?></div>
    <div class="field"><label for="new_password_confirmation">Confirm new password</label>
      <input id="new_password_confirmation" name="new_password_confirmation" type="password" autocomplete="new-password" required></div>
    <p><button class="btn btn-primary" type="submit">Change password</button></p>
  </form>
</section>
