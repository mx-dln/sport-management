<?php
require_once __DIR__ . '/../../controllers/AthleteController.php';
require_once __DIR__ . '/../../controllers/ScheduleController.php';
require_once __DIR__ . '/../../controllers/SmsController.php';
require_once __DIR__ . '/../../controllers/ReportController.php';
require_once __DIR__ . '/../../controllers/AthleteHistoryController.php';
require_role(['admin', 'sports_coordinator']);

$pageTitle = 'Reports Center';
$reportController = new ReportController($pdo);
$athletes = (new AthleteController($pdo))->all($_GET);
$schedules = (new ScheduleController($pdo))->all($_GET);
$missing = $reportController->missingRequirements();
$sms = (new SmsController($pdo))->logs();
$historyController = new AthleteHistoryController($pdo);
$historyRecords = $historyController->all();
$achievements = $historyController->athleteAchievements();
$medalsBySport = $historyController->medalsBySport();
$medalsByYear = $historyController->medalsByYear();
$today = date('F j, Y');
$reportCards = [
    ['title' => 'Athlete Report', 'count' => count($athletes), 'hint' => 'Master list of registered athletes', 'target' => 'athlete-report'],
    ['title' => 'Schedule Report', 'count' => count($schedules), 'hint' => 'Training sessions and venues', 'target' => 'schedule-report'],
    ['title' => 'Missing Documents', 'count' => count($missing), 'hint' => 'Required files still incomplete', 'target' => 'missing-report'],
    ['title' => 'Athletic History', 'count' => count($historyRecords), 'hint' => 'Achievements and medal counts', 'target' => 'history-report'],
    ['title' => 'SMS Logs', 'count' => count($sms), 'hint' => 'Message delivery records', 'target' => 'sms-report'],
];
require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72">
<?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?>
<main class="p-4 lg:p-6">

<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-col gap-4 p-6 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wide text-blue-600"><?= e($today) ?></p>
            <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-950">Reports Center</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Review printable summaries for athletes, schedules, requirements, and SMS activity.</p>
        </div>
        <button class="btn-primary no-print" data-print>Print Current Reports</button>
    </div>
</section>

<section class="no-print mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
    <?php foreach ($reportCards as $card): ?>
        <a class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md" href="#<?= e($card['target']) ?>">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-slate-500"><?= e($card['title']) ?></p>
                    <p class="mt-2 text-4xl font-black tracking-tight text-slate-950"><?= e((string)$card['count']) ?></p>
                </div>
                <span class="rounded-xl bg-blue-50 px-3 py-2 text-xs font-bold text-blue-600">Open</span>
            </div>
            <p class="mt-2 text-sm text-slate-500"><?= e($card['hint']) ?></p>
        </a>
    <?php endforeach; ?>
</section>

<form class="no-print mt-6 grid gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-[1fr_1fr_auto]" method="get">
    <input type="hidden" name="page" value="reports">
    <input class="form-input" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Search athlete, team, sport...">
    <select class="form-input" name="athlete_status">
        <option value="">Any status</option>
        <?php foreach (['Active', 'Inactive'] as $status): ?>
            <option value="<?= e($status) ?>" <?= ($_GET['athlete_status'] ?? '') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn-primary">Apply Filters</button>
</form>

