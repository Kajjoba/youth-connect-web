<section class="container narrow section">
  <div class="certificate" data-confetti role="article" aria-label="Certificate">
    <div class="cert-inner">
      <p class="cert-brand">YouthConnect Platform</p>
      <p class="muted">This certifies that</p>
      <h1 class="cert-name"><?= e($c['name']) ?></h1>
      <p class="muted"><?= $c['kind'] === 'course' ? 'has successfully completed' : 'has successfully completed the collaborative project' ?></p>
      <h2 class="cert-title"><?= e(preg_replace('/^Certificate of (Completion|Collaboration): /', '', $c['title'])) ?></h2>
      <p class="muted"><?= e(date('j F Y', strtotime($c['issued_at']))) ?></p>
      <p class="cert-code">Verification code <strong><?= e($c['code']) ?></strong></p>
    </div>
  </div>
  <p class="center"><span class="badge badge-green">✓ Verified: issued by YouthConnect</span></p>
  <p class="center no-print"><button class="btn btn-outline" type="button" data-print>Print or save as PDF</button></p>
</section>
