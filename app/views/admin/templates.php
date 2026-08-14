<?php
require_once __DIR__ . '/../../controllers/DocumentController.php';
require_role(['admin', 'sports_coordinator']);
$pageTitle = 'Downloadable Forms';
$templates = (new DocumentController($pdo))->templates();
require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72"><?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?><main class="p-4 lg:p-6">
<form class="mb-6 grid gap-3 rounded-xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-4" method="post" enctype="multipart/form-data" action="<?= project_url('app/ajax/document_ajax.php') ?>" data-ajax-form>
<input type="hidden" name="action" value="template"><input class="form-input" name="title" placeholder="Form title" required><input class="form-input" name="description" placeholder="Description"><input class="form-input" type="file" name="template_file" required><button class="btn-primary">Upload Template</button></form>
<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"><?php foreach ($templates as $t): ?><article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><h3 class="font-bold"><?= e($t['title']) ?></h3><p class="mt-1 text-sm text-slate-500"><?= e($t['description']) ?></p><a class="mt-4 inline-block text-sm font-semibold text-blue-600" href="#attachment-preview" data-attachment-preview data-attachment-url="<?= e(app_url($t['file_path'])) ?>" data-attachment-name="<?= e($t['original_name']) ?>">Preview blank form</a></article><?php endforeach; ?></div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
