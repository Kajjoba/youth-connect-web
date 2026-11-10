<section class="container section">
  <h1>Users</h1>
  <form method="get" class="row filter-row"><label class="sr" for="q">Search users</label><input id="q" name="q" type="search" placeholder="Name or email" value="<?= e($q) ?>">
    <label class="sr" for="role">Role</label><select id="role" name="role"><option value="">All roles</option><?php foreach (['participant', 'facilitator', 'mentor', 'stakeholder', 'admin'] as $r): ?><option <?= $role === $r ? 'selected' : '' ?>><?= e($r) ?></option><?php endforeach; ?></select>
    <button class="btn btn-outline" type="submit">Filter</button></form>

  <details class="card"><summary><strong>+ Create a staff or participant account</strong></summary>
    <form method="post" action="<?= e(url('admin/users')) ?>" novalidate><?= csrf_field() ?>
      <div class="field-row">
        <div class="field"><label for="c-name">Full name</label><input id="c-name" name="name" required maxlength="120"></div>
        <div class="field"><label for="c-email">Email</label><input id="c-email" name="email" type="email" required maxlength="190"></div>
        <div class="field"><label for="c-role">Role</label><select id="c-role" name="role"><?php foreach (['facilitator', 'mentor', 'stakeholder', 'admin', 'participant'] as $r): ?><option><?= e($r) ?></option><?php endforeach; ?></select></div>
      </div>
      <p class="muted small">A temporary password is generated and shown once. Ask the person to change it after logging in.</p>
      <button class="btn btn-primary" type="submit">Create account</button></form></details>

  <div class="table-wrap"><table class="table"><thead><tr><th>User</th><th>Country</th><th>Role</th><th>Status</th><th>Level 2</th><th></th></tr></thead><tbody>
  <?php foreach ($users as $u): ?>
    <tr>
      <td data-label="User"><strong><?= e($u['name']) ?></strong><br><span class="muted small"><?= e($u['email']) ?></span>
        <form method="post" action="<?= e(url('admin/users/' . $u['id'] . '/update')) ?>" id="f<?= (int)$u['id'] ?>"><?= csrf_field() ?></form></td>
      <td data-label="Country"><?= e($u['country'] ?: '–') ?></td>
      <td data-label="Role"><label class="sr" for="r<?= (int)$u['id'] ?>">Role</label><select id="r<?= (int)$u['id'] ?>" name="role" form="f<?= (int)$u['id'] ?>"><?php foreach (['participant', 'facilitator', 'mentor', 'stakeholder', 'admin'] as $r): ?><option <?= $u['role'] === $r ? 'selected' : '' ?>><?= e($r) ?></option><?php endforeach; ?></select></td>
      <td data-label="Status"><label class="sr" for="s<?= (int)$u['id'] ?>">Status</label><select id="s<?= (int)$u['id'] ?>" name="status" form="f<?= (int)$u['id'] ?>"><option <?= $u['status'] === 'active' ? 'selected' : '' ?>>active</option><option <?= $u['status'] === 'suspended' ? 'selected' : '' ?>>suspended</option></select></td>
      <td data-label="Level 2"><label class="check"><input type="checkbox" name="level2_override" value="1" form="f<?= (int)$u['id'] ?>" <?= $u['level2_override'] ? 'checked' : '' ?>> allow</label></td>
      <td><button class="btn btn-outline btn-sm" type="submit" form="f<?= (int)$u['id'] ?>">Save</button>
        <form method="post" action="<?= e(url('admin/users/' . $u['id'] . '/reset')) ?>" class="inline" data-confirm="Create a new temporary password for <?= e($u['email']) ?>?"><?= csrf_field() ?><button class="btn btn-outline btn-sm" type="submit">Reset password</button></form></td>
    </tr>
  <?php endforeach; ?></tbody></table></div>
</section>
