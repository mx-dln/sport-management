<?php
require_once __DIR__ . '/../../controllers/SettingsController.php';
require_role(['admin']);
$pageTitle = 'System Settings';
$controller = new SettingsController();
$settings = $controller->current();
require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72"><?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?>
<main class="p-4 lg:p-6">
<?php require __DIR__ . '/../../includes/alerts.php'; ?>
<form class="space-y-6" method="post" enctype="multipart/form-data" action="<?= project_url('app/ajax/settings_ajax.php') ?>" data-ajax-form data-validate>
    <input type="hidden" name="action" value="save">
    <div class="grid gap-6 xl:grid-cols-[420px_1fr]">
        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="mb-4 text-lg font-bold text-slate-950 dark:text-white">Branding</h2>
            <label class="mb-3 block">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">App Name</span>
                <input class="form-input mt-1" name="app_name" required value="<?= e($settings['app_name']) ?>">
            </label>
            <label class="mb-3 block">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Short Name</span>
                <input class="form-input mt-1" name="app_short_name" required maxlength="12" value="<?= e($settings['app_short_name']) ?>">
            </label>
            <label class="mb-3 block">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">School / Client Name</span>
                <input class="form-input mt-1" name="school_name" required value="<?= e($settings['school_name']) ?>">
            </label>
            <label class="mb-3 block">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Theme Color</span>
                <div class="mt-1 flex gap-2">
                    <input class="h-10 w-14 rounded-lg border border-slate-300" type="color" name="theme_color" value="<?= e($settings['theme_color']) ?>" data-color-picker>
                    <input class="form-input" value="<?= e($settings['theme_color']) ?>" data-color-text maxlength="7" pattern="#[0-9A-Fa-f]{6}">
                </div>
            </label>
            <label class="mb-4 block">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">App Icon / Logo</span>
                <input class="form-input mt-1" type="file" name="app_icon" accept=".jpg,.jpeg,.png,.webp,.ico">
                <span class="mt-1 block text-xs text-slate-500">Allowed: JPG, PNG, WEBP, ICO up to 2MB.</span>
            </label>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="mb-4 text-lg font-bold text-slate-950 dark:text-white">Preview</h2>
            <div class="rounded-xl border border-slate-200 p-5 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <?php if (!empty($settings['icon_path'])): ?>
                        <img class="h-14 w-14 rounded-2xl object-cover" src="<?= e(app_url($settings['icon_path'])) ?>" alt="Current icon">
                    <?php else: ?>
                        <div class="grid h-14 w-14 place-items-center rounded-2xl font-black text-white" style="background: <?= e($settings['theme_color']) ?>;"><?= e($settings['app_short_name']) ?></div>
                    <?php endif; ?>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide" style="color: <?= e($settings['theme_color']) ?>;"><?= e($settings['school_name']) ?></p>
                        <h3 class="text-xl font-bold text-slate-950 dark:text-white"><?= e($settings['app_name']) ?></h3>
                    </div>
                </div>
                <div class="mt-5 flex flex-wrap gap-2">
                    <span class="rounded-lg px-3 py-2 text-sm font-semibold text-white" style="background: <?= e($settings['theme_color']) ?>;">Primary Button</span>
                    <span class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold dark:border-slate-700" style="color: <?= e($settings['theme_color']) ?>;">Navigation Link</span>
                </div>
            </div>
        </section>
    </div>

    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-950 dark:text-white">Semaphore SMS Configuration</h2>
                <p class="text-sm text-slate-500">Controls SMS sending for announcements, schedules, and quick SMS dispatch.</p>
            </div>
            <label class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-2 text-sm font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                <input type="checkbox" name="semaphore_enabled" value="1" <?= (($settings['semaphore_enabled'] ?? '0') === '1') ? 'checked' : '' ?>>
                Enable SMS
            </label>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <label class="block">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Provider</span>
                <input class="form-input mt-1" name="semaphore_provider" value="<?= e($settings['semaphore_provider'] ?? 'Semaphore') ?>">
            </label>
            <label class="block">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Sender Name</span>
                <input class="form-input mt-1" name="semaphore_sender_name" value="<?= e($settings['semaphore_sender_name_display'] ?? '') ?>" placeholder="Example: TechBridge">
            </label>
            <label class="block md:col-span-2">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">API Endpoint</span>
                <input class="form-input mt-1" name="semaphore_endpoint" value="<?= e($settings['semaphore_endpoint'] ?? 'https://api.semaphore.co/api/v4/messages') ?>">
            </label>
            <label class="block md:col-span-2">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">API Key</span>
                <input class="form-input mt-1" type="password" name="semaphore_api_key" value="<?= e($settings['semaphore_api_key_display'] ?? '') ?>" autocomplete="new-password">
                <span class="mt-1 block text-xs text-slate-500">Saved securely as encoded text in the local settings file.</span>
            </label>
        </div>
        <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/40">
            <h3 class="text-sm font-black uppercase tracking-wide text-slate-700 dark:text-slate-300">Test SMS</h3>
            <div class="mt-3 grid gap-3 md:grid-cols-[1fr_auto]">
                <input class="form-input" name="test_sms_number" inputmode="tel" placeholder="0912 345 6789">
                <button class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-700" type="submit" name="action" value="test_sms" data-loading-text="Sending SMS...">Send Test SMS</button>
            </div>
            <p class="mt-2 text-xs text-slate-500">This saves the current settings first, then sends one test SMS.</p>
        </div>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-950 dark:text-white">SMTP Email Configuration</h2>
                <p class="text-sm text-slate-500">Current SMTP settings for future email notifications and account messages.</p>
            </div>
            <label class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-2 text-sm font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                <input type="checkbox" name="smtp_enabled" value="1" <?= (($settings['smtp_enabled'] ?? '0') === '1') ? 'checked' : '' ?>>
                Enable SMTP
            </label>
        </div>
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <label class="block lg:col-span-2">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">SMTP Host</span>
                <input class="form-input mt-1" name="smtp_host" value="<?= e($settings['smtp_host'] ?? '') ?>" placeholder="smtp.gmail.com">
            </label>
            <label class="block">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Port</span>
                <input class="form-input mt-1" type="number" name="smtp_port" min="1" max="65535" value="<?= e($settings['smtp_port'] ?? '587') ?>">
            </label>
            <label class="block">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Encryption</span>
                <select class="form-input mt-1" name="smtp_encryption">
                    <?php foreach (['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'None'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= (($settings['smtp_encryption'] ?? 'tls') === $value) ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="block lg:col-span-2">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">SMTP Username</span>
                <input class="form-input mt-1" name="smtp_username" value="<?= e($settings['smtp_username'] ?? '') ?>">
            </label>
            <label class="block lg:col-span-2">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">SMTP Password</span>
                <input class="form-input mt-1" type="password" name="smtp_password" value="<?= e($settings['smtp_password_display'] ?? '') ?>" autocomplete="new-password">
            </label>
            <label class="block lg:col-span-2">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">From Email</span>
                <input class="form-input mt-1" type="email" name="smtp_from_email" value="<?= e($settings['smtp_from_email'] ?? '') ?>" placeholder="sports@isu.edu.ph">
            </label>
            <label class="block lg:col-span-2">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">From Name</span>
                <input class="form-input mt-1" name="smtp_from_name" value="<?= e($settings['smtp_from_name'] ?? '') ?>" placeholder="Sports Management Information System">
            </label>
        </div>
        <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/40">
            <h3 class="text-sm font-black uppercase tracking-wide text-slate-700 dark:text-slate-300">Test Email</h3>
            <div class="mt-3 grid gap-3 md:grid-cols-[1fr_auto]">
                <input class="form-input" type="email" name="test_email_to" placeholder="recipient@example.com">
                <button class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-700" type="submit" name="action" value="test_email" data-loading-text="Sending Email...">Send Test Email</button>
            </div>
            <p class="mt-2 text-xs text-slate-500">This saves the current settings first, then sends one SMTP test email.</p>
        </div>
    </section>

    <div class="sticky bottom-4 z-10 flex justify-end">
        <button class="btn-primary shadow-lg" type="submit" name="action" value="save">Save Settings</button>
    </div>
</form>
</main>
<script>
const colorPicker = document.querySelector('[data-color-picker]');
const colorText = document.querySelector('[data-color-text]');
if (colorPicker && colorText) {
    colorPicker.addEventListener('input', () => colorText.value = colorPicker.value);
    colorText.addEventListener('input', () => {
        if (/^#[0-9A-Fa-f]{6}$/.test(colorText.value)) colorPicker.value = colorText.value;
    });
}
</script>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
