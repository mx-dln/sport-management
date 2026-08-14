<?php
require_once __DIR__ . '/../../controllers/CompetitionController.php';
require_role(['admin', 'sports_coordinator']);
$pageTitle = 'Competitions';
$competition = new CompetitionController($pdo);
$competitions = $competition->all($_GET);
$sports = $competition->sports();

$perPage = 9;
$currentPage = max(1, (int)($_GET['p'] ?? 1));
$totalCompetitions = count($competitions);
$totalPages = max(1, (int)ceil($totalCompetitions / $perPage));
$currentPage = min($currentPage, $totalPages);
$pagedCompetitions = array_slice($competitions, ($currentPage - 1) * $perPage, $perPage);
$pageUrl = function (int $pageNumber): string {
    $params = array_merge($_GET, ['page' => 'competition', 'p' => $pageNumber]);
    return app_url('index.php?' . http_build_query($params));
};

$levels = ['School', 'Division', 'Regional', 'National'];
$statuses = ['Upcoming', 'Ongoing', 'Completed'];
$selectedQ = trim((string)($_GET['q'] ?? ''));
$selectedSport = $_GET['sport_id'] ?? '';
$selectedLevel = $_GET['level'] ?? '';
$selectedStatus = $_GET['status'] ?? '';

require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72"><?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?><main class="p-4 lg:p-6">
<div class="mb-6 flex flex-wrap items-end justify-between gap-3">
    <form class="flex flex-wrap items-end gap-3" method="get" action="<?= e(app_url('index.php')) ?>">
        <input type="hidden" name="page" value="competition">
        <label class="block">
            <span class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Search</span>
            <input class="form-input" name="q" placeholder="Competition name" value="<?= e($selectedQ) ?>">
        </label>
        <label class="block">
            <span class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Sport</span>
            <select class="form-input" name="sport_id">
                <option value="">All</option>
                <?php foreach ($sports as $sport): ?>
                    <option value="<?= e($sport['id']) ?>" <?= $selectedSport === (string)$sport['id'] ? 'selected' : '' ?>><?= e($sport['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="block">
            <span class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Level</span>
            <select class="form-input" name="level">
                <option value="">All</option>
                <?php foreach ($levels as $level): ?>
                    <option value="<?= e($level) ?>" <?= $selectedLevel === $level ? 'selected' : '' ?>><?= e($level) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="block">
            <span class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Status</span>
            <select class="form-input" name="status">
                <option value="">All</option>
                <?php foreach ($statuses as $status): ?>
                    <option value="<?= e($status) ?>" <?= $selectedStatus === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="btn-muted" type="submit">Filter</button>
        <?php if ($selectedQ !== '' || $selectedSport !== '' || $selectedLevel !== '' || $selectedStatus !== ''): ?>
            <a class="btn-muted" href="<?= e(app_url('index.php?page=competition')) ?>">Reset</a>
        <?php endif; ?>
    </form>
    <button class="btn-primary" type="button" data-modal-open="#competition-add-modal">Add Competition</button>
</div>

<?php if (!$pagedCompetitions): ?>
    <div class="rounded-xl border border-slate-200 bg-white p-10 text-center text-sm text-slate-500 shadow-sm">No competitions match your filters.</div>
<?php endif; ?>

<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
    <?php foreach ($pagedCompetitions as $competitionRow): ?>
        <?php
        $statusClass = match ($competitionRow['status']) {
            'Ongoing' => 'status-active',
            'Completed' => 'status-neutral',
            default => 'status-pending',
        };
        ?>
        <article class="flex flex-col rounded-2xl border border-slate-200 bg-white shadow-sm">
            <header class="border-b border-slate-200 p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Competition #<?= e((string)$competitionRow['id']) ?></p>
                        <h2 class="mt-1 truncate text-lg font-black text-slate-950"><?= e($competitionRow['name']) ?></h2>
                        <p class="mt-1 text-sm text-slate-500"><?= e($competitionRow['sport_name'] ?: 'Any sport') ?> · <?= e($competitionRow['category']) ?></p>
                    </div>
                    <span class="status-pill shrink-0 <?= e($statusClass) ?>"><?= e($competitionRow['status']) ?></span>
                </div>
            </header>
            <div class="flex-1 space-y-2 p-5 text-sm">
                <div class="flex justify-between gap-3"><span class="text-slate-500">Competition Level</span><span class="font-semibold"><?= e($competitionRow['level']) ?></span></div>
                <div class="flex justify-between gap-3"><span class="text-slate-500">Event Type</span><span class="font-semibold"><?= e($competitionRow['event_type'] ?: '—') ?></span></div>
                <div class="flex justify-between gap-3"><span class="text-slate-500">Venue</span><span class="font-semibold"><?= e($competitionRow['venue'] ?: '—') ?></span></div>
                <div class="flex justify-between gap-3"><span class="text-slate-500">Organizer</span><span class="font-semibold"><?= e($competitionRow['organizer'] ?: '—') ?></span></div>
                <div class="flex justify-between gap-3"><span class="text-slate-500">Dates</span><span class="font-semibold"><?= e(($competitionRow['start_date'] ?: '—') . ' to ' . ($competitionRow['end_date'] ?: '—')) ?></span></div>
                <div class="flex justify-between gap-3"><span class="text-slate-500">Registration Deadline</span><span class="font-semibold"><?= e($competitionRow['registration_deadline'] ?: '—') ?></span></div>
                <div class="flex justify-between gap-3"><span class="text-slate-500">Participants</span><span class="font-semibold"><?= e((string)$competitionRow['participant_count']) ?> athlete(s)</span></div>
            </div>
            <footer class="flex flex-wrap gap-2 border-t border-slate-200 p-5">
                <a class="btn-primary flex-1 text-center" href="<?= e(app_url('index.php?page=competition_manage&id=' . $competitionRow['id'])) ?>">Manage</a>
                <button class="btn-muted" type="button" data-modal-open="#competition-edit-modal-<?= e((string)$competitionRow['id']) ?>">Edit</button>
                <form method="post" action="<?= project_url('app/ajax/competition_ajax.php') ?>" data-ajax-form data-confirm="Delete this competition? Participants and results will also be removed.">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= e((string)$competitionRow['id']) ?>">
                    <button class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-bold text-rose-600 hover:bg-rose-50" type="submit">Delete</button>
                </form>
            </footer>
        </article>
    <?php endforeach; ?>
</div>

<?php if ($totalPages > 1): ?>
    <nav class="mt-4 flex items-center justify-center gap-2 text-sm">
        <a class="rounded-lg border border-slate-300 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50 <?= $currentPage <= 1 ? 'pointer-events-none opacity-40' : '' ?>" href="<?= e($pageUrl($currentPage - 1)) ?>">Previous</a>
        <?php for ($page = max(1, $currentPage - 2); $page <= min($totalPages, $currentPage + 2); $page++): ?>
            <a class="rounded-lg border px-3 py-2 font-semibold <?= $page === $currentPage ? 'border-transparent text-white' : 'border-slate-300 text-slate-700 hover:bg-slate-50' ?>" style="<?= $page === $currentPage ? 'background: var(--theme-color);' : '' ?>" href="<?= e($pageUrl($page)) ?>"><?= e((string)$page) ?></a>
        <?php endfor; ?>
        <a class="rounded-lg border border-slate-300 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50 <?= $currentPage >= $totalPages ? 'pointer-events-none opacity-40' : '' ?>" href="<?= e($pageUrl($currentPage + 1)) ?>">Next</a>
    </nav>
<?php endif; ?>

<div id="competition-add-modal" class="fixed inset-0 z-[70] hidden bg-slate-950/60 p-4 backdrop-blur-sm" data-modal>
    <div class="mx-auto flex min-h-full max-w-2xl items-center">
        <section class="w-full overflow-hidden rounded-2xl bg-white shadow-2xl">
            <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-blue-600">Competition Module</p>
                    <h2 class="text-lg font-black text-slate-950">Add Competition</h2>
                </div>
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" type="button" data-modal-close>Close</button>
            </header>
            <form class="grid gap-3 p-5 md:grid-cols-2" method="post" action="<?= project_url('app/ajax/competition_ajax.php') ?>" data-ajax-form data-validate>
                <label class="block md:col-span-2">
                    <span class="text-sm font-semibold text-slate-700">Competition Name</span>
                    <input class="form-input mt-1" name="name" placeholder="e.g. Regional Sports Meet 2026" required>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Sport</span>
                    <select class="form-input mt-1" name="sport_id">
                        <option value="">Any / All sports</option>
                        <?php foreach ($sports as $sport): ?>
                            <option value="<?= e($sport['id']) ?>"><?= e($sport['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Category</span>
                    <select class="form-input mt-1" name="category">
                        <option value="Individual">Individual</option>
                        <option value="Team">Team</option>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Event Type</span>
                    <input class="form-input mt-1" name="event_type" placeholder="e.g. Singles, Relay, 100m">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Competition Level</span>
                    <select class="form-input mt-1" name="level">
                        <?php foreach ($levels as $level): ?>
                            <option value="<?= e($level) ?>"><?= e($level) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Venue</span>
                    <input class="form-input mt-1" name="venue">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Organizer</span>
                    <input class="form-input mt-1" name="organizer">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Start Date</span>
                    <input class="form-input mt-1" type="date" name="start_date">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">End Date</span>
                    <input class="form-input mt-1" type="date" name="end_date">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Registration Deadline</span>
                    <input class="form-input mt-1" type="date" name="registration_deadline">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Competition Status</span>
                    <select class="form-input mt-1" name="status">
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?= e($status) ?>"><?= e($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button class="btn-primary md:col-span-2">Save Competition</button>
            </form>
        </section>
    </div>
</div>

<?php foreach ($competitions as $competitionRow): ?>
    <div id="competition-edit-modal-<?= e((string)$competitionRow['id']) ?>" class="fixed inset-0 z-[70] hidden bg-slate-950/60 p-4 backdrop-blur-sm" data-modal>
        <div class="mx-auto flex min-h-full max-w-2xl items-center">
            <section class="w-full overflow-hidden rounded-2xl bg-white shadow-2xl">
                <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-blue-600">Competition Module</p>
                        <h2 class="text-lg font-black text-slate-950">Edit Competition</h2>
                    </div>
                    <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" type="button" data-modal-close>Close</button>
                </header>
                <form class="grid gap-3 p-5 md:grid-cols-2" method="post" action="<?= project_url('app/ajax/competition_ajax.php') ?>" data-ajax-form data-validate>
                    <input type="hidden" name="id" value="<?= e((string)$competitionRow['id']) ?>">
                    <label class="block md:col-span-2">
                        <span class="text-sm font-semibold text-slate-700">Competition Name</span>
                        <input class="form-input mt-1" name="name" value="<?= e($competitionRow['name']) ?>" required>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Sport</span>
                        <select class="form-input mt-1" name="sport_id">
                            <option value="">Any / All sports</option>
                            <?php foreach ($sports as $sport): ?>
                                <option value="<?= e($sport['id']) ?>" <?= (string)($competitionRow['sport_id'] ?? '') === (string)$sport['id'] ? 'selected' : '' ?>><?= e($sport['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Category</span>
                        <select class="form-input mt-1" name="category">
                            <?php foreach (['Individual', 'Team'] as $category): ?>
                                <option value="<?= e($category) ?>" <?= ($competitionRow['category'] ?? '') === $category ? 'selected' : '' ?>><?= e($category) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Event Type</span>
                        <input class="form-input mt-1" name="event_type" value="<?= e($competitionRow['event_type'] ?? '') ?>">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Competition Level</span>
                        <select class="form-input mt-1" name="level">
                            <?php foreach ($levels as $level): ?>
                                <option value="<?= e($level) ?>" <?= ($competitionRow['level'] ?? '') === $level ? 'selected' : '' ?>><?= e($level) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Venue</span>
                        <input class="form-input mt-1" name="venue" value="<?= e($competitionRow['venue'] ?? '') ?>">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Organizer</span>
                        <input class="form-input mt-1" name="organizer" value="<?= e($competitionRow['organizer'] ?? '') ?>">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Start Date</span>
                        <input class="form-input mt-1" type="date" name="start_date" value="<?= e($competitionRow['start_date'] ?? '') ?>">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">End Date</span>
                        <input class="form-input mt-1" type="date" name="end_date" value="<?= e($competitionRow['end_date'] ?? '') ?>">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Registration Deadline</span>
                        <input class="form-input mt-1" type="date" name="registration_deadline" value="<?= e($competitionRow['registration_deadline'] ?? '') ?>">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Competition Status</span>
                        <select class="form-input mt-1" name="status">
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?= e($status) ?>" <?= ($competitionRow['status'] ?? '') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <button class="btn-primary md:col-span-2">Save Changes</button>
                </form>
            </section>
        </div>
    </div>
<?php endforeach; ?>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
