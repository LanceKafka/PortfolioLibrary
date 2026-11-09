<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && csrf_valid($_POST['csrf'] ?? null)) {
    admin_logout();
}
redirect('login.php');
