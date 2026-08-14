<?php
require_once __DIR__ . '/../../controllers/SmsController.php';
require_role(['admin', 'sports_coordinator']);
$pageTitle = 'SMS Notification Logs';
$logs = (new SmsController($pdo))->logs();
require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72"><?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?><main class="p-4 lg:p-6">
<form class="mb-6 grid gap-3 rounded-xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-3" method="post" action="<?= project_url('app/ajax/sms_ajax.php') ?>" data-ajax-form><input class="form-input" name="recipient_name" placeholder="Recipient name" required><input class="form-input" name="phone_number" placeholder="Phone number" required><input class="form-input" name="message" placeholder="Message" required><button class="btn-primary md:col-span-3">Send / Log SMS</button></form>
<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm"><table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="table-th">Recipient</th><th class="table-th">Phone</th><th class="table-th">Message</th><th class="table-th">Status</th><th class="table-th">Sent At</th></tr></thead><tbody><?php foreach ($logs as $l): ?><tr><td class="table-td"><?= e($l['recipient_name']) ?></td><td class="table-td"><?= e($l['phone_number']) ?></td><td class="table-td"><?= e($l['message']) ?></td><td class="table-td"><?= e($l['status']) ?></td><td class="table-td"><?= e(format_datetime_12($l['sent_at'] ?? '')) ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
