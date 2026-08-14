<?php
require_once __DIR__ . '/../../controllers/AthleteController.php';
require_once __DIR__ . '/../../controllers/SportController.php';
require_once __DIR__ . '/../../controllers/AthleteHistoryController.php';
require_role(['athlete']);
$pageTitle = 'My Profile';
$stmt = $pdo->prepare('SELECT * FROM athletes WHERE user_id=? LIMIT 1');
$stmt->execute([current_user()['id']]);
$athlete = $stmt->fetch();
$sports = (new SportController($pdo))->all();
$historyStats = $athlete ? (new AthleteHistoryController($pdo))->stats((int)$athlete['id']) : [];
require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72"><?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?>
<main class="p-4 lg:p-6">
<?php require __DIR__ . '/../../includes/alerts.php'; ?>
<?php if (!$athlete): ?>
    <div class="rounded-xl bg-white p-5 shadow-sm">Your athlete biodata is not yet linked. Please contact the sports office.</div>
<?php else: ?>
    <section class="mb-6 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-black text-slate-950">Edit Biodata</h2>
            <p class="mt-1 text-sm text-slate-500">Update the information shown on your athlete profile and printable biodata.</p>
        </div>
        <a class="btn-primary text-center" href="<?= e(app_url('index.php?page=athlete_print&id=' . $athlete['id'])) ?>">View Printable Profile</a>
    </section>

    <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-black text-slate-950">Athletic Achievements</h2>
                <p class="mt-1 text-sm text-slate-500">Your previous competitions, medals, and placings.</p>
            </div>
            <a class="btn-muted text-center" href="<?= e(app_url('index.php?page=history')) ?>">Manage Athletic History</a>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4 xl:grid-cols-8">
            <?php
            $profileStatCards = [
                ['Competitions', $historyStats['total'] ?? 0, '🏆'],
                ['Gold', $historyStats['gold'] ?? 0, '🥇'],
                ['Silver', $historyStats['silver'] ?? 0, '🥈'],
                ['Bronze', $historyStats['bronze'] ?? 0, '🥉'],
                ['Total Medals', $historyStats['medals'] ?? 0, '🏅'],
                ['1st Place', $historyStats['first_place'] ?? 0, '🥇'],
                ['2nd Place', $historyStats['second_place'] ?? 0, '🥈'],
                ['3rd Place', $historyStats['third_place'] ?? 0, '🥉'],
            ];
            ?>
            <?php foreach ($profileStatCards as [$label, $value, $emoji]): ?>
                <div class="rounded-xl bg-slate-50 p-3 text-center">
                    <p class="text-lg"><?= e($emoji) ?></p>
                    <p class="text-xl font-black text-slate-950"><?= e((string)$value) ?></p>
                    <p class="text-xs font-semibold text-slate-500"><?= e($label) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <form class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" method="post" enctype="multipart/form-data" action="<?= project_url('app/ajax/athlete_ajax.php') ?>" data-ajax-form data-validate>
        <input type="hidden" name="id" value="<?= e((string)$athlete['id']) ?>">
        <input type="hidden" name="user_id" value="<?= e((string)current_user()['id']) ?>">
        <input type="hidden" name="athlete_status" value="<?= e($athlete['athlete_status'] ?: 'Active') ?>">
        <div class="grid gap-4 md:grid-cols-3">
            <label class="block">
                <span class="text-sm font-medium">Student ID</span>
                <input class="form-input mt-1" name="student_id" required value="<?= e($athlete['student_id']) ?>">
            </label>
            <label class="block">
                <span class="text-sm font-medium">First Name</span>
                <input class="form-input mt-1" name="first_name" required value="<?= e($athlete['first_name']) ?>">
            </label>
            <label class="block">
                <span class="text-sm font-medium">Middle Name</span>
                <input class="form-input mt-1" name="middle_name" value="<?= e($athlete['middle_name']) ?>">
            </label>
            <label class="block">
                <span class="text-sm font-medium">Last Name</span>
                <input class="form-input mt-1" name="last_name" required value="<?= e($athlete['last_name']) ?>">
            </label>
            <label class="block">
                <span class="text-sm font-medium">Gender</span>
                <select class="form-input mt-1" name="gender">
                    <option value="">Select gender</option>
                    <?php foreach (['Male','Female'] as $gender): ?><option <?= $athlete['gender'] === $gender ? 'selected' : '' ?>><?= e($gender) ?></option><?php endforeach; ?>
                </select>
            </label>
            <label class="block">
                <span class="text-sm font-medium">Birthdate</span>
                <input class="form-input mt-1" type="date" name="birthdate" value="<?= e($athlete['birthdate']) ?>">
            </label>
            <?php foreach (['course'=>'Course','year_level'=>'Year Level','section'=>'Section','contact_number'=>'Contact Number','guardian_name'=>'Guardian Name','guardian_contact'=>'Guardian Contact','emergency_contact'=>'Emergency Contact','height'=>'Height','weight'=>'Weight','blood_type'=>'Blood Type','medical_condition'=>'Medical Condition','position'=>'Position'] as $name=>$label): ?>
                <label class="block">
                    <span class="text-sm font-medium"><?= e($label) ?></span>
                    <input class="form-input mt-1" name="<?= e($name) ?>" value="<?= e($athlete[$name]) ?>">
                </label>
            <?php endforeach; ?>
            <label class="block">
                <span class="text-sm font-medium">Sport</span>
                <select class="form-input mt-1" name="sport_id">
                    <option value="">Select sport</option>
                    <?php foreach ($sports as $sport): ?><option value="<?= e((string)$sport['id']) ?>" <?= (int)$athlete['sport_id'] === (int)$sport['id'] ? 'selected' : '' ?>><?= e($sport['name']) ?></option><?php endforeach; ?>
                </select>
            </label>
            <div class="rounded-lg bg-slate-50 p-3 text-sm text-slate-500">Team assignment is managed by the sports office or your coach.</div>
            <label class="block">
                <span class="text-sm font-medium">Profile Photo / 2x2</span>
                <input class="form-input mt-1" type="file" name="profile_photo" accept=".jpg,.jpeg,.png">
            </label>
            <label class="block md:col-span-3">
                <span class="text-sm font-medium">Address</span>
                <textarea class="form-input mt-1" name="address" rows="3"><?= e($athlete['address']) ?></textarea>
            </label>
        </div>
        <div class="mt-5 flex justify-end">
            <button class="btn-primary" type="submit">Save Profile</button>
        </div>
    </form>
<?php endif; ?>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
