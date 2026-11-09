<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require_admin();
$adminTitle = 'Overview'; $adminKey = 'dashboard';

$n = static fn (string $sql): int => (int) db()->query($sql)->fetchColumn();
$stats = [
    'Projects'  => [$n('SELECT COUNT(*) FROM projects'), 'admin/projects.php'],
    'Skills'    => [$n('SELECT COUNT(*) FROM skills'), 'admin/skills.php'],
    'Achievements' => [$n('SELECT COUNT(*) FROM achievements'), 'admin/achievements.php'],
    'New inquiries' => [$n("SELECT COUNT(*) FROM inquiries WHERE inquiry_status='pending'"), 'admin/inquiries.php?status=pending'],
];
$recent = db()->query('SELECT inquiry_id, sender_name, inquiry_subject, date_submitted, inquiry_status, email_status FROM inquiries ORDER BY inquiry_id DESC LIMIT 6')->fetchAll();
$failed = $n("SELECT COUNT(*) FROM inquiries WHERE email_status='failed'");
require __DIR__ . '/includes/admin-header.php';
?>
<div class="adm-stats">
  <?php foreach ($stats as $label => [$count, $href]): ?>
    <a class="adm-stat" href="<?= e(url($href)) ?>"><strong><?= $count ?></strong><span><?= e($label) ?></span></a>
  <?php endforeach; ?>
</div>
<?php if (!mail_is_configured()): ?>
  <div class="adm-flash adm-warning">No email handler is configured yet — inquiries are saved but no email is sent. Set <code>FORMSPREE_ENDPOINT</code> or fill in the MAIL_* values in <code>.env</code>.</div>
<?php endif; ?>
<?php if ($failed): ?>
  <div class="adm-flash adm-warning"><?= $failed ?> inquiry email(s) failed to send. <a href="<?= e(url('admin/inquiries.php?email=failed')) ?>">Review and retry</a>.</div>
<?php endif; ?>
<h2>Latest letters</h2>
<table class="adm-table"><thead><tr><th>From</th><th>Subject</th><th>Received</th><th>Status</th><th>Email</th></tr></thead><tbody>
<?php foreach ($recent as $r): ?>
  <tr><td><?= e($r['sender_name']) ?></td><td><a href="<?= e(url('admin/inquiries.php?view=' . $r['inquiry_id'])) ?>"><?= e($r['inquiry_subject']) ?></a></td>
  <td><?= e(date('M j, g:i A', strtotime($r['date_submitted']))) ?></td><td><span class="tag tag-<?= e($r['inquiry_status']) ?>"><?= e($r['inquiry_status']) ?></span></td>
  <td><span class="tag tag-mail-<?= e($r['email_status']) ?>"><?= e($r['email_status']) ?></span></td></tr>
<?php endforeach; if (!$recent): ?><tr><td colspan="5">No letters yet.</td></tr><?php endif; ?>
</tbody></table>
<?php require __DIR__ . '/includes/admin-footer.php'; ?>
