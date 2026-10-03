<?php
require_once __DIR__ . '/../../controllers/ReportController.php';
require_once __DIR__ . '/../../controllers/ScheduleController.php';
require_once __DIR__ . '/../../controllers/AnnouncementController.php';
require_role(['admin', 'sports_coordinator']);
$pageTitle = 'Admin Dashboard';
$reports = new ReportController($pdo);
$counts = $reports->dashboardCounts();
$upcoming = (new ScheduleController($pdo))->all(['date_from' => date('Y-m-d')]);
$announcements = array_slice((new AnnouncementController($pdo))->all(), 0, 5);

// Fetch recent athlete history records for the bottom section
$recentHistory = [];
try {
    $histStmt = $pdo->query("SELECT ah.*, a.student_id, a.first_name, a.last_name, s.name AS sport_name FROM athlete_histories ah JOIN athletes a ON a.id = ah.athlete_id LEFT JOIN sports s ON s.id = ah.sport_id ORDER BY ah.created_at DESC LIMIT 8");
    $recentHistory = $histStmt->fetchAll();
} catch (Exception $e) {}
$today = date('F j, Y');
$statCards = [
    ['key' => 'athletes', 'label' => 'Total Athletes', 'hint' => 'Registered profiles', 'page' => 'athletes'],
    ['key' => 'sports', 'label' => 'Sports', 'hint' => 'Active programs', 'page' => 'sports'],
    ['key' => 'teams', 'label' => 'Teams', 'hint' => 'Managed teams', 'page' => 'teams'],
    ['key' => 'coaches', 'label' => 'Coaches', 'hint' => 'Assigned staff', 'page' => 'users'],
    ['key' => 'pending_documents', 'label' => 'Pending Docs', 'hint' => 'Need review', 'page' => 'documents'],
    ['key' => 'sms_sent', 'label' => 'SMS Logs', 'hint' => 'Message records', 'page' => 'sms'],
];
$maxCount = max(1, ...array_map(fn($card) => (int)($counts[$card['key']] ?? 0), $statCards));
$programMixCards = array_slice($statCards, 0, 4);
$programMixColors = ['#2563eb', '#16a34a', '#f97316', '#9333ea'];
$programMixTotal = array_sum(array_map(fn($card) => (int)($counts[$card['key']] ?? 0), $programMixCards));
$programMixGradient = '';
$programMixStart = 0.0;
foreach ($programMixCards as $index => $card) {
    $value = (int)($counts[$card['key']] ?? 0);
    $percent = $programMixTotal > 0 ? ($value / $programMixTotal) * 100 : 0;
    $programMixEnd = $programMixStart + $percent;
    $color = $programMixColors[$index % count($programMixColors)];
    if ($percent > 0) {
        $programMixGradient .= ($programMixGradient ? ', ' : '') . $color . ' ' . round($programMixStart, 2) . '% ' . round($programMixEnd, 2) . '%';
    }
    $programMixCards[$index]['value'] = $value;
    $programMixCards[$index]['percent'] = $percent;
    $programMixCards[$index]['color'] = $color;
    $programMixStart = $programMixEnd;
}
if ($programMixGradient === '') {
    $programMixGradient = '#e2e8f0 0% 100%';
}
require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72">
<?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?>
<main class="p-4 lg:p-6">
<?php require __DIR__ . '/../../includes/alerts.php'; ?>

<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="grid gap-6 p-6 lg:grid-cols-[1fr_360px] lg:p-7">
        <div>
            <p class="text-xs font-bold uppercase tracking-wide text-blue-600"><?= e($today) ?></p>
            <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-950">Welcome back, <?= e(current_user()['name'] ?? 'Admin') ?></h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Monitor athletes, training schedules, document compliance, and communications from one command center.</p>
        </div>
    </div>
</section>

<section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
<?php foreach ($statCards as $card): ?>
    <?php $value = (int)($counts[$card['key']] ?? 0); $percent = min(100, ($value / $maxCount) * 100); ?>
    <a class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md" href="<?= e(app_url('index.php?page=' . $card['page'])) ?>">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-slate-500"><?= e($card['label']) ?></p>
                <p class="mt-2 text-4xl font-black tracking-tight text-slate-950"><?= e((string)$value) ?></p>
            </div>
            <span class="rounded-xl bg-blue-50 px-3 py-2 text-xs font-bold text-blue-600">View</span>
        </div>
        <p class="mt-2 text-sm text-slate-500"><?= e($card['hint']) ?></p>
        <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100">
            <div class="h-full rounded-full transition-all group-hover:opacity-80" style="width: <?= e((string)$percent) ?>%; background: var(--theme-color);"></div>
        </div>
    </a>
<?php endforeach; ?>
</section>

