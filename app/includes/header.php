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
