<?php
require_once __DIR__ . '/../../controllers/DocumentController.php';
require_once __DIR__ . '/../../controllers/AthleteController.php';
require_role(['admin', 'sports_coordinator']);
$pageTitle = 'Requirement Documents';
$docs = new DocumentController($pdo);
$requirements = $docs->requirements();
$uploads = $docs->uploads();
$athletes = (new AthleteController($pdo))->all();
$uploadsByAthlete = [];
foreach ($uploads as $upload) {
    $athleteId = (int)$upload['athlete_id'];
    if (!isset($uploadsByAthlete[$athleteId])) {
        $uploadsByAthlete[$athleteId] = [
            'name' => $upload['athlete_name'],
            'documents' => [],
        ];
    }
    $uploadsByAthlete[$athleteId]['documents'][] = $upload;
}
require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72"><?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?><main class="p-4 lg:p-6">
<div class="grid gap-6 xl:grid-cols-2">
<form class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm" method="post" action="<?= project_url('app/ajax/document_ajax.php') ?>" data-ajax-form>
<input type="hidden" name="action" value="requirement"><h2 class="mb-4 font-bold">Define Requirement</h2><input class="form-input mb-3" name="title" placeholder="Document title" required><textarea class="form-input mb-3" name="description" placeholder="Description"></textarea><label class="mb-4 flex gap-2 text-sm"><input type="checkbox" name="is_required" checked> Required</label><button class="btn-primary">Save Requirement</button></form>
<form class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm" method="post" enctype="multipart/form-data" action="<?= project_url('app/ajax/document_ajax.php') ?>" data-ajax-form>
<input type="hidden" name="action" value="upload_document"><h2 class="mb-4 font-bold">Upload Athlete Document</h2>
<select class="form-input mb-3" name="athlete_id" required><option value="">Athlete</option><?php foreach ($athletes as $a): ?><option value="<?= e($a['id']) ?>"><?= e($a['last_name'] . ', ' . $a['first_name']) ?></option><?php endforeach; ?></select>
<select class="form-input mb-3" name="requirement_type_id" required><option value="">Requirement</option><?php foreach ($requirements as $r): ?><option value="<?= e($r['id']) ?>"><?= e($r['title']) ?></option><?php endforeach; ?></select>
<input class="form-input mb-3" type="file" name="document_file" accept=".pdf,.jpg,.jpeg,.png" required><button class="btn-primary">Upload</button></form>
</div>
<section class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <button class="requirements-toggle flex w-full items-center justify-between gap-3 p-4 text-left" type="button" data-toggle-panel data-target="#defined-requirements-panel">
        <div>
            <h2 class="font-bold">Defined Requirements</h2>
            <p class="text-sm text-slate-500">These are the document requirements athletes see and submit.</p>
        </div>
        <span class="flex items-center gap-3">
            <span class="requirements-count rounded-full px-3 py-1 text-xs font-bold"><?= e((string)count($requirements)) ?> requirements</span>
            <svg class="requirements-chevron h-5 w-5 transition" data-chevron aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m6 9 6 6 6-6"></path>
            </svg>
        </span>
    </button>
    <div id="defined-requirements-panel" class="hidden overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="table-th">Requirement</th>
                    <th class="table-th">Description</th>
                    <th class="table-th">Type</th>
                    <th class="table-th">Created</th>
                    <th class="table-th">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requirements as $requirement): ?>
                    <tr>
                        <td class="table-td font-semibold"><?= e($requirement['title']) ?></td>
                        <td class="table-td"><?= e($requirement['description'] ?: 'No description') ?></td>
                        <td class="table-td">
                            <span class="status-pill <?= !empty($requirement['is_required']) ? 'status-submitted' : 'status-neutral' ?>">
                                <?= !empty($requirement['is_required']) ? 'Required' : 'Optional' ?>
                            </span>
                        </td>
                        <td class="table-td text-slate-500"><?= e(format_datetime_12($requirement['created_at'] ?? '')) ?></td>
                        <td class="table-td">
                            <div class="flex flex-wrap gap-2">
                                <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50" type="button" data-modal-open="#requirement-edit-modal-<?= e((string)$requirement['id']) ?>">Edit</button>
                                <form method="post" action="<?= project_url('app/ajax/document_ajax.php') ?>" data-ajax-form data-confirm="Delete this requirement? Uploaded files for this requirement will also be removed.">
                                    <input type="hidden" name="action" value="delete_requirement">
                                    <input type="hidden" name="id" value="<?= e((string)$requirement['id']) ?>">
                                    <button class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-bold text-rose-600 hover:bg-rose-50" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$requirements): ?>
                    <tr>
                        <td class="table-td text-center text-slate-500" colspan="5">No requirements defined yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php foreach ($requirements as $requirement): ?>
    <div id="requirement-edit-modal-<?= e((string)$requirement['id']) ?>" class="fixed inset-0 z-[70] hidden bg-slate-950/60 p-4 backdrop-blur-sm" data-modal>
        <div class="mx-auto flex min-h-full max-w-xl items-center">
            <section class="w-full overflow-hidden rounded-2xl bg-white shadow-2xl">
                <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Requirement Setup</p>
                        <h2 class="text-lg font-black text-slate-950">Edit Requirement</h2>
                    </div>
                    <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" type="button" data-modal-close>Close</button>
                </header>
                <form class="grid gap-3 p-5" method="post" action="<?= project_url('app/ajax/document_ajax.php') ?>" data-ajax-form>
                    <input type="hidden" name="action" value="requirement">
                    <input type="hidden" name="id" value="<?= e((string)$requirement['id']) ?>">
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Title</span>
                        <input class="form-input mt-1" name="title" value="<?= e($requirement['title']) ?>" required>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Description</span>
                        <textarea class="form-input mt-1" name="description" rows="3"><?= e($requirement['description'] ?? '') ?></textarea>
                    </label>
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <input type="checkbox" name="is_required" <?= !empty($requirement['is_required']) ? 'checked' : '' ?>>
                        Required document
                    </label>
                    <button class="btn-primary">Save Changes</button>
                </form>
            </section>
        </div>
    </div>
