<?php
require_once __DIR__ . '/../../controllers/SportController.php';
require_role(['admin', 'sports_coordinator']);
$pageTitle = 'Sports Categories';
$sports = (new SportController($pdo))->all();
require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72"><?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?><main class="p-4 lg:p-6">
<form class="mb-6 grid gap-3 rounded-xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-[1fr_2fr_auto]" method="post" action="<?= project_url('app/ajax/sport_ajax.php') ?>" data-ajax-form>
<input class="form-input" name="name" placeholder="Sport name" required><input class="form-input" name="description" placeholder="Description"><button class="btn-primary">Save Sport</button></form>
<div class="rounded-xl border border-slate-200 bg-white shadow-sm"><table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="table-th">Sport</th><th class="table-th">Description</th><th class="table-th">Status</th><th class="table-th">Action</th></tr></thead><tbody>
<?php foreach ($sports as $s): ?>
<tr>
    <td class="table-td font-medium"><?= e($s['name']) ?></td>
    <td class="table-td"><?= e($s['description'] ?: '—') ?></td>
    <td class="table-td"><span class="status-pill <?= $s['status'] === 'active' ? 'status-active' : 'status-inactive' ?>"><?= e($s['status']) ?></span></td>
    <td class="table-td">
        <div class="flex flex-wrap gap-2">
            <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50" type="button" data-modal-open="#sport-edit-modal-<?= e((string)$s['id']) ?>">Edit</button>
            <form method="post" action="<?= project_url('app/ajax/sport_ajax.php') ?>" data-ajax-form data-confirm="Delete this sport?">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= e((string)$s['id']) ?>">
                <button class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-bold text-rose-600 hover:bg-rose-50" type="submit">Delete</button>
            </form>
        </div>
    </td>
</tr>
<?php endforeach; ?>
<?php if (!$sports): ?><tr><td class="table-td py-10 text-center text-slate-500" colspan="4">No sports yet.</td></tr><?php endif; ?>
</tbody></table></div>

<?php foreach ($sports as $s): ?>
<div id="sport-edit-modal-<?= e((string)$s['id']) ?>" class="fixed inset-0 z-[70] hidden bg-slate-950/60 p-4 backdrop-blur-sm" data-modal>
    <div class="mx-auto flex min-h-full max-w-lg items-center">
        <section class="w-full overflow-hidden rounded-2xl bg-white shadow-2xl">
            <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Sports Category</p>
                    <h2 class="text-lg font-black text-slate-950">Edit Sport</h2>
                </div>
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" type="button" data-modal-close>Close</button>
            </header>
            <form class="grid gap-3 p-5" method="post" action="<?= project_url('app/ajax/sport_ajax.php') ?>" data-ajax-form data-validate>
                <input type="hidden" name="id" value="<?= e((string)$s['id']) ?>">
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Sport Name</span>
                    <input class="form-input mt-1" name="name" value="<?= e($s['name']) ?>" required>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Description</span>
                    <textarea class="form-input mt-1" name="description" rows="3"><?= e($s['description'] ?? '') ?></textarea>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Status</span>
                    <select class="form-input mt-1" name="status">
                        <option value="active" <?= $s['status'] === 'active' ? 'selected' : '' ?>>active</option>
                        <option value="inactive" <?= $s['status'] === 'inactive' ? 'selected' : '' ?>>inactive</option>
                    </select>
                </label>
                <button class="btn-primary">Save Changes</button>
            </form>
        </section>
    </div>
</div>
<?php endforeach; ?>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
