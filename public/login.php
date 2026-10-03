<?php
require_once __DIR__ . '/../app/controllers/AuthController.php';

$controller = new AuthController($pdo);
if (is_post()) {
    $controller->login($_POST);
}
$pageTitle = 'ISU Portal Login';

// Determine if we should show the centralized login form immediately
$requestedPortal = strtolower(trim((string)($_GET['portal'] ?? '')));
$hasError = !empty($_SESSION['flash']);
$showLoginForm = $hasError || !empty($_GET['action']) || in_array($requestedPortal, ['admin', 'coach', 'athlete', 'administrator', 'instructor', 'student', 'login'], true);

$portalLabel = match ($requestedPortal) {
    'admin', 'administrator' => 'Administrator',
    'coach', 'instructor' => 'Coach',
    'athlete', 'student' => 'Athlete',
    default => 'Centralized Portal',
};

$loginBulletinItems = [];
try {
    $annStmt = $pdo->query("SELECT title, body FROM announcements ORDER BY id DESC LIMIT 5");
    foreach ($annStmt->fetchAll() as $ann) {
        $loginBulletinItems[] = '📢 ' . e($ann['title']) . ': ' . e($ann['body']);
    }
} catch (Exception $e) {}

if (empty($loginBulletinItems)) {
    $loginBulletinItems[] = '🏀 Basketball varsity rosters & schedule updates are active.';
    $loginBulletinItems[] = '📄 Reminder: Please ensure PSA Birth Certificate and Certificate of Grades (COG) are uploaded.';
    $loginBulletinItems[] = '🏆 Academic Year 2026-2027 Sports Program is in full swing.';
}

require __DIR__ . '/../app/includes/header.php';
?>

<style>
.portal-bg {
    position: relative;
    isolation: isolate;
    background-image: url('<?= app_url('assets/images/login.png') ?>');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
}
.portal-bg::before {
    content: '';
    position: absolute;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    background: linear-gradient(135deg, rgba(0, 103, 56, 0.36), rgba(15, 23, 42, 0.2));
}
html.dark .portal-bg::before {
    background: linear-gradient(135deg, rgba(2, 6, 23, 0.72), rgba(0, 103, 56, 0.34));
}
.portal-bg > * {
    position: relative;
    z-index: 1;
}
.portal-topbar {
    background: rgba(255, 255, 255, 0.96) !important;
    border-color: rgba(226, 232, 240, 0.9) !important;
    color: #0f172a !important;
    backdrop-filter: blur(10px);
}
html.dark .portal-topbar {
    background: rgba(15, 23, 42, 0.94) !important;
    border-color: rgba(51, 65, 85, 0.9) !important;
    color: #f8fafc !important;
}
.portal-topbar .portal-brand {
    color: #006738 !important;
}
html.dark .portal-topbar .portal-brand {
    color: #86efac !important;
}
.portal-login-bulletin {
    color: #475569 !important;
}
html.dark .portal-login-bulletin {
    color: #cbd5e1 !important;
}
.portal-login-card {
    background: rgba(255, 255, 255, 0.94) !important;
    border-color: rgba(255, 255, 255, 0.9) !important;
    color: #0f172a !important;
}
html.dark .portal-login-card {
    background: rgba(15, 23, 42, 0.92) !important;
    border-color: #1e293b !important;
    color: #f8fafc !important;
}
html:not(.dark) .portal-login-card .form-input,
html:not(.dark) .portal-login-card input:not([type="checkbox"]):not([type="radio"]) {
    background: #ffffff !important;
    border-color: #cbd5e1 !important;
    color: #0f172a !important;
}
html:not(.dark) .portal-login-card input::placeholder {
    color: #94a3b8 !important;
}
.portal-login-footer {
    background: transparent !important;
    border-color: transparent !important;
    color: rgba(255, 255, 255, 0.94) !important;
}
.portal-pill-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 0.68rem 1.25rem;
    font-size: 0.95rem;
    font-weight: 700;
    color: #ffffff;
    background-color: #236329;
    border-radius: 9999px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
    transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
    text-decoration: none;
    border: none;
    cursor: pointer;
}
.portal-pill-btn:hover {
    background-color: #2c7a33;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    color: #ffffff;
}
.portal-pill-btn:active {
    transform: scale(0.98);
}
body > footer.no-print {
    display: none !important;
}
</style>

