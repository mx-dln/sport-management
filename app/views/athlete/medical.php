<?php
require_once __DIR__ . '/../../controllers/MedicalController.php';
require_role(['athlete']);
$pageTitle = 'My Medical Records';
$medical = new MedicalController($pdo);

$stmt = $pdo->prepare('SELECT id, student_id, first_name, last_name FROM athletes WHERE user_id = ? LIMIT 1');
$stmt->execute([(int)current_user()['id']]);
$athlete = $stmt->fetch();
$records = $athlete ? $medical->forAthlete((int)$athlete['id']) : [];

function athlete_medical_value(?string $value): string
{
    return $value !== null && $value !== '' ? e($value) : '<span class="text-slate-400">—</span>';
}

require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72"><?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?><main class="p-4 lg:p-6">
<?php if (!$athlete): ?>
    <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm font-semibold text-amber-800">No athlete profile is linked to your account. Contact the administrator.</div>
<?php else: ?>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-black text-slate-950"><?= e($athlete['first_name'] . ' ' . $athlete['last_name']) ?></h2>
            <p class="text-sm text-slate-500">Athlete ID: <?= e($athlete['student_id']) ?> · <?= e((string)count($records)) ?> medical record(s)</p>
        </div>
    </div>

    <?php if (!$records): ?>
        <div class="rounded-xl border border-slate-200 bg-white p-10 text-center text-sm text-slate-500 shadow-sm">No medical records on file yet.</div>
    <?php endif; ?>

    <?php foreach ($records as $record): ?>
        <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Medical Record #<?= e((string)$record['id']) ?> · <?= e($record['exam_date'] ?? 'Date not set') ?></p>
                    <h3 class="text-lg font-black text-slate-950">Medical Examination</h3>
                </div>
                <?php if ($record['clearance_status']): ?>
                    <span class="status-pill <?= $record['clearance_status'] === 'Fit to Play' ? 'status-active' : 'status-rejected' ?>"><?= e($record['clearance_status']) ?></span>
                <?php endif; ?>
            </header>
            <div class="grid gap-4 p-5 md:grid-cols-2">
                <section class="rounded-xl border border-slate-200 p-4">
                    <p class="mb-2 text-xs font-black uppercase tracking-wide text-slate-400">Vitals</p>
                    <dl class="space-y-1 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Height</dt><dd class="font-semibold"><?= athlete_medical_value($record['height']) ?></dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Weight</dt><dd class="font-semibold"><?= athlete_medical_value($record['weight']) ?></dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Blood Type</dt><dd class="font-semibold"><?= athlete_medical_value($record['blood_type']) ?></dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Blood Pressure</dt><dd class="font-semibold"><?= athlete_medical_value($record['blood_pressure']) ?></dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Heart Rate</dt><dd class="font-semibold"><?= athlete_medical_value($record['heart_rate']) ?></dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Physical Fitness</dt><dd class="font-semibold"><?= athlete_medical_value($record['fitness_status']) ?></dd></div>
                    </dl>
                </section>
                <section class="rounded-xl border border-slate-200 p-4">
                    <p class="mb-2 text-xs font-black uppercase tracking-wide text-slate-400">Health Notes</p>
                    <dl class="space-y-1 text-sm">
                        <div><dt class="text-slate-500">Allergies</dt><dd class="font-semibold"><?= athlete_medical_value($record['allergies']) ?></dd></div>
                        <div><dt class="text-slate-500">Existing Medical Conditions</dt><dd class="font-semibold"><?= athlete_medical_value($record['medical_conditions']) ?></dd></div>
                        <div><dt class="text-slate-500">Current Medications</dt><dd class="font-semibold"><?= athlete_medical_value($record['medications']) ?></dd></div>
                        <div><dt class="text-slate-500">Injury History</dt><dd class="font-semibold"><?= athlete_medical_value($record['injury_history']) ?></dd></div>
                        <div><dt class="text-slate-500">Recent Injury</dt><dd class="font-semibold"><?= athlete_medical_value($record['recent_injury']) ?></dd></div>
                    </dl>
                </section>
                <section class="rounded-xl border border-slate-200 p-4">
                    <p class="mb-2 text-xs font-black uppercase tracking-wide text-slate-400">Physician</p>
                    <dl class="space-y-1 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Physician / Medical Officer</dt><dd class="font-semibold"><?= athlete_medical_value($record['physician_name']) ?></dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Remarks</dt><dd class="font-semibold"><?= athlete_medical_value($record['physician_remarks']) ?></dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Next Check-up</dt><dd class="font-semibold"><?= athlete_medical_value($record['next_checkup_date']) ?></dd></div>
                    </dl>
                </section>
                <section class="rounded-xl border border-slate-200 p-4">
                    <p class="mb-2 text-xs font-black uppercase tracking-wide text-slate-400">Medical Certificate</p>
                    <?php if ($record['certificate_path']): ?>
                        <a class="text-sm font-semibold text-blue-600 underline" href="<?= e(app_url($record['certificate_path'])) ?>" data-attachment-preview data-attachment-url="<?= e(app_url($record['certificate_path'])) ?>" data-attachment-name="<?= e($record['certificate_name'] ?: 'Medical Certificate') ?>"><?= e($record['certificate_name']) ?></a>
                    <?php else: ?>
                        <p class="text-sm text-slate-400">No certificate uploaded.</p>
                    <?php endif; ?>
                </section>
            </div>
        </section>
    <?php endforeach; ?>
<?php endif; ?>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
