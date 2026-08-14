<?php
require_once __DIR__ . '/../../controllers/MedicalController.php';
require_once __DIR__ . '/../../controllers/AthleteController.php';
require_role(['admin', 'sports_coordinator']);
$pageTitle = 'Medical Records';
$medical = new MedicalController($pdo);
$records = $medical->records($_GET);
$athletes = (new AthleteController($pdo))->all();

$perPage = 10;
$currentPage = max(1, (int)($_GET['p'] ?? 1));
$totalRecords = count($records);
$totalPages = max(1, (int)ceil($totalRecords / $perPage));
$currentPage = min($currentPage, $totalPages);
$pagedRecords = array_slice($records, ($currentPage - 1) * $perPage, $perPage);
$pageUrl = function (int $pageNumber): string {
    $params = array_merge($_GET, ['page' => 'medical', 'p' => $pageNumber]);
    return app_url('index.php?' . http_build_query($params));
};

$clearanceOptions = ['Fit to Play', 'Not Fit to Play'];
$bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
$selectedClearance = $_GET['clearance_status'] ?? '';
$selectedQ = trim((string)($_GET['q'] ?? ''));

function medical_field(?string $value): string
{
    return $value !== null && $value !== '' ? e($value) : '<span class="text-slate-400">—</span>';
}

require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72"><?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?><main class="p-4 lg:p-6">
<div class="mb-6 flex flex-wrap items-end justify-between gap-3">
    <form class="flex flex-wrap items-end gap-3" method="get" action="<?= e(app_url('index.php')) ?>">
        <input type="hidden" name="page" value="medical">
        <label class="block">
            <span class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Search Athlete</span>
            <input class="form-input" name="q" placeholder="Student ID or name" value="<?= e($selectedQ) ?>">
        </label>
        <label class="block">
            <span class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Clearance</span>
            <select class="form-input" name="clearance_status">
                <option value="">All</option>
                <?php foreach ($clearanceOptions as $option): ?>
                    <option value="<?= e($option) ?>" <?= $selectedClearance === $option ? 'selected' : '' ?>><?= e($option) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="btn-muted" type="submit">Filter</button>
        <?php if ($selectedQ !== '' || $selectedClearance !== ''): ?>
            <a class="btn-muted" href="<?= e(app_url('index.php?page=medical')) ?>">Reset</a>
        <?php endif; ?>
    </form>
    <button class="btn-primary" type="button" data-modal-open="#medical-add-modal">Add Medical Record</button>
</div>

<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="w-full text-sm" data-enhance-table="false">
        <thead class="bg-slate-50">
            <tr>
                <th class="table-th">Athlete ID</th>
                <th class="table-th">Athlete</th>
                <th class="table-th">Sport / Team</th>
                <th class="table-th">Exam Date</th>
                <th class="table-th">Clearance</th>
                <th class="table-th">Physician</th>
                <th class="table-th">Next Check-up</th>
                <th class="table-th">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pagedRecords as $record): ?>
                <tr>
                    <td class="table-td font-semibold text-slate-500"><?= e($record['student_id']) ?></td>
                    <td class="table-td font-semibold"><?= e(trim(($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? ''))) ?></td>
                    <td class="table-td"><?= e($record['sport_name'] ?: '—') ?><?= $record['team_name'] ? ' / ' . e($record['team_name']) : '' ?></td>
                    <td class="table-td"><?= medical_field($record['exam_date']) ?></td>
                    <td class="table-td">
                        <?php if ($record['clearance_status']): ?>
                            <span class="status-pill <?= $record['clearance_status'] === 'Fit to Play' ? 'status-active' : 'status-rejected' ?>"><?= e($record['clearance_status']) ?></span>
                        <?php else: ?>
                            <span class="text-slate-400">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="table-td"><?= medical_field($record['physician_name']) ?></td>
                    <td class="table-td"><?= medical_field($record['next_checkup_date']) ?></td>
                    <td class="table-td">
                        <div class="flex flex-wrap gap-2">
                            <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50" type="button" data-modal-open="#medical-view-modal-<?= e((string)$record['id']) ?>">View</button>
                            <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50" type="button" data-modal-open="#medical-edit-modal-<?= e((string)$record['id']) ?>">Edit</button>
                            <form method="post" action="<?= project_url('app/ajax/medical_ajax.php') ?>" data-ajax-form data-confirm="Delete this medical record?">
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
                    <td class="table-td py-10 text-center text-slate-500" colspan="8">No medical records match your filters.</td>
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

