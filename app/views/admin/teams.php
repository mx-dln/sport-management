<?php
require_once __DIR__ . '/../../controllers/TeamController.php';
require_once __DIR__ . '/../../controllers/SportController.php';
require_role(['admin', 'sports_coordinator']);
$pageTitle = 'Team Management';
$sports = (new SportController($pdo))->all();
$coaches = $pdo->query("SELECT id, name, phone_number FROM users WHERE role='coach' AND status='active' ORDER BY name")->fetchAll();
$teamController = new TeamController($pdo);
$teams = $teamController->all($_GET);
$athletes = $pdo->query('SELECT id, student_id, first_name, last_name, position, profile_photo FROM athletes ORDER BY last_name, first_name')->fetchAll();

// Check if basketball pre-selected
$selectedSportFilter = trim((string)($_GET['sport_id'] ?? ''));
$isBasketballQuick = isset($_GET['sport_select']) && strtolower($_GET['sport_select']) === 'basketball';
$basketballSportId = 0;
foreach ($sports as $s) {
    if (stripos($s['name'], 'Basketball') !== false) {
        $basketballSportId = (int)$s['id'];
        break;
    }
}
if ($isBasketballQuick && $basketballSportId > 0 && empty($selectedSportFilter)) {
    $selectedSportFilter = (string)$basketballSportId;
}

require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72">
<?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?>
<main class="p-4 lg:p-6">

<!-- Header Section & Actions -->
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-2xl font-black text-slate-950 dark:text-white">Teams &amp; Athlete Rosters</h2>
        <p class="text-sm text-slate-500">Organize sports teams, assign coaches, and manage athlete rosters in gallery or table view.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="<?= e(app_url('index.php?page=athlete_gallery')) ?>" class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-600 bg-emerald-50 px-3.5 py-2 text-xs font-bold text-emerald-800 shadow-sm hover:bg-emerald-100 transition dark:bg-emerald-950 dark:border-emerald-800 dark:text-emerald-300">
            <span>🏛️</span>
            <span>SCUAA Form 2 Gallery</span>
        </a>
        <button type="button" onclick="presetBasketballTeam()" class="inline-flex items-center gap-1.5 rounded-xl bg-amber-500 px-3.5 py-2 text-xs font-bold text-white shadow-sm hover:bg-amber-600 transition">
            <span>🏀</span>
            <span>Form Basketball Team</span>
        </button>
        <div class="inline-flex rounded-xl border border-slate-200 bg-white p-1 shadow-xs dark:border-slate-800 dark:bg-slate-900">
            <button type="button" id="btn-view-gallery" class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-bold text-white transition" onclick="switchTeamView('gallery')">
                ⊞ Gallery Format
            </button>
            <button type="button" id="btn-view-table" class="rounded-lg px-3 py-1.5 text-xs font-bold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white transition" onclick="switchTeamView('table')">
                ☰ Table Format
            </button>
        </div>
    </div>
</div>

