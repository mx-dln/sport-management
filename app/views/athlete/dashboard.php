<?php
require_once __DIR__ . '/../../controllers/AthleteController.php';
require_once __DIR__ . '/../../controllers/ScheduleController.php';
require_once __DIR__ . '/../../controllers/AnnouncementController.php';
require_once __DIR__ . '/../../controllers/AthleteHistoryController.php';
require_role(['athlete']);

$pageTitle = 'Athlete Dashboard';
$stmt = $pdo->prepare('SELECT * FROM athletes WHERE user_id=? LIMIT 1');
$stmt->execute([current_user()['id']]);
$athlete = $stmt->fetch();
$historyStats = $athlete ? (new AthleteHistoryController($pdo))->stats((int)$athlete['id']) : [];
$docs = $athlete ? (new AthleteController($pdo))->documents((int)$athlete['id']) : [];
$approvedDocs = count(array_filter($docs, fn($d) => ($d['status'] ?? '') === 'Approved'));
$submittedDocs = count(array_filter($docs, fn($d) => in_array(($d['status'] ?? ''), ['Submitted', 'Approved'], true)));
$schedules = [];
$scheduleController = new ScheduleController($pdo);
$scheduleController->autoCompletePastSchedules();
if ($athlete) {
    $stmt = $pdo->prepare('SELECT ts.*, s.name sport_name, t.name team_name FROM training_schedules ts JOIN team_members tm ON tm.team_id=ts.team_id JOIN teams t ON t.id=ts.team_id LEFT JOIN sports s ON s.id=ts.sport_id WHERE tm.athlete_id=? AND ts.training_date>=? ORDER BY ts.training_date, ts.start_time');
    $stmt->execute([$athlete['id'], date('Y-m-d')]);
    $schedules = $stmt->fetchAll();
}
$announcements = array_slice((new AnnouncementController($pdo))->all(), 0, 5);
$today = date('F j, Y');
$displayName = $athlete ? trim(($athlete['first_name'] ?? '') . ' ' . ($athlete['last_name'] ?? '')) : (current_user()['name'] ?? 'Athlete');
$cards = [
    ['label' => 'Biodata Status', 'value' => $athlete ? ($athlete['athlete_status'] ?? 'Active') : 'Incomplete', 'hint' => 'Profile record', 'page' => 'profile'],
    ['label' => 'Requirements', 'value' => $approvedDocs . '/' . count($docs), 'hint' => $submittedDocs . ' submitted files', 'page' => 'documents'],
    ['label' => 'Upcoming Trainings', 'value' => count($schedules), 'hint' => 'Sessions assigned to your teams', 'page' => 'schedules'],
    ['label' => 'Athletic History', 'value' => $historyStats['total'] ?? 0, 'hint' => ($historyStats['medals'] ?? 0) . ' medal(s) earned', 'page' => 'history'],
];
require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72">
<?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?>
<main class="p-4 lg:p-6">

<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="grid gap-6 p-6 lg:grid-cols-[1fr_auto] lg:items-center">
        <div>
            <p class="text-xs font-bold uppercase tracking-wide text-blue-600"><?= e($today) ?></p>
            <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-950">Hello, <?= e($displayName) ?></h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Keep your biodata complete, track document status, and check your upcoming training schedule.</p>
        </div>
        <a class="btn-primary no-print" href="<?= e(app_url('index.php?page=profile')) ?>">Edit Profile</a>
    </div>
</section>

<section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
    <?php foreach ($cards as $card): ?>
        <a class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md" href="<?= e(app_url('index.php?page=' . $card['page'])) ?>">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-slate-500"><?= e($card['label']) ?></p>
                    <p class="mt-2 text-3xl font-black tracking-tight text-slate-950"><?= e((string)$card['value']) ?></p>
                </div>
                <span class="rounded-xl bg-blue-50 px-3 py-2 text-xs font-bold text-blue-600">View</span>
            </div>
            <p class="mt-2 text-sm text-slate-500"><?= e($card['hint']) ?></p>
        </a>
    <?php endforeach; ?>
</section>

<section class="mt-6 grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 p-5">
            <div>
                <h2 class="text-lg font-bold text-slate-950">Upcoming Training</h2>
                <p class="text-sm text-slate-500">Sessions from your assigned teams</p>
            </div>
            <a class="text-sm font-semibold text-blue-600" href="<?= e(app_url('index.php?page=schedules')) ?>">View All</a>
        </div>
        <div class="divide-y divide-slate-100">
            <?php foreach (array_slice($schedules, 0, 6) as $s): ?>
                <article class="grid gap-3 p-5 sm:grid-cols-[100px_1fr_auto] sm:items-center">
                    <div class="rounded-xl bg-slate-50 p-3 text-center">
                        <p class="text-xs font-bold uppercase text-slate-500"><?= e(date('M', strtotime($s['training_date']))) ?></p>
                        <p class="text-2xl font-black text-slate-950"><?= e(date('d', strtotime($s['training_date']))) ?></p>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-950"><?= e($s['team_name'] ?? 'Team') ?></h3>
                        <p class="mt-1 text-sm text-slate-500"><?= e(($s['sport_name'] ?? 'Sport') . ' at ' . ($s['venue'] ?? 'Venue')) ?></p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"><?= e(format_time_range($s['start_time'] ?? '', $s['end_time'] ?? '')) ?></span>
                </article>
            <?php endforeach; ?>
            <?php if (!$schedules): ?>
                <div class="p-8 text-center text-sm text-slate-500">No upcoming training schedules yet.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid gap-6">
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 p-5">
                <div>
                    <h2 class="text-lg font-bold text-slate-950">Document Progress</h2>
                    <p class="text-sm text-slate-500">Requirement completion summary</p>
                </div>
                <a class="text-sm font-semibold text-blue-600" href="<?= e(app_url('index.php?page=documents')) ?>">Manage</a>
            </div>
            <div class="p-5">
                <?php $completion = count($docs) ? round(($approvedDocs / count($docs)) * 100) : 0; ?>
                <div class="flex items-end justify-between gap-3">
                    <div>
                        <p class="text-4xl font-black text-slate-950"><?= e((string)$completion) ?>%</p>
                        <p class="text-sm text-slate-500"><?= e((string)$approvedDocs) ?> approved of <?= e((string)count($docs)) ?> requirements</p>
                    </div>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-600"><?= e((string)$submittedDocs) ?> submitted</span>
                </div>
                <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full" style="width: <?= e((string)$completion) ?>%; background: var(--theme-color);"></div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 p-5">
                <div>
                    <h2 class="text-lg font-bold text-slate-950">Announcements</h2>
                    <p class="text-sm text-slate-500">Recent updates from the sports office</p>
                </div>
                <a class="text-sm font-semibold text-blue-600" href="<?= e(app_url('index.php?page=announcements')) ?>">Open</a>
            </div>
            <div class="space-y-3 p-5">
                <?php foreach ($announcements as $a): ?>
                    <article class="rounded-xl bg-blue-50 p-4">
                        <h3 class="font-bold text-slate-950"><?= e($a['title']) ?></h3>
                        <p class="mt-1 line-clamp-2 text-sm leading-6 text-slate-500"><?= e($a['body']) ?></p>
                    </article>
                <?php endforeach; ?>
                <?php if (!$announcements): ?>
                    <div class="rounded-xl bg-slate-50 p-6 text-center text-sm text-slate-500">No announcements posted yet.</div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</section>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
