<?php
require_once __DIR__ . '/../../controllers/SmsController.php';
require_role(['admin', 'sports_coordinator']);
$pageTitle = 'SMS Notification Logs';
$logs = (new SmsController($pdo))->logs();
require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72">
<?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?>
<main class="p-4 lg:p-6">

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-2xl font-black text-slate-950 dark:text-white">SMS Notification Center</h2>
        <p class="text-sm text-slate-500">Dispatch SMS alerts, training reminders, and view transmission logs.</p>
    </div>
    <div class="flex items-center gap-2">
        <button type="button" class="smis-cmd-btn primary" data-modal-open="#construct-message-modal">
            <span>✉️</span>
            <span>Construct Message</span>
        </button>
    </div>
</div>

<!-- Send SMS Form -->
<section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="mb-3 flex items-center justify-between">
        <h3 class="text-sm font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300">Quick SMS Dispatch</h3>
        <span class="text-xs text-blue-600 font-semibold">11-digit numbers only (09XXXXXXXXX)</span>
    </div>
    <form class="grid gap-4 md:grid-cols-3" method="post" action="<?= project_url('app/ajax/sms_ajax.php') ?>" data-ajax-form data-validate>
        <label class="block">
            <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Recipient Name <span class="text-rose-600 font-bold">*</span></span>
            <input class="form-input mt-1 w-full" name="recipient_name" placeholder="Full Name / Team Name" required>
        </label>
        <label class="block">
            <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Phone Number (11 Digits) <span class="text-rose-600 font-bold">*</span></span>
            <input class="form-input mt-1 w-full" type="tel" name="phone_number" placeholder="09XXXXXXXXX (11 digits)" pattern="09[0-9]{9}" minlength="11" maxlength="11" inputmode="numeric" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 11)" data-phone-11 required>
        </label>
        <label class="block md:col-span-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Message Body <span class="text-rose-600 font-bold">*</span></span>
                <span id="quick-sms-counter" class="text-xs text-slate-400">0 / 160 (1 SMS)</span>
            </div>
            <textarea id="quick-sms-body" class="form-input mt-1 w-full" name="message" rows="3" placeholder="Enter notification message here..." required></textarea>
        </label>
        <div class="flex items-center justify-between md:col-span-3">
            <p class="text-xs text-slate-400">Need templates or multi-channel delivery? Use the <button type="button" class="text-blue-600 font-bold underline" data-modal-open="#construct-message-modal">Construct Message Tool</button>.</p>
            <button class="btn-primary">Send / Log SMS</button>
        </div>
    </form>
</section>

<!-- Transmission Logs Table -->
<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="border-b border-slate-100 p-5 dark:border-slate-800 flex items-center justify-between">
        <div>
            <h3 class="font-bold text-slate-950 dark:text-white">Transmission Logs</h3>
            <p class="text-xs text-slate-500">History of SMS dispatches with status and timestamps.</p>
        </div>
        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
            <?= count($logs) ?> logs recorded
        </span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800">
                <tr>
                    <th class="table-th">Recipient</th>
                    <th class="table-th">Phone Number (11 Digits)</th>
                    <th class="table-th">Message</th>
                    <th class="table-th">Status</th>
                    <th class="table-th text-right">Sent Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $l): ?>
                    <tr class="border-t border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                        <td class="table-td font-semibold text-slate-900 dark:text-white"><?= e($l['recipient_name']) ?></td>
                        <td class="table-td font-medium"><?= e($l['phone_number']) ?></td>
                        <td class="table-td max-w-sm truncate text-slate-600 dark:text-slate-400"><?= e($l['message']) ?></td>
                        <td class="table-td"><span class="status-pill status-active"><?= e(ucwords(str_replace('_', ' ', (string)$l['status']))) ?></span></td>
                        <td class="table-td text-right text-xs text-slate-500"><?= e(format_datetime_12($l['sent_at'] ?? '')) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$logs): ?>
                    <tr><td class="table-td text-center text-slate-400 py-8" colspan="5">No SMS logs recorded yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const body = document.getElementById('quick-sms-body');
    const counter = document.getElementById('quick-sms-counter');
    if (body && counter) {
        body.addEventListener('input', () => {
            const len = body.value.length;
            const segments = Math.max(1, Math.ceil(len / 160));
            counter.textContent = `${len} / 160 (${segments} SMS)`;
        });
    }
});
</script>

</main>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
