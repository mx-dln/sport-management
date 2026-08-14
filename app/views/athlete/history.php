<?php
require_once __DIR__ . '/../../controllers/AthleteHistoryController.php';
require_once __DIR__ . '/../../controllers/SportController.php';
require_role(['athlete']);
$pageTitle = 'My Athletic History';
$history = new AthleteHistoryController($pdo);
$sports = (new SportController($pdo))->all();

$stmt = $pdo->prepare('SELECT id, student_id, first_name, last_name FROM athletes WHERE user_id = ? LIMIT 1');
$stmt->execute([(int)current_user()['id']]);
$athlete = $stmt->fetch();
$records = $athlete ? $history->forAthlete((int)$athlete['id']) : [];
$stats = $athlete ? $history->stats((int)$athlete['id']) : [];

$statCards = [
    ['label' => 'Competitions', 'value' => $stats['total'], 'emoji' => '🏆'],
    ['label' => 'Gold', 'value' => $stats['gold'], 'emoji' => '🥇'],
    ['label' => 'Silver', 'value' => $stats['silver'], 'emoji' => '🥈'],
    ['label' => 'Bronze', 'value' => $stats['bronze'], 'emoji' => '🥉'],
    ['label' => 'Total Medals', 'value' => $stats['medals'], 'emoji' => '🏅'],
    ['label' => '1st Place', 'value' => $stats['first_place'], 'emoji' => '🥇'],
    ['label' => '2nd Place', 'value' => $stats['second_place'], 'emoji' => '🥈'],
    ['label' => '3rd Place', 'value' => $stats['third_place'], 'emoji' => '🥉'],
];

$medalEmoji = ['Gold' => '🥇', 'Silver' => '🥈', 'Bronze' => '🥉', 'Other' => '🏅', 'None' => ''];
$medalClass = ['Gold' => 'status-active', 'Silver' => 'status-pending', 'Bronze' => 'status-submitted', 'Other' => 'status-neutral', 'None' => 'status-neutral'];

require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72"><?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?><main class="p-4 lg:p-6">
<?php if (!$athlete): ?>
    <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm font-semibold text-amber-800">No athlete profile is linked to your account. Contact the administrator.</div>
<?php else: ?>
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-2xl font-black text-slate-950">Athletic History &amp; Achievements</h2>
        <p class="mt-1 text-sm text-slate-500">Record your previous competitions and achievements — <?= e($athlete['first_name'] . ' ' . $athlete['last_name']) ?> (<?= e($athlete['student_id']) ?>)</p>
    </div>
    <button class="btn-primary" type="button" data-modal-open="#history-add-modal">Add History</button>
</div>

<section class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4 xl:grid-cols-8">
    <?php foreach ($statCards as $card): ?>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm">
            <p class="text-2xl"><?= e($card['emoji']) ?></p>
            <p class="mt-1 text-2xl font-black text-slate-950"><?= e((string)$card['value']) ?></p>
            <p class="text-xs font-semibold text-slate-500"><?= e($card['label']) ?></p>
        </div>
    <?php endforeach; ?>
</section>

<?php if (!$records): ?>
    <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center text-sm text-slate-500 shadow-sm">
        No athletic history recorded yet. Click <b>Add History</b> to record your first competition.
    </div>
<?php endif; ?>

<div class="space-y-4">
    <?php foreach ($records as $record): ?>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400"><?= e((string)($record['competition_year'] ?? 'Year not set')) ?></p>
                    <h3 class="mt-1 text-lg font-black text-slate-950"><?= $record['medal'] && $record['medal'] !== 'None' ? e($medalEmoji[$record['medal']]) . ' ' : '' ?><?= e($record['competition_name']) ?></h3>
                    <p class="mt-1 text-sm text-slate-600"><?= e($record['sport_name'] ?: 'Sport not specified') ?><?= $record['event_name'] ? ' — ' . e($record['event_name']) : '' ?> · <span class="status-pill status-neutral"><?= e($record['competition_level']) ?></span></p>
                </div>
                <div class="flex shrink-0 gap-2">
                    <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50" type="button" data-modal-open="#history-edit-modal-<?= e((string)$record['id']) ?>">Edit</button>
                    <form method="post" action="<?= project_url('app/ajax/athlete_history_ajax.php') ?>" data-ajax-form data-confirm="Delete this history record?">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= e((string)$record['id']) ?>">
                        <button class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-bold text-rose-600 hover:bg-rose-50" type="submit">Delete</button>
                    </form>
                </div>
            </div>
            <div class="mt-3 grid gap-2 rounded-xl bg-slate-50 p-4 text-sm sm:grid-cols-2">
                <?php if ($record['result']): ?>
                    <p><span class="font-semibold text-slate-500">Result:</span> <span class="font-bold"><?= e($record['result']) ?></span><?= $record['medal'] !== 'None' ? ' · <span class="status-pill ' . e($medalClass[$record['medal']]) . '">' . e($record['medal']) . '</span>' : '' ?></p>
                <?php else: ?>
                    <p><span class="text-slate-500">Result:</span> <span class="font-semibold">Participant</span></p>
                <?php endif; ?>
                <?php if ($record['organization']): ?><p><span class="font-semibold text-slate-500">Represented:</span> <?= e($record['organization']) ?></p><?php endif; ?>
                <?php if ($record['location']): ?><p><span class="font-semibold text-slate-500">Location:</span> <?= e($record['location']) ?></p><?php endif; ?>
                <?php if ($record['description']): ?><p class="sm:col-span-2"><span class="font-semibold text-slate-500">Details:</span> <?= e($record['description']) ?></p><?php endif; ?>
                <?php if ($record['proof_file']): ?>
                    <p class="sm:col-span-2">
                        <span class="font-semibold text-slate-500">Proof:</span>
                        <a class="font-semibold text-blue-600 underline" href="<?= e(app_url($record['proof_file'])) ?>" data-attachment-preview data-attachment-url="<?= e(app_url($record['proof_file'])) ?>" data-attachment-name="<?= e($record['proof_name'] ?: 'Proof Document') ?>"><?= e($record['proof_name'] ?: 'View document') ?></a>
                    </p>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</div>

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
                <input type="hidden" name="athlete_id" value="<?= e((string)$athlete['id']) ?>">
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
                    <input class="form-input mt-1" name="result" placeholder="e.g. 1st Place, 2nd Place, Participant">
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
<?php endif; ?>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