<div class="portal-bg relative flex min-h-screen flex-col justify-between">
    <!-- Top Bar (Matching Image 2 green banner) -->
    <header class="portal-topbar no-print z-10 border-b px-4 py-2 text-xs font-semibold shadow-xs">
        <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex shrink-0 items-center gap-2">
                <span class="portal-brand tracking-wide">ISU CAUAYAN</span>
                <span class="text-slate-300 dark:text-slate-600">|</span>
                <span class="text-slate-700 dark:text-slate-200">Sports Management Information System</span>
            </div>
            <div class="flex min-w-0 flex-1 items-center gap-3 lg:max-w-4xl">
                <span class="shrink-0 rounded-md bg-blue-600 px-2 py-0.5 text-[10px] font-black uppercase tracking-wider text-white shadow-xs">
                    Bulletin
                </span>
                <div class="portal-login-bulletin smis-roll-bar min-w-0 flex-1 text-xs">
                    <div class="smis-marquee-track gap-8">
                        <?php foreach ($loginBulletinItems as $item): ?>
                            <span class="font-medium"><?= $item ?></span>
                            <span class="text-slate-300 dark:text-slate-600">•</span>
                        <?php endforeach; ?>
                        <?php foreach ($loginBulletinItems as $item): ?>
                            <span class="font-medium"><?= $item ?></span>
                            <span class="text-slate-300 dark:text-slate-600">•</span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button class="shrink-0 rounded border border-slate-200 bg-white px-2 py-0.5 text-[10px] font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700" type="button" data-patch-open>
                    Notes
                </button>
            </div>
        </div>
    </header>

    <!-- Main Container: Centered Card -->
    <main class="flex flex-1 items-center justify-center p-4">
        <section class="portal-login-card w-full max-w-[370px] rounded-2xl border border-white/80 bg-white/90 p-6 shadow-2xl backdrop-blur-md transition-all duration-300 dark:border-slate-800 dark:bg-slate-900/90 sm:p-7">
            
            <!-- Branding Header (Matching Image 2) -->
            <div class="mb-5 text-center">
                <?php if (app_icon_url()): ?>
                    <img class="mx-auto h-20 w-20 object-contain drop-shadow-md" src="<?= e(app_icon_url()) ?>" alt="ISU Logo">
                <?php else: ?>
                    <div class="mx-auto grid h-20 w-20 place-items-center rounded-full bg-[#006738] text-xl font-black text-white shadow-md">ISU</div>
                <?php endif; ?>
                <h1 class="mt-3 text-lg font-black tracking-tight text-slate-900 dark:text-white">
                    Isabela State University
                </h1>
                <p class="mt-0.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                    Cauayan City, Isabela
                </p>
            </div>

            <!-- View 1: Portal Role Selection (Default State Matching Image 2) -->
            <div id="portal-select-view" class="<?= $showLoginForm ? 'hidden' : 'block' ?> space-y-3">
                <button type="button" class="portal-pill-btn" onclick="openPortalLogin('Administrator')">
                    Administrator
                </button>
                <button type="button" class="portal-pill-btn" onclick="openPortalLogin('Coach')">
                    Coach
                </button>
                <button type="button" class="portal-pill-btn" onclick="openPortalLogin('Athlete')">
                    Athlete
                </button>
                <a class="flex w-full items-center justify-center rounded-full border border-emerald-700 bg-white/90 px-5 py-2.5 text-sm font-black text-emerald-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-50 hover:text-emerald-900 dark:border-emerald-500/60 dark:bg-slate-950/70 dark:text-emerald-300 dark:hover:bg-emerald-950" href="<?= e(app_url('register.php')) ?>">
                    Athlete Sign Up
                </a>
            </div>

            <!-- View 2: Centralized Login Form (Revealed upon selecting role) -->
            <div id="portal-login-view" class="<?= $showLoginForm ? 'block' : 'hidden' ?>">
                <div class="mb-4 flex items-center justify-between border-b border-slate-200/80 pb-2.5 dark:border-slate-800">
                    <span id="portal-selected-badge" class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-900 dark:bg-emerald-950 dark:text-emerald-300">
                        <?= e($portalLabel) ?> Sign In
                    </span>
                    <button type="button" onclick="showPortalSelection()" class="text-xs font-bold text-slate-500 hover:text-slate-900 dark:hover:text-white transition">
                        ← Change Portal
                    </button>
                </div>

                <?php require __DIR__ . '/../app/includes/alerts.php'; ?>

                <form method="post" action="<?= e(app_url('login.php')) ?>" class="space-y-3.5" data-validate>
                    <label class="block text-left">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Institutional Email</span>
                        <input id="login-email" class="form-input mt-1 text-sm" type="email" name="email" placeholder="user@isu.edu.ph" required autofocus>
                    </label>
                    <label class="block text-left">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Password</span>
                        <div class="mt-1 flex overflow-hidden rounded-lg border border-slate-300 bg-white focus-within:border-emerald-600 dark:border-slate-700 dark:bg-slate-950">
                            <input id="login-password" class="w-full border-0 bg-transparent px-3 py-2 text-sm text-slate-900 focus:outline-none dark:text-white" type="password" name="password" placeholder="••••••••" required data-password-field>
                            <button class="border-l border-slate-300 px-3 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800" type="button" data-password-toggle>Show</button>
                        </div>
                    </label>
                    
                    <button class="portal-pill-btn mt-4 w-full shadow-md" type="submit">
                        Sign In
                    </button>
                </form>
            </div>

        </section>
    </main>

    <!-- Footer Copyright -->
    <footer class="portal-login-footer no-print py-2 text-center text-xs text-white/90 drop-shadow-sm">
        &copy; <?= date('Y') ?> Isabela State University - Sports Management Information System
    </footer>
