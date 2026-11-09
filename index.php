<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$pageKey   = 'home';
$bodyClass = 'page-home';
$owner     = setting('owner_name', 'Library Owner');

$cats      = lib_categories();
$projects  = lib_project_volumes();
$achieved  = lib_achievement_volumes();
$volumes   = array_merge($projects, $achieved);

// Everything the browser needs: the volumes (for the reader + mosaic) and the portrait recipe.
page_data('volumes', $volumes);
page_data('portrait', [
    'owner'  => $owner,
    'src'    => media_url(setting('profile_image')),
    'config' => json_decode(setting('portrait_config', '{}'), true) ?: new stdClass(),
]);

$intro = setting('intro_message', 'Welcome to my library. Every book holds a piece of my journey.');
$introLines = preg_split('/(?<=[.!?])\s+/', $intro, 2) ?: [$intro];

$byCat = [];
foreach ($projects as $v) {
    $byCat[$v['category']['slug']][] = $v;
}
$groupedAch = [];
foreach ($achieved as $v) {
    $groupedAch[$v['group'] ?: 'Achievements'][] = $v;
}

$scripts = ['library-core.js', 'reader.js', 'shelf.js'];
require __DIR__ . '/includes/header.php';
?>
<section class="hero" aria-labelledby="hero-title">
  <div class="hero-head">
    <h1 id="hero-title" class="hero-title"><?= e($introLines[0]) ?><?php if (isset($introLines[1])): ?><br><span class="hero-title-2"><?= e($introLines[1]) ?></span><?php endif; ?></h1>
    <p class="hero-hint"><?= e(setting('intro_instruction', 'Pick a book to explore my work.')) ?></p>
  </div>

  <a class="skip-inline" href="#index">Skip the bookshelf portrait and go to the index of books</a>
  <div class="case" data-case>
    <div class="case-crown">
      <span class="brass-plate"><?= e($owner) ?> — <?= e(setting('site_title')) ?></span>
    </div>
    <div class="case-body">
      <div class="mosaic-scroll" data-mosaic-scroll>
        <div class="mosaic" id="mosaic" data-state="loading" role="group"
             aria-label="Bookshelf forming a pixel portrait of <?= e($owner) ?> and their name. Books marked with gold bands open projects and achievements."></div>
      </div>
      <div class="mosaic-loading" data-mosaic-loading role="status"><span class="pixel-bar" aria-hidden="true"></span><span>Shelving the books…</span></div>
      <noscript><p class="mosaic-noscript">The bookshelf portrait needs JavaScript. The index below lists every volume.</p></noscript>
    </div>
    <div class="case-tools">
      <button type="button" class="tool" data-find aria-pressed="false">Find the volumes</button>
      <span class="tool-group" role="group" aria-label="Zoom the bookshelf">
        <button type="button" class="tool tool-sq" data-zoom="-1" aria-label="Zoom out">−</button>
        <button type="button" class="tool tool-sq" data-zoom="1" aria-label="Zoom in">+</button>
      </span>
      <p class="tools-note" data-note>Books with a gold band are my work. The rest make up the picture.</p>
    </div>
  </div>

  <nav class="aisles" aria-label="Other rooms of the library">
    <a href="<?= e(url('about.php')) ?>">About the Author</a>
    <a href="<?= e(url('projects.php')) ?>">The Collection</a>
    <a href="<?= e(url('skills.php')) ?>">Skills &amp; Achievements</a>
    <a href="<?= e(url('inquiry.php')) ?>">Correspondence Desk</a>
  </nav>
</section>

<section class="index" id="index" aria-labelledby="index-title">
  <header class="section-head">
    <h2 id="index-title">The Index</h2>
    <p>Every volume from the shelf above, standing in order by chapter. Each shelf is a chapter of the journey.</p>
    <?= render_legend() ?>
  </header>
  <div class="shelves">
  <?php foreach ($cats as $cat): if (empty($byCat[$cat['slug']])) continue; ?>
    <?= render_shelf('shelf-' . $cat['slug'], $cat['name'], (string) $cat['description'], (string) $cat['emblem'], $cat['color'], $byCat[$cat['slug']], false, $cat['slug']) ?>
  <?php endforeach; ?>
  <?php if ($achieved): ?>
    <?= render_shelf('shelf-achievements', 'Achievements & Milestones', 'Certifications and other milestones, in gold.', '*', GOLD, $achieved, false, 'achievement') ?>
  <?php endif; ?>
    </div>
  <?php if (!$projects && !$achieved): ?>
    <p class="empty">The shelves are empty for now. Add a project from the admin dashboard and it will appear here as a book.</p>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
