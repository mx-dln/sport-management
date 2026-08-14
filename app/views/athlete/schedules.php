<?php
require_once __DIR__ . '/../../controllers/ScheduleController.php';
require_role(['athlete']);
$pageTitle = 'My Training Schedule';
$scheduleController = new ScheduleController($pdo);
$scheduleController->autoCompletePastSchedules();
$stmt = $pdo->prepare('SELECT id FROM athletes WHERE user_id=? LIMIT 1');
$stmt->execute([current_user()['id']]);
$athleteId = (int)($stmt->fetchColumn() ?: 0);
$stmt = $pdo->prepare('SELECT ts.*, s.name sport_name, t.name team_name, att.status attendance_status, att.remarks attendance_remarks FROM training_schedules ts JOIN team_members tm ON tm.team_id=ts.team_id JOIN teams t ON t.id=ts.team_id LEFT JOIN sports s ON s.id=ts.sport_id LEFT JOIN attendance att ON att.schedule_id=ts.id AND att.athlete_id=tm.athlete_id WHERE tm.athlete_id=? ORDER BY ts.training_date DESC, ts.start_time');
$stmt->execute([$athleteId]);
$schedules = $stmt->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72"><?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?><main class="p-4 lg:p-6"><div class="grid gap-4 md:grid-cols-2"><?php foreach ($schedules as $s): ?><article class="rounded-xl bg-white p-5 shadow-sm"><div class="flex items-start justify-between gap-3"><h3 class="font-bold"><?= e($s['sport_name'].' - '.$s['team_name']) ?></h3><span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700"><?= e($s['status'] ?? 'Scheduled') ?></span></div><p class="mt-2 text-sm"><?= e($s['training_date'].' '.format_time_range($s['start_time'] ?? '', $s['end_time'] ?? '')) ?></p><p class="text-sm text-slate-500"><?= e($s['venue']) ?></p><div class="mt-4 grid gap-3 rounded-lg bg-slate-50 p-3 text-sm sm:grid-cols-2"><div><p class="text-xs font-bold uppercase text-slate-400">Attendance Status</p><p class="mt-1 font-semibold text-slate-900"><?= e($s['attendance_status'] ?? '') ?></p></div><div><p class="text-xs font-bold uppercase text-slate-400">Remarks</p><p class="mt-1 text-slate-700"><?= e($s['attendance_remarks'] ?? '') ?></p></div></div></article><?php endforeach; ?><?php if (!$schedules): ?><section class="rounded-xl bg-white p-8 text-center text-sm text-slate-500 md:col-span-2">No training schedules yet.</section><?php endif; ?></div><?php require __DIR__ . '/../../includes/footer.php'; ?>
