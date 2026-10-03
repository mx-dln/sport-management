<?php
require_once __DIR__ . '/../../controllers/AthleteController.php';
require_once __DIR__ . '/../../controllers/ScheduleController.php';
require_once __DIR__ . '/../../controllers/SmsController.php';
require_once __DIR__ . '/../../controllers/ReportController.php';
require_once __DIR__ . '/../../controllers/AthleteHistoryController.php';
require_once __DIR__ . '/../../controllers/SportController.php';
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
$allSports = (new SportController($pdo))->all();
$today = date('F j, Y');

// Coaches Query
$coachesStmt = $pdo->query("
    SELECT u.id, u.name, u.email, u.phone_number, u.status, u.created_at,
           GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR ', ') AS sports_coached,
           GROUP_CONCAT(DISTINCT t.name ORDER BY t.name SEPARATOR ', ') AS teams_coached,
           COUNT(DISTINCT tm.athlete_id) AS total_athletes
    FROM users u
    LEFT JOIN teams t ON t.coach_id = u.id
    LEFT JOIN sports s ON s.id = t.sport_id
    LEFT JOIN team_members tm ON tm.team_id = t.id
    WHERE u.role = 'coach'
    GROUP BY u.id
    ORDER BY u.name ASC
");
$coaches = $coachesStmt ? $coachesStmt->fetchAll() : [];

// Sport Masterlist & Document Compliance Query
$selectedReportSport = (int)($_GET['sport_id'] ?? 0);
$sportMasterlistStmt = $pdo->prepare("
    SELECT a.*,
           COALESCE(NULLIF(GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR ', '), ''), ps.name) AS sport_name,
           COALESCE(NULLIF(GROUP_CONCAT(DISTINCT t.name ORDER BY t.name SEPARATOR ', '), ''), pt.name) AS team_name,
           (SELECT ad.status FROM athlete_documents ad JOIN requirement_types rt ON rt.id=ad.requirement_type_id WHERE ad.athlete_id=a.id AND (rt.title LIKE '%Birth%' OR rt.title LIKE '%PSA%') LIMIT 1) AS birth_cert_status,
           (SELECT ad.status FROM athlete_documents ad JOIN requirement_types rt ON rt.id=ad.requirement_type_id WHERE ad.athlete_id=a.id AND (rt.title LIKE '%Grade%' OR rt.title LIKE '%COG%') LIMIT 1) AS cog_status,
           (SELECT ad.status FROM athlete_documents ad JOIN requirement_types rt ON rt.id=ad.requirement_type_id WHERE ad.athlete_id=a.id AND rt.title LIKE '%Medical%' LIMIT 1) AS medical_status
    FROM athletes a
    LEFT JOIN team_members tm ON tm.athlete_id=a.id
    LEFT JOIN teams t ON t.id=tm.team_id
    LEFT JOIN sports s ON s.id=t.sport_id
    LEFT JOIN sports ps ON ps.id=a.sport_id
    LEFT JOIN teams pt ON pt.id=a.team_id
    WHERE (? = 0 OR s.id = ? OR ps.id = ?)
    GROUP BY a.id
    ORDER BY a.last_name, a.first_name
");
$sportMasterlistStmt->execute([$selectedReportSport, $selectedReportSport, $selectedReportSport]);
$sportAthletes = $sportMasterlistStmt->fetchAll();

$selectedSportObj = null;
if ($selectedReportSport > 0) {
    foreach ($allSports as $sp) {
        if ((int)$sp['id'] === $selectedReportSport) {
            $selectedSportObj = $sp;
            break;
        }
    }
}

