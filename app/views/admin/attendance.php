<?php
require_once __DIR__ . '/../../controllers/ScheduleController.php';
require_once __DIR__ . '/../../controllers/AttendanceController.php';
require_role(['admin', 'sports_coordinator', 'coach']);
$pageTitle = 'Attendance Monitoring';
$scheduleId = (int)($_GET['schedule_id'] ?? 0);
$scheduleFilter = [];
if ((current_user()['role'] ?? '') === 'coach') {
    $scheduleFilter['coach_id'] = current_user()['id'];
}
$schedules = (new ScheduleController($pdo))->all($scheduleFilter);
$rows = $scheduleId ? (new AttendanceController($pdo))->forSchedule($scheduleId) : [];
$selectedSchedule = null;
foreach ($schedules as $schedule) {
    if ((int)$schedule['id'] === $scheduleId) {
        $selectedSchedule = $schedule;
        break;
    }
}
$month = preg_match('/^\d{4}-\d{2}$/', $_GET['month'] ?? '') ? $_GET['month'] : date('Y-m');
$monthStart = new DateTimeImmutable($month . '-01');
$calendarStart = $monthStart->modify('monday this week');
$monthEnd = $monthStart->modify('last day of this month');
$calendarEnd = $monthEnd->modify('sunday this week');
$prevMonth = $monthStart->modify('-1 month')->format('Y-m');
$nextMonth = $monthStart->modify('+1 month')->format('Y-m');
$schedulesByDate = [];
foreach ($schedules as $schedule) {
    $schedulesByDate[$schedule['training_date']][] = $schedule;
}
require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72"><?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?>
<main class="p-4 lg:p-6">
<section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <h2 class="text-xl font-black text-slate-950">Attendance Calendar</h2>
            <p class="mt-1 text-sm text-slate-500">Choose a training schedule from the calendar or selector to mark attendance.</p>
        </div>
        <form class="flex flex-col gap-2 sm:flex-row sm:items-center" method="get" action="<?= e(app_url('index.php')) ?>">
            <input type="hidden" name="page" value="attendance">
            <input type="hidden" name="month" value="<?= e($month) ?>">
            <select class="form-input min-w-[320px]" name="schedule_id" onchange="this.form.submit()">
                <option value="">Choose schedule</option>
                <?php foreach ($schedules as $s): ?>
                    <option value="<?= e((string)$s['id']) ?>" <?= $scheduleId===(int)$s['id']?'selected':'' ?>><?= e($s['training_date'].' '.format_time_range($s['start_time'] ?? '', $s['end_time'] ?? '').' - '.$s['team_name'].' @ '.$s['venue']) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</section>

<div class="grid gap-6 xl:grid-cols-[minmax(280px,1fr)_3fr]">
<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-slate-200 p-4">
        <a class="rounded-lg border border-slate-300 px-2 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50" href="<?= e(app_url('index.php?page=attendance&month=' . $prevMonth . ($scheduleId ? '&schedule_id=' . $scheduleId : ''))) ?>">Prev</a>
        <h2 class="text-sm font-black text-slate-950"><?= e($monthStart->format('M Y')) ?></h2>
        <a class="rounded-lg border border-slate-300 px-2 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50" href="<?= e(app_url('index.php?page=attendance&month=' . $nextMonth . ($scheduleId ? '&schedule_id=' . $scheduleId : ''))) ?>">Next</a>
    </div>
    <div class="grid grid-cols-7 border-b border-slate-200 bg-slate-50 text-center text-[10px] font-black uppercase tracking-wide text-slate-500">
        <?php foreach (['M','T','W','T','F','S','S'] as $day): ?>
            <div class="p-2"><?= e($day) ?></div>
        <?php endforeach; ?>
    </div>
    <div class="grid grid-cols-7">
        <?php for ($day = $calendarStart; $day <= $calendarEnd; $day = $day->modify('+1 day')): ?>
            <?php $date = $day->format('Y-m-d'); $inMonth = $day->format('Y-m') === $month; $daySchedules = $schedulesByDate[$date] ?? []; ?>
            <a class="min-h-14 border-b border-r border-slate-100 p-2 text-xs <?= $inMonth ? 'bg-white' : 'bg-slate-50 text-slate-400' ?> <?= $date === date('Y-m-d') ? 'ring-1 ring-inset ring-blue-200' : '' ?>" href="<?= e(!empty($daySchedules) ? app_url('index.php?page=attendance&month=' . $month . '&schedule_id=' . $daySchedules[0]['id']) : '#') ?>">
                <span class="font-bold"><?= e($day->format('j')) ?></span>
                <?php if (!empty($daySchedules)): ?>
                    <span class="mt-1 block h-1.5 w-1.5 rounded-full" style="background: var(--theme-color);"></span>
                <?php endif; ?>
            </a>
        <?php endfor; ?>
    </div>
    <div class="max-h-72 space-y-2 overflow-y-auto p-4">
        <?php foreach ($schedules as $schedule): ?>
            <?php if (str_starts_with((string)$schedule['training_date'], $month)): ?>
                <a class="block rounded-lg border px-3 py-2 text-xs <?= (int)$schedule['id'] === $scheduleId ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-blue-50 hover:text-blue-700' ?>" href="<?= e(app_url('index.php?page=attendance&month=' . $month . '&schedule_id=' . $schedule['id'])) ?>">
                    <b><?= e(date('M d', strtotime($schedule['training_date']))) ?></b> <?= e(format_time_12($schedule['start_time'] ?? '')) ?> - <?= e($schedule['team_name'] ?? 'Team') ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>

