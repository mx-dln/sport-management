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
<div class="grid gap-6 xl:grid-cols-[420px_1fr]">
    <form class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm" method="post" enctype="multipart/form-data" action="<?= project_url('app/ajax/settings_ajax.php') ?>" data-ajax-form data-validate>
        <h2 class="mb-4 text-lg font-bold">Branding</h2>
        <label class="mb-3 block">
            <span class="text-sm font-medium">App Name</span>
            <input class="form-input mt-1" name="app_name" required value="<?= e($settings['app_name']) ?>">
        </label>
        <label class="mb-3 block">
            <span class="text-sm font-medium">Short Name</span>
            <input class="form-input mt-1" name="app_short_name" required maxlength="12" value="<?= e($settings['app_short_name']) ?>">
        </label>
        <label class="mb-3 block">
            <span class="text-sm font-medium">School / Client Name</span>
            <input class="form-input mt-1" name="school_name" required value="<?= e($settings['school_name']) ?>">
        </label>
        <label class="mb-3 block">
            <span class="text-sm font-medium">Theme Color</span>
            <div class="mt-1 flex gap-2">
                <input class="h-10 w-14 rounded-lg border border-slate-300" type="color" name="theme_color" value="<?= e($settings['theme_color']) ?>" data-color-picker>
                <input class="form-input" value="<?= e($settings['theme_color']) ?>" data-color-text maxlength="7" pattern="#[0-9A-Fa-f]{6}">
            </div>
        </label>
        <label class="mb-4 block">
            <span class="text-sm font-medium">App Icon / Logo</span>
            <input class="form-input mt-1" type="file" name="app_icon" accept=".jpg,.jpeg,.png,.webp,.ico">
            <span class="mt-1 block text-xs text-slate-500">Allowed: JPG, PNG, WEBP, ICO up to 2MB.</span>
        </label>
        <button class="btn-primary" type="submit">Save Settings</button>
    </form>

    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-lg font-bold">Preview</h2>
        <div class="rounded-xl border border-slate-200 p-5">
            <div class="flex items-center gap-3">
                <?php if (!empty($settings['icon_path'])): ?>
                    <img class="h-14 w-14 rounded-2xl object-cover" src="<?= e(app_url($settings['icon_path'])) ?>" alt="Current icon">
                <?php else: ?>
                    <div class="grid h-14 w-14 place-items-center rounded-2xl font-black text-white" style="background: <?= e($settings['theme_color']) ?>;"><?= e($settings['app_short_name']) ?></div>
                <?php endif; ?>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide" style="color: <?= e($settings['theme_color']) ?>;"><?= e($settings['school_name']) ?></p>
                    <h3 class="text-xl font-bold"><?= e($settings['app_name']) ?></h3>
                </div>
            </div>
            <div class="mt-5 flex flex-wrap gap-2">
                <span class="rounded-lg px-3 py-2 text-sm font-semibold text-white" style="background: <?= e($settings['theme_color']) ?>;">Primary Button</span>
                <span class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold" style="color: <?= e($settings['theme_color']) ?>;">Navigation Link</span>
            </div>
        </div>
    </section>
</div>
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