<!-- Team Creation Form -->
<section id="team-form-section" class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="mb-3 flex items-center justify-between">
        <h3 id="form-heading" class="text-sm font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300">
            <?= $isBasketballQuick ? '🏀 Form Basketball Team' : 'Create / Add New Team' ?>
        </h3>
        <span class="text-xs text-slate-400">Fill in details and assign an initial coach</span>
    </div>
    <form class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5" method="post" action="<?= project_url('app/ajax/team_ajax.php') ?>" data-ajax-form data-validate>
        <select id="team-sport-select" class="form-input" name="sport_id" required>
            <option value="">Select Sport</option>
            <?php foreach ($sports as $s): ?>
                <option value="<?= e($s['id']) ?>" <?= ((string)$s['id'] === (string)($isBasketballQuick ? $basketballSportId : $selectedSportFilter)) ? 'selected' : '' ?>>
                    <?= stripos($s['name'], 'Basketball') !== false ? '🏀 ' : (stripos($s['name'], 'Volleyball') !== false ? '🏐 ' : '⚽ ') ?><?= e($s['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select class="form-input" name="coach_id">
            <option value="">Assign Coach (Optional)</option>
            <?php foreach ($coaches as $c): ?>
                <option value="<?= e($c['id']) ?>"><?= e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <input id="team-name-input" class="form-input" name="name" placeholder="Team Name (e.g. Blue Falcons Basketball)" value="<?= $isBasketballQuick ? 'Blue Falcons Basketball' : '' ?>" required>
        <input class="form-input" name="description" placeholder="Description / Division / Varsity">
        <button class="btn-primary flex items-center justify-center gap-1.5">
            <span>Save Team</span>
        </button>
    </form>
</section>

<!-- Sport Filter Pills -->
<div class="mb-6 flex flex-wrap items-center gap-2">
    <a href="<?= e(app_url('index.php?page=teams')) ?>" class="rounded-xl px-3 py-1.5 text-xs font-bold transition <?= empty($selectedSportFilter) ? 'bg-slate-900 text-white' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300' ?>">
        All Sports (<?= count($teams) ?>)
    </a>
    <?php foreach ($sports as $s): ?>
        <?php 
        $sportCount = count(array_filter($teams, fn($t) => (int)($t['sport_id'] ?? 0) === (int)$s['id']));
        $isActive = (string)$selectedSportFilter === (string)$s['id'];
        $icon = stripos($s['name'], 'Basketball') !== false ? '🏀' : (stripos($s['name'], 'Volleyball') !== false ? '🏐' : (stripos($s['name'], 'Badminton') !== false ? '🏸' : (stripos($s['name'], 'Athletics') !== false ? '🏃' : '♟️')));
        ?>
        <a href="<?= e(app_url('index.php?page=teams&sport_id=' . $s['id'])) ?>" class="rounded-xl px-3 py-1.5 text-xs font-bold transition <?= $isActive ? 'bg-blue-600 text-white' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300' ?>">
            <?= $icon ?> <?= e($s['name']) ?> (<?= $sportCount ?>)
        </a>
    <?php endforeach; ?>
</div>

<!-- ================= 1. GALLERY FORMAT VIEW ================= -->
<div id="teams-gallery-view" class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
    <?php foreach ($teams as $t): ?>
        <?php 
        if (!empty($selectedSportFilter) && (string)$t['sport_id'] !== (string)$selectedSportFilter) {
            continue;
        }
        $roster = $teamController->roster((int)$t['id']);
        $isBball = stripos($t['sport_name'] ?? '', 'Basketball') !== false;
        ?>
        <article class="team-gallery-card flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div>
                <!-- Team Card Header -->
                <div class="border-b border-slate-100 p-5 dark:border-slate-800 <?= $isBball ? 'bg-amber-50/50 dark:bg-amber-950/20' : 'bg-slate-50/60 dark:bg-slate-800/40' ?>">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="grid h-11 w-11 place-items-center rounded-2xl text-xl shadow-xs <?= $isBball ? 'bg-amber-500 text-white' : 'bg-blue-600 text-white' ?>">
                                <?= $isBball ? '🏀' : (stripos($t['sport_name'] ?? '', 'Volleyball') !== false ? '🏐' : '🏅') ?>
                            </span>
                            <div>
                                <h3 class="font-black text-slate-950 dark:text-white"><?= e($t['name']) ?></h3>
                                <p class="text-xs font-semibold text-slate-500"><?= e($t['sport_name'] ?: 'Sport') ?> · <span class="uppercase text-[11px] <?= $t['status'] === 'active' ? 'text-emerald-600 font-bold' : 'text-slate-400' ?>"><?= e($t['status']) ?></span></p>
                            </div>
                        </div>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            <?= count($roster) ?> athletes
                        </span>
                    </div>
                    <?php if (!empty($t['description'])): ?>
                        <p class="mt-3 text-xs text-slate-600 line-clamp-2 dark:text-slate-400"><?= e($t['description']) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Coach Information -->
                <div class="border-b border-slate-100 px-5 py-3 text-xs flex items-center justify-between dark:border-slate-800">
                    <span class="text-slate-500 font-medium">Head Coach:</span>
                    <?php if ($t['coach_name']): ?>
                        <span class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            <?= e($t['coach_name']) ?>
                        </span>
                    <?php else: ?>
                        <button type="button" class="text-blue-600 font-semibold hover:underline" data-modal-open="#team-coach-modal-<?= e($t['id']) ?>">+ Assign Coach</button>
                    <?php endif; ?>
                </div>

                <!-- Athlete Roster Gallery Strip -->
                <div class="p-5">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Team Roster Gallery</span>
                        <button type="button" class="text-xs font-semibold text-blue-600 hover:text-blue-700" data-modal-open="#team-members-modal-<?= e($t['id']) ?>">View All (<?= count($roster) ?>)</button>
                    </div>

                    <?php if ($roster): ?>
                        <div class="grid grid-cols-3 gap-2.5">
                            <?php foreach (array_slice($roster, 0, 6) as $m): ?>
                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-2 text-center shadow-2xs dark:border-slate-800 dark:bg-slate-800/60">
                                    <?php if (!empty($m['profile_photo'])): ?>
                                        <img class="mx-auto h-10 w-10 rounded-full object-cover border border-slate-200" src="<?= e(app_url($m['profile_photo'])) ?>" alt="<?= e($m['first_name']) ?>">
                                    <?php else: ?>
                                        <div class="mx-auto grid h-10 w-10 place-items-center rounded-full bg-blue-100 text-xs font-black text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                                            <?= e(substr($m['first_name'], 0, 1) . substr($m['last_name'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <p class="mt-1.5 truncate text-[11px] font-bold text-slate-900 dark:text-white"><?= e($m['last_name']) ?></p>
                                    <p class="truncate text-[10px] text-slate-500"><?= e($m['position'] ?: ($isBball ? 'Player' : 'Member')) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="rounded-xl border border-dashed border-slate-200 p-6 text-center text-xs text-slate-400 dark:border-slate-800">
                            No athletes assigned yet.<br>Click below to build team roster.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Card Actions -->
            <div class="border-t border-slate-100 p-4 bg-slate-50/50 flex items-center justify-between gap-2 dark:border-slate-800 dark:bg-slate-800/30">
                <button type="button" class="smis-cmd-btn primary" data-modal-open="#team-assign-modal-<?= e($t['id']) ?>">
                    <span>+ Assign Athlete</span>
                </button>
                <div class="flex items-center gap-1.5">
                    <a href="<?= e(app_url('index.php?page=athlete_gallery&team_id=' . $t['id'])) ?>" class="rounded-lg border border-emerald-300 bg-emerald-50 px-2.5 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950 dark:border-emerald-800 dark:text-emerald-300" title="Print SCUAA Form 2 Gallery">
                        🖨️ Form 2
                    </a>
                    <button type="button" class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" data-modal-open="#team-edit-modal-<?= e((string)$t['id']) ?>">
                        Edit
                    </button>
                    <form method="post" action="<?= project_url('app/ajax/team_ajax.php') ?>" data-ajax-form data-confirm="Delete this team?">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= e((string)$t['id']) ?>">
                        <button type="submit" class="rounded-lg border border-rose-200 px-2.5 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-50 dark:border-rose-900 dark:hover:bg-rose-950">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </article>
    <?php endforeach; ?>

    <?php if (!$teams): ?>
        <div class="col-span-full rounded-2xl border border-slate-200 bg-white p-12 text-center text-sm text-slate-500 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            No teams found. Click <b>Form Basketball Team</b> or add a new team above.
        </div>
    <?php endif; ?>
</div>

<!-- ================= 2. TABLE FORMAT VIEW ================= -->
<div id="teams-table-view" class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-800">
            <tr>
                <th class="table-th">Team Name</th>
                <th class="table-th">Sport</th>
                <th class="table-th">Coach</th>
                <th class="table-th">Members</th>
                <th class="table-th">Assign Athlete</th>
                <th class="table-th">Status</th>
                <th class="table-th text-right">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($teams as $t): ?>
                <?php 
                if (!empty($selectedSportFilter) && (string)$t['sport_id'] !== (string)$selectedSportFilter) {
                    continue;
                }
                $roster = $teamController->roster((int)$t['id']);
                ?>
                <tr class="border-t border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                    <td class="table-td font-bold text-slate-950 dark:text-white"><?= e($t['name']) ?></td>
                    <td class="table-td"><?= e($t['sport_name']) ?></td>
                    <td class="table-td">
                        <button class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300" type="button" data-modal-open="#team-coach-modal-<?= e($t['id']) ?>">
                            <?= e($t['coach_name'] ?: 'Assign coach') ?>
                        </button>
                    </td>
                    <td class="table-td">
                        <button class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300" type="button" data-modal-open="#team-members-modal-<?= e($t['id']) ?>">
                            👥 <?= e((string)count($roster)) ?> athletes
                        </button>
                    </td>
                    <td class="table-td">
                        <button class="smis-cmd-btn primary text-xs" type="button" data-modal-open="#team-assign-modal-<?= e($t['id']) ?>">
                            + Assign
                        </button>
                    </td>
                    <td class="table-td">
                        <span class="status-pill <?= $t['status'] === 'active' ? 'status-active' : 'status-neutral' ?>"><?= strtoupper((string)$t['status']) ?></span>
                    </td>
                    <td class="table-td text-right">
                        <div class="flex justify-end gap-1.5">
                            <a href="<?= e(app_url('index.php?page=athlete_gallery&team_id=' . $t['id'])) ?>" class="rounded-lg border border-emerald-300 bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-700 hover:bg-emerald-100" title="Print SCUAA Form 2 Gallery">🖨️ Form 2</a>
                            <button class="rounded-lg border border-slate-300 px-2.5 py-1 text-xs font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300" type="button" data-modal-open="#team-edit-modal-<?= e((string)$t['id']) ?>">Edit</button>
                            <form method="post" action="<?= project_url('app/ajax/team_ajax.php') ?>" data-ajax-form data-confirm="Delete this team?">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= e((string)$t['id']) ?>">
                                <button class="rounded-lg border border-rose-200 px-2.5 py-1 text-xs font-bold text-rose-600 hover:bg-rose-50" type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- ================= MODALS ================= -->
<?php foreach ($teams as $t): ?>
    <?php $roster = $teamController->roster((int)$t['id']); ?>
    <?php $rosterIds = array_map('intval', array_column($roster, 'id')); ?>
    <?php $availableAthletes = array_values(array_filter($athletes, fn($athlete) => !in_array((int)$athlete['id'], $rosterIds, true))); ?>

    <!-- Edit Team Modal -->
    <div id="team-edit-modal-<?= e((string)$t['id']) ?>" class="fixed inset-0 z-[70] hidden bg-slate-950/60 p-4 backdrop-blur-sm" data-modal>
        <div class="mx-auto flex min-h-full max-w-lg items-center">
            <section class="w-full overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900 dark:border dark:border-slate-800">
                <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Team Management</p>
                        <h2 class="text-lg font-black text-slate-950 dark:text-white">Edit Team</h2>
                        <p class="text-xs text-slate-500"><?= e($t['name']) ?></p>
                    </div>
                    <button class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300" type="button" data-modal-close>Close</button>
                </header>
                <form class="grid gap-3 p-5" method="post" action="<?= project_url('app/ajax/team_ajax.php') ?>" data-ajax-form data-validate>
                    <input type="hidden" name="id" value="<?= e((string)$t['id']) ?>">
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Team Name</span>
                        <input class="form-input mt-1 w-full" name="name" value="<?= e($t['name']) ?>" required>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Sport</span>
                        <select class="form-input mt-1 w-full" name="sport_id" required>
                            <option value="">Select sport</option>
                            <?php foreach ($sports as $sport): ?>
                                <option value="<?= e($sport['id']) ?>" <?= (int)($t['sport_id'] ?? 0) === (int)$sport['id'] ? 'selected' : '' ?>><?= e($sport['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Coach</span>
                        <select class="form-input mt-1 w-full" name="coach_id">
                            <option value="">Unassigned</option>
                            <?php foreach ($coaches as $coach): ?>
                                <option value="<?= e($coach['id']) ?>" <?= (int)($t['coach_id'] ?? 0) === (int)$coach['id'] ? 'selected' : '' ?>><?= e($coach['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Description</span>
                        <textarea class="form-input mt-1 w-full" name="description" rows="3"><?= e($t['description'] ?? '') ?></textarea>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Status</span>
                        <select class="form-input mt-1 w-full" name="status">
                            <option value="active" <?= $t['status'] === 'active' ? 'selected' : '' ?>>ACTIVE</option>
                            <option value="inactive" <?= $t['status'] === 'inactive' ? 'selected' : '' ?>>INACTIVE</option>
                        </select>
                    </label>
                    <button class="btn-primary mt-2">Save Changes</button>
                </form>
            </section>
        </div>
    </div>

    <!-- Assign Coach Modal -->
    <div id="team-coach-modal-<?= e($t['id']) ?>" class="fixed inset-0 z-[70] hidden bg-slate-950/60 p-4 backdrop-blur-sm" data-modal>
        <div class="mx-auto flex min-h-full max-w-md items-center">
            <section class="w-full overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900 dark:border dark:border-slate-800">
                <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400"><?= e($t['sport_name']) ?></p>
                        <h2 class="text-lg font-black text-slate-950 dark:text-white">Assign Coach</h2>
                        <p class="text-xs text-slate-500"><?= e($t['name']) ?></p>
                    </div>
                    <button class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300" type="button" data-modal-close>Close</button>
                </header>
                <form class="grid gap-3 p-5" method="post" action="<?= project_url('app/ajax/team_ajax.php') ?>" data-ajax-form>
                    <input type="hidden" name="action" value="assign_coach">
                    <input type="hidden" name="team_id" value="<?= e($t['id']) ?>">
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Select Coach</span>
                        <select class="form-input mt-1 w-full" name="coach_id">
                            <option value="">Unassigned</option>
                            <?php foreach ($coaches as $coach): ?>
                                <option value="<?= e($coach['id']) ?>" <?= (int)($t['coach_id'] ?? 0) === (int)$coach['id'] ? 'selected' : '' ?>><?= e($coach['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <button class="btn-primary mt-2">Save Coach Assignment</button>
                </form>
            </section>
        </div>
    </div>

    <!-- Assign Athlete Modal -->
    <div id="team-assign-modal-<?= e($t['id']) ?>" class="fixed inset-0 z-[70] hidden bg-slate-950/60 p-4 backdrop-blur-sm" data-modal>
        <div class="mx-auto flex min-h-full max-w-lg items-center">
            <section class="w-full overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900 dark:border dark:border-slate-800">
                <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400"><?= e($t['sport_name']) ?></p>
                        <h2 class="text-lg font-black text-slate-950 dark:text-white">Assign Athlete to Roster</h2>
                        <p class="text-xs text-slate-500"><?= e($t['name']) ?></p>
                    </div>
                    <button class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300" type="button" data-modal-close>Close</button>
                </header>
                <form class="grid gap-3 p-5" method="post" action="<?= project_url('app/ajax/team_ajax.php') ?>" data-ajax-form>
                    <input type="hidden" name="action" value="assign_member">
                    <input type="hidden" name="team_id" value="<?= e($t['id']) ?>">
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Choose Athlete</span>
                        <select class="form-input mt-1 w-full" name="athlete_id" required>
                            <option value="">Select athlete</option>
                            <?php foreach ($availableAthletes as $a): ?>
                                <option value="<?= e($a['id']) ?>"><?= e($a['last_name'] . ', ' . $a['first_name'] . ' (' . $a['student_id'] . ')') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <?php if (!$availableAthletes): ?>
                        <p class="rounded-xl bg-slate-50 p-3 text-xs text-slate-500 dark:bg-slate-800">All registered athletes are already on this team.</p>
                    <?php endif; ?>
                    <button class="btn-primary mt-2" <?= !$availableAthletes ? 'disabled' : '' ?>>Add to Team Roster</button>
                </form>
            </section>
        </div>
    </div>

    <!-- Team Members Modal -->
    <div id="team-members-modal-<?= e($t['id']) ?>" class="fixed inset-0 z-[70] hidden bg-slate-950/60 p-4 backdrop-blur-sm" data-modal>
        <div class="mx-auto flex min-h-full max-w-xl items-center">
            <section class="w-full overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900 dark:border dark:border-slate-800">
                <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400"><?= e($t['sport_name']) ?></p>
                        <h2 class="text-lg font-black text-slate-950 dark:text-white"><?= e($t['name']) ?> Roster</h2>
                        <p class="text-xs text-slate-500"><?= e((string)count($roster)) ?> active member<?= count($roster) === 1 ? '' : 's' ?></p>
                    </div>
                    <button class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300" type="button" data-modal-close>Close</button>
                </header>
                <div class="max-h-[60vh] overflow-y-auto p-5 smis-scrollbar">
                    <?php foreach ($roster as $member): ?>
                        <form class="mb-2 flex items-center justify-between rounded-xl bg-slate-50 p-3 text-sm dark:bg-slate-800" method="post" action="<?= project_url('app/ajax/team_ajax.php') ?>" data-ajax-form>
                            <input type="hidden" name="action" value="remove_member">
                            <input type="hidden" name="team_id" value="<?= e($t['id']) ?>">
                            <input type="hidden" name="athlete_id" value="<?= e($member['id']) ?>">
                            <div class="flex items-center gap-3">
                                <?php if (!empty($member['profile_photo'])): ?>
                                    <img class="h-9 w-9 rounded-full object-cover border" src="<?= e(app_url($member['profile_photo'])) ?>" alt="<?= e($member['first_name']) ?>">
                                <?php else: ?>
                                    <div class="grid h-9 w-9 place-items-center rounded-full bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                                        <?= e(substr($member['first_name'], 0, 1) . substr($member['last_name'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white"><?= e($member['last_name'] . ', ' . $member['first_name']) ?></p>
                                    <p class="text-xs text-slate-500"><?= e($member['student_id']) ?> · <?= e($member['position'] ?: 'Player') ?></p>
                                </div>
                            </div>
                            <button class="rounded-lg border border-rose-200 px-2.5 py-1 text-xs font-bold text-rose-600 hover:bg-rose-50 dark:border-rose-900 dark:hover:bg-rose-950" type="submit">Remove</button>
                        </form>
                    <?php endforeach; ?>
                    <?php if (!$roster): ?>
                        <div class="rounded-xl bg-slate-50 p-8 text-center text-xs text-slate-400 dark:bg-slate-800">No members assigned to this team yet.</div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
<?php endforeach; ?>

<script>
function switchTeamView(mode) {
    const galleryView = document.getElementById('teams-gallery-view');
    const tableView = document.getElementById('teams-table-view');
    const btnGallery = document.getElementById('btn-view-gallery');
    const btnTable = document.getElementById('btn-view-table');

    if (mode === 'table') {
        galleryView.classList.add('hidden');
        tableView.classList.remove('hidden');
        btnTable.className = 'rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-bold text-white transition';
        btnGallery.className = 'rounded-lg px-3 py-1.5 text-xs font-bold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white transition';
        localStorage.setItem('teams_view_mode', 'table');
    } else {
        tableView.classList.add('hidden');
        galleryView.classList.remove('hidden');
        btnGallery.className = 'rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-bold text-white transition';
        btnTable.className = 'rounded-lg px-3 py-1.5 text-xs font-bold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white transition';
        localStorage.setItem('teams_view_mode', 'gallery');
    }
}

function presetBasketballTeam() {
    const sportSelect = document.getElementById('team-sport-select');
    const nameInput = document.getElementById('team-name-input');
    const heading = document.getElementById('form-heading');

    if (sportSelect) {
        for (let opt of sportSelect.options) {
            if (opt.textContent.toLowerCase().includes('basketball')) {
                sportSelect.value = opt.value;
                break;
            }
        }
    }
    if (nameInput) {
        if (!nameInput.value || nameInput.value === 'Team name') {
            nameInput.value = 'Varsity Basketball Team';
        }
        nameInput.focus();
    }
    if (heading) {
        heading.textContent = '🏀 Form Basketball Team';
    }
    document.getElementById('team-form-section')?.scrollIntoView({ behavior: 'smooth' });
}

// Restore user view preference
document.addEventListener('DOMContentLoaded', () => {
    const savedMode = localStorage.getItem('teams_view_mode');
    if (savedMode === 'table') {
        switchTeamView('table');
    }
});
</script>

</main>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
