<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$pageKey   = 'skills';
$pageTitle = 'Skills & Achievements';
$pageDescription = 'Technical skills shelved as books, and certifications as golden volumes.';
$bodyClass = 'page-skills';

$skills = lib_skill_volumes();
$ach    = lib_achievement_volumes();
page_data('volumes', array_merge($skills, $ach));

$skillGroups = [];
foreach ($skills as $v) {
    $skillGroups[$v['category']['name']][] = $v;
}
$achGroups = [];
foreach ($ach as $v) {
    $achGroups[$v['group'] ?: 'Achievements'][] = $v;
}
$rated = count(array_filter($skills, static fn ($s) => $s['level'] !== null));

$scripts = ['reader.js'];
require __DIR__ . '/includes/header.php';
?>
<section class="page-intro">
  <h1>Skills &amp; Achievements</h1>
  <p>Knowledge acquired, shelved as small books, and milestones earned, bound in gold. Open any spine for the details.</p>
</section>

<section class="room" aria-labelledby="skills-h">
  <header class="section-head">
    <h2 id="skills-h">The Reference Shelves</h2>
    <p><?= $rated ? 'Pips on a spine show proficiency.' : 'Proficiency levels have not been rated yet, so spines carry no pips. They can be set from the admin dashboard.' ?></p>
  </header>
  <div class="shelves">
  <?php $i = 0; foreach ($skillGroups as $name => $list): $i++; $category = $list[0]['category'] ?? ['slug' => 'skill', 'name' => $name, 'color' => '#5f1a28', 'emblem' => '']; ?>
    <?= render_shelf('skills-' . $i, $name, '', (string) ($category['emblem'] ?? ''), (string) ($category['color'] ?? '#5f1a28'), $list, false, (string) ($category['slug'] ?? 'skill')) ?>
  <?php endforeach; ?>
  </div>
  <?php if (!$skills): ?><p class="empty">No skills have been shelved yet.</p><?php endif; ?>
</section>

<section class="room room-gold" aria-labelledby="ach-h">
  <header class="section-head">
    <h2 id="ach-h">The Golden Shelf</h2>
    <p>Certifications and milestones. Open a gold spine to see the certificate itself.</p>
  </header>
  <div class="shelves">
  <?php $j = 0; foreach ($achGroups as $name => $list): $j++; ?>
    <?= render_shelf('gold-' . $j, $name, count($list) . (count($list) === 1 ? ' certificate' : ' certificates'), '*', GOLD, $list, false, 'achievement') ?>
  <?php endforeach; ?>
  </div>
  <?php if (!$ach): ?><p class="empty">No achievements have been shelved yet.</p><?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