<div id="medical-add-modal" class="fixed inset-0 z-[70] hidden bg-slate-950/60 p-4 backdrop-blur-sm" data-modal>
    <div class="mx-auto flex min-h-full max-w-3xl items-center">
        <section class="max-h-[86vh] w-full overflow-hidden rounded-2xl bg-white shadow-2xl">
            <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-blue-600">Medical Module</p>
                    <h2 class="text-lg font-black text-slate-950">Add Medical Record</h2>
                </div>
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" type="button" data-modal-close>Close</button>
            </header>
            <form class="grid max-h-[70vh] gap-3 overflow-y-auto p-5 md:grid-cols-2" method="post" enctype="multipart/form-data" action="<?= project_url('app/ajax/medical_ajax.php') ?>" data-ajax-form data-validate>
                <label class="block md:col-span-2">
                    <span class="text-sm font-semibold text-slate-700">Athlete</span>
                    <select class="form-input mt-1" name="athlete_id" required data-athlete-basics="<?= e(project_url('app/ajax/medical_ajax.php')) ?>">
                        <option value="">Select athlete</option>
                        <?php foreach ($athletes as $athlete): ?>
                            <option value="<?= e($athlete['id']) ?>"><?= e($athlete['student_id'] . ' — ' . $athlete['last_name'] . ', ' . $athlete['first_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Date of Medical Examination</span>
                    <input class="form-input mt-1" type="date" name="exam_date">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Physical Fitness Status</span>
                    <input class="form-input mt-1" name="fitness_status" placeholder="e.g. Excellent, Good">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Height</span>
                    <input class="form-input mt-1" name="height" placeholder="e.g. 175 cm">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Weight</span>
                    <input class="form-input mt-1" name="weight" placeholder="e.g. 68 kg">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Blood Type</span>
                    <select class="form-input mt-1" name="blood_type">
                        <option value="">Select</option>
                        <?php foreach ($bloodTypes as $type): ?>
                            <option value="<?= e($type) ?>"><?= e($type) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Blood Pressure</span>
                    <input class="form-input mt-1" name="blood_pressure" placeholder="e.g. 120/80">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Heart Rate</span>
                    <input class="form-input mt-1" name="heart_rate" placeholder="e.g. 72 bpm">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Medical Clearance Status</span>
                    <select class="form-input mt-1" name="clearance_status">
                        <option value="">Select</option>
                        <?php foreach ($clearanceOptions as $option): ?>
                            <option value="<?= e($option) ?>"><?= e($option) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Next Medical Check-up Date</span>
                    <input class="form-input mt-1" type="date" name="next_checkup_date">
                </label>
                <label class="block md:col-span-2">
                    <span class="text-sm font-semibold text-slate-700">Allergies</span>
                    <textarea class="form-input mt-1" name="allergies" rows="2" placeholder="List known allergies, or none"></textarea>
                </label>
                <label class="block md:col-span-2">
                    <span class="text-sm font-semibold text-slate-700">Existing Medical Conditions</span>
                    <textarea class="form-input mt-1" name="medical_conditions" rows="2"></textarea>
                </label>
                <label class="block md:col-span-2">
                    <span class="text-sm font-semibold text-slate-700">Current Medications</span>
                    <textarea class="form-input mt-1" name="medications" rows="2"></textarea>
                </label>
                <label class="block md:col-span-2">
                    <span class="text-sm font-semibold text-slate-700">Injury History</span>
                    <textarea class="form-input mt-1" name="injury_history" rows="2"></textarea>
                </label>
                <label class="block md:col-span-2">
                    <span class="text-sm font-semibold text-slate-700">Recent Injury (if any)</span>
                    <textarea class="form-input mt-1" name="recent_injury" rows="2"></textarea>
                </label>
                <label class="block md:col-span-2">
                    <span class="text-sm font-semibold text-slate-700">Medical Certificate</span>
                    <input class="form-input mt-1" type="file" name="certificate_file" accept=".pdf,.jpg,.jpeg,.png">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Physician / Medical Officer Name</span>
                    <input class="form-input mt-1" name="physician_name">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Physician Remarks</span>
                    <input class="form-input mt-1" name="physician_remarks">
                </label>
                <button class="btn-primary md:col-span-2">Save Medical Record</button>
            </form>
        </section>
    </div>
</div>

<?php foreach ($records as $record): ?>
    <div id="medical-view-modal-<?= e((string)$record['id']) ?>" class="fixed inset-0 z-[70] hidden bg-slate-950/60 p-4 backdrop-blur-sm" data-modal>
        <div class="mx-auto flex min-h-full max-w-3xl items-center">
            <section class="max-h-[86vh] w-full overflow-hidden rounded-2xl bg-white shadow-2xl">
                <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-blue-600">Medical Record #<?= e((string)$record['id']) ?></p>
                        <h2 class="text-lg font-black text-slate-950"><?= e(trim(($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? ''))) ?></h2>
                    </div>
                    <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" type="button" data-modal-close>Close</button>
                </header>
                <div class="grid max-h-[70vh] gap-4 overflow-y-auto p-5 md:grid-cols-2">
                    <section class="rounded-xl border border-slate-200 p-4">
                        <p class="mb-2 text-xs font-black uppercase tracking-wide text-slate-400">Athlete Information</p>
                        <dl class="space-y-1 text-sm">
                            <div class="flex justify-between gap-3"><dt class="text-slate-500">Athlete ID</dt><dd class="font-semibold"><?= e($record['student_id']) ?></dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-slate-500">Sport / Team</dt><dd class="font-semibold"><?= e(($record['sport_name'] ?: '—') . ($record['team_name'] ? ' / ' . $record['team_name'] : '')) ?></dd></div>
                        </dl>
                    </section>
                    <section class="rounded-xl border border-slate-200 p-4">
                        <p class="mb-2 text-xs font-black uppercase tracking-wide text-slate-400">Medical Information</p>
                        <dl class="space-y-1 text-sm">
                            <div class="flex justify-between gap-3"><dt class="text-slate-500">Medical Record ID</dt><dd class="font-semibold">#<?= e((string)$record['id']) ?></dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-slate-500">Exam Date</dt><dd class="font-semibold"><?= medical_field($record['exam_date']) ?></dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-slate-500">Height</dt><dd class="font-semibold"><?= medical_field($record['height']) ?></dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-slate-500">Weight</dt><dd class="font-semibold"><?= medical_field($record['weight']) ?></dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-slate-500">Blood Type</dt><dd class="font-semibold"><?= medical_field($record['blood_type']) ?></dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-slate-500">Blood Pressure</dt><dd class="font-semibold"><?= medical_field($record['blood_pressure']) ?></dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-slate-500">Heart Rate</dt><dd class="font-semibold"><?= medical_field($record['heart_rate']) ?></dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-slate-500">Physical Fitness</dt><dd class="font-semibold"><?= medical_field($record['fitness_status']) ?></dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-slate-500">Clearance</dt><dd class="font-semibold"><?= medical_field($record['clearance_status']) ?></dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-slate-500">Next Check-up</dt><dd class="font-semibold"><?= medical_field($record['next_checkup_date']) ?></dd></div>
                        </dl>
                    </section>
                    <section class="rounded-xl border border-slate-200 p-4 md:col-span-2">
                        <p class="mb-2 text-xs font-black uppercase tracking-wide text-slate-400">Health Notes</p>
                        <dl class="grid gap-2 text-sm md:grid-cols-2">
                            <div><dt class="text-slate-500">Allergies</dt><dd class="font-semibold"><?= medical_field($record['allergies']) ?></dd></div>
                            <div><dt class="text-slate-500">Existing Medical Conditions</dt><dd class="font-semibold"><?= medical_field($record['medical_conditions']) ?></dd></div>
                            <div><dt class="text-slate-500">Current Medications</dt><dd class="font-semibold"><?= medical_field($record['medications']) ?></dd></div>
                            <div><dt class="text-slate-500">Injury History</dt><dd class="font-semibold"><?= medical_field($record['injury_history']) ?></dd></div>
                            <div><dt class="text-slate-500">Recent Injury</dt><dd class="font-semibold"><?= medical_field($record['recent_injury']) ?></dd></div>
                        </dl>
                    </section>
                    <section class="rounded-xl border border-slate-200 p-4 md:col-span-2">
                        <p class="mb-2 text-xs font-black uppercase tracking-wide text-slate-400">Physician</p>
                        <dl class="grid gap-2 text-sm md:grid-cols-2">
                            <div><dt class="text-slate-500">Physician / Medical Officer</dt><dd class="font-semibold"><?= medical_field($record['physician_name']) ?></dd></div>
                            <div><dt class="text-slate-500">Remarks</dt><dd class="font-semibold"><?= medical_field($record['physician_remarks']) ?></dd></div>
                            <div><dt class="text-slate-500">Medical Certificate</dt>
                                <dd class="font-semibold">
                                    <?php if ($record['certificate_path']): ?>
                                        <a class="font-semibold text-blue-600 underline" href="<?= e(app_url($record['certificate_path'])) ?>" data-attachment-preview data-attachment-url="<?= e(app_url($record['certificate_path'])) ?>" data-attachment-name="<?= e($record['certificate_name'] ?: 'Medical Certificate') ?>"><?= e($record['certificate_name']) ?></a>
                                    <?php else: ?>
                                        <span class="text-slate-400">—</span>
                                    <?php endif; ?>
                                </dd>
                            </div>
                            <div><dt class="text-slate-500">Recorded At</dt><dd class="font-semibold"><?= medical_field($record['created_at']) ?></dd></div>
                        </dl>
                    </section>
                </div>
            </section>
        </div>
    </div>

    <div id="medical-edit-modal-<?= e((string)$record['id']) ?>" class="fixed inset-0 z-[70] hidden bg-slate-950/60 p-4 backdrop-blur-sm" data-modal>
        <div class="mx-auto flex min-h-full max-w-3xl items-center">
            <section class="max-h-[86vh] w-full overflow-hidden rounded-2xl bg-white shadow-2xl">
                <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-blue-600">Medical Module</p>
                        <h2 class="text-lg font-black text-slate-950">Edit Medical Record</h2>
                    </div>
                    <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" type="button" data-modal-close>Close</button>
                </header>
                <form class="grid max-h-[70vh] gap-3 overflow-y-auto p-5 md:grid-cols-2" method="post" enctype="multipart/form-data" action="<?= project_url('app/ajax/medical_ajax.php') ?>" data-ajax-form data-validate>
                    <input type="hidden" name="id" value="<?= e((string)$record['id']) ?>">
                    <input type="hidden" name="athlete_id" value="<?= e((string)$record['athlete_id']) ?>">
                    <p class="text-sm font-bold text-slate-700 md:col-span-2"><?= e($record['student_id'] . ' — ' . $record['first_name'] . ' ' . $record['last_name']) ?></p>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Date of Medical Examination</span>
                        <input class="form-input mt-1" type="date" name="exam_date" value="<?= e($record['exam_date'] ?? '') ?>">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Physical Fitness Status</span>
                        <input class="form-input mt-1" name="fitness_status" value="<?= e($record['fitness_status'] ?? '') ?>" placeholder="e.g. Excellent, Good">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Height</span>
                        <input class="form-input mt-1" name="height" value="<?= e($record['height'] ?? '') ?>">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Weight</span>
                        <input class="form-input mt-1" name="weight" value="<?= e($record['weight'] ?? '') ?>">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Blood Type</span>
                        <select class="form-input mt-1" name="blood_type">
                            <option value="">Select</option>
                            <?php foreach ($bloodTypes as $type): ?>
                                <option value="<?= e($type) ?>" <?= ($record['blood_type'] ?? '') === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Blood Pressure</span>
                        <input class="form-input mt-1" name="blood_pressure" value="<?= e($record['blood_pressure'] ?? '') ?>">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Heart Rate</span>
                        <input class="form-input mt-1" name="heart_rate" value="<?= e($record['heart_rate'] ?? '') ?>">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Medical Clearance Status</span>
                        <select class="form-input mt-1" name="clearance_status">
                            <option value="">Select</option>
                            <?php foreach ($clearanceOptions as $option): ?>
                                <option value="<?= e($option) ?>" <?= ($record['clearance_status'] ?? '') === $option ? 'selected' : '' ?>><?= e($option) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Next Medical Check-up Date</span>
                        <input class="form-input mt-1" type="date" name="next_checkup_date" value="<?= e($record['next_checkup_date'] ?? '') ?>">
                    </label>
                    <label class="block md:col-span-2">
                        <span class="text-sm font-semibold text-slate-700">Allergies</span>
                        <textarea class="form-input mt-1" name="allergies" rows="2"><?= e($record['allergies'] ?? '') ?></textarea>
                    </label>
                    <label class="block md:col-span-2">
                        <span class="text-sm font-semibold text-slate-700">Existing Medical Conditions</span>
                        <textarea class="form-input mt-1" name="medical_conditions" rows="2"><?= e($record['medical_conditions'] ?? '') ?></textarea>
                    </label>
                    <label class="block md:col-span-2">
                        <span class="text-sm font-semibold text-slate-700">Current Medications</span>
                        <textarea class="form-input mt-1" name="medications" rows="2"><?= e($record['medications'] ?? '') ?></textarea>
                    </label>
                    <label class="block md:col-span-2">
                        <span class="text-sm font-semibold text-slate-700">Injury History</span>
                        <textarea class="form-input mt-1" name="injury_history" rows="2"><?= e($record['injury_history'] ?? '') ?></textarea>
                    </label>
                    <label class="block md:col-span-2">
                        <span class="text-sm font-semibold text-slate-700">Recent Injury (if any)</span>
                        <textarea class="form-input mt-1" name="recent_injury" rows="2"><?= e($record['recent_injury'] ?? '') ?></textarea>
                    </label>
                    <label class="block md:col-span-2">
                        <span class="text-sm font-semibold text-slate-700">Medical Certificate</span>
                        <?php if ($record['certificate_path']): ?>
                            <p class="mt-1 text-xs text-slate-500">Current: <a class="font-semibold text-blue-600 underline" href="<?= e(app_url($record['certificate_path'])) ?>" data-attachment-preview data-attachment-url="<?= e(app_url($record['certificate_path'])) ?>" data-attachment-name="<?= e($record['certificate_name'] ?: 'Medical Certificate') ?>"><?= e($record['certificate_name']) ?></a> — upload a new file to replace it.</p>
                        <?php endif; ?>
                        <input class="form-input mt-1" type="file" name="certificate_file" accept=".pdf,.jpg,.jpeg,.png">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Physician / Medical Officer Name</span>
                        <input class="form-input mt-1" name="physician_name" value="<?= e($record['physician_name'] ?? '') ?>">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Physician Remarks</span>
                        <input class="form-input mt-1" name="physician_remarks" value="<?= e($record['physician_remarks'] ?? '') ?>">
                    </label>
                    <button class="btn-primary md:col-span-2">Save Changes</button>
                </form>
            </section>
        </div>
    </div>
<?php endforeach; ?>

<script>
document.querySelectorAll('[data-athlete-basics]').forEach((select) => {
    select.addEventListener('change', () => {
        const form = select.closest('form');
        if (!select.value) return;
        const data = new FormData();
        data.append('action', 'basics');
        data.append('athlete_id', select.value);
        fetch(select.dataset.athleteBasics, {
            method: 'POST',
            body: data,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((response) => response.json())
            .then((result) => {
                if (!result.ok || !result.athlete) return;
                const athlete = result.athlete;
                const setIfEmpty = (name, value) => {
                    const field = form.querySelector(`[name="${name}"]`);
                    if (field && !field.value) field.value = value ?? '';
                };
                setIfEmpty('height', athlete.height);
                setIfEmpty('weight', athlete.weight);
                setIfEmpty('blood_type', athlete.blood_type);
                setIfEmpty('medical_conditions', athlete.medical_condition);
            });
    });
});
</script>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
