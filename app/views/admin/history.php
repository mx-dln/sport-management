<?php
require_once __DIR__ . '/../../controllers/AthleteHistoryController.php';
require_once __DIR__ . '/../../controllers/SportController.php';
require_once __DIR__ . '/../../controllers/AthleteController.php';
require_role(['admin', 'sports_coordinator']);
$pageTitle = 'Athletic History';
$history = new AthleteHistoryController($pdo);
$sports = (new SportController($pdo))->all();
$athletes = (new AthleteController($pdo))->all();
$records = $history->all($_GET);

$perPage = 10;
$currentPage = max(1, (int)($_GET['p'] ?? 1));
$totalRecords = count($records);
$totalPages = max(1, (int)ceil($totalRecords / $perPage));
$currentPage = min($currentPage, $totalPages);
$pagedRecords = array_slice($records, ($currentPage - 1) * $perPage, $perPage);
$pageUrl = function (int $pageNumber): string {
    $params = array_merge($_GET, ['page' => 'history', 'p' => $pageNumber]);
    return app_url('index.php?' . http_build_query($params));
};

$years = range((int)date('Y') + 1, 2015);
$selectedQ = trim((string)($_GET['q'] ?? ''));
$selectedYear = $_GET['year'] ?? '';
$selectedSport = $_GET['sport_id'] ?? '';
$selectedLevel = $_GET['level'] ?? '';
$selectedMedal = $_GET['medal'] ?? '';
$selectedResult = trim((string)($_GET['result'] ?? ''));

$medalEmoji = ['Gold' => '🥇', 'Silver' => '🥈', 'Bronze' => '🥉', 'Other' => '🏅', 'None' => ''];

require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72"><?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?><main class="p-4 lg:p-6">
<div class="mb-6 flex flex-wrap items-end justify-between gap-3">
    <div>
        <h2 class="text-2xl font-black text-slate-950">Athletic History Records</h2>
        <p class="mt-1 text-sm text-slate-500"><?= e((string)count($records)) ?> previous competition record(s) across all athletes.</p>
    </div>
    <button class="btn-primary" type="button" data-modal-open="#history-add-modal">Add History</button>
</div>

