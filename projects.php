<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$pageKey   = 'projects';
$pageTitle = 'The Collection';
$pageDescription = 'Every project in the library, shelved by category. Filter the shelves and open any book.';
$bodyClass = 'page-collection';

$cats    = lib_categories();
$volumes = lib_project_volumes();
page_data('volumes', $volumes);

$active = (string) ($_GET['category'] ?? 'all');
if ($active !== 'all' && !in_array($active, array_column($cats, 'slug'), true)) {
    $active = 'all';
}
$byCat = [];
foreach ($volumes as $v) {
    $byCat[$v['category']['slug']][] = $v;
}

$scripts = ['reader.js', 'collection.js'];
require __DIR__ . '/includes/header.php';
?>
<section class="page-intro">
  <h1>The Collection</h1>
  <p>Browse every project by category. Thick books are the big builds, thin ones are small exercises, dark spines are research and bright ones are creative experiments.</p>
</section>

<section class="collection" aria-label="Project shelves">
  <div class="filters" role="group" aria-label="Filter by category">
    <a class="chip" href="<?= e(url('projects.php')) ?>" data-filter="all" aria-pressed="<?= $active === 'all' ? 'true' : 'false' ?>">All <span class="chip-n"><?= count($volumes) ?></span></a>
    <?php foreach ($cats as $cat): if (!$cat['project_count']) continue; ?>
      <a class="chip" href="<?= e(url('projects.php?category=' . $cat['slug'])) ?>" data-filter="<?= e($cat['slug']) ?>"
         style="--cat:<?= e($cat['color']) ?>" aria-pressed="<?= $active === $cat['slug'] ? 'true' : 'false' ?>"><?= e($cat['name']) ?> <span class="chip-n"><?= (int) $cat['project_count'] ?></span></a>
    <?php endforeach; ?>
  </div>
  <?= render_legend() ?>
  <p class="filter-status visually-hidden" role="status" aria-live="polite" data-status></p>

  <div class="shelves" data-shelves>
    <?php foreach ($cats as $cat): if (empty($byCat[$cat['slug']])) continue; ?>
      <?= render_shelf('shelf-' . $cat['slug'], $cat['name'], (string) $cat['description'], (string) $cat['emblem'], $cat['color'], $byCat[$cat['slug']], $active !== 'all' && $active !== $cat['slug'], $cat['slug']) ?>
    <?php endforeach; ?>
    <?php if (!$volumes): ?><p class="empty">No projects have been shelved yet.</p><?php endif; ?>
  </div>
  <p class="end-note">Looking for certifications and tools? They have their own room: <a href="<?= e(url('skills.php')) ?>">Skills &amp; Achievements</a>.</p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