<section class="print-card mt-6 space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm">
        <h1 class="text-2xl font-black"><?= e(school_name()) ?></h1>
        <p class="mt-1 text-sm text-slate-500"><?= e(app_setting('app_name')) ?> - Administrative Reports</p>
        <p class="mt-1 text-xs text-slate-400">Generated <?= e($today) ?></p>
    </div>

    <article id="athlete-report" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-5">
            <div>
                <h2 class="text-lg font-bold text-slate-950">Athlete Master List</h2>
                <p class="text-sm text-slate-500">Registered athletes and their current sports/team assignments.</p>
            </div>
            <div class="no-print flex items-center gap-2">
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50" type="button" data-export-table="#athlete-report-table" data-filename="athlete-report.csv">Export CSV</button>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600"><?= e((string)count($athletes)) ?> records</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table id="athlete-report-table" class="w-full text-sm" data-enhance-table="true">
                <thead class="bg-slate-50"><tr><th class="table-th">Student ID</th><th class="table-th">Name</th><th class="table-th">Sport</th><th class="table-th">Team</th><th class="table-th">Status</th></tr></thead>
                <tbody>
                    <?php foreach ($athletes as $a): ?>
                        <tr><td class="table-td"><?= e($a['student_id']) ?></td><td class="table-td font-semibold"><?= e($a['last_name'] . ', ' . $a['first_name']) ?></td><td class="table-td"><?= e($a['sport_name'] ?? '') ?></td><td class="table-td"><?= e($a['team_name'] ?? '') ?></td><td class="table-td"><?= e($a['athlete_status'] ?? '') ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>

    <article id="schedule-report" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-5">
            <div>
                <h2 class="text-lg font-bold text-slate-950">Training Schedule Report</h2>
                <p class="text-sm text-slate-500">Training calendar, teams, venues, and current schedule status.</p>
            </div>
            <div class="no-print flex items-center gap-2">
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50" type="button" data-export-table="#schedule-report-table" data-filename="schedule-report.csv">Export CSV</button>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600"><?= e((string)count($schedules)) ?> schedules</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table id="schedule-report-table" class="w-full text-sm" data-enhance-table="true">
                <thead class="bg-slate-50"><tr><th class="table-th">Date</th><th class="table-th">Time</th><th class="table-th">Team</th><th class="table-th">Sport</th><th class="table-th">Venue</th><th class="table-th">Status</th></tr></thead>
                <tbody>
                    <?php foreach ($schedules as $s): ?>
                        <tr><td class="table-td"><?= e($s['training_date']) ?></td><td class="table-td"><?= e(format_time_range($s['start_time'] ?? '', $s['end_time'] ?? '')) ?></td><td class="table-td font-semibold"><?= e($s['team_name']) ?></td><td class="table-td"><?= e($s['sport_name']) ?></td><td class="table-td"><?= e($s['venue']) ?></td><td class="table-td"><?= e($s['status']) ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>

    <article id="missing-report" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-5">
            <div>
                <h2 class="text-lg font-bold text-slate-950">Missing Requirements</h2>
                <p class="text-sm text-slate-500">Required documents that have not been submitted yet.</p>
            </div>
            <div class="no-print flex items-center gap-2">
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50" type="button" data-export-table="#missing-report-table" data-filename="missing-requirements-report.csv">Export CSV</button>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600"><?= e((string)count($missing)) ?> missing</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table id="missing-report-table" class="w-full text-sm" data-enhance-table="true">
                <thead class="bg-slate-50"><tr><th class="table-th">Student ID</th><th class="table-th">Athlete</th><th class="table-th">Missing Requirement</th></tr></thead>
                <tbody>
                    <?php foreach ($missing as $m): ?>
                        <tr><td class="table-td"><?= e($m['student_id']) ?></td><td class="table-td font-semibold"><?= e($m['athlete_name']) ?></td><td class="table-td"><?= e($m['requirement_title']) ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>

    <article id="history-report" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-5">
            <div>
                <h2 class="text-lg font-bold text-slate-950">Athletic History &amp; Achievements</h2>
                <p class="text-sm text-slate-500">Athletes with previous competition experience, medal counts by sport and by year.</p>
            </div>
            <div class="no-print flex items-center gap-2">
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50" type="button" data-export-table="#history-achievements-table" data-filename="athletic-history-report.csv">Export CSV</button>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600"><?= e((string)count($historyRecords)) ?> records</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table id="history-achievements-table" class="w-full text-sm" data-enhance-table="true">
                <thead class="bg-slate-50"><tr><th class="table-th">Student ID</th><th class="table-th">Athlete</th><th class="table-th">Competitions</th><th class="table-th">Gold</th><th class="table-th">Silver</th><th class="table-th">Bronze</th><th class="table-th">Total Medals</th><th class="table-th">Highest Level</th><th class="table-th">Regional/National</th></tr></thead>
                <tbody>
                    <?php foreach ($achievements as $ach): ?>
                        <tr>
                            <td class="table-td"><?= e($ach['student_id']) ?></td>
                            <td class="table-td font-semibold"><?= e($ach['athlete_name']) ?></td>
                            <td class="table-td"><?= e((string)$ach['competitions']) ?></td>
                            <td class="table-td"><?= e((string)$ach['gold']) ?></td>
                            <td class="table-td"><?= e((string)$ach['silver']) ?></td>
                            <td class="table-td"><?= e((string)$ach['bronze']) ?></td>
                            <td class="table-td font-bold"><?= e((string)$ach['medals']) ?></td>
                            <td class="table-td"><?= e($ach['top_level'] ?? '—') ?></td>
                            <td class="table-td"><?= in_array($ach['top_level'] ?? '', ['Regional', 'National', 'International'], true) ? 'Yes' : 'No' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$achievements): ?>
                        <tr><td class="table-td py-10 text-center text-slate-500" colspan="9">No athletic history records yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="grid gap-4 border-t border-slate-100 p-5 lg:grid-cols-2">
            <div>
                <h3 class="mb-3 text-sm font-black uppercase tracking-wide text-slate-500">Medal Counts by Sport</h3>
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-sm" data-enhance-table="false">
                        <thead class="bg-slate-50"><tr><th class="table-th">Sport</th><th class="table-th">Entries</th><th class="table-th">Gold</th><th class="table-th">Silver</th><th class="table-th">Bronze</th></tr></thead>
                        <tbody>
                            <?php foreach ($medalsBySport as $ms): ?>
                                <tr><td class="table-td font-semibold"><?= e($ms['sport_name']) ?></td><td class="table-td"><?= e((string)$ms['total']) ?></td><td class="table-td"><?= e((string)$ms['gold']) ?></td><td class="table-td"><?= e((string)$ms['silver']) ?></td><td class="table-td"><?= e((string)$ms['bronze']) ?></td></tr>
                            <?php endforeach; ?>
                            <?php if (!$medalsBySport): ?>
                                <tr><td class="table-td py-6 text-center text-slate-500" colspan="5">No data yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div>
                <h3 class="mb-3 text-sm font-black uppercase tracking-wide text-slate-500">Medal Counts by Year</h3>
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-sm" data-enhance-table="false">
                        <thead class="bg-slate-50"><tr><th class="table-th">Year</th><th class="table-th">Entries</th><th class="table-th">Gold</th><th class="table-th">Silver</th><th class="table-th">Bronze</th></tr></thead>
                        <tbody>
                            <?php foreach ($medalsByYear as $my): ?>
                                <tr><td class="table-td font-semibold"><?= e((string)$my['year']) ?></td><td class="table-td"><?= e((string)$my['total']) ?></td><td class="table-td"><?= e((string)$my['gold']) ?></td><td class="table-td"><?= e((string)$my['silver']) ?></td><td class="table-td"><?= e((string)$my['bronze']) ?></td></tr>
                            <?php endforeach; ?>
                            <?php if (!$medalsByYear): ?>
                                <tr><td class="table-td py-6 text-center text-slate-500" colspan="5">No data yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </article>

    <article id="sms-report" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-5">
            <div>
                <h2 class="text-lg font-bold text-slate-950">SMS Logs Report</h2>
                <p class="text-sm text-slate-500">Communication history sent or logged by the system.</p>
            </div>
            <div class="no-print flex items-center gap-2">
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50" type="button" data-export-table="#sms-report-table" data-filename="sms-logs-report.csv">Export CSV</button>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600"><?= e((string)count($sms)) ?> logs</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table id="sms-report-table" class="w-full text-sm" data-enhance-table="true">
                <thead class="bg-slate-50"><tr><th class="table-th">Recipient</th><th class="table-th">Phone</th><th class="table-th">Status</th><th class="table-th">Date</th></tr></thead>
                <tbody>
                    <?php foreach ($sms as $l): ?>
                        <tr><td class="table-td font-semibold"><?= e($l['recipient_name']) ?></td><td class="table-td"><?= e($l['phone_number']) ?></td><td class="table-td"><?= e($l['status']) ?></td><td class="table-td"><?= e(format_datetime_12($l['sent_at'] ?? '')) ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
