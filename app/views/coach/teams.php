<?php
require_once __DIR__ . '/../../controllers/TeamController.php';
require_role(['coach']);

$pageTitle = 'My Teams';
$teamController = new TeamController($pdo);
$teams = $teamController->all(['coach_id' => current_user()['id']]);
$athletes = $pdo->query('SELECT id, student_id, first_name, last_name FROM athletes ORDER BY last_name, first_name')->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72">
<?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?>
<main class="p-4 lg:p-6">
<div class="grid gap-4 lg:grid-cols-2">
<?php foreach ($teams as $t): ?>
    <?php $roster = $teamController->roster((int)$t['id']); ?>
    <?php $rosterIds = array_map('intval', array_column($roster, 'id')); ?>
    <?php $availableAthletes = array_values(array_filter($athletes, fn($athlete) => !in_array((int)$athlete['id'], $rosterIds, true))); ?>
    <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-bold"><?= e($t['name']) ?></h2>
        <p class="text-sm text-slate-500"><?= e($t['sport_name']) ?></p>
        <form class="mt-4 flex gap-2" method="post" action="<?= project_url('app/ajax/team_ajax.php') ?>" data-ajax-form>
            <input type="hidden" name="action" value="assign_member">
            <input type="hidden" name="team_id" value="<?= e($t['id']) ?>">
            <select class="form-input" name="athlete_id" required>
                <option value=""><?= $availableAthletes ? 'Assign athlete' : 'No available athletes' ?></option>
                <?php foreach ($availableAthletes as $a): ?>
                    <option value="<?= e($a['id']) ?>"><?= e($a['last_name'] . ', ' . $a['first_name'] . ' (' . $a['student_id'] . ')') ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn-primary" <?= !$availableAthletes ? 'disabled' : '' ?>>Add</button>
        </form>
        <?php if (!$availableAthletes): ?>
            <p class="mt-2 rounded-lg bg-slate-50 p-3 text-sm text-slate-500">All athletes are already assigned to this team.</p>
        <?php endif; ?>
        <div class="mt-4 flex items-center justify-between">
            <h3 class="font-semibold">Roster <span class="text-sm font-normal text-slate-500">(<?= e((string)count($roster)) ?>)</span></h3>
            <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" type="button" data-modal-open="#team-roster-modal-<?= e($t['id']) ?>">Show Members</button>
        </div>
    </article>
    <div id="team-roster-modal-<?= e($t['id']) ?>" class="fixed inset-0 z-[70] hidden bg-slate-950/60 p-4 backdrop-blur-sm" data-modal>
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
                    <?php foreach ($roster as $a): ?>
                        <form class="mb-2 flex items-center justify-between rounded-lg bg-slate-50 p-3 text-sm" method="post" action="<?= project_url('app/ajax/team_ajax.php') ?>" data-ajax-form>
                            <input type="hidden" name="action" value="remove_member">
                            <input type="hidden" name="team_id" value="<?= e($t['id']) ?>">
                            <input type="hidden" name="athlete_id" value="<?= e($a['id']) ?>">
                            <span><b><?= e($a['student_id']) ?></b> - <?= e($a['last_name'] . ', ' . $a['first_name']) ?></span>
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
<?php if (!$teams): ?>
    <section class="rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm lg:col-span-2">
        <h2 class="text-xl font-black text-slate-950">No assigned teams yet</h2>
        <p class="mt-2 text-sm text-slate-500">Only teams assigned by the admin will appear here.</p>
    </section>
<?php endif; ?>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
