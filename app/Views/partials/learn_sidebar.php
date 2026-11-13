<?php
/** @var array $course @var array $syllabus @var array $items @var array $active */
$byMod = [];
foreach ($items as $it) $byMod[$it['module_id']][] = $it;
$activeModule = null;
foreach ($items as $it) if ([$it['kind'], $it['id']] === $active) $activeModule = $it['module_id'];
?>
<aside class="player-nav" aria-label="Course outline">
  <a class="back small" href="<?= e(url('course/' . $course['slug'])) ?>">&larr; <?= e($course['title']) ?></a>
  <?php if (!empty($progress)): ?>
    <div class="progress" role="progressbar" aria-valuenow="<?= (int)$progress['pct'] ?>" aria-valuemin="0" aria-valuemax="100"><span style="width:<?= (int)$progress['pct'] ?>%"></span></div>
    <p class="small muted"><?= (int)$progress['pct'] ?>% complete</p>
    <?php $sbCert = (int)$progress['pct'] >= 100 && auth_user() ? \App\Core\DB::val('SELECT code FROM certificates WHERE user_id = ? AND course_id = ? AND kind = ?', [auth_user()['id'], $course['id'], 'course']) : null; ?>
    <?php if ($sbCert): ?><a class="btn btn-primary btn-block" href="<?= e(url('certificate/' . $sbCert . '?download=1')) ?>">🎓 Get your certificate</a><?php endif; ?>
  <?php endif; ?>
  <?php foreach ($syllabus as $m): $list = $byMod[$m['id']] ?? []; $open = $activeModule === null ? $m === reset($syllabus) : $activeModule == $m['id']; ?>
    <details class="pn-week" <?= $open ? 'open' : '' ?>>
      <summary><span class="week-no">Week <?= (int)$m['week_no'] ?></span> <?= e($m['title']) ?></summary>
      <ul>
        <?php foreach ($list as $it): $isActive = [$it['kind'], $it['id']] === $active; ?>
          <li class="<?= $isActive ? 'on' : '' ?>">
            <a href="<?= e(url($it['url'])) ?>"><span class="tick <?= $it['done'] ? 'done' : '' ?>" aria-label="<?= $it['done'] ? 'Completed' : 'Not completed' ?>"><?= $it['done'] ? '✓' : '' ?></span>
              <span><?= e($it['title']) ?><br><span class="muted small"><?= $it['kind'] === 'quiz' ? 'Quiz' : ($it['kind'] === 'assignment' ? 'Assignment' : ucfirst($it['type'])) ?> · <?= (int)$it['minutes'] ?> min</span></span></a>
          </li>
        <?php endforeach; ?>
      </ul>
    </details>
  <?php endforeach; ?>
  <p><a class="small" href="<?= e(url('learn/' . $course['slug'] . '/discussion')) ?>">💬 Course discussion</a></p>
</aside>