<?php endforeach; ?>

<section class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="flex items-center justify-between gap-3 p-4">
        <div>
            <h2 class="font-bold">Athlete Documents</h2>
            <p class="text-sm text-slate-500">One row per athlete. Open details to review all submitted files.</p>
        </div>
        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600"><?= e((string)count($uploadsByAthlete)) ?> athletes</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="table-th">Athlete</th>
                    <th class="table-th">Documents</th>
                    <th class="table-th">Approved</th>
                    <th class="table-th">Pending / Submitted</th>
                    <th class="table-th">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($uploadsByAthlete as $athleteId => $group): ?>
                    <?php
                    $athleteDocs = $group['documents'];
                    $approved = count(array_filter($athleteDocs, fn($doc) => ($doc['status'] ?? '') === 'Approved'));
                    $pending = count(array_filter($athleteDocs, fn($doc) => in_array(($doc['status'] ?? ''), ['Pending', 'Submitted'], true)));
                    ?>
                    <tr>
                        <td class="table-td font-semibold"><?= e($group['name']) ?></td>
                        <td class="table-td"><?= e((string)count($athleteDocs)) ?> file<?= count($athleteDocs) === 1 ? '' : 's' ?></td>
                        <td class="table-td"><?= e((string)$approved) ?></td>
                        <td class="table-td"><?= e((string)$pending) ?></td>
                        <td class="table-td">
                            <button
                                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                                type="button"
                                data-modal-open="#athlete-documents-modal-<?= e((string)$athleteId) ?>"
                            >
                                <svg class="h-4 w-4" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <span>View Details</span>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$uploadsByAthlete): ?>
                    <tr>
                        <td class="table-td text-center text-slate-500" colspan="5">No uploaded documents yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php foreach ($uploadsByAthlete as $athleteId => $group): ?>
    <div id="athlete-documents-modal-<?= e((string)$athleteId) ?>" class="fixed inset-0 z-[70] hidden bg-slate-950/60 p-4 backdrop-blur-sm" data-modal>
        <div class="mx-auto flex min-h-full max-w-5xl items-center">
            <section class="w-full overflow-hidden rounded-2xl bg-white shadow-2xl">
                <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Submitted Documents</p>
                        <h2 class="text-lg font-black text-slate-950"><?= e($group['name']) ?></h2>
                        <p class="text-sm text-slate-500"><?= e((string)count($group['documents'])) ?> uploaded file<?= count($group['documents']) === 1 ? '' : 's' ?></p>
                    </div>
                    <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" type="button" data-modal-close>Close</button>
                </header>
                <div class="max-h-[70vh] overflow-y-auto p-5">
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-sm" data-enhance-table="false">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="table-th">Requirement</th>
                                    <th class="table-th">File</th>
                                    <th class="table-th">Status</th>
                                    <th class="table-th">Uploaded</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($group['documents'] as $doc): ?>
                                    <tr>
                                        <td class="table-td font-semibold"><?= e($doc['requirement_title']) ?></td>
                                        <td class="table-td">
                                            <a class="text-blue-600" href="#attachment-preview" data-attachment-preview data-attachment-url="<?= e(app_url($doc['file_path'])) ?>" data-attachment-name="<?= e($doc['original_name']) ?>">
                                                <?= e($doc['original_name']) ?>
                                            </a>
                                        </td>
                                        <td class="table-td">
                                            <select class="form-input min-w-36" data-ajax-status data-url="<?= project_url('app/ajax/document_ajax.php') ?>" data-id="<?= e($doc['id']) ?>" data-action="status">
                                                <?php foreach (['Pending','Submitted','Approved','Rejected'] as $st): ?>
                                                    <option <?= $doc['status'] === $st ? 'selected' : '' ?>><?= e($st) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td class="table-td text-slate-500"><?= e(format_datetime_12($doc['uploaded_at'] ?? '')) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
<?php endforeach; ?>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
