<?php
require_once __DIR__ . '/../app/controllers/AuthController.php';

$controller = new AuthController($pdo);
if (is_post()) {
    $controller->login($_POST);
}
$pageTitle = 'Login';
require __DIR__ . '/../app/includes/header.php';
?>
<main class="relative flex min-h-screen items-center justify-center bg-gradient-to-br from-blue-50 via-white to-slate-100 px-4">
    <button class="absolute right-4 top-4 rounded-lg border border-slate-300 bg-white/90 px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm backdrop-blur hover:bg-slate-50" type="button" data-patch-open>Patch Notes v<?= e(app_version()) ?></button>
    <section class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-xl">
        <div class="mb-6 text-center">
            <?php if (app_icon_url()): ?>
                <img class="mx-auto mb-3 h-14 w-14 rounded-2xl object-cover" src="<?= e(app_icon_url()) ?>" alt="<?= e(app_setting('app_name')) ?> icon">
            <?php else: ?>
                <div class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-2xl text-xl font-black text-white" style="background: var(--theme-color);"><?= e(substr(app_setting('app_short_name', 'SM'), 0, 4)) ?></div>
            <?php endif; ?>
            <h1 class="text-2xl font-bold"><?= e(app_setting('app_name')) ?></h1>
            <p class="mt-1 text-sm text-slate-500">Athlete profiling, documents, schedules, reports, and SMS logs.</p>
        </div>
        <?php require __DIR__ . '/../app/includes/alerts.php'; ?>
        <?php
        $devLogins = [
            ['label' => 'Admin', 'email' => 'admin@sports.test', 'password' => 'admin123'],
            ['label' => 'Coach', 'email' => 'coach@sports.test', 'password' => 'admin123'],
            ['label' => 'Athlete', 'email' => 'athlete@sports.test', 'password' => 'admin123'],
        ];
        ?>
        <form method="post" class="space-y-4" data-validate>
            <label class="block">
                <span class="text-sm font-medium">Email</span>
                <input id="login-email" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" type="email" name="email" required value="admin@sports.test">
            </label>
            <label class="block">
                <span class="text-sm font-medium">Password</span>
                <div class="mt-1 flex overflow-hidden rounded-lg border border-slate-300 focus-within:border-blue-500">
                    <input id="login-password" class="w-full border-0 px-3 py-2 focus:outline-none" type="password" name="password" required value="admin123" data-password-field>
                    <button class="border-l border-slate-300 px-3 text-sm font-semibold text-slate-600 hover:bg-slate-50" type="button" data-password-toggle>Show</button>
                </div>
            </label>
            <button class="w-full rounded-lg px-4 py-2.5 font-semibold text-white" style="background: var(--theme-color);">Sign in</button>
            <div class="border-t border-slate-200 pt-4">
                <p class="mb-2 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Development quick login</p>
                <div class="grid gap-2 sm:grid-cols-3">
                    <?php foreach ($devLogins as $login): ?>
                        <button
                            type="button"
                            class="dev-login rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100"
                            data-email="<?= e($login['email']) ?>"
                            data-password="<?= e($login['password']) ?>"
                        >
                            <?= e($login['label']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </form>
        <div class="mt-4 grid gap-3">
            <a class="block rounded-lg border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50" href="<?= e(app_url('register.php')) ?>">Register as Athlete</a>
            <p class="text-center text-xs text-slate-500">All demo accounts use password: admin123</p>
        </div>
    </section>
</main>
<div class="fixed inset-0 z-50 hidden bg-slate-950/70 p-4 backdrop-blur-sm" data-patch-modal>
    <div class="mx-auto flex min-h-full max-w-3xl items-center">
        <section class="max-h-[86vh] w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-blue-600"><?= e(app_setting('app_short_name', 'SMIS')) ?></p>
                    <h2 class="text-xl font-black text-slate-950">Patch Notes</h2>
                    <p class="mt-1 text-xs font-semibold text-slate-500">Current version: v<?= e(app_version()) ?></p>
                </div>
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" type="button" data-patch-close>Close</button>
            </header>
            <div class="max-h-[70vh] overflow-y-auto p-5">
                <section class="mb-5 rounded-2xl border border-blue-100 bg-blue-50/80 p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-blue-700">Current Development Build</p>
                    <h3 class="mt-1 text-2xl font-black text-slate-950">v<?= e(app_version()) ?></h3>
                    <p class="mt-1 text-sm text-slate-600">This version includes the new medical and competition modules, bulk SMS notifications, and the latest attendance, notification, document, team, report, theme, and biodata improvements.</p>
                </section>
                <div class="space-y-5 text-sm text-slate-700">
                    <section class="rounded-2xl border border-blue-100 bg-blue-50/70 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="font-black text-slate-950">v1.9.0 - Medical and Competition Modules</h3>
                            <span class="rounded-full bg-blue-600 px-3 py-1 text-xs font-black text-white">Latest</span>
                        </div>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-500">August 2, 2026</p>
                        <ul class="mt-3 list-disc space-y-1 pl-5">
                            <li>Added Medical Records module with athlete vitals, health notes, clearance status, and certificate uploads for admin/coordinator.</li>
                            <li>Added My Medical Records view so athletes can see their own medical records.</li>
                            <li>Added Competitions module with competition profiles, status, filters, and athlete participation management.</li>
                            <li>Added competition results with rank, medal, score/time, and result status.</li>
                            <li>Added competition SMS notifications with individual or bulk sending to athletes, coaches, and teams.</li>
                            <li>Added contact numbers to user management for SMS notification use.</li>
                        </ul>
                    </section>
                    <section class="rounded-2xl border border-slate-200 p-4">
                        <h3 class="font-black text-slate-950">v1.8.0 - Attendance Polish</h3>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-500">May 24, 2026</p>
                        <ul class="mt-3 list-disc space-y-1 pl-5">
                            <li>Standardized all schedule and log times to 12-hour AM/PM format.</li>
                            <li>Added attendance CSV download inside the Mark Attendance card.</li>
                            <li>Completed schedules now show attendance as view-only and hide the save button.</li>
                            <li>Athlete schedules now show attendance status and remarks.</li>
                            <li>Cleaned CSV export so selected attendance status exports correctly.</li>
                        </ul>
                    </section>
                    <section class="rounded-2xl border border-slate-200 p-4">
                        <h3 class="font-black text-slate-950">v1.7.0 - Admin Notifications</h3>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-500">May 24, 2026</p>
                        <ul class="mt-3 list-disc space-y-1 pl-5">
                            <li>Added admin/coordinator document upload notifications.</li>
                            <li>Added bell notification panel with recent uploads, unread badge, date/time, and light/dark styling.</li>
                            <li>Added Messenger-style popup notification and `notif.wav` sound playback.</li>
                            <li>Notification clicks now open the uploader's submitted document modal directly.</li>
                            <li>Unread notification badge now persists after page reload until the bell panel is opened.</li>
                        </ul>
                    </section>
                    <section class="rounded-2xl border border-slate-200 p-4">
                        <h3 class="font-black text-slate-950">v1.6.0 - Documents and Requirements</h3>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-500">May 24, 2026</p>
                        <ul class="mt-3 list-disc space-y-1 pl-5">
                            <li>Grouped uploaded documents by athlete with a View Details modal.</li>
                            <li>Added attachment preview modal for images and supported files.</li>
                            <li>Added Defined Requirements list with collapse/expand toggle.</li>
                            <li>Added create, edit, and delete actions for requirements.</li>
                            <li>Protected requirement and document status management for admin/coordinator roles.</li>
                        </ul>
                    </section>
                    <section class="rounded-2xl border border-slate-200 p-4">
                        <h3 class="font-black text-slate-950">v1.5.0 - Teams and Coach Access</h3>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-500">May 18, 2026</p>
                        <ul class="mt-3 list-disc space-y-1 pl-5">
                            <li>Changed team membership to allow athletes in different teams and sports.</li>
                            <li>Restricted athletes from choosing their own team.</li>
                            <li>Added admin-controlled coach assignment per team.</li>
                            <li>Limited coaches to only assigned teams and assigned-team attendance.</li>
                            <li>Moved team members and athlete assignment into compact modals.</li>
                            <li>Prevented already assigned athletes from appearing again in the same team dropdown.</li>
                        </ul>
                    </section>
                    <section class="rounded-2xl border border-slate-200 p-4">
                        <h3 class="font-black text-slate-950">v1.4.0 - Attendance and Scheduling</h3>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-500">May 18, 2026</p>
                        <ul class="mt-3 list-disc space-y-1 pl-5">
                            <li>Added compact attendance calendar beside the attendance table.</li>
                            <li>Fixed schedule selection so it stays on Attendance instead of returning to Dashboard.</li>
                            <li>Automatically marks past schedules as Completed.</li>
                            <li>Saving attendance marks the schedule as Completed unless it is Cancelled.</li>
                            <li>Added schedule access checks for coaches.</li>
                        </ul>
                    </section>
                    <section class="rounded-2xl border border-slate-200 p-4">
                        <h3 class="font-black text-slate-950">v1.3.0 - Reports and Dashboards</h3>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-500">May 17, 2026</p>
                        <ul class="mt-3 list-disc space-y-1 pl-5">
                            <li>Redesigned Admin Reports into a reports center with cards, filters, print, CSV export, and pagination.</li>
                            <li>Redesigned Admin, Coach, and Athlete dashboards with professional cards and summaries.</li>
                            <li>Improved table UI across the app with search, filters, and pagination.</li>
                            <li>Hid admin accounts from the Users list.</li>
                            <li>Moved Add User into a modal.</li>
                        </ul>
                    </section>
                    <section class="rounded-2xl border border-slate-200 p-4">
                        <h3 class="font-black text-slate-950">v1.2.0 - Biodata and Printing</h3>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-500">May 17, 2026</p>
                        <ul class="mt-3 list-disc space-y-1 pl-5">
                            <li>Redesigned printable biodata with professional header, profile card, and print-ready layout.</li>
                            <li>Moved course and address under the profile card.</li>
                            <li>Removed attachments from print output and separated submitted attachments on-screen.</li>
                            <li>Added back and print buttons for biodata view.</li>
                            <li>Connected uploaded 2x2 picture to the biodata profile photo.</li>
                        </ul>
                    </section>
                    <section class="rounded-2xl border border-slate-200 p-4">
                        <h3 class="font-black text-slate-950">v1.1.0 - Settings, Theme, and Navigation</h3>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-500">May 17, 2026</p>
                        <ul class="mt-3 list-disc space-y-1 pl-5">
                            <li>Cleaned URLs to hide direct app view paths and public folder links.</li>
                            <li>Added grouped sidebar navigation with icons.</li>
                            <li>Added system settings for app name, school/client name, theme color, and icon.</li>
                            <li>Added light and dark mode with saved browser preference.</li>
                            <li>Improved dark-mode styling across cards, tables, modals, forms, and notification panels.</li>
                        </ul>
                    </section>
                    <section class="rounded-2xl border border-slate-200 p-4">
                        <h3 class="font-black text-slate-950">v1.0.0 - Registration and Core Setup</h3>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-500">May 17, 2026</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            <li>Added development quick login for Admin, Coach, and Athlete.</li>
                            <li>Added athlete registration wizard with account, biodata, and documents steps.</li>
                            <li>Added show password toggles and registration success notification page.</li>
                            <li>Added athlete edit profile and biodata completion support.</li>
                            <li>Fixed initial route/auth issues and database import guidance.</li>
                            <li>Enabled SMS config support and schedule SMS result messages.</li>
                        </ul>
                    </section>
                </div>
            </div>
        </section>
    </div>
</div>
<script>
document.querySelectorAll('.dev-login').forEach((button) => {
    button.addEventListener('click', () => {
        document.getElementById('login-email').value = button.dataset.email;
        document.getElementById('login-password').value = button.dataset.password;
        button.closest('form').requestSubmit();
    });
});

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const field = button.parentElement.querySelector('[data-password-field]');
        const showing = field.type === 'text';
        field.type = showing ? 'password' : 'text';
        button.textContent = showing ? 'Show' : 'Hide';
    });
});

const patchModal = document.querySelector('[data-patch-modal]');
document.querySelector('[data-patch-open]')?.addEventListener('click', () => {
    patchModal?.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
});
document.querySelectorAll('[data-patch-close]').forEach((button) => {
    button.addEventListener('click', () => {
        patchModal?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    });
});
patchModal?.addEventListener('click', (event) => {
    if (event.target === patchModal) {
        patchModal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
});
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        patchModal?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
});
</script>
</body>
</html>
