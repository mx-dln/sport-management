<?php require_once __DIR__ . '/../helpers/auth.php'; ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? app_setting('app_name')) ?></title>
    <?php if (app_icon_url()): ?>
        <link rel="icon" href="<?= e(app_icon_url()) ?>">
    <?php endif; ?>
    <script>
        (() => {
            const storedTheme = localStorage.getItem('smis-theme');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (storedTheme === 'dark' || (!storedTheme && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= app_url('assets/css/tailwind.css') ?>">
    <style>
        :root { --theme-color: <?= e(app_setting('theme_color', '#2563eb')) ?>; }
    </style>
</head>
<body class="bg-slate-100 text-slate-900">
<div class="smis-splash fixed inset-0 z-[100] flex items-center justify-center bg-gradient-to-br from-blue-50 via-white to-slate-100" data-splash data-splash-duration="900" role="status" aria-live="polite">
    <div class="flex flex-col items-center gap-5 px-6 text-center">
        <?php if (app_icon_url()): ?>
            <img class="h-20 w-20 rounded-3xl object-cover shadow-lg" src="<?= e(app_icon_url()) ?>" alt="<?= e(app_setting('app_name')) ?> icon">
        <?php else: ?>
            <div class="grid h-20 w-20 place-items-center rounded-3xl text-2xl font-black text-white shadow-lg" style="background: var(--theme-color);"><?= e(substr(app_setting('app_short_name', 'SMIS'), 0, 4)) ?></div>
        <?php endif; ?>
        <div>
            <h1 class="text-2xl font-black text-slate-900"><?= e(app_setting('app_name')) ?></h1>
            <p class="mt-1 text-sm font-semibold text-slate-500"><?= e(school_name()) ?></p>
        </div>
        <div class="w-64">
            <div class="smis-progress is-animating" data-splash-progress>
                <span class="smis-progress-bar"></span>
            </div>
        </div>
        <p class="text-xs font-bold uppercase tracking-widest text-slate-400" data-splash-status>Loading&hellip;</p>
    </div>
</div>
<noscript><style>.smis-splash{display:none!important}</style></noscript>
<div class="smis-topbar" data-top-progress hidden>
    <span class="smis-topbar-bar"></span>
</div>
<script>
(function () {
    function loadingSpinner(size) {
        return '<svg class="' + (size || 'h-4 w-4') + ' shrink-0 animate-spin" aria-hidden="true" viewBox="0 0 24 24" fill="none">' +
            '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>' +
            '<path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 0 1 4 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
    }
    window.loadingSpinner = loadingSpinner;

    var topBar = document.querySelector('[data-top-progress]');
    var topTimer = null;

    window.startTopProgress = function () {
        if (!topBar) return;
        clearTimeout(topTimer);
        topBar.hidden = false;
        topBar.classList.remove('is-complete');
        topBar.classList.add('is-active');
    };

    window.finishTopProgress = function () {
        if (!topBar) return;
        clearTimeout(topTimer);
        topBar.classList.remove('is-active');
        topBar.classList.add('is-complete');
        topTimer = setTimeout(function () {
            topBar.hidden = true;
            topBar.classList.remove('is-complete');
        }, 600);
    };

    var splash = document.querySelector('[data-splash]');
    if (splash) {
        var progress = splash.querySelector('[data-splash-progress]');
        var status = splash.querySelector('[data-splash-status]');
        var done = false;

        function finish() {
            if (done) return;
            done = true;
            if (progress) {
                progress.classList.remove('is-animating');
                progress.classList.add('is-complete');
            }
            if (status) status.textContent = 'Ready';
            setTimeout(function () { splash.classList.add('is-hidden'); }, 250);
            setTimeout(function () { splash.remove(); }, 800);
        }

        var minDuration = Number(splash.dataset.splashDuration || 900) || 900;
        var startedAt = Date.now();

        window.addEventListener('load', function () {
            setTimeout(finish, Math.max(0, minDuration - (Date.now() - startedAt)));
        });
        setTimeout(finish, minDuration + 1600);
    }
})();
</script>
