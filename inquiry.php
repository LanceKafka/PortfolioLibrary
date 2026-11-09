<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$pageKey   = 'inquiry';
$pageTitle = 'Correspondence Desk';
$pageDescription = 'Write a letter: job opportunities, freelance projects, collaborations and questions are all welcome.';
$bodyClass = 'page-inquiry';
$owner     = setting('owner_name', 'the author');

// No-JS fallback: the action redirects back here with the outcome (and the visitor's text on failure).
$flash = flash_pull();
$old   = $_SESSION['old_inquiry'] ?? [];
unset($_SESSION['old_inquiry']);
$errors = $_SESSION['inquiry_errors'] ?? [];
unset($_SESSION['inquiry_errors']);

$scripts = ['inquiry.js'];
require __DIR__ . '/includes/header.php';
$field = static fn (string $k): string => e($old[$k] ?? '');
$err   = static fn (string $k): string => isset($errors[$k]) ? '<p class="field-error" id="err-' . $k . '">' . e($errors[$k]) . '</p>' : '<p class="field-error" id="err-' . $k . '" hidden></p>';
?>
<section class="page-intro">
  <h1>The Correspondence Desk</h1>
  <p>Recruiters, clients, collaborators and fellow developers — leave a letter and it will reach <?= e($owner) ?> directly.</p>
</section>

<section class="desk" data-desk aria-label="Inquiry form">
  <div class="desk-surface">
    <?php foreach ($flash as $f): ?>
      <div class="notice notice-<?= e($f['type']) ?>" role="status"><?= e($f['message']) ?></div>
    <?php endforeach; ?>

    <form class="letter" id="inquiry-form" method="post" action="<?= e(url('actions/submit-inquiry.php')) ?>" novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="form_age" value="<?= e(form_age_token()) ?>">
      <div class="hp" aria-hidden="true"><label>Leave this field empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

      <p class="letter-date"><?= e(date('F j, Y')) ?></p>
      <p class="letter-salutation">Dear <?= e($owner) ?>,</p>

      <div class="field">
        <label for="f-name">Your name <span class="req" aria-hidden="true">*</span></label>
        <input id="f-name" name="name" type="text" required maxlength="100" autocomplete="name" value="<?= $field('name') ?>" aria-describedby="err-name"<?= isset($errors['name']) ? ' aria-invalid="true"' : '' ?>>
        <?= $err('name') ?>
      </div>
      <div class="field">
        <label for="f-email">Email address <span class="req" aria-hidden="true">*</span></label>
        <input id="f-email" name="email" type="email" required maxlength="190" autocomplete="email" value="<?= $field('email') ?>" aria-describedby="err-email"<?= isset($errors['email']) ? ' aria-invalid="true"' : '' ?>>
        <?= $err('email') ?>
      </div>
      <div class="field-row">
        <div class="field">
          <label for="f-subject">Subject <span class="req" aria-hidden="true">*</span></label>
          <input id="f-subject" name="subject" type="text" required maxlength="150" value="<?= $field('subject') ?>" aria-describedby="err-subject"<?= isset($errors['subject']) ? ' aria-invalid="true"' : '' ?>>
          <?= $err('subject') ?>
        </div>
        <div class="field">
          <label for="f-category">Category <span class="opt">(optional)</span></label>
          <select id="f-category" name="category" aria-describedby="err-category">
            <option value="">Choose one…</option>
            <?php foreach (INQUIRY_CATEGORIES as $c): ?>
              <option value="<?= e($c) ?>"<?= ($old['category'] ?? '') === $c ? ' selected' : '' ?>><?= e($c) ?></option>
            <?php endforeach; ?>
          </select>
          <?= $err('category') ?>
        </div>
      </div>
      <div class="field">
        <label for="f-message">Your message <span class="req" aria-hidden="true">*</span></label>
        <textarea id="f-message" name="message" required rows="9" minlength="10" maxlength="5000" aria-describedby="err-message msg-count"<?= isset($errors['message']) ? ' aria-invalid="true"' : '' ?>><?= $field('message') ?></textarea>
        <p class="counter" id="msg-count" aria-live="off"><span data-count>0</span> / 5000</p>
        <?= $err('message') ?>
      </div>

      <div class="letter-foot">
        <p class="letter-sign">Sincerely,<br><span data-sign>you</span></p>
        <button class="send" type="submit" data-send><span class="send-label">Send Inquiry</span><span class="send-wait" aria-hidden="true">Sealing…</span></button>
      </div>
      <p class="form-status" role="status" aria-live="polite" data-status></p>
    </form>

    <div class="envelope" aria-hidden="true"><div class="env-back"></div><div class="env-flap"></div><div class="env-front"></div><span class="env-stamp">SENT</span></div>

    <div class="confirmation" data-confirm hidden tabindex="-1">
      <h2 data-confirm-title>Your letter is on its way</h2>
      <p data-confirm-text></p>
      <button type="button" class="send send-quiet" data-again>Write another letter</button>
    </div>
  </div>
  <aside class="desk-note">
    <h2>Before you write</h2>
    <p>Letters are saved in the library's ledger and also delivered by email, so nothing gets lost. Replies come from <?= e($owner) ?> to the address you give here.</p>
  </aside>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
