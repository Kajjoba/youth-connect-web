<section class="container narrow section">
  <p class="small"><a href="<?= e(url('teams')) ?>">&larr; Collaborate</a></p>
  <h1>Start a team</h1>
  <p class="muted">The strongest teams mix countries and disciplines, for example agriculture, software, data and environment. You become the team lead.</p>
  <form method="post" action="<?= e(url('teams')) ?>" novalidate><?= csrf_field() ?>
    <div class="field"><label for="name">Team name</label><input id="name" name="name" required maxlength="100" value="<?= e(old('name')) ?>"><?= field_error('name') ?></div>
    <div class="field"><label for="description">What does the team want to achieve?</label><textarea id="description" name="description" rows="3" maxlength="500"><?= e(old('description')) ?></textarea></div>
    <div class="field"><label for="looking_for">Skills or countries you are looking for</label><input id="looking_for" name="looking_for" maxlength="255" placeholder="e.g. data analyst from Tanzania, agronomist from Kenya" value="<?= e(old('looking_for')) ?>"></div>
    <div class="field"><label for="discipline">Your discipline</label><select id="discipline" name="discipline" required><option value="">Choose…</option><?php foreach ($disciplines as $d): ?><option <?= old('discipline') === $d ? 'selected' : '' ?>><?= e($d) ?></option><?php endforeach; ?></select><?= field_error('discipline') ?></div>
    <label class="check"><input type="checkbox" name="is_open" value="1" checked> Open: other eligible participants can ask to join</label>
    <p><button class="btn btn-primary btn-lg" type="submit">Create team</button></p>
  </form>
</section>
