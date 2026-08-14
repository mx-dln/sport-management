<?php
require_once __DIR__ . '/../app/controllers/AuthController.php';

$controller = new AuthController($pdo);
if (is_post()) {
    $controller->registerAthlete($_POST, $_FILES);
}
$sports = $controller->sports();
$requirements = $controller->requirements();
$pageTitle = 'Athlete Registration';
require __DIR__ . '/../app/includes/header.php';
?>
<main class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-slate-100 px-4 py-8">
    <section class="mx-auto w-full max-w-4xl rounded-2xl border border-slate-200 bg-white p-6 shadow-xl lg:p-8">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <?php if (app_icon_url()): ?>
                    <img class="mb-3 h-12 w-12 rounded-2xl object-cover" src="<?= e(app_icon_url()) ?>" alt="<?= e(app_setting('app_name')) ?> icon">
                <?php else: ?>
                    <div class="mb-3 grid h-12 w-12 place-items-center rounded-2xl text-lg font-black text-white" style="background: var(--theme-color);"><?= e(substr(app_setting('app_short_name', 'SM'), 0, 4)) ?></div>
                <?php endif; ?>
                <h1 class="text-2xl font-bold">Athlete Registration</h1>
                <p class="mt-1 text-sm text-slate-500">Complete your account, profile, and requirement documents.</p>
            </div>
            <a class="text-sm font-semibold text-blue-600 hover:text-blue-700" href="<?= e(app_url('login.php')) ?>">Back to login</a>
        </div>
        <?php require __DIR__ . '/../app/includes/alerts.php'; ?>
        <div class="grid grid-cols-3 gap-2 text-center text-xs font-semibold">
            <button type="button" class="register-step-tab rounded-lg bg-blue-600 px-2 py-2 text-white" data-step-target="1">1. Account</button>
            <button type="button" class="register-step-tab rounded-lg bg-slate-100 px-2 py-2 text-slate-600" data-step-target="2">2. Profile</button>
            <button type="button" class="register-step-tab rounded-lg bg-slate-100 px-2 py-2 text-slate-600" data-step-target="3">3. Documents</button>
        </div>
        <form method="post" enctype="multipart/form-data" class="mt-6" data-validate id="athlete-register-form">
            <input type="hidden" name="auth_action" value="register_athlete">
            <section class="register-step grid gap-3 sm:grid-cols-2" data-step="1">
                <label class="block">
                    <span class="text-sm font-medium">Student ID</span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="student_id" required>
                </label>
                <label class="block">
                    <span class="text-sm font-medium">Email</span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" type="email" name="email" required>
                </label>
                <label class="block">
                    <span class="text-sm font-medium">Password</span>
                    <div class="mt-1 flex overflow-hidden rounded-lg border border-slate-300 focus-within:border-blue-500">
                        <input class="w-full border-0 px-3 py-2 focus:outline-none" type="password" name="password" required minlength="6" data-password-field>
                        <button class="border-l border-slate-300 px-3 text-sm font-semibold text-slate-600 hover:bg-slate-50" type="button" data-password-toggle>Show</button>
                    </div>
                </label>
                <label class="block">
                    <span class="text-sm font-medium">Confirm Password</span>
                    <div class="mt-1 flex overflow-hidden rounded-lg border border-slate-300 focus-within:border-blue-500">
                        <input class="w-full border-0 px-3 py-2 focus:outline-none" type="password" name="confirm_password" required minlength="6" data-password-field>
                        <button class="border-l border-slate-300 px-3 text-sm font-semibold text-slate-600 hover:bg-slate-50" type="button" data-password-toggle>Show</button>
                    </div>
                </label>
            </section>
            <section class="register-step hidden grid gap-3 sm:grid-cols-2" data-step="2">
                <label class="block">
                    <span class="text-sm font-medium">First Name</span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="first_name" required>
                </label>
                <label class="block">
                    <span class="text-sm font-medium">Last Name</span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="last_name" required>
                </label>
                <label class="block">
                    <span class="text-sm font-medium">Middle Name</span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="middle_name">
                </label>
                <label class="block">
                    <span class="text-sm font-medium">Contact Number</span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="contact_number" placeholder="09XXXXXXXXX">
                </label>
                <label class="block">
                    <span class="text-sm font-medium">Gender</span>
                    <select class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="gender">
                        <option value="">Select gender</option>
                        <option>Male</option>
                        <option>Female</option>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-medium">Birthdate</span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" type="date" name="birthdate">
                </label>
                <label class="block">
                    <span class="text-sm font-medium">Course</span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="course">
                </label>
                <label class="block">
                    <span class="text-sm font-medium">Year Level</span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="year_level">
                </label>
                <label class="block">
                    <span class="text-sm font-medium">Section</span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="section">
                </label>
                <label class="block">
                    <span class="text-sm font-medium">Guardian Name</span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="guardian_name">
                </label>
                <label class="block">
                    <span class="text-sm font-medium">Guardian Contact</span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="guardian_contact">
                </label>
                <label class="block">
                    <span class="text-sm font-medium">Emergency Contact</span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="emergency_contact">
                </label>
                <label class="block">
                    <span class="text-sm font-medium">Height</span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="height" placeholder="175 cm">
                </label>
                <label class="block">
                    <span class="text-sm font-medium">Weight</span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="weight" placeholder="68 kg">
                </label>
                <label class="block">
                    <span class="text-sm font-medium">Blood Type</span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="blood_type">
                </label>
                <label class="block">
                    <span class="text-sm font-medium">Medical Condition</span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="medical_condition" placeholder="None">
                </label>
                <label class="block">
                    <span class="text-sm font-medium">Sport</span>
                    <select class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="sport_id">
                        <option value="">Select later</option>
                        <?php foreach ($sports as $sport): ?>
                            <option value="<?= e((string)$sport['id']) ?>"><?= e($sport['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <div class="rounded-lg bg-slate-50 p-3 text-sm text-slate-500">Team assignment is handled by the sports office or coach after registration.</div>
                <label class="block">
                    <span class="text-sm font-medium">Position</span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="position">
                </label>
                <label class="block sm:col-span-2">
                    <span class="text-sm font-medium">Address</span>
                    <textarea class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="address" rows="2"></textarea>
                </label>
            </section>
            <section class="register-step hidden" data-step="3">
                <div class="mb-3 rounded-lg bg-blue-50 p-3 text-sm text-blue-800">Upload available documents now, or skip and complete them later from the athlete dashboard.</div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <?php foreach ($requirements as $requirement): ?>
                        <label class="block rounded-lg border border-slate-200 p-3">
                            <span class="flex items-center justify-between gap-3 text-sm font-semibold">
                                <span><?= e($requirement['title']) ?></span>
                                <span class="text-xs <?= $requirement['is_required'] ? 'text-red-600' : 'text-slate-400' ?>"><?= $requirement['is_required'] ? 'Required' : 'Optional' ?></span>
                            </span>
                            <?php if (!empty($requirement['description'])): ?>
                                <span class="mt-1 block text-xs text-slate-500"><?= e($requirement['description']) ?></span>
                            <?php endif; ?>
                            <input class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none" type="file" name="documents[<?= e((string)$requirement['id']) ?>]" accept=".pdf,.jpg,.jpeg,.png">
                        </label>
                    <?php endforeach; ?>
                </div>
            </section>
            <div class="mt-6 flex items-center justify-between gap-3">
                <button type="button" class="register-prev hidden rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Back</button>
                <button type="button" class="register-next ml-auto rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Next</button>
                <button class="register-submit hidden rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Submit Registration</button>
            </div>
        </form>
    </section>
</main>
<script>
let registerStep = 1;

function showRegisterStep(step) {
    registerStep = Math.max(1, Math.min(3, step));
    document.querySelectorAll('.register-step').forEach((panel) => {
        panel.classList.toggle('hidden', Number(panel.dataset.step) !== registerStep);
    });
    document.querySelectorAll('.register-step-tab').forEach((tab) => {
        const active = Number(tab.dataset.stepTarget) === registerStep;
        tab.classList.toggle('bg-blue-600', active);
        tab.classList.toggle('text-white', active);
        tab.classList.toggle('bg-slate-100', !active);
        tab.classList.toggle('text-slate-600', !active);
    });
    document.querySelector('.register-prev').classList.toggle('hidden', registerStep === 1);
    document.querySelector('.register-next').classList.toggle('hidden', registerStep === 3);
    document.querySelector('.register-submit').classList.toggle('hidden', registerStep !== 3);
}

function currentStepIsValid() {
    const fields = document.querySelectorAll(`.register-step[data-step="${registerStep}"] input, .register-step[data-step="${registerStep}"] select`);
    return Array.from(fields).every((field) => field.reportValidity());
}

document.querySelector('.register-next').addEventListener('click', () => {
    if (currentStepIsValid()) showRegisterStep(registerStep + 1);
});
document.querySelector('.register-prev').addEventListener('click', () => showRegisterStep(registerStep - 1));
document.querySelectorAll('.register-step-tab').forEach((tab) => {
    tab.addEventListener('click', () => {
        const target = Number(tab.dataset.stepTarget);
        if (target < registerStep || currentStepIsValid()) showRegisterStep(target);
    });
});
showRegisterStep(1);

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const field = button.parentElement.querySelector('[data-password-field]');
        const showing = field.type === 'text';
        field.type = showing ? 'password' : 'text';
        button.textContent = showing ? 'Show' : 'Hide';
    });
});
</script>
</body>
</html>
