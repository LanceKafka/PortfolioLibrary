<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$pageKey   = 'about';
$pageTitle = 'About the Author';
$bodyClass = 'page-about';
$owner     = setting('owner_name', 'Library Owner');
$pageDescription = 'About ' . $owner . ' — education, interests, experience and philosophy.';

$sections = db()->query('SELECT * FROM about_sections WHERE is_visible = 1 ORDER BY sort_order, section_id')->fetchAll();
$portrait = media_url(setting('profile_image'));
$portraitAlt = media_url(setting('profile_image_alt'));
$reel = trim(setting('demo_reel'));
$reelUrl = ($reel !== '' && is_file(ROOT_DIR . '/' . $reel)) ? media_url($reel) : null;

$counts = [
    'projects'     => (int) db()->query('SELECT COUNT(*) FROM projects WHERE is_published = 1')->fetchColumn(),
    'achievements' => (int) db()->query('SELECT COUNT(*) FROM achievements')->fetchColumn(),
    'skills'       => (int) db()->query('SELECT COUNT(*) FROM skills')->fetchColumn(),
];
$numerals = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
$socials = array_filter(['GitHub' => setting('github_url'), 'LinkedIn' => setting('linkedin_url'), 'Facebook' => setting('facebook_url')], static fn ($u) => valid_url($u));

$scripts = ['about.js'];
require __DIR__ . '/includes/header.php';
?>
<article class="about-book" aria-labelledby="about-title">
  <section class="titlepage">
    <figure class="frontispiece">
      <?php if ($portrait): ?>
        <div class="frontispiece-frame">
          <img src="<?= e($portrait) ?>" alt="Portrait of <?= e($owner) ?>"<?= $portraitAlt ? ' data-alt-src="' . e($portraitAlt) . '"' : '' ?> width="1600" height="900" loading="eager">
        </div>
      <?php endif; ?>
      <figcaption>Frontispiece — the author, <?= e($owner) ?><?php if ($portraitAlt): ?><span class="visually-hidden"> (hover or tap the portrait to change the pose)</span><?php endif; ?></figcaption>
    </figure>
    <div class="titlepage-text">
      <p class="titlepage-kicker">About the Author</p>
      <h1 id="about-title"><?= e($owner) ?></h1>
      <p class="titlepage-sub"><?= e(setting('tagline')) ?></p>
      <dl class="colophon-list">
        <div><dt>Based in</dt><dd><?= e(setting('location')) ?></dd></div>
        <div><dt>On the shelves</dt><dd><?= $counts['projects'] ?> projects · <?= $counts['achievements'] ?> achievements · <?= $counts['skills'] ?> skills</dd></div>
      </dl>
      <?php if ($socials): ?>
        <ul class="inline-links">
          <?php foreach ($socials as $label => $href): ?><li><a href="<?= e($href) ?>" target="_blank" rel="noopener noreferrer"><?= e($label) ?></a></li><?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </section>

  <?php if ($sections): ?>
  <nav class="contents" aria-label="Contents">
    <h2>Contents</h2>
    <ol>
      <?php foreach ($sections as $i => $s): ?>
        <li><a href="#ch-<?= e($s['section_key']) ?>"><span class="roman"><?= $numerals[$i] ?? ($i + 1) ?></span><span><?= e($s['heading']) ?></span><span class="leader" aria-hidden="true"></span></a></li>
      <?php endforeach; ?>
    </ol>
  </nav>

  <div class="chapters">
    <?php foreach ($sections as $i => $s): $paras = preg_split('/\n{2,}/', trim($s['body'])) ?: []; ?>
      <section class="chapter" id="ch-<?= e($s['section_key']) ?>" aria-labelledby="h-<?= e($s['section_key']) ?>">
        <p class="chapter-no">Chapter <?= $numerals[$i] ?? ($i + 1) ?></p>
        <h2 id="h-<?= e($s['section_key']) ?>"><?= e($s['heading']) ?></h2>
        <?php if (trim($s['body']) === ''): ?>
          <p class="unwritten">This chapter is still being written.</p>
        <?php else: foreach ($paras as $p): ?>
          <p><?= nl2br(e($p)) ?></p>
        <?php endforeach; endif; ?>
      </section>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if ($reelUrl): ?>
    <section class="reel" aria-labelledby="reel-h">
      <h2 id="reel-h">Project reel</h2>
      <video controls preload="metadata"><source src="<?= e($reelUrl) ?>" type="video/mp4"></video>
    </section>
  <?php endif; ?>

  <p class="about-next">Continue reading: <a href="<?= e(url('projects.php')) ?>">The Collection</a> · <a href="<?= e(url('skills.php')) ?>">Skills &amp; Achievements</a> · <a href="<?= e(url('inquiry.php')) ?>">Write a letter</a></p>
</article>
<?php require __DIR__ . '/includes/footer.php'; ?>
