<?php
require_once __DIR__ . '/../../controllers/DocumentController.php';
require_once __DIR__ . '/../../controllers/AthleteController.php';
require_role(['athlete']);
$pageTitle = 'My Documents';
$stmt = $pdo->prepare('SELECT * FROM athletes WHERE user_id=? LIMIT 1');
$stmt->execute([current_user()['id']]);
$athlete = $stmt->fetch();
$docs = new DocumentController($pdo);
$requirements = $docs->requirements();
$myDocs = $athlete ? (new AthleteController($pdo))->documents((int)$athlete['id']) : [];
require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72"><?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?><main class="p-4 lg:p-6">
<?php if ($athlete): ?><form class="mb-6 grid gap-3 rounded-xl bg-white p-5 shadow-sm md:grid-cols-3" method="post" enctype="multipart/form-data" action="<?= project_url('app/ajax/document_ajax.php') ?>" data-ajax-form><input type="hidden" name="athlete_id" value="<?= e($athlete['id']) ?>"><select class="form-input" name="requirement_type_id" required><option value="">Requirement</option><?php foreach ($requirements as $r): ?><option value="<?= e($r['id']) ?>"><?= e($r['title']) ?></option><?php endforeach; ?></select><input class="form-input" type="file" name="document_file" required><button class="btn-primary">Upload</button></form><?php endif; ?>
<div class="rounded-xl bg-white shadow-sm"><table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="table-th">Requirement</th><th class="table-th">Status</th><th class="table-th">File</th></tr></thead><tbody><?php foreach ($myDocs as $d): ?><tr><td class="table-td"><?= e($d['title']) ?></td><td class="table-td"><?= e($d['status'] ?? 'Missing') ?></td><td class="table-td"><?php if ($d['file_path']): ?><a class="text-blue-600" href="#attachment-preview" data-attachment-preview data-attachment-url="<?= e(app_url($d['file_path'])) ?>" data-attachment-name="<?= e($d['original_name'] ?? $d['title']) ?>">View</a><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
