<?php
require_once __DIR__ . '/../../controllers/AnnouncementController.php';
require_once __DIR__ . '/../../controllers/SportController.php';
require_once __DIR__ . '/../../controllers/TeamController.php';
require_role(['admin', 'sports_coordinator']);
$pageTitle = 'Announcements';
$sports = (new SportController($pdo))->all();
$teams = (new TeamController($pdo))->all();
$announcements = (new AnnouncementController($pdo))->all();
require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72"><?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?><main class="p-4 lg:p-6">
<form class="mb-6 grid gap-3 rounded-xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-2" method="post" action="<?= project_url('app/ajax/announcement_ajax.php') ?>" data-ajax-form><input class="form-input" name="title" placeholder="Title" required><select class="form-input" name="sport_id"><option value="">All sports</option><?php foreach ($sports as $s): ?><option value="<?= e($s['id']) ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select><select class="form-input" name="team_id"><option value="">All teams</option><?php foreach ($teams as $t): ?><option value="<?= e($t['id']) ?>"><?= e($t['name']) ?></option><?php endforeach; ?></select><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="send_sms"> Send SMS to selected team</label><textarea class="form-input md:col-span-2" name="body" placeholder="Announcement" required></textarea><button class="btn-primary md:col-span-2">Post Announcement</button></form>
<div class="space-y-4"><?php foreach ($announcements as $a): ?><article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><div class="flex items-start justify-between gap-3"><div class="min-w-0"><h3 class="font-bold"><?= e($a['title']) ?></h3><p class="mt-2 text-sm"><?= e($a['body']) ?></p><p class="mt-3 text-xs text-slate-500"><?= e($a['sport_name'] ?? 'All sports') ?> / <?= e($a['team_name'] ?? 'All teams') ?> · <?= e(format_datetime_12($a['created_at'] ?? '')) ?></p></div><div class="flex shrink-0 gap-2"><button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50" type="button" data-modal-open="#announcement-edit-modal-<?= e((string)$a['id']) ?>">Edit</button><form method="post" action="<?= project_url('app/ajax/announcement_ajax.php') ?>" data-ajax-form data-confirm="Delete this announcement?"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e((string)$a['id']) ?>"><button class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-bold text-rose-600 hover:bg-rose-50" type="submit">Delete</button></form></div></div></article><?php endforeach; ?></div>

<?php foreach ($announcements as $a): ?>
<div id="announcement-edit-modal-<?= e((string)$a['id']) ?>" class="fixed inset-0 z-[70] hidden bg-slate-950/60 p-4 backdrop-blur-sm" data-modal>
    <div class="mx-auto flex min-h-full max-w-xl items-center">
        <section class="w-full overflow-hidden rounded-2xl bg-white shadow-2xl">
            <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-blue-600">Announcement</p>
                    <h2 class="text-lg font-black text-slate-950">Edit Announcement</h2>
                </div>
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" type="button" data-modal-close>Close</button>
            </header>
            <form class="grid gap-3 p-5" method="post" action="<?= project_url('app/ajax/announcement_ajax.php') ?>" data-ajax-form data-validate>
                <input type="hidden" name="id" value="<?= e((string)$a['id']) ?>">
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Title</span>
                    <input class="form-input mt-1" name="title" value="<?= e($a['title']) ?>" required>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Sport</span>
                    <select class="form-input mt-1" name="sport_id">
                        <option value="">All sports</option>
                        <?php foreach ($sports as $sport): ?>
                            <option value="<?= e($sport['id']) ?>" <?= (string)($a['sport_id'] ?? '') === (string)$sport['id'] ? 'selected' : '' ?>><?= e($sport['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Team</span>
                    <select class="form-input mt-1" name="team_id">
                        <option value="">All teams</option>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?= e($team['id']) ?>" <?= (string)($a['team_id'] ?? '') === (string)$team['id'] ? 'selected' : '' ?>><?= e($team['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Body</span>
                    <textarea class="form-input mt-1" name="body" rows="4" required><?= e($a['body']) ?></textarea>
                </label>
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                    <input type="checkbox" name="send_sms"> Send SMS to selected team
                </label>
                <button class="btn-primary">Save Changes</button>
            </form>
        </section>
    </div>
</div>
<?php endforeach; ?>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

