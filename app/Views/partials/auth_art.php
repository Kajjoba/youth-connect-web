<?php
// Decorative animated background for the sign-in / join pages: pure CSS, no images, no scripts. Hidden in Lite mode.
// Each icon: desktop x,y, size, symbol, delay, duration, phone x,y (extras are hidden on phones).
$icons = [
  ['6%', '8%', '3rem', '🌱', 0, 9, '8%', '4%'], ['24%', '9%', '2.6rem', '💻', .6, 10, '40%', '3%'], ['56%', '6%', '2.8rem', '🌾', 1.6, 9, '76%', '5%'],
  ['10%', '80%', '3.2rem', '🤖', 1.2, 11, '9%', '89%'], ['30%', '82%', '3rem', '🤝', 2, 12, '42%', '91%'], ['50%', '80%', '3rem', '🌍', .3, 11, '80%', '88%'],
  ['68%', '3%', '2.8rem', '📚', 2.4, 10, '88%', '15%'], ['93%', '6%', '2.6rem', '📡', 1, 12, '3%', '15%'],
  ['20%', '92%', '2.2rem', '🚜', .8, 10, '', '', 'x'], ['42%', '5%', '2.2rem', '🧠', 1.8, 13, '', '', 'x'], ['56.5%', '50%', '2.2rem', '💧', 2.6, 9, '', '', 'x'],
  ['88%', '90%', '2.4rem', '🎓', .4, 11, '', '', 'x'], ['40%', '92%', '2.2rem', '📊', 1.4, 12, '', '', 'x'], ['38%', '20%', '2.2rem', '☀️', 2.2, 10, '', '', 'x'],
];
?>
<div class="auth-art" aria-hidden="true">
  <i class="blob b1"></i><i class="blob b2"></i><i class="blob b3"></i>
  <?php foreach ($icons as $i): ?><span class="ico<?= !empty($i[8]) ? ' x' : '' ?>" style="--x:<?= $i[0] ?>;--y:<?= $i[1] ?>;--mx:<?= $i[6] ?: $i[0] ?>;--my:<?= $i[7] ?: $i[1] ?>;--s:<?= $i[2] ?>;--d:-<?= $i[4] ?>s;--t:<?= $i[5] ?>s"><?= $i[3] ?></span><?php endforeach; ?>
</div>
