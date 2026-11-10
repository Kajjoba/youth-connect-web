<?php $by = fn(array $rows, string $k) => array_column($rows, 'n', $k); $roles = $by($s['users'], 'role'); $projects = $by($s['projects'], 'status'); $courses = $by($s['courses'], 'status'); $max = max(1, ...array_column($s['countries'], 'n') ?: [1]); ?>
<section class="container section">
  <h1>Programme dashboard</h1>
  <div class="grid grid-4 stats">
    <div class="card stat"><strong><?= array_sum($roles) ?></strong><span>Users</span><small><?= (int)($roles['participant'] ?? 0) ?> participants</small></div>
    <div class="card stat"><strong><?= (int)$s['enrolments'] ?></strong><span>Enrolments</span><small><?= (int)$s['completions'] ?> completed</small></div>
    <div class="card stat"><strong><?= (int)$s['active7'] ?></strong><span>Active this week</span><small>logged in, last 7 days</small></div>
    <div class="card stat"><strong><?= (int)$s['certs'] ?></strong><span>Certificates issued</span><small><?= (int)$s['teams'] ?> teams</small></div>
  </div>

  <div class="grid grid-2">
    <div class="card"><h2>Users by role</h2>
      <ul class="plain"><?php foreach ($roles as $r => $n): ?><li class="row between"><span><?= e(ucfirst($r)) ?></span><strong><?= (int)$n ?></strong></li><?php endforeach; ?></ul>
      <p><a href="<?= e(url('admin/users')) ?>">Manage users &rarr;</a></p></div>
    <div class="card"><h2>Projects by stage</h2>
      <?php if (!$projects): ?><p class="muted">No projects yet.</p><?php endif; ?>
      <ul class="plain"><?php foreach ($projects as $st => $n): ?><li class="row between"><span><?= e(status_label($st)) ?></span><strong><?= (int)$n ?></strong></li><?php endforeach; ?></ul>
      <p><a href="<?= e(url('admin/projects')) ?>">All projects &rarr;</a></p></div>
    <div class="card"><h2>Participants by country</h2>
      <ul class="dist"><?php foreach ($s['countries'] as $c): ?><li><span><?= e($c['country']) ?></span><span class="bar"><i style="width:<?= round($c['n'] * 100 / $max) ?>%"></i></span><span class="small"><?= (int)$c['n'] ?></span></li><?php endforeach; ?></ul></div>
    <div class="card"><h2>Courses</h2>
      <ul class="plain"><?php foreach ($courses as $st => $n): ?><li class="row between"><span><?= e(ucfirst($st)) ?></span><strong><?= (int)$n ?></strong></li><?php endforeach; ?></ul>
      <p><a href="<?= e(url('manage/courses')) ?>">Manage courses &rarr;</a> · <a href="<?= e(url('admin/categories')) ?>">Categories</a> · <a href="<?= e(url('admin/audit')) ?>">Audit log</a></p></div>
  </div>
</section>
