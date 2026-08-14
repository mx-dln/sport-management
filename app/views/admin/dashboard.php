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
        <div class="divide-y divide-slate-100">
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
            <div class="space-y-3 p-5">
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
            <p class="mt-1 text-sm text-slate-500">Quick comparison of core records.</p>
            <div class="mt-4 space-y-3">
                <?php foreach (array_slice($statCards, 0, 4) as $card): ?>
                    <?php $value = (int)($counts[$card['key']] ?? 0); ?>
                    <div>
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <span class="font-semibold text-slate-600"><?= e($card['label']) ?></span>
                            <span class="text-slate-500"><?= e((string)$value) ?></span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full" style="width: <?= e((string)min(100, ($value / $maxCount) * 100)) ?>%; background: var(--theme-color);"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</section>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
