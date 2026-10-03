<?php
$user = current_user();
$userRole = $user['role'] ?? '';

// Fetch active announcements / ticker items for the Roll Bar
$rollBarItems = [];
try {
    $annStmt = $pdo->query("SELECT title, body FROM announcements ORDER BY id DESC LIMIT 5");
    $annList = $annStmt->fetchAll();
    foreach ($annList as $ann) {
        $rollBarItems[] = '📢 ' . e($ann['title']) . ': ' . e(mb_strimwidth($ann['body'], 0, 75, '...'));
    }
} catch (Exception $e) {}

if (empty($rollBarItems)) {
    $rollBarItems[] = '🏀 Basketball varsity rosters & schedule updates are active.';
    $rollBarItems[] = '📄 Reminder: Please ensure PSA Birth Certificate and Certificate of Grades (COG) are uploaded.';
    $rollBarItems[] = '🏆 Academic Year 2026-2027 Sports Program is in full swing.';
}
?>
<header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur dark:bg-slate-900/95 dark:border-slate-800">
    <div class="flex h-16 items-center justify-between px-4 lg:px-6">
        <div class="flex items-center gap-3">
            <button class="rounded-lg border border-slate-200 px-3 py-2 text-sm lg:hidden dark:border-slate-700 dark:text-slate-200" data-sidebar-toggle>
                ☰
            </button>
            <div class="hidden md:block">
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-600 dark:text-blue-400">
                    <?= e(school_name()) ?>
                </p>
                <h1 class="text-lg font-bold text-slate-950 dark:text-white">
                    <?= e($pageTitle ?? 'Dashboard') ?>
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-2 sm:gap-3 text-sm">
            <?php if (in_array($userRole, ['admin', 'sports_coordinator'], true)): ?>
                <div class="relative">
                    <button class="notification-toggle relative inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700" type="button" title="Document notifications" data-notification-toggle>
                        <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <span class="absolute -right-1 -top-1 hidden min-w-5 rounded-full bg-rose-600 px-1.5 py-0.5 text-center text-[10px] font-black text-white" data-doc-notification-badge><span data-doc-notification-count>0</span></span>
                    </button>
                    <section class="notification-panel absolute right-0 top-12 z-[85] hidden w-[min(380px,calc(100vw-2rem))] overflow-hidden rounded-2xl border shadow-2xl dark:bg-slate-900 dark:border-slate-800" data-notification-panel>
                        <div class="notification-panel-header flex items-center justify-between border-b px-5 py-4 dark:border-slate-800">
                            <h2 class="text-xl font-black text-slate-950 dark:text-white">Notifications</h2>
                            <a class="text-xs font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400" href="<?= e(app_url('index.php?page=documents')) ?>">View documents</a>
                        </div>
                        <div class="max-h-[70vh] overflow-y-auto p-3" data-notification-list>
                            <div class="space-y-3 p-2" data-notification-loading>
                                <?php for ($i = 0; $i < 6; $i++): ?>
                                    <div class="flex items-center gap-3">
                                        <span class="notification-skeleton-avatar h-12 w-12 rounded-full bg-slate-200 dark:bg-slate-800"></span>
                                        <span class="notification-skeleton-line h-3 flex-1 rounded-full bg-slate-200 dark:bg-slate-800"></span>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </section>
                </div>
            <?php endif; ?>

            <button class="theme-toggle inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" type="button" data-theme-toggle onclick="toggleTheme(event)" aria-label="Toggle dark mode">
                <svg class="theme-icon-moon h-4 w-4" data-theme-moon aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12.8A9 9 0 1 1 11.2 3 7 7 0 0 0 21 12.8Z"></path>
                </svg>
                <svg class="theme-icon-sun hidden h-4 w-4" data-theme-sun aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="4"></circle>
                    <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"></path>
                </svg>
                <span class="hidden sm:inline" data-theme-label>Dark</span>
            </button>
            <span class="hidden text-slate-600 sm:inline dark:text-slate-300"><?= e($user['name'] ?? 'Guest') ?></span>
            <a class="rounded-lg bg-slate-900 px-3 py-2 font-medium text-white hover:bg-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700" href="<?= app_url('logout.php') ?>">Logout</a>
        </div>
    </div>

    <!-- ===== NO-SCROLL BANNER & COMMAND BUTTONS & ROLL BAR ===== -->
    <div class="no-print smis-no-scroll-banner border-t border-slate-200/80 px-4 py-2 lg:px-6 dark:border-slate-800">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <!-- Left: Roll Bar (Marquee Ticker) -->
            <div class="flex min-w-0 flex-1 items-center gap-3">
                <span class="shrink-0 rounded-md bg-blue-600 px-2 py-0.5 text-[10px] font-black uppercase tracking-wider text-white shadow-xs">
                    Bulletin
                </span>
                <div class="smis-roll-bar min-w-0 flex-1 text-xs text-slate-600 dark:text-slate-300">
                    <div class="smis-marquee-track gap-8">
                        <?php foreach ($rollBarItems as $item): ?>
                            <span class="font-medium"><?= $item ?></span>
                            <span class="text-slate-300 dark:text-slate-600">•</span>
                        <?php endforeach; ?>
                        <!-- Repeat track once for seamless continuous loop -->
                        <?php foreach ($rollBarItems as $item): ?>
                            <span class="font-medium"><?= $item ?></span>
                            <span class="text-slate-300 dark:text-slate-600">•</span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Right: Command Buttons -->
            <div class="flex shrink-0 items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
                <?php if (in_array($userRole, ['admin', 'sports_coordinator', 'coach'], true)): ?>
                    <a href="<?= e(app_url('index.php?page=teams&sport_select=basketball')) ?>" class="smis-cmd-btn" title="Create or View Basketball Teams">
                        <span>🏀</span>
                        <span>Form Team</span>
                    </a>
                    <button type="button" class="smis-cmd-btn" data-modal-open="#construct-message-modal" title="Draft SMS or Announcement">
                        <span>✉️</span>
                        <span>Construct Message</span>
                    </button>
                    <button type="button" class="smis-cmd-btn" data-modal-open="#scan-upload-modal" title="Scan PSA Birth Certificate or COG">
                        <span>📄</span>
                        <span>Scan / Upload</span>
                    </button>
                    <a href="<?= e(app_url('index.php?page=reports#athlete-report')) ?>" class="smis-cmd-btn" title="Print Masterlist">
                        <span>🖨️</span>
                        <span>Print Masterlist</span>
                    </a>
                <?php else: ?>
                    <button type="button" class="smis-cmd-btn" data-modal-open="#scan-upload-modal" title="Scan or Upload PSA Birth Certificate / COG">
                        <span>📄</span>
                        <span>Scan Birth Cert / COG</span>
                    </button>
                    <a href="<?= e(app_url('index.php?page=history')) ?>" class="smis-cmd-btn">
                        <span>🏆</span>
                        <span>Athletic History</span>
                    </a>
                    <a href="<?= e(app_url('index.php?page=schedules')) ?>" class="smis-cmd-btn">
                        <span>📅</span>
                        <span>My Schedule</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<?php require_once __DIR__ . '/command_modals.php'; ?>