</div>

<!-- Patch Notes Modal -->
<div class="fixed inset-0 z-50 hidden bg-slate-950/70 p-4 backdrop-blur-sm" data-patch-modal>
    <div class="mx-auto flex min-h-full max-w-3xl items-center">
        <section class="max-h-[86vh] w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900">
            <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-blue-600 dark:text-blue-400"><?= e(app_setting('app_short_name', 'SMIS')) ?></p>
                    <h2 class="text-xl font-black text-slate-950 dark:text-white">Patch Notes</h2>
                    <p class="mt-1 text-xs font-semibold text-slate-500">Current version: v<?= e(app_version()) ?></p>
                </div>
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300" type="button" data-patch-close>Close</button>
            </header>
            <div class="max-h-[70vh] overflow-y-auto p-5 text-sm text-slate-700 dark:text-slate-300">
                <section class="mb-5 rounded-2xl border border-blue-100 bg-blue-50/80 p-4 dark:border-slate-800 dark:bg-slate-800/50">
                    <p class="text-xs font-bold uppercase tracking-wide text-blue-700 dark:text-blue-400">Current Development Build</p>
                    <h3 class="mt-1 text-2xl font-black text-slate-950 dark:text-white">v<?= e(app_version()) ?></h3>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Features official SCUAA Form 2 gallery table with dual university &amp; games emblems, enhanced light &amp; dark mode, and unified ISU SIAS portal styling.</p>
                </section>
                <div class="space-y-4">
                    <div class="rounded-xl border border-slate-200 p-3.5 dark:border-slate-800">
                        <h4 class="font-bold text-slate-900 dark:text-white">SCUAA Form 2 Official Entry Form &amp; Gallery</h4>
                        <p class="text-xs text-slate-500 mt-1">Exact 7-column athlete table structure per row, photo frames, bold uppercase format, and landscape printing.</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 p-3.5 dark:border-slate-800">
                        <h4 class="font-bold text-slate-900 dark:text-white">Harmonized Light &amp; Dark Theme</h4>
                        <p class="text-xs text-slate-500 mt-1">Configured class-level theme control across all tables, cards, toolbars, and scrollbars.</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
function openPortalLogin(role) {
    document.getElementById('portal-select-view').classList.add('hidden');
    document.getElementById('portal-login-view').classList.remove('hidden');
    const badge = document.getElementById('portal-selected-badge');
    if (badge) {
        badge.textContent = role + ' Sign In';
    }
    const emailField = document.getElementById('login-email');
    if (emailField) {
        emailField.focus();
    }
}

function showPortalSelection() {
    document.getElementById('portal-login-view').classList.add('hidden');
    document.getElementById('portal-select-view').classList.remove('hidden');
}

document.addEventListener('DOMContentLoaded', () => {
    // Show patch notes modal toggle
    document.querySelector('[data-patch-open]')?.addEventListener('click', () => {
        document.querySelector('[data-patch-modal]')?.classList.remove('hidden');
    });
    document.querySelector('[data-patch-close]')?.addEventListener('click', () => {
        document.querySelector('[data-patch-modal]')?.classList.add('hidden');
    });
    document.querySelector('[data-patch-modal]')?.addEventListener('click', (e) => {
        if (e.target.matches('[data-patch-modal]')) {
            e.currentTarget.classList.add('hidden');
        }
    });
});
</script>

<?php require __DIR__ . '/../app/includes/footer.php'; ?>
