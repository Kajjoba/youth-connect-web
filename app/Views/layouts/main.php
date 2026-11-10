<?php
use App\Core\{Auth, Notify, Session};
$u = Auth::user();
$unread = $u ? Notify::unreadCount((int)$u['id']) : 0;
$lite = lite_mode();
$flashes = Session::pullFlash();
$q = $_GET['q'] ?? '';
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'YouthConnect') ?> · YouthConnect</title>
<meta name="description" content="YouthConnect is a regional learning and collaboration platform for young people in Uganda and East Africa.">
<meta name="theme-color" content="#0b5cd5">
<meta name="ycp-base" content="<?= e(base_path()) ?>">
<meta name="ycp-csrf" content="<?= e(csrf_token()) ?>">
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Ccircle cx='12' cy='16' r='9' fill='%230b5cd5'/%3E%3Ccircle cx='20' cy='16' r='9' fill='%23f2a900' fill-opacity='.85'/%3E%3C/svg%3E">
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</head>
<body class="<?= $lite ? 'lite' : '' ?>">
<a class="skip" href="#main">Skip to content</a>
<header class="topbar">
  <div class="container topbar-in">
    <a class="logo" href="<?= e(url('')) ?>" aria-label="YouthConnect home">
      <svg width="30" height="30" viewBox="0 0 32 32" aria-hidden="true"><circle cx="12" cy="16" r="9" fill="#0b5cd5"/><circle cx="20" cy="16" r="9" fill="#f2a900" fill-opacity=".85"/></svg>
      <span>Youth<b>Connect</b></span>
    </a>
    <button class="nav-toggle" aria-expanded="false" aria-controls="nav" aria-label="Menu">&#9776;</button>
    <nav id="nav" class="nav">
      <a href="<?= e(url('courses')) ?>">Explore</a>
      <form class="search" action="<?= e(url('courses')) ?>" method="get" role="search" autocomplete="off">
        <label class="sr" for="q">Search activities</label>
        <input id="q" name="q" type="search" value="<?= e($q) ?>" placeholder="What do you want to learn?" data-suggest="<?= e(url('api/search')) ?>">
        <button type="submit" aria-label="Search">&#128269;</button>
        <ul class="suggest" hidden></ul>
      </form>
      <?php if ($u): ?>
        <?php if ($u['role'] === 'participant'): ?>
          <a href="<?= e(url('my-learning')) ?>">My learning</a>
          <a href="<?= e(url('teams')) ?>">Collaborate</a>
        <?php elseif ($u['role'] === 'facilitator'): ?>
          <a href="<?= e(url('manage/courses')) ?>">My courses</a>
          <a href="<?= e(url('manage/submissions')) ?>">To grade</a>
          <a href="<?= e(url('staff/projects')) ?>">Projects</a>
        <?php elseif ($u['role'] === 'admin'): ?>
          <a href="<?= e(url('admin')) ?>">Admin</a>
          <a href="<?= e(url('manage/courses')) ?>">Courses</a>
        <?php else: ?>
          <a href="<?= e(url('staff/projects')) ?>">My projects</a>
        <?php endif; ?>
        <a class="bell" href="<?= e(url('notifications')) ?>" aria-label="Notifications<?= $unread ? ', ' . $unread . ' unread' : '' ?>">&#128276;<?php if ($unread): ?><span class="dot" id="bell-count"><?= $unread > 9 ? '9+' : $unread ?></span><?php endif; ?></a>
        <details class="menu">
          <summary aria-label="Account menu"><span class="avatar"><?= e(initials($u['name'])) ?></span></summary>
          <div class="menu-pop">
            <p class="menu-name"><strong><?= e($u['name']) ?></strong><br><span class="muted small"><?= e(ucfirst($u['role'])) ?></span></p>
            <a href="<?= e(url('profile')) ?>">Profile &amp; settings</a>
            <form method="post" action="<?= e(url('lite')) ?>"><?= csrf_field() ?><input type="hidden" name="lite" value="<?= $lite ? '0' : '1' ?>">
              <button class="linklike" type="submit"><?= $lite ? 'Turn off Lite mode' : 'Turn on Lite mode (saves data)' ?></button></form>
            <form method="post" action="<?= e(url('logout')) ?>"><?= csrf_field() ?><button class="linklike" type="submit">Log out</button></form>
          </div>
        </details>
      <?php else: ?>
        <form method="post" action="<?= e(url('lite')) ?>" class="inline"><?= csrf_field() ?><input type="hidden" name="lite" value="<?= $lite ? '0' : '1' ?>">
          <button class="linklike small" type="submit" title="Lite mode hides decorative graphics and background updates to save mobile data"><?= $lite ? 'Lite mode: on' : 'Lite mode' ?></button></form>
        <a href="<?= e(url('login')) ?>">Log in</a>
        <a class="btn btn-primary" href="<?= e(url('register')) ?>">Join for free</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<?php if ($flashes): ?>
<div class="container flashes" role="status" aria-live="polite">
  <?php foreach ($flashes as $f): ?><div class="alert alert-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div><?php endforeach; ?>
</div>
<?php endif; ?>

<main id="main"><?= $content ?></main>

<div id="net-banner" class="net-banner" hidden role="status">You are offline. Your drafts are saved on this device and will send when you are back online.</div>

<footer class="footer">
  <div class="container footer-in">
    <div>
      <a class="logo logo-sm" href="<?= e(url('')) ?>"><svg width="22" height="22" viewBox="0 0 32 32" aria-hidden="true"><circle cx="12" cy="16" r="9" fill="#0b5cd5"/><circle cx="20" cy="16" r="9" fill="#f2a900" fill-opacity=".85"/></svg><span>Youth<b>Connect</b></span></a>
      <p class="muted small">Digital and AI literacy, employability and collaboration for young people across East Africa.</p>
    </div>
    <div class="footer-links small">
      <a href="<?= e(url('courses')) ?>">Explore activities</a>
      <a href="<?= e(url('register')) ?>">Join for free</a>
      <a href="<?= e(url('teams')) ?>">Collaborate</a>
    </div>
  </div>
</footer>
</body>
</html>