<section class="mt-6 grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 p-5">
            <div>
                <h2 class="text-lg font-bold">Upcoming Training</h2>
                <p class="text-sm text-slate-500">Next sessions from today onward</p>
            </div>
            <a class="text-sm font-semibold text-blue-600" href="<?= e(app_url('index.php?page=schedules')) ?>">Manage</a>
        </div>
        <div class="divide-y divide-slate-100 max-h-96 overflow-y-auto smis-scrollbar">
            <?php foreach (array_slice($upcoming, 0, 6) as $s): ?>
                <article class="grid gap-3 p-5 sm:grid-cols-[110px_1fr_auto] sm:items-center">
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
            <?php if (!$upcoming): ?>
                <div class="p-8 text-center text-sm text-slate-500">No upcoming training schedules yet.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid gap-6">
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 p-5">
                <div>
                    <h2 class="text-lg font-bold">Recent Announcements</h2>
                    <p class="text-sm text-slate-500">Latest messages for teams and athletes</p>
                </div>
                <a class="text-sm font-semibold text-blue-600" href="<?= e(app_url('index.php?page=announcements')) ?>">Open</a>
            </div>
            <div class="space-y-3 p-5 max-h-80 overflow-y-auto smis-scrollbar">
                <?php foreach ($announcements as $a): ?>
                    <article class="rounded-xl bg-slate-50 p-4">
                        <h3 class="font-bold text-slate-950"><?= e($a['title']) ?></h3>
                        <p class="mt-1 line-clamp-2 text-sm leading-6 text-slate-500"><?= e($a['body']) ?></p>
                    </article>
                <?php endforeach; ?>
                <?php if (!$announcements): ?>
                    <div class="rounded-xl bg-slate-50 p-6 text-center text-sm text-slate-500">No announcements posted yet.</div>
                <?php endif; ?>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-bold">Program Mix</h2>
            <p class="mt-1 text-sm text-slate-500">Pie chart comparison of core records.</p>
            <div class="mt-5 grid gap-5 sm:grid-cols-[180px_1fr] sm:items-center">
                <div class="mx-auto grid h-44 w-44 place-items-center rounded-full shadow-inner" style="background: conic-gradient(<?= e($programMixGradient) ?>);">
                    <div class="grid h-24 w-24 place-items-center rounded-full bg-white text-center shadow-sm">
                        <div>
                            <p class="text-2xl font-black text-slate-950"><?= e((string)$programMixTotal) ?></p>
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Total</p>
                        </div>
                    </div>
                </div>
                <div class="space-y-3">
                    <?php foreach ($programMixCards as $card): ?>
                        <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-3 py-2">
                            <div class="flex min-w-0 items-center gap-2">
                                <span class="h-3 w-3 shrink-0 rounded-full" style="background: <?= e($card['color']) ?>;"></span>
                                <span class="truncate text-sm font-semibold text-slate-700"><?= e($card['label']) ?></span>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-black text-slate-950"><?= e((string)$card['value']) ?></p>
                                <p class="text-xs font-semibold text-slate-500"><?= e((string)round($card['percent'])) ?>%</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </div>
</section>

<!-- ================= BOTTOM ATHLETE HISTORY & ACHIEVEMENTS SECTION ================= -->
<section id="admin-athlete-history" class="mt-6 rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800">
        <div>
            <div class="flex items-center gap-2">
                <span class="grid h-7 w-7 place-items-center rounded-lg bg-amber-100 text-sm dark:bg-amber-950">🏆</span>
                <h2 class="text-lg font-black text-slate-950 dark:text-white">Recent Athlete Achievements &amp; History</h2>
            </div>
            <p class="text-xs text-slate-500 mt-1">Latest athletic milestones and competition entries recorded by varsity athletes.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= e(app_url('index.php?page=reports#history-report')) ?>" class="smis-cmd-btn primary">
                View Full History Report
            </a>
            <a href="<?= e(app_url('index.php?page=history')) ?>" class="smis-cmd-btn">
                Add History Record
            </a>
        </div>
    </div>

    <div class="max-h-80 overflow-y-auto smis-scrollbar">
        <table class="w-full text-sm" data-enhance-table="false">
            <thead class="bg-slate-50 dark:bg-slate-800 sticky top-0">
                <tr>
                    <th class="table-th">Student ID</th>
                    <th class="table-th">Athlete Name</th>
                    <th class="table-th">Competition</th>
                    <th class="table-th">Sport &amp; Event</th>
                    <th class="table-th">Level</th>
                    <th class="table-th">Result / Medal</th>
                    <th class="table-th text-right">Proof File</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentHistory as $rh): ?>
                    <?php
                    $mBadge = match ($rh['medal'] ?? '') {
                        'Gold' => '🥇 Gold',
                        'Silver' => '🥈 Silver',
                        'Bronze' => '🥉 Bronze',
                        default => $rh['medal'] ? e($rh['medal']) : 'Participant',
                    };
                    ?>
                    <tr class="border-t border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                        <td class="table-td font-semibold text-slate-600 dark:text-slate-400"><?= e($rh['student_id']) ?></td>
                        <td class="table-td font-bold text-slate-950 dark:text-white">
                            <a href="<?= e(app_url('index.php?page=athlete_print&id=' . $rh['athlete_id'])) ?>" class="hover:text-blue-600">
                                <?= e($rh['last_name'] . ', ' . $rh['first_name']) ?>
                            </a>
                        </td>
                        <td class="table-td font-medium"><?= e($rh['competition_name']) ?></td>
                        <td class="table-td"><?= e($rh['sport_name'] ?: 'Sport') ?><?= $rh['event_name'] ? ' (' . e($rh['event_name']) . ')' : '' ?></td>
                        <td class="table-td"><span class="status-pill status-neutral"><?= e($rh['competition_level']) ?></span></td>
                        <td class="table-td font-semibold text-slate-800 dark:text-slate-200"><?= $mBadge ?><?= $rh['result'] ? ' - ' . e($rh['result']) : '' ?></td>
                        <td class="table-td text-right">
                            <?php if (!empty($rh['proof_file'])): ?>
                                <a href="<?= e(app_url($rh['proof_file'])) ?>" data-attachment-preview data-attachment-url="<?= e(app_url($rh['proof_file'])) ?>" data-attachment-name="<?= e($rh['competition_name']) ?> Proof" class="text-xs font-bold text-blue-600 hover:underline">
                                    View Proof
                                </a>
                            <?php else: ?>
                                <span class="text-xs text-slate-400">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$recentHistory): ?>
                    <tr>
                        <td class="table-td text-center text-slate-400 py-8" colspan="7">
                            No athlete competition history entries recorded yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
