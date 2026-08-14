<?php
require_once __DIR__ . '/../app/helpers/auth.php';
require_role(['athlete']);
$pageTitle = 'Registration Complete';
$dashboardUrl = dashboard_path('athlete');
require __DIR__ . '/../app/includes/header.php';
?>
<main class="flex min-h-screen items-center justify-center bg-gradient-to-br from-blue-50 via-white to-slate-100 px-4">
    <section class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-xl">
        <?php if (app_icon_url()): ?>
            <img class="mx-auto mb-4 h-16 w-16 rounded-2xl object-cover" src="<?= e(app_icon_url()) ?>" alt="<?= e(app_setting('app_name')) ?> icon">
        <?php else: ?>
            <div class="mx-auto mb-4 grid h-16 w-16 place-items-center rounded-2xl text-xl font-black text-white" style="background: var(--theme-color);"><?= e(substr(app_setting('app_short_name', 'SM'), 0, 4)) ?></div>
        <?php endif; ?>
        <h1 class="text-2xl font-black text-slate-950">Registration Complete</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">Your athlete account and biodata have been created successfully. Redirecting you to your dashboard...</p>
        <a class="mt-6 inline-block rounded-lg px-4 py-2.5 text-sm font-semibold text-white" style="background: var(--theme-color);" href="<?= e($dashboardUrl) ?>">Go to Dashboard</a>
    </section>
</main>
<script>
setTimeout(() => {
    window.location.href = <?= json_encode($dashboardUrl) ?>;
}, 2500);
</script>
</body>
</html>