$reportCards = [
    ['title' => 'Athlete Master List', 'count' => count($athletes), 'hint' => 'All registered student-athletes', 'target' => 'athlete-report'],
    ['title' => 'Coach Master List', 'count' => count($coaches), 'hint' => 'Appointed coaches & assignments', 'target' => 'coach-report'],
    ['title' => 'Sport Masterlist & Compliance', 'count' => count($sportAthletes), 'hint' => 'Per-sport roster, PSA & COG status', 'target' => 'sport-masterlist-report'],
    ['title' => 'Schedule Report', 'count' => count($schedules), 'hint' => 'Training sessions and venues', 'target' => 'schedule-report'],
    ['title' => 'Missing Requirements', 'count' => count($missing), 'hint' => 'Required files still incomplete', 'target' => 'missing-report'],
    ['title' => 'Athletic History', 'count' => count($historyRecords), 'hint' => 'Achievements & medal tally', 'target' => 'history-report'],
    ['title' => 'SMS Logs', 'count' => count($sms), 'hint' => 'Communication delivery logs', 'target' => 'sms-report'],
];

require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72">
<?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?>
<main class="p-4 lg:p-6">

<section class="no-print overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex flex-col gap-4 p-6 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wide text-blue-600 dark:text-blue-400"><?= e($today) ?></p>
            <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-950 dark:text-white">Reports &amp; Master Lists Center</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Generate, review, and print official summaries for athletes, coaches, sport masterlists, document compliance, and communications.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="<?= e(app_url('index.php?page=athlete_gallery')) ?>" class="smis-cmd-btn primary no-print">
                <span>🏛️</span>
                <span>SCUAA Form 2 Gallery</span>
            </a>
            <button class="btn-primary no-print" data-print>Print Active Reports</button>
        </div>
    </div>
</section>

<!-- Quick Navigation Cards -->
<section class="no-print mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
    <?php foreach ($reportCards as $card): ?>
        <a class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900" href="#<?= e($card['target']) ?>" data-report-open="<?= e($card['target']) ?>">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-xs font-semibold text-slate-500 truncate"><?= e($card['title']) ?></p>
                    <p class="mt-1 text-2xl font-black tracking-tight text-slate-950 dark:text-white"><?= e((string)$card['count']) ?></p>
                </div>
                <span class="rounded-lg bg-blue-50 px-2 py-1 text-[10px] font-bold text-blue-600 dark:bg-blue-950 dark:text-blue-400">Open</span>
            </div>
            <p class="mt-1.5 text-[11px] text-slate-400 line-clamp-1"><?= e($card['hint']) ?></p>
        </a>
    <?php endforeach; ?>
</section>

