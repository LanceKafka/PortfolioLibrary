<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

if (admin_user()) {
    redirect('dashboard.php');
}
$error = null;
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!csrf_valid($_POST['csrf'] ?? null)) {
        $error = 'Session expired. Please try again.';
    } elseif (!rate_limit_allows('login', 5, 900)) {
        $error = 'Too many attempts. Please wait 15 minutes.';
    } else {
        $u = post_str('username', 60);
        if (admin_login($u, (string) ($_POST['password'] ?? ''))) {
            $next = (string) ($_GET['next'] ?? '');
            // only allow local, relative targets (prevents open redirects)
            $ok = $next !== '' && preg_match('#^[a-z0-9_/\-]+\.php(\?[a-z0-9=&_\-]*)?$#i', $next) && !str_contains($next, '//');
            redirect($ok ? $next : 'dashboard.php');
        }
        rate_limit_hit('login');
        $error = 'Those credentials did not match.';
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow"><title>Librarian’s login</title>
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>"></head>
<body class="adm-login"><main>
<form method="post" class="adm-card" autocomplete="off">
  <p class="adm-login-kicker">The Developer’s Library · Private access</p>
  <h1>Librarian’s login</h1>
  <p>Sign in to manage the collection, achievements, and correspondence.</p>
  <?php if ($error): ?><div class="adm-flash adm-error" role="alert"><?= e($error) ?></div><?php endif; ?>
  <?= csrf_field() ?>
  <label>Username <input name="username" required maxlength="60" autofocus autocomplete="username"></label>
  <label>Password <input name="password" type="password" required autocomplete="current-password"></label>
  <button type="submit">Enter</button>
  <p><a href="<?= e(url('index.php')) ?>">← Return to the library</a></p>
</form></main></body></html>