<?php if ($scheduleId && $selectedSchedule): ?>
    <?php $attendanceReadOnly = ($selectedSchedule['status'] ?? '') === 'Completed'; ?>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-black text-slate-950">Mark Attendance</h2>
                <p class="mt-1 text-sm text-slate-500"><?= e($selectedSchedule['training_date'].' '.format_time_range($selectedSchedule['start_time'] ?? '', $selectedSchedule['end_time'] ?? '').' - '.$selectedSchedule['team_name'].' @ '.$selectedSchedule['venue']) ?></p>
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="rounded-full bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700"><?= e($selectedSchedule['status'] ?? 'Scheduled') ?></span>
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" type="button" data-export-table="#attendance-export-table" data-filename="attendance-<?= e((string)$scheduleId) ?>.csv">Download CSV</button>
            </div>
        </div>
        <form method="post" action="<?= project_url('app/ajax/attendance_ajax.php') ?>" data-ajax-form>
            <input type="hidden" name="schedule_id" value="<?= e((string)$scheduleId) ?>">
            <div class="overflow-x-auto">
                <table id="attendance-export-table" class="w-full text-sm" data-enhance-table="false">
                    <thead class="bg-slate-50"><tr><th class="table-th">Athlete</th><th class="table-th">Status</th><th class="table-th">Remarks</th></tr></thead>
                    <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td class="table-td font-semibold"><?= e($r['student_id'].' - '.$r['last_name'].', '.$r['first_name']) ?></td>
                            <td class="table-td">
                                <?php if ($attendanceReadOnly): ?>
                                    <span class="status-pill status-neutral"><?= e($r['status'] ?: 'Present') ?></span>
                                <?php else: ?>
                                    <select class="form-input" name="attendance[<?= e((string)$r['athlete_id']) ?>][status]"><?php foreach (['Present','Absent','Late','Excused'] as $st): ?><option <?= ($r['status'] ?: 'Present')===$st?'selected':'' ?>><?= e($st) ?></option><?php endforeach; ?></select>
                                <?php endif; ?>
                            </td>
                            <td class="table-td">
                                <?php if ($attendanceReadOnly): ?>
                                    <?= e($r['remarks'] ?: '-') ?>
                                <?php else: ?>
                                    <input class="form-input" name="attendance[<?= e((string)$r['athlete_id']) ?>][remarks]" value="<?= e($r['remarks']) ?>">
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$rows): ?><tr><td class="table-td py-10 text-center text-slate-500" colspan="3">No team members assigned to this schedule yet.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 p-4">
                <?php if ($attendanceReadOnly): ?>
                    <p class="rounded-lg bg-slate-50 p-3 text-sm font-semibold text-slate-500">This schedule is completed. Attendance is view-only.</p>
                <?php else: ?>
                    <button class="btn-primary">Save Attendance</button>
                <?php endif; ?>
            </div>
        </form>
    </section>
<?php elseif ($scheduleId): ?>
    <div class="rounded-xl border border-rose-200 bg-rose-50 p-5 text-sm font-semibold text-rose-700">Schedule not found or you do not have access to it.</div>
<?php else: ?>
    <section class="grid min-h-96 place-items-center rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
        <div>
            <h2 class="text-xl font-black text-slate-950">Select a schedule</h2>
            <p class="mt-2 text-sm text-slate-500">Choose a schedule from the calendar or dropdown to start marking attendance.</p>
        </div>
    </section>
<?php endif; ?>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
