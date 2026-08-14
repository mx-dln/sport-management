<?php
require_once __DIR__ . '/../../controllers/ScheduleController.php';
require_once __DIR__ . '/../../controllers/SportController.php';
require_once __DIR__ . '/../../controllers/TeamController.php';
require_role(['admin', 'sports_coordinator']);
$pageTitle = 'Training Schedules';
$sports = (new SportController($pdo))->all();
$teams = (new TeamController($pdo))->all();
$coaches = $pdo->query("SELECT id,name FROM users WHERE role='coach' ORDER BY name")->fetchAll();
$schedules = (new ScheduleController($pdo))->all($_GET);
require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72"><?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?><main class="p-4 lg:p-6">
<form class="mb-6 grid gap-3 rounded-xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-4" method="post" action="<?= project_url('app/ajax/schedule_ajax.php') ?>" data-ajax-form>
<select class="form-input" name="sport_id" required><option value="">Sport</option><?php foreach ($sports as $s): ?><option value="<?= e($s['id']) ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select><select class="form-input" name="team_id" required><option value="">Team</option><?php foreach ($teams as $t): ?><option value="<?= e($t['id']) ?>"><?= e($t['name']) ?></option><?php endforeach; ?></select><select class="form-input" name="coach_id" required><option value="">Coach</option><?php foreach ($coaches as $c): ?><option value="<?= e($c['id']) ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select><input class="form-input" type="date" name="training_date" required><input class="form-input" type="time" name="start_time" required><input class="form-input" type="time" name="end_time" required><input class="form-input" name="venue" placeholder="Venue" required><select class="form-input" name="status"><option>Scheduled</option><option>Updated</option><option>Cancelled</option><option>Completed</option></select><textarea class="form-input md:col-span-3" name="description" placeholder="Description"></textarea><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="send_sms"> Send SMS</label><button class="btn-primary md:col-span-4">Save Schedule</button></form>
<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"><?php foreach ($schedules as $s): ?><article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><div class="flex items-center justify-between"><h3 class="font-bold"><?= e($s['team_name']) ?></h3><span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700"><?= e($s['status']) ?></span></div><p class="mt-2 text-sm"><?= e($s['sport_name']) ?> - <?= e($s['coach_name']) ?></p><p class="mt-2 text-sm text-slate-600"><?= e($s['training_date']) ?>, <?= e(format_time_range($s['start_time'] ?? '', $s['end_time'] ?? '')) ?></p><p class="text-sm text-slate-600"><?= e($s['venue']) ?></p><?php if ($s['description']): ?><p class="mt-2 text-sm text-slate-500"><?= e($s['description']) ?></p><?php endif; ?><div class="mt-4 flex gap-2"><button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50" type="button" data-modal-open="#schedule-edit-modal-<?= e((string)$s['id']) ?>">Edit</button><form method="post" action="<?= project_url('app/ajax/schedule_ajax.php') ?>" data-ajax-form data-confirm="Delete this training schedule and its attendance records?"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e((string)$s['id']) ?>"><button class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-bold text-rose-600 hover:bg-rose-50" type="submit">Delete</button></form></div></article><?php endforeach; ?></div>

<?php foreach ($schedules as $s): ?>
<div id="schedule-edit-modal-<?= e((string)$s['id']) ?>" class="fixed inset-0 z-[70] hidden bg-slate-950/60 p-4 backdrop-blur-sm" data-modal>
    <div class="mx-auto flex min-h-full max-w-2xl items-center">
        <section class="max-h-[86vh] w-full overflow-hidden rounded-2xl bg-white shadow-2xl">
            <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-blue-600">Training Schedule</p>
                    <h2 class="text-lg font-black text-slate-950">Edit Schedule</h2>
                </div>
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" type="button" data-modal-close>Close</button>
            </header>
            <form class="grid max-h-[70vh] gap-3 overflow-y-auto p-5 md:grid-cols-2" method="post" action="<?= project_url('app/ajax/schedule_ajax.php') ?>" data-ajax-form data-validate>
                <input type="hidden" name="id" value="<?= e((string)$s['id']) ?>">
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Sport</span>
                    <select class="form-input mt-1" name="sport_id" required>
                        <?php foreach ($sports as $sport): ?>
                            <option value="<?= e($sport['id']) ?>" <?= (string)($s['sport_id'] ?? '') === (string)$sport['id'] ? 'selected' : '' ?>><?= e($sport['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Team</span>
                    <select class="form-input mt-1" name="team_id" required>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?= e($team['id']) ?>" <?= (string)($s['team_id'] ?? '') === (string)$team['id'] ? 'selected' : '' ?>><?= e($team['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Coach</span>
                    <select class="form-input mt-1" name="coach_id" required>
                        <?php foreach ($coaches as $coach): ?>
                            <option value="<?= e($coach['id']) ?>" <?= (string)($s['coach_id'] ?? '') === (string)$coach['id'] ? 'selected' : '' ?>><?= e($coach['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Training Date</span>
                    <input class="form-input mt-1" type="date" name="training_date" value="<?= e($s['training_date']) ?>" required>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Start Time</span>
                    <input class="form-input mt-1" type="time" name="start_time" value="<?= e($s['start_time']) ?>" required>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">End Time</span>
                    <input class="form-input mt-1" type="time" name="end_time" value="<?= e($s['end_time']) ?>" required>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Venue</span>
                    <input class="form-input mt-1" name="venue" value="<?= e($s['venue']) ?>" required>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Status</span>
                    <select class="form-input mt-1" name="status">
                        <?php foreach (['Scheduled', 'Updated', 'Cancelled', 'Completed'] as $status): ?>
                            <option value="<?= e($status) ?>" <?= ($s['status'] ?? '') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block md:col-span-2">
                    <span class="text-sm font-semibold text-slate-700">Description</span>
                    <textarea class="form-input mt-1" name="description" rows="3"><?= e($s['description'] ?? '') ?></textarea>
                </label>
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 md:col-span-2">
                    <input type="checkbox" name="send_sms"> Send SMS update to team members
                </label>
                <button class="btn-primary md:col-span-2">Save Changes</button>
            </form>
        </section>
    </div>
</div>
<?php endforeach; ?>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
