<?php
require_once __DIR__ . '/../../controllers/TeamController.php';
require_once __DIR__ . '/../../controllers/SportController.php';
require_role(['admin', 'sports_coordinator']);
$pageTitle = 'Team Management';
$sports = (new SportController($pdo))->all();
$coaches = $pdo->query("SELECT id,name FROM users WHERE role='coach' AND status='active' ORDER BY name")->fetchAll();
$teamController = new TeamController($pdo);
$teams = $teamController->all($_GET);
$athletes = $pdo->query('SELECT id, student_id, first_name, last_name FROM athletes ORDER BY last_name, first_name')->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72"><?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?><main class="p-4 lg:p-6">
<form class="mb-6 grid gap-3 rounded-xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-5" method="post" action="<?= project_url('app/ajax/team_ajax.php') ?>" data-ajax-form>
<select class="form-input" name="sport_id" required><option value="">Sport</option><?php foreach ($sports as $s): ?><option value="<?= e($s['id']) ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select>
<select class="form-input" name="coach_id"><option value="">Coach</option><?php foreach ($coaches as $c): ?><option value="<?= e($c['id']) ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select>
<input class="form-input" name="name" placeholder="Team name" required><input class="form-input" name="description" placeholder="Description"><button class="btn-primary">Save Team</button></form>
<div class="rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="w-full text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="table-th">Team</th>
                <th class="table-th">Sport</th>
                <th class="table-th">Coach</th>
                <th class="table-th">Members</th>
                <th class="table-th">Assign Athlete</th>
                <th class="table-th">Status</th>
                <th class="table-th">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($teams as $t): ?>
                <?php $roster = $teamController->roster((int)$t['id']); ?>
                <tr>
                    <td class="table-td font-medium"><?= e($t['name']) ?></td>
                    <td class="table-td"><?= e($t['sport_name']) ?></td>
                    <td class="table-td">
                        <button
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                            type="button"
                            data-modal-open="#team-coach-modal-<?= e($t['id']) ?>"
                            title="Assign coach"
                        >
                            <svg class="h-4 w-4" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <span><?= e($t['coach_name'] ?: 'Assign coach') ?></span>
                        </button>
                    </td>
                    <td class="table-td">
                        <button
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                            type="button"
                            data-modal-open="#team-members-modal-<?= e($t['id']) ?>"
                            title="Show members"
                        >
                            <svg class="h-4 w-4" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <span><?= e((string)count($roster)) ?></span>
                        </button>
                    </td>
                    <td class="table-td">
                        <button
                            class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-white"
                            type="button"
                            style="background: var(--theme-color);"
                            data-modal-open="#team-assign-modal-<?= e($t['id']) ?>"
                            title="Assign athlete"
                        >
                            <svg class="h-4 w-4" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 5v14M5 12h14"></path>
                            </svg>
                            <span>Assign</span>
                        </button>
                    </td>
                    <td class="table-td"><?= e($t['status']) ?></td>
                    <td class="table-td">
                        <div class="flex flex-wrap gap-2">
                            <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50" type="button" data-modal-open="#team-edit-modal-<?= e((string)$t['id']) ?>">Edit</button>
                            <form method="post" action="<?= project_url('app/ajax/team_ajax.php') ?>" data-ajax-form data-confirm="Delete this team?">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= e((string)$t['id']) ?>">
                                <button class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-bold text-rose-600 hover:bg-rose-50" type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php foreach ($teams as $t): ?>
    <?php $roster = $teamController->roster((int)$t['id']); ?>
    <?php $rosterIds = array_map('intval', array_column($roster, 'id')); ?>
    <?php $availableAthletes = array_values(array_filter($athletes, fn($athlete) => !in_array((int)$athlete['id'], $rosterIds, true))); ?>

    <div id="team-edit-modal-<?= e((string)$t['id']) ?>" class="fixed inset-0 z-[70] hidden bg-slate-950/60 p-4 backdrop-blur-sm" data-modal>
        <div class="mx-auto flex min-h-full max-w-lg items-center">
            <section class="w-full overflow-hidden rounded-2xl bg-white shadow-2xl">
                <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Team Management</p>
                        <h2 class="text-lg font-black text-slate-950">Edit Team</h2>
                        <p class="text-sm text-slate-500"><?= e($t['name']) ?></p>
                    </div>
                    <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" type="button" data-modal-close>Close</button>
                </header>
                <form class="grid gap-3 p-5" method="post" action="<?= project_url('app/ajax/team_ajax.php') ?>" data-ajax-form data-validate>
                    <input type="hidden" name="id" value="<?= e((string)$t['id']) ?>">
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Team Name</span>
                        <input class="form-input mt-1" name="name" value="<?= e($t['name']) ?>" required>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Sport</span>
                        <select class="form-input mt-1" name="sport_id" required>
                            <option value="">Select sport</option>
                            <?php foreach ($sports as $sport): ?>
                                <option value="<?= e($sport['id']) ?>" <?= (int)($t['sport_id'] ?? 0) === (int)$sport['id'] ? 'selected' : '' ?>><?= e($sport['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Coach</span>
                        <select class="form-input mt-1" name="coach_id">
                            <option value="">Unassigned</option>
                            <?php foreach ($coaches as $coach): ?>
                                <option value="<?= e($coach['id']) ?>" <?= (int)($t['coach_id'] ?? 0) === (int)$coach['id'] ? 'selected' : '' ?>><?= e($coach['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Description</span>
                        <textarea class="form-input mt-1" name="description" rows="3"><?= e($t['description'] ?? '') ?></textarea>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Status</span>
                        <select class="form-input mt-1" name="status">
                            <option value="active" <?= $t['status'] === 'active' ? 'selected' : '' ?>>active</option>
                            <option value="inactive" <?= $t['status'] === 'inactive' ? 'selected' : '' ?>>inactive</option>
                        </select>
                    </label>
                    <button class="btn-primary">Save Changes</button>
                </form>
            </section>
        </div>
    </div>
    <div id="team-coach-modal-<?= e($t['id']) ?>" class="fixed inset-0 z-[70] hidden bg-slate-950/60 p-4 backdrop-blur-sm" data-modal>
        <div class="mx-auto flex min-h-full max-w-xl items-center">
            <section class="w-full overflow-hidden rounded-2xl bg-white shadow-2xl">
                <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400"><?= e($t['sport_name']) ?></p>
                        <h2 class="text-lg font-black text-slate-950">Assign Coach</h2>
                        <p class="text-sm text-slate-500"><?= e($t['name']) ?></p>
                    </div>
                    <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" type="button" data-modal-close>Close</button>
                </header>
                <form class="grid gap-3 p-5" method="post" action="<?= project_url('app/ajax/team_ajax.php') ?>" data-ajax-form>
                    <input type="hidden" name="action" value="assign_coach">
                    <input type="hidden" name="team_id" value="<?= e($t['id']) ?>">
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Coach</span>
                        <select class="form-input mt-1" name="coach_id">
                            <option value="">Unassigned</option>
                            <?php foreach ($coaches as $coach): ?>
                                <option value="<?= e($coach['id']) ?>" <?= (int)($t['coach_id'] ?? 0) === (int)$coach['id'] ? 'selected' : '' ?>><?= e($coach['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <button class="btn-primary">Save Coach Assignment</button>
                </form>
            </section>
        </div>
    </div>

    <div id="team-assign-modal-<?= e($t['id']) ?>" class="fixed inset-0 z-[70] hidden bg-slate-950/60 p-4 backdrop-blur-sm" data-modal>
        <div class="mx-auto flex min-h-full max-w-xl items-center">
            <section class="w-full overflow-hidden rounded-2xl bg-white shadow-2xl">
                <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400"><?= e($t['sport_name']) ?></p>
                        <h2 class="text-lg font-black text-slate-950">Assign Athlete</h2>
                        <p class="text-sm text-slate-500"><?= e($t['name']) ?></p>
                    </div>
                    <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" type="button" data-modal-close>Close</button>
                </header>
                <form class="grid gap-3 p-5" method="post" action="<?= project_url('app/ajax/team_ajax.php') ?>" data-ajax-form>
                    <input type="hidden" name="action" value="assign_member">
                    <input type="hidden" name="team_id" value="<?= e($t['id']) ?>">
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Athlete</span>
                        <select class="form-input mt-1" name="athlete_id" required>
                            <option value="">Select athlete</option>
                            <?php foreach ($availableAthletes as $a): ?>
                                <option value="<?= e($a['id']) ?>"><?= e($a['last_name'] . ', ' . $a['first_name'] . ' (' . $a['student_id'] . ')') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <?php if (!$availableAthletes): ?>
                        <p class="rounded-lg bg-slate-50 p-3 text-sm text-slate-500">All athletes are already assigned to this team.</p>
                    <?php endif; ?>
                    <button class="btn-primary" <?= !$availableAthletes ? 'disabled' : '' ?>>Add to Team</button>
                </form>
            </section>
        </div>
    </div>

    <div id="team-members-modal-<?= e($t['id']) ?>" class="fixed inset-0 z-[70] hidden bg-slate-950/60 p-4 backdrop-blur-sm" data-modal>
        <div class="mx-auto flex min-h-full max-w-2xl items-center">
            <section class="w-full overflow-hidden rounded-2xl bg-white shadow-2xl">
                <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400"><?= e($t['sport_name']) ?></p>
                        <h2 class="text-lg font-black text-slate-950"><?= e($t['name']) ?> Members</h2>
                        <p class="text-sm text-slate-500"><?= e((string)count($roster)) ?> member<?= count($roster) === 1 ? '' : 's' ?></p>
                    </div>
                    <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" type="button" data-modal-close>Close</button>
                </header>
                <div class="max-h-[65vh] overflow-y-auto p-5">
                    <?php foreach ($roster as $member): ?>
                        <form class="mb-2 flex items-center justify-between rounded-lg bg-slate-50 p-3 text-sm" method="post" action="<?= project_url('app/ajax/team_ajax.php') ?>" data-ajax-form>
                            <input type="hidden" name="action" value="remove_member">
                            <input type="hidden" name="team_id" value="<?= e($t['id']) ?>">
                            <input type="hidden" name="athlete_id" value="<?= e($member['id']) ?>">
                            <span><b><?= e($member['student_id']) ?></b> - <?= e($member['last_name'] . ', ' . $member['first_name']) ?></span>
                            <button class="font-bold text-rose-600" type="submit">Remove</button>
                        </form>
                    <?php endforeach; ?>
                    <?php if (!$roster): ?>
                        <p class="rounded bg-slate-50 p-6 text-center text-sm text-slate-500">No members yet.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
<?php endforeach; ?>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