<!-- Filter Form -->
<form class="no-print mt-6 grid gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-[1fr_1fr_1fr_auto] dark:border-slate-800 dark:bg-slate-900" method="get">
    <input type="hidden" name="page" value="reports">
    <input class="form-input" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Search student ID, name, coach...">
    <select class="form-input" name="sport_id">
        <option value="">All Sports</option>
        <?php foreach ($allSports as $sport): ?>
            <option value="<?= e((string)$sport['id']) ?>" <?= $selectedReportSport === (int)$sport['id'] ? 'selected' : '' ?>>
                <?= stripos($sport['name'], 'Basketball') !== false ? '🏀 ' : '' ?><?= e($sport['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <select class="form-input" name="athlete_status">
        <option value="">Any Status</option>
        <?php foreach (['Active', 'Inactive', 'Graduated', 'Injured'] as $status): ?>
            <option value="<?= e($status) ?>" <?= ($_GET['athlete_status'] ?? '') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn-primary">Apply Filters</button>
</form>


<style>
[data-report-header] {
    cursor: pointer;
}
.report-section.is-collapsed [data-report-body],
.report-section.is-collapsed > .data-table-toolbar,
.report-section.is-collapsed > .data-table-pager {
    display: none !important;
}
.report-section-toggle {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border-radius: 9999px;
    background: #eff6ff;
    color: #1d4ed8;
    padding: 0.25rem 0.65rem;
    font-size: 0.7rem;
    font-weight: 800;
}
html.dark .report-section-toggle {
    background: #1e3a8a55;
    color: #bfdbfe;
}
.report-section.is-collapsed .report-section-toggle-icon {
    transform: rotate(-90deg);
}
.report-section-toggle-icon {
    transition: transform 0.16s ease;
}
@media print {
    .report-section.is-collapsed [data-report-body],
    .report-section.is-collapsed > .data-table-toolbar,
    .report-section.is-collapsed > .data-table-pager {
        display: block !important;
    }
}
</style>

<section class="print-card mt-6 space-y-8">
    <!-- Printable Letterhead -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h1 class="text-2xl font-black text-slate-950 dark:text-white"><?= e(school_name()) ?></h1>
        <p class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-400"><?= e(app_setting('app_name')) ?> — Official Administrative Reports</p>
        <p class="mt-1 text-xs text-slate-400">Generated on <?= e($today) ?> · Prepared by Sports Office</p>
    </div>

    <!-- ================= 1. ATHLETE MASTER LIST ================= -->
    <article id="athlete-report" data-report-section class="report-section is-collapsed overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div data-report-header class="flex items-center justify-between gap-3 border-b border-slate-100 p-5 dark:border-slate-800">
            <div>
                <h2 class="text-lg font-bold text-slate-950 dark:text-white">Athlete Master List</h2>
                <p class="text-sm text-slate-500">Official master list of registered athletes with sport and team assignments.</p>
            </div>
            <div class="no-print flex items-center gap-2">
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300" type="button" data-print-report="#athlete-report">Print</button>
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300" type="button" data-export-table="#athlete-report-table" data-filename="athlete-masterlist.csv">Export CSV</button>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300"><?= e((string)count($athletes)) ?> records</span>
            </div>
        </div>
        <div data-report-body class="overflow-x-auto">
            <table id="athlete-report-table" class="w-full text-sm" data-enhance-table="true">
                <thead class="bg-slate-50 dark:bg-slate-800">
                    <tr>
                        <th class="table-th">Student ID</th>
                        <th class="table-th">Name</th>
                        <th class="table-th">Sport</th>
                        <th class="table-th">Team</th>
                        <th class="table-th">Contact (11 Digits)</th>
                        <th class="table-th">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($athletes as $a): ?>
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="table-td font-semibold"><?= e($a['student_id']) ?></td>
                            <td class="table-td font-bold text-slate-900 dark:text-white"><?= e($a['last_name'] . ', ' . $a['first_name']) ?></td>
                            <td class="table-td"><?= e($a['sport_name'] ?? '—') ?></td>
                            <td class="table-td"><?= e($a['team_name'] ?? '—') ?></td>
                            <td class="table-td"><?= e($a['contact_number'] ?: '—') ?></td>
                            <td class="table-td"><span class="status-pill status-active"><?= e($a['athlete_status'] ?? 'Active') ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>

    <!-- ================= 2. MASTER LIST OF COACHES (PRINT COACHES) ================= -->
    <article id="coach-report" data-report-section class="report-section is-collapsed overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div data-report-header class="flex items-center justify-between gap-3 border-b border-slate-100 p-5 dark:border-slate-800">
            <div>
                <h2 class="text-lg font-bold text-slate-950 dark:text-white">Master List of Coaches</h2>
                <p class="text-sm text-slate-500">Official roster of appointed athletic coaches, contact info, assigned sports, and teams.</p>
            </div>
            <div class="no-print flex items-center gap-2">
                <button class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-bold text-white hover:bg-blue-700 shadow-xs" type="button" data-print-report="#coach-report">
                    🖨️ Print Coaches
                </button>
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300" type="button" data-export-table="#coach-report-table" data-filename="coach-masterlist.csv">
                    Export CSV
                </button>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300"><?= e((string)count($coaches)) ?> coaches</span>
            </div>
        </div>
        <div data-report-body class="overflow-x-auto">
            <table id="coach-report-table" class="w-full text-sm" data-enhance-table="true">
                <thead class="bg-slate-50 dark:bg-slate-800">
                    <tr>
                        <th class="table-th">#</th>
                        <th class="table-th">Coach Name</th>
                        <th class="table-th">Email Address</th>
                        <th class="table-th">Phone Number (11 Digits)</th>
                        <th class="table-th">Assigned Sport(s)</th>
                        <th class="table-th">Teams Coached</th>
                        <th class="table-th text-center">Athletes</th>
                        <th class="table-th">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $cIdx = 1; foreach ($coaches as $coach): ?>
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="table-td text-slate-400"><?= $cIdx++ ?></td>
                            <td class="table-td font-bold text-slate-900 dark:text-white"><?= e($coach['name']) ?></td>
                            <td class="table-td"><?= e($coach['email']) ?></td>
                            <td class="table-td font-medium"><?= e($coach['phone_number'] ?: '—') ?></td>
                            <td class="table-td font-semibold text-blue-600 dark:text-blue-400"><?= e($coach['sports_coached'] ?: 'General Athletics') ?></td>
                            <td class="table-td"><?= e($coach['teams_coached'] ?: '—') ?></td>
                            <td class="table-td text-center font-bold"><?= e((string)$coach['total_athletes']) ?></td>
                            <td class="table-td"><span class="status-pill status-active"><?= strtoupper((string)$coach['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$coaches): ?>
                        <tr><td class="table-td text-center text-slate-500 py-8" colspan="8">No coaches found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>

    <!-- ================= 3. MASTERLIST PER SPORT (GENERATE REPORTS, PRINTABLE, SCAN/UPLOAD) ================= -->
    <article id="sport-masterlist-report" data-report-section class="report-section is-collapsed overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div data-report-header class="flex flex-col gap-4 border-b border-slate-100 p-5 md:flex-row md:items-center md:justify-between dark:border-slate-800">
            <div>
                <div class="flex items-center gap-2">
                    <span class="rounded bg-blue-600 px-2 py-0.5 text-xs font-black uppercase text-white">Per Sport</span>
                    <h2 class="text-lg font-bold text-slate-950 dark:text-white">
                        <?= $selectedSportObj ? e($selectedSportObj['name']) . ' Masterlist' : 'Masterlist Per Sport &amp; Document Compliance' ?>
                    </h2>
                </div>
                <p class="mt-1 text-sm text-slate-500">Roster per sport with scan and upload verification status for PSA Birth Certificate and COG.</p>
            </div>
            <div class="no-print flex flex-wrap items-center gap-2">
                <a href="<?= e(app_url('index.php?page=athlete_gallery' . ($selectedReportSport > 0 ? '&sport_id=' . $selectedReportSport : ''))) ?>" class="rounded-lg border border-emerald-600 bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-800 hover:bg-emerald-100 shadow-xs dark:bg-emerald-950 dark:text-emerald-300 dark:border-emerald-800">
                    🏛️ SCUAA Form 2 Gallery
                </a>
                <button class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-700 shadow-xs" type="button" data-print-report="#sport-masterlist-report">
                    🖨️ Print Sport Masterlist
                </button>
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300" type="button" data-export-table="#sport-masterlist-table" data-filename="sport-masterlist.csv">
                    Export CSV
                </button>
                <button class="smis-cmd-btn" type="button" data-modal-open="#scan-upload-modal">
                    📄 Scan / Upload Docs
                </button>
            </div>
        </div>

        <div data-report-body class="overflow-x-auto">
            <table id="sport-masterlist-table" class="w-full text-sm" data-enhance-table="true">
                <thead class="bg-slate-50 dark:bg-slate-800">
                    <tr>
                        <th class="table-th">Student ID</th>
                        <th class="table-th">Athlete Name</th>
                        <th class="table-th">Sport</th>
                        <th class="table-th">Team</th>
                        <th class="table-th">Course &amp; Year</th>
                        <th class="table-th">Contact (11 Digits)</th>
                        <th class="table-th text-center">PSA Birth Cert</th>
                        <th class="table-th text-center">COG / Grade Slip</th>
                        <th class="table-th text-center">Medical Clearance</th>
                        <th class="table-th text-center">Compliance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sportAthletes as $sa): ?>
                        <?php
                        $bcStatus = $sa['birth_cert_status'] ?? 'Missing';
                        $cogStatus = $sa['cog_status'] ?? 'Missing';
                        $medStatus = $sa['medical_status'] ?? 'Missing';
                        $isFullyCompliant = in_array($bcStatus, ['Approved', 'Submitted'], true) && in_array($cogStatus, ['Approved', 'Submitted'], true);
                        ?>
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="table-td font-semibold"><?= e($sa['student_id']) ?></td>
                            <td class="table-td font-bold text-slate-950 dark:text-white"><?= e($sa['last_name'] . ', ' . $sa['first_name']) ?></td>
                            <td class="table-td font-medium"><?= e($sa['sport_name'] ?: '—') ?></td>
                            <td class="table-td"><?= e($sa['team_name'] ?: '—') ?></td>
                            <td class="table-td"><?= e(($sa['course'] ?? '') . ' ' . ($sa['year_level'] ?? '')) ?></td>
                            <td class="table-td"><?= e($sa['contact_number'] ?: '—') ?></td>
                            <td class="table-td text-center">
                                <span class="status-pill <?= $bcStatus === 'Approved' ? 'status-active' : ($bcStatus === 'Submitted' ? 'status-pending' : 'status-inactive') ?>">
                                    <?= e($bcStatus) ?>
                                </span>
                            </td>
                            <td class="table-td text-center">
                                <span class="status-pill <?= $cogStatus === 'Approved' ? 'status-active' : ($cogStatus === 'Submitted' ? 'status-pending' : 'status-inactive') ?>">
                                    <?= e($cogStatus) ?>
                                </span>
                            </td>
                            <td class="table-td text-center">
                                <span class="status-pill <?= $medStatus === 'Approved' ? 'status-active' : ($medStatus === 'Submitted' ? 'status-pending' : 'status-neutral') ?>">
                                    <?= e($medStatus) ?>
                                </span>
                            </td>
                            <td class="table-td text-center">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-bold <?= $isFullyCompliant ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300' ?>">
                                    <?= $isFullyCompliant ? '✓ Cleared' : 'Incomplete' ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$sportAthletes): ?>
                        <tr><td class="table-td text-center text-slate-500 py-8" colspan="10">No athletes found for this sport selection.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>

    <!-- ================= 4. TRAINING SCHEDULE REPORT ================= -->
    <article id="schedule-report" data-report-section class="report-section is-collapsed overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div data-report-header class="flex items-center justify-between gap-3 border-b border-slate-100 p-5 dark:border-slate-800">
            <div>
                <h2 class="text-lg font-bold text-slate-950 dark:text-white">Training Schedule Report</h2>
                <p class="text-sm text-slate-500">Training calendar, teams, venues, and current schedule status.</p>
            </div>
            <div class="no-print flex items-center gap-2">
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300" type="button" data-print-report="#schedule-report">Print</button>
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300" type="button" data-export-table="#schedule-report-table" data-filename="schedule-report.csv">Export CSV</button>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300"><?= e((string)count($schedules)) ?> schedules</span>
            </div>
        </div>
        <div data-report-body class="overflow-x-auto">
            <table id="schedule-report-table" class="w-full text-sm" data-enhance-table="true">
                <thead class="bg-slate-50 dark:bg-slate-800">
                    <tr><th class="table-th">Date</th><th class="table-th">Time</th><th class="table-th">Team</th><th class="table-th">Sport</th><th class="table-th">Venue</th><th class="table-th">Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($schedules as $s): ?>
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="table-td"><?= e($s['training_date']) ?></td>
                            <td class="table-td"><?= e(format_time_range($s['start_time'] ?? '', $s['end_time'] ?? '')) ?></td>
                            <td class="table-td font-semibold"><?= e($s['team_name']) ?></td>
                            <td class="table-td"><?= e($s['sport_name']) ?></td>
                            <td class="table-td"><?= e($s['venue']) ?></td>
                            <td class="table-td"><?= e($s['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>

    <!-- ================= 5. MISSING REQUIREMENTS REPORT ================= -->
    <article id="missing-report" data-report-section class="report-section is-collapsed overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div data-report-header class="flex items-center justify-between gap-3 border-b border-slate-100 p-5 dark:border-slate-800">
            <div>
                <h2 class="text-lg font-bold text-slate-950 dark:text-white">Missing Requirements Report</h2>
                <p class="text-sm text-slate-500">Required documents (Birth Certificate, COG, Medical, etc.) still pending submission.</p>
            </div>
            <div class="no-print flex items-center gap-2">
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300" type="button" data-print-report="#missing-report">Print</button>
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300" type="button" data-export-table="#missing-report-table" data-filename="missing-requirements-report.csv">Export CSV</button>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300"><?= e((string)count($missing)) ?> missing</span>
            </div>
        </div>
        <div data-report-body class="overflow-x-auto">
            <table id="missing-report-table" class="w-full text-sm" data-enhance-table="true">
                <thead class="bg-slate-50 dark:bg-slate-800">
                    <tr><th class="table-th">Student ID</th><th class="table-th">Athlete Name</th><th class="table-th">Pending Requirement</th><th class="table-th text-right">Action</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($missing as $m): ?>
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="table-td"><?= e($m['student_id']) ?></td>
                            <td class="table-td font-semibold"><?= e($m['athlete_name']) ?></td>
                            <td class="table-td text-rose-600 font-bold"><?= e($m['requirement_title']) ?></td>
                            <td class="table-td text-right">
                                <button type="button" class="smis-cmd-btn text-xs" data-modal-open="#scan-upload-modal">Scan / Upload</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>

    <!-- ================= 6. ATHLETIC HISTORY & ACHIEVEMENTS ================= -->
    <article id="history-report" data-report-section class="report-section is-collapsed overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div data-report-header class="flex items-center justify-between gap-3 border-b border-slate-100 p-5 dark:border-slate-800">
            <div>
                <h2 class="text-lg font-bold text-slate-950 dark:text-white">Athletic History &amp; Achievements</h2>
                <p class="text-sm text-slate-500">Athletes with competition experience, medals tally, and provincial/regional/national achievements.</p>
            </div>
            <div class="no-print flex items-center gap-2">
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300" type="button" data-print-report="#history-report">Print</button>
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300" type="button" data-export-table="#history-achievements-table" data-filename="athletic-history-report.csv">Export CSV</button>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300"><?= e((string)count($historyRecords)) ?> entries</span>
            </div>
        </div>
        <div data-report-body class="overflow-x-auto">
            <table id="history-achievements-table" class="w-full text-sm" data-enhance-table="true">
                <thead class="bg-slate-50 dark:bg-slate-800">
                    <tr><th class="table-th">Student ID</th><th class="table-th">Athlete</th><th class="table-th">Competitions</th><th class="table-th">Gold</th><th class="table-th">Silver</th><th class="table-th">Bronze</th><th class="table-th">Total Medals</th><th class="table-th">Top Level</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($achievements as $ach): ?>
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="table-td"><?= e($ach['student_id']) ?></td>
                            <td class="table-td font-semibold"><?= e($ach['athlete_name']) ?></td>
                            <td class="table-td"><?= e((string)$ach['competitions']) ?></td>
                            <td class="table-td">🥇 <?= e((string)$ach['gold']) ?></td>
                            <td class="table-td">🥈 <?= e((string)$ach['silver']) ?></td>
                            <td class="table-td">🥉 <?= e((string)$ach['bronze']) ?></td>
                            <td class="table-td font-black text-slate-950 dark:text-white"><?= e((string)$ach['medals']) ?></td>
                            <td class="table-td"><span class="status-pill status-neutral"><?= e($ach['top_level'] ?? '—') ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>

    <!-- ================= 7. SMS LOGS REPORT ================= -->
    <article id="sms-report" data-report-section class="report-section is-collapsed overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div data-report-header class="flex items-center justify-between gap-3 border-b border-slate-100 p-5 dark:border-slate-800">
            <div>
                <h2 class="text-lg font-bold text-slate-950 dark:text-white">SMS Logs Report</h2>
                <p class="text-sm text-slate-500">Communication history sent or logged by the sports management system.</p>
            </div>
            <div class="no-print flex items-center gap-2">
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300" type="button" data-print-report="#sms-report">Print</button>
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300" type="button" data-export-table="#sms-report-table" data-filename="sms-logs-report.csv">Export CSV</button>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300"><?= e((string)count($sms)) ?> logs</span>
            </div>
        </div>
        <div data-report-body class="overflow-x-auto">
            <table id="sms-report-table" class="w-full text-sm" data-enhance-table="true">
                <thead class="bg-slate-50 dark:bg-slate-800">
                    <tr><th class="table-th">Recipient</th><th class="table-th">Phone (11 Digits)</th><th class="table-th">Message</th><th class="table-th">Status</th><th class="table-th">Date</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($sms as $l): ?>
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="table-td font-semibold"><?= e($l['recipient_name']) ?></td>
                            <td class="table-td"><?= e($l['phone_number']) ?></td>
                            <td class="table-td max-w-xs truncate"><?= e($l['message']) ?></td>
                            <td class="table-td"><span class="status-pill status-active"><?= e(ucwords(str_replace('_', ' ', (string)$l['status']))) ?></span></td>
                            <td class="table-td text-xs text-slate-500"><?= e(format_datetime_12($l['sent_at'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

</main>
</div>

<script>
(() => {
    const updateReportLabel = (section) => {
        const label = section.querySelector('[data-report-toggle-label]');
        if (label) label.textContent = section.classList.contains('is-collapsed') ? 'Show table' : 'Hide table';
    };

    const toggleReportSection = (section, forceOpen = null) => {
        if (!section) return;
        if (forceOpen === true) {
            section.classList.remove('is-collapsed');
        } else if (forceOpen === false) {
            section.classList.add('is-collapsed');
        } else {
            section.classList.toggle('is-collapsed');
        }
        updateReportLabel(section);
    };

    const initReportAccordions = () => {
        document.querySelectorAll('[data-report-section]').forEach((section) => {
            const header = section.querySelector('[data-report-header]');
            const actions = header?.querySelector('.no-print');
            if (actions && !actions.querySelector('[data-report-toggle]')) {
                const toggle = document.createElement('button');
                toggle.type = 'button';
                toggle.className = 'report-section-toggle';
                toggle.dataset.reportToggle = section.id;
                toggle.innerHTML = '<span class="report-section-toggle-icon">⌄</span><span data-report-toggle-label>Show table</span>';
                actions.prepend(toggle);
            }
            updateReportLabel(section);
        });
    };

    initReportAccordions();
    document.addEventListener('DOMContentLoaded', initReportAccordions);

    document.addEventListener('click', (event) => {
        const card = event.target.closest('[data-report-open]');
        if (card) {
            event.preventDefault();
            const section = document.getElementById(card.dataset.reportOpen);
            toggleReportSection(section, true);
            section?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            return;
        }

        const toggle = event.target.closest('[data-report-toggle]');
        if (toggle) {
            event.preventDefault();
            event.stopPropagation();
            toggleReportSection(document.getElementById(toggle.dataset.reportToggle));
            return;
        }

        const header = event.target.closest('[data-report-header]');
        if (header && !event.target.closest('button, a, select, input, textarea')) {
            toggleReportSection(header.closest('[data-report-section]'));
        }
    });
})();
</script>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
