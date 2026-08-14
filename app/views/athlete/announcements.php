<?php
require_once __DIR__ . '/../../controllers/AnnouncementController.php';
require_role(['athlete']);
$pageTitle = 'Announcements';
$announcements = (new AnnouncementController($pdo))->all();
require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72"><?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?><main class="p-4 lg:p-6"><?php foreach ($announcements as $a): ?><article class="mb-4 rounded-xl bg-white p-5 shadow-sm"><h3 class="font-bold"><?= e($a['title']) ?></h3><p class="mt-2 text-sm"><?= e($a['body']) ?></p><p class="mt-2 text-xs text-slate-500"><?= e(format_datetime_12($a['created_at'] ?? '')) ?></p></article><?php endforeach; ?><?php require __DIR__ . '/../../includes/footer.php'; ?>
