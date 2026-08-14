<?php
require_once __DIR__ . '/../../controllers/TeamController.php';
require_once __DIR__ . '/../../controllers/ScheduleController.php';
require_once __DIR__ . '/../../controllers/AnnouncementController.php';
require_role(['coach']);

$pageTitle = 'Coach Dashboard';
$uid = current_user()['id'];
$teamController = new TeamController($pdo);
$teams = $teamController->all(['coach_id' => $uid]);
$schedules = (new ScheduleController($pdo))->all(['coach_id' => $uid, 'date_from' => date('Y-m-d')]);
$announcements = array_slice((new AnnouncementController($pdo))->all(), 0, 5);
$memberCount = 0;
foreach ($teams as $team) {
    $memberCount += count($teamController->roster((int)$team['id']));
}
$today = date('F j, Y');
$cards = [
    ['label' => 'Assigned Teams', 'value' => count($teams), 'hint' => 'Teams under your care', 'page' => 'teams'],
    ['label' => 'Team Members', 'value' => $memberCount, 'hint' => 'Athletes across teams', 'page' => 'teams'],
    ['label' => 'Upcoming Trainings', 'value' => count($schedules), 'hint' => 'Sessions from today onward', 'page' => 'schedules'],
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
            <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-950">Welcome back, <?= e(current_user()['name'] ?? 'Coach') ?></h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Track your teams, manage training schedules, and keep athletes informed from one coaching workspace.</p>
        </div>
        <a class="btn-primary no-print" href="<?= e(app_url('index.php?page=attendance')) ?>">Open Attendance</a>
    </div>
</section>

<section class="mt-6 grid gap-4 md:grid-cols-3">
    <?php foreach ($cards as $card): ?>
        <a class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md" href="<?= e(app_url('index.php?page=' . $card['page'])) ?>">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-slate-500"><?= e($card['label']) ?></p>
                    <p class="mt-2 text-4xl font-black tracking-tight text-slate-950"><?= e((string)$card['value']) ?></p>
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
                <h2 class="text-lg font-bold text-slate-950">Upcoming Trainings</h2>
                <p class="text-sm text-slate-500">Your next team sessions</p>
            </div>
            <a class="text-sm font-semibold text-blue-600" href="<?= e(app_url('index.php?page=schedules')) ?>">Manage</a>
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
                    <h2 class="text-lg font-bold text-slate-950">My Teams</h2>
                    <p class="text-sm text-slate-500">Roster overview</p>
                </div>
                <a class="text-sm font-semibold text-blue-600" href="<?= e(app_url('index.php?page=teams')) ?>">Open</a>
            </div>
            <div class="space-y-3 p-5">
                <?php foreach ($teams as $team): ?>
                    <?php $rosterCount = count($teamController->roster((int)$team['id'])); ?>
                    <article class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 p-4">
                        <div>
                            <h3 class="font-bold text-slate-950"><?= e($team['name']) ?></h3>
                            <p class="text-sm text-slate-500"><?= e($team['sport_name']) ?></p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600"><?= e((string)$rosterCount) ?> members</span>
                    </article>
                <?php endforeach; ?>
                <?php if (!$teams): ?>
                    <div class="rounded-xl bg-slate-50 p-6 text-center text-sm text-slate-500">No teams assigned yet.</div>
                <?php endif; ?>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 p-5">
                <div>
                    <h2 class="text-lg font-bold text-slate-950">Announcements</h2>
                    <p class="text-sm text-slate-500">Recent team messages</p>
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