<form class="mb-6 grid gap-3 rounded-xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-4" method="get" action="<?= e(app_url('index.php')) ?>">
    <input type="hidden" name="page" value="history">
    <input class="form-input" name="q" placeholder="Search athlete or competition" value="<?= e($selectedQ) ?>">
    <select class="form-input" name="year">
        <option value="">All Years</option>
        <?php foreach ($years as $year): ?>
            <option value="<?= e((string)$year) ?>" <?= $selectedYear === (string)$year ? 'selected' : '' ?>><?= e((string)$year) ?></option>
        <?php endforeach; ?>
    </select>
    <select class="form-input" name="sport_id">
        <option value="">All Sports</option>
        <?php foreach ($sports as $sport): ?>
            <option value="<?= e($sport['id']) ?>" <?= $selectedSport === (string)$sport['id'] ? 'selected' : '' ?>><?= e($sport['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <select class="form-input" name="level">
        <option value="">All Levels</option>
        <?php foreach (AthleteHistoryController::LEVELS as $level): ?>
            <option value="<?= e($level) ?>" <?= $selectedLevel === $level ? 'selected' : '' ?>><?= e($level) ?></option>
        <?php endforeach; ?>
    </select>
    <select class="form-input" name="medal">
        <option value="">All Medals</option>
        <?php foreach (AthleteHistoryController::MEDALS as $medal): ?>
            <option value="<?= e($medal) ?>" <?= $selectedMedal === $medal ? 'selected' : '' ?>><?= e($medal) ?></option>
        <?php endforeach; ?>
    </select>
    <input class="form-input" name="result" placeholder="Result (e.g. 1st)" value="<?= e($selectedResult) ?>">
    <button class="btn-muted" type="submit">Filter</button>
    <a class="btn-muted text-center" href="<?= e(app_url('index.php?page=history')) ?>">Reset</a>
</form>

<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="w-full text-sm" data-enhance-table="false">
        <thead class="bg-slate-50">
            <tr>
                <th class="table-th">Athlete</th>
                <th class="table-th">Competition</th>
                <th class="table-th">Level</th>
                <th class="table-th">Sport / Event</th>
                <th class="table-th">Year</th>
                <th class="table-th">Result</th>
                <th class="table-th">Medal</th>
                <th class="table-th">Proof</th>
                <th class="table-th">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pagedRecords as $record): ?>
                <tr>
                    <td class="table-td">
                        <p class="font-semibold"><?= e($record['last_name'] . ', ' . $record['first_name']) ?></p>
                        <p class="text-xs text-slate-500"><?= e($record['student_id']) ?></p>
                    </td>
                    <td class="table-td font-semibold"><?= e($record['competition_name']) ?></td>
                    <td class="table-td"><span class="status-pill status-neutral"><?= e($record['competition_level']) ?></span></td>
                    <td class="table-td"><?= e(($record['sport_name'] ?: '—') . ($record['event_name'] ? ' / ' . $record['event_name'] : '')) ?></td>
                    <td class="table-td"><?= e($record['competition_year'] ?: '—') ?></td>
                    <td class="table-td"><?= e($record['result'] ?: '—') ?></td>
                    <td class="table-td">
                        <?php if ($record['medal'] && $record['medal'] !== 'None'): ?>
                            <span class="status-pill <?= match ($record['medal']) { 'Gold' => 'status-active', 'Silver' => 'status-pending', 'Bronze' => 'status-submitted', default => 'status-neutral' } ?>"><?= e($medalEmoji[$record['medal']]) ?> <?= e($record['medal']) ?></span>
                        <?php else: ?>
                            <span class="text-slate-400">None</span>
                        <?php endif; ?>
                    </td>
                    <td class="table-td">
                        <?php if ($record['proof_file']): ?>
                            <a class="font-semibold text-blue-600 underline" href="<?= e(app_url($record['proof_file'])) ?>" data-attachment-preview data-attachment-url="<?= e(app_url($record['proof_file'])) ?>" data-attachment-name="<?= e($record['proof_name'] ?: 'Proof Document') ?>">View</a>
                        <?php else: ?>
                            <span class="text-slate-400">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="table-td">
                        <div class="flex flex-wrap gap-2">
                            <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50" type="button" data-modal-open="#history-edit-modal-<?= e((string)$record['id']) ?>">Edit</button>
                            <form method="post" action="<?= project_url('app/ajax/athlete_history_ajax.php') ?>" data-ajax-form data-confirm="Delete this history record?">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= e((string)$record['id']) ?>">
                                <button class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-bold text-rose-600 hover:bg-rose-50" type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$pagedRecords): ?>
                <tr>
                    <td class="table-td py-10 text-center text-slate-500" colspan="9">No history records match your filters.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
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

<div id="history-add-modal" class="fixed inset-0 z-[70] hidden bg-slate-950/60 p-4 backdrop-blur-sm" data-modal>
    <div class="mx-auto flex min-h-full max-w-3xl items-center">
        <section class="max-h-[86vh] w-full overflow-hidden rounded-2xl bg-white shadow-2xl">
            <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-blue-600">Athletic History</p>
                    <h2 class="text-lg font-black text-slate-950">Add History</h2>
                </div>
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" type="button" data-modal-close>Close</button>
            </header>
            <form class="grid max-h-[70vh] gap-3 overflow-y-auto p-5 md:grid-cols-2" method="post" enctype="multipart/form-data" action="<?= project_url('app/ajax/athlete_history_ajax.php') ?>" data-ajax-form data-validate>
                <label class="block md:col-span-2">
                    <span class="text-sm font-semibold text-slate-700">Athlete</span>
                    <select class="form-input mt-1" name="athlete_id" required>
                        <option value="">Select athlete</option>
                        <?php foreach ($athletes as $athlete): ?>
                            <option value="<?= e($athlete['id']) ?>"><?= e($athlete['student_id'] . ' — ' . $athlete['last_name'] . ', ' . $athlete['first_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Competition Name</span>
                    <input class="form-input mt-1" name="competition_name" placeholder="e.g. Regional Meet, CAVRAA" required>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Competition Level</span>
                    <select class="form-input mt-1" name="competition_level" required>
                        <option value="">Select level</option>
                        <?php foreach (AthleteHistoryController::LEVELS as $level): ?>
                            <option value="<?= e($level) ?>"><?= e($level) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Sport</span>
                    <select class="form-input mt-1" name="sport_id">
                        <option value="">Select sport</option>
                        <?php foreach ($sports as $sport): ?>
                            <option value="<?= e($sport['id']) ?>"><?= e($sport['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Event Name</span>
                    <input class="form-input mt-1" name="event_name" placeholder="e.g. 100m, Basketball">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Year</span>
                    <input class="form-input mt-1" type="number" name="competition_year" min="1900" max="<?= e((string)((int)date('Y') + 1)) ?>" placeholder="e.g. 2025">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Result</span>
                    <input class="form-input mt-1" name="result" placeholder="e.g. 1st Place, Participant">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Medal</span>
                    <select class="form-input mt-1" name="medal">
                        <?php foreach (AthleteHistoryController::MEDALS as $medal): ?>
                            <option value="<?= e($medal) ?>"><?= e($medal) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Organization Represented</span>
                    <input class="form-input mt-1" name="organization" placeholder="e.g. Isabela, ABC High School">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Location</span>
                    <input class="form-input mt-1" name="location" placeholder="e.g. Tuguegarao">
                </label>
                <label class="block md:col-span-2">
                    <span class="text-sm font-semibold text-slate-700">Description</span>
                    <textarea class="form-input mt-1" name="description" rows="2" placeholder="Optional additional information"></textarea>
                </label>
                <label class="block md:col-span-2">
                    <span class="text-sm font-semibold text-slate-700">Proof / Certificate</span>
                    <input class="form-input mt-1" type="file" name="proof_file" accept=".pdf,.jpg,.jpeg,.png">
                    <p class="mt-1 text-xs text-slate-500">Optional. PDF, JPG, JPEG, or PNG up to 8 MB.</p>
                </label>
                <button class="btn-primary md:col-span-2">Save History</button>
            </form>
        </section>
    </div>
</div>

<?php foreach ($records as $record): ?>
    <div id="history-edit-modal-<?= e((string)$record['id']) ?>" class="fixed inset-0 z-[70] hidden bg-slate-950/60 p-4 backdrop-blur-sm" data-modal>
        <div class="mx-auto flex min-h-full max-w-3xl items-center">
            <section class="max-h-[86vh] w-full overflow-hidden rounded-2xl bg-white shadow-2xl">
                <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-blue-600">Athletic History</p>
                        <h2 class="text-lg font-black text-slate-950">Edit History</h2>
                    </div>
                    <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" type="button" data-modal-close>Close</button>
                </header>
                <form class="grid max-h-[70vh] gap-3 overflow-y-auto p-5 md:grid-cols-2" method="post" enctype="multipart/form-data" action="<?= project_url('app/ajax/athlete_history_ajax.php') ?>" data-ajax-form data-validate>
                    <input type="hidden" name="id" value="<?= e((string)$record['id']) ?>">
                    <input type="hidden" name="athlete_id" value="<?= e((string)$record['athlete_id']) ?>">
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Competition Name</span>
                        <input class="form-input mt-1" name="competition_name" value="<?= e($record['competition_name']) ?>" required>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Competition Level</span>
                        <select class="form-input mt-1" name="competition_level" required>
                            <option value="">Select level</option>
                            <?php foreach (AthleteHistoryController::LEVELS as $level): ?>
                                <option value="<?= e($level) ?>" <?= ($record['competition_level'] ?? '') === $level ? 'selected' : '' ?>><?= e($level) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Sport</span>
                        <select class="form-input mt-1" name="sport_id">
                            <option value="">Select sport</option>
                            <?php foreach ($sports as $sport): ?>
                                <option value="<?= e($sport['id']) ?>" <?= (string)($record['sport_id'] ?? '') === (string)$sport['id'] ? 'selected' : '' ?>><?= e($sport['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Event Name</span>
                        <input class="form-input mt-1" name="event_name" value="<?= e($record['event_name'] ?? '') ?>">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Year</span>
                        <input class="form-input mt-1" type="number" name="competition_year" min="1900" max="<?= e((string)((int)date('Y') + 1)) ?>" value="<?= e($record['competition_year'] ?? '') ?>">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Result</span>
                        <input class="form-input mt-1" name="result" value="<?= e($record['result'] ?? '') ?>">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Medal</span>
                        <select class="form-input mt-1" name="medal">
                            <?php foreach (AthleteHistoryController::MEDALS as $medal): ?>
                                <option value="<?= e($medal) ?>" <?= ($record['medal'] ?? 'None') === $medal ? 'selected' : '' ?>><?= e($medal) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Organization Represented</span>
                        <input class="form-input mt-1" name="organization" value="<?= e($record['organization'] ?? '') ?>">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Location</span>
                        <input class="form-input mt-1" name="location" value="<?= e($record['location'] ?? '') ?>">
                    </label>
                    <label class="block md:col-span-2">
                        <span class="text-sm font-semibold text-slate-700">Description</span>
                        <textarea class="form-input mt-1" name="description" rows="2"><?= e($record['description'] ?? '') ?></textarea>
                    </label>
                    <label class="block md:col-span-2">
                        <span class="text-sm font-semibold text-slate-700">Proof / Certificate</span>
                        <?php if ($record['proof_file']): ?>
                            <p class="mt-1 text-xs text-slate-500">Current: <a class="font-semibold text-blue-600 underline" href="<?= e(app_url($record['proof_file'])) ?>" data-attachment-preview data-attachment-url="<?= e(app_url($record['proof_file'])) ?>" data-attachment-name="<?= e($record['proof_name'] ?: 'Proof Document') ?>"><?= e($record['proof_name']) ?></a> — upload a new file to replace it.</p>
                        <?php endif; ?>
                        <input class="form-input mt-1" type="file" name="proof_file" accept=".pdf,.jpg,.jpeg,.png">
                    </label>
                    <button class="btn-primary md:col-span-2">Save Changes</button>
                </form>
            </section>
        </div>
    </div>
<?php endforeach; ?>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
