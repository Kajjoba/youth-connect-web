<?php
/** @var array $c certificate row + name, course_title, skills, level, hours_per_week, weeks, facilitator @var string $abs @var bool $mine */
$isCourse = $c['kind'] === 'course';
$heading = $isCourse ? 'Certificate of Completion' : 'Certificate of Collaboration';
$subject = trim((string)preg_replace('/^Certificate of (Completion|Collaboration): /', '', $c['title']));
$skills = array_filter(array_map('trim', explode(',', (string)($c['skills'] ?? ''))));
$date = date('j F Y', strtotime($c['issued_at']));
// Rosette seal: a 28-point star polygon
$pts = [];
for ($i = 0; $i < 56; $i++) { $r = $i % 2 ? 40 : 48; $a = M_PI * $i / 28; $pts[] = round(50 + $r * sin($a), 2) . ',' . round(50 - $r * cos($a), 2); }
$shareText = 'I completed "' . $subject . '" on YouthConnect. Verify my certificate: ' . $abs;
$linkedin = 'https://www.linkedin.com/profile/add?startTask=CERTIFICATION_NAME&name=' . rawurlencode($subject) . '&organizationName=YouthConnect&issueYear=' . date('Y', strtotime($c['issued_at']))
          . '&issueMonth=' . date('n', strtotime($c['issued_at'])) . '&certId=' . rawurlencode($c['code']) . '&certUrl=' . rawurlencode($abs);
?>
<link rel="stylesheet" href="<?= e(asset('css/certificate.css')) ?>">
<section class="container cert-page section">
  <?php if ($mine): ?>
  <div class="cert-toolbar no-print">
    <div><h1>Your certificate</h1><p class="muted">Congratulations, <?= e(explode(' ', $c['name'])[0]) ?>! You earned this by finishing the course. No approval needed.</p></div>
    <div class="cert-actions">
      <button class="btn btn-primary" type="button" data-print>⬇ Download PDF</button>
      <a class="btn btn-outline" target="_blank" rel="noopener" href="<?= e('https://wa.me/?text=' . rawurlencode($shareText)) ?>">Share on WhatsApp</a>
      <a class="btn btn-outline" target="_blank" rel="noopener" href="<?= e($linkedin) ?>">Add to LinkedIn</a>
      <button class="btn btn-outline" type="button" data-copy="<?= e($abs) ?>">Copy link</button>
    </div>
    <p class="muted small">Tip: in the print window choose <strong>Save as PDF</strong> as the destination (landscape, no margins).</p>
  </div>
  <?php endif; ?>

  <div class="cert-wrap">
    <article class="cert" aria-label="<?= e($heading) ?>"<?= $mine ? ' data-confetti' : '' ?>>
      <i class="cert-pattern" aria-hidden="true"></i>
      <svg class="cert-mark" viewBox="0 0 100 100" aria-hidden="true"><circle cx="38" cy="50" r="30" fill="#0b5cd5"/><circle cx="62" cy="50" r="30" fill="#f2a900"/></svg>
      <?php foreach (['tl', 'tr', 'bl', 'br'] as $pos): ?>
        <svg class="cert-corner <?= $pos ?>" viewBox="0 0 60 60" aria-hidden="true"><path d="M4 58V12Q4 4 12 4H58" fill="none" stroke="#c98e00" stroke-width="2.6"/><path d="M13 58V20Q13 13 20 13H58" fill="none" stroke="#c98e00" stroke-width="1"/><circle cx="11" cy="11" r="3.6" fill="#c98e00"/></svg>
      <?php endforeach; ?>

      <div class="cert-body">
        <div class="cert-logo"><svg viewBox="0 0 64 32" aria-hidden="true"><circle cx="24" cy="16" r="14" fill="#0b5cd5"/><circle cx="40" cy="16" r="14" fill="#f2a900" fill-opacity=".88"/></svg><span>Youth<b>Connect</b></span></div>
        <h1 class="cert-title-main"><?= e($heading) ?></h1>
        <div class="cert-divider" aria-hidden="true"><i></i><b></b><i></i></div>
        <p class="cert-small">This certificate is proudly presented to</p>
        <p class="cert-name"><?= e($c['name']) ?></p>
        <p class="cert-small"><?= $isCourse ? 'for successfully completing all lessons, weekly quizzes and assignments of' : 'for successfully completing the collaborative project' ?></p>
        <h2 class="cert-course"><?= e($subject) ?></h2>
        <?php if ($isCourse && !empty($c['weeks'])): ?>
          <p class="cert-meta"><?= (int)$c['weeks'] ?> weeks · <?= (int)$c['hours_per_week'] ?> hours per week · <?= e($c['level']) ?> level</p>
        <?php endif; ?>
        <?php if ($skills): ?><p class="cert-skills"><span>Skills gained</span> <?= e(implode('  •  ', array_slice($skills, 0, 5))) ?></p><?php endif; ?>

        <div class="cert-foot">
          <div class="cert-sign"><span class="sig"><?= e($c['facilitator'] ?: 'YouthConnect') ?></span><i></i><small><?= $isCourse ? 'Lead Facilitator' : 'Programme Team' ?></small></div>
          <svg class="cert-seal" viewBox="0 0 100 130" aria-label="Verified seal">
            <defs><radialGradient id="g<?= e($c['id']) ?>" cx="50%" cy="38%" r="70%"><stop offset="0" stop-color="#ffe08a"/><stop offset="0.6" stop-color="#f2a900"/><stop offset="1" stop-color="#b57a00"/></radialGradient></defs>
            <polygon points="30,78 20,128 40,116 50,128 52,84" fill="#0b5cd5"/><polygon points="70,78 80,128 60,116 50,128 48,84" fill="#0847a6"/>
            <polygon points="<?= implode(' ', $pts) ?>" fill="url(#g<?= e($c['id']) ?>)"/>
            <circle cx="50" cy="50" r="33" fill="none" stroke="#fff" stroke-width="1.6" stroke-opacity=".9"/><circle cx="50" cy="50" r="28" fill="#0f1f3d"/>
            <path d="M36 51l10 10 19-22" fill="none" stroke="#ffd25e" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <div class="cert-sign"><span class="sig plain"><?= e($date) ?></span><i></i><small>Date of issue</small></div>
        </div>
        <p class="cert-id">Certificate ID <strong><?= e($c['code']) ?></strong> · Verify at <?= e(preg_replace('#^https?://#', '', $abs)) ?></p>
      </div>
    </article>
  </div>

  <p class="center no-print"><span class="badge badge-green">✓ Verified: genuine certificate issued by YouthConnect</span></p>
  <p class="center muted small no-print">Anyone with this page's link can confirm that this certificate is real. Only the holder's name and course are shown.</p>
  <?php if ($mine): ?><p class="center no-print"><a href="<?= e(url('my-learning')) ?>">&larr; Back to my learning</a></p><?php endif; ?>
</section>
