<?php $user = current_user(); ?>
<header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur">
    <div class="flex h-16 items-center justify-between px-4 lg:px-6">
        <button class="rounded-lg border border-slate-200 px-3 py-2 text-sm lg:hidden" data-sidebar-toggle>
            ☰
        </button>
        <div class="hidden md:block">
            <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">
                <?= e(school_name()) ?>
            </p>
            <h1 class="text-lg font-bold">
                <?= e($pageTitle ?? 'Dashboard') ?>
            </h1>
        </div>
        <div class="flex items-center gap-3 text-sm">
            <?php if (in_array(($user['role'] ?? ''), ['admin', 'sports_coordinator'], true)): ?>
                <div class="relative">
                    <button class="notification-toggle relative inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200" type="button" title="Document notifications" data-notification-toggle>
                        <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <span class="absolute -right-1 -top-1 hidden min-w-5 rounded-full bg-rose-600 px-1.5 py-0.5 text-center text-[10px] font-black text-white" data-doc-notification-badge><span data-doc-notification-count>0</span></span>
                    </button>
                    <section class="notification-panel absolute right-0 top-12 z-[85] hidden w-[min(380px,calc(100vw-2rem))] overflow-hidden rounded-2xl border shadow-2xl" data-notification-panel>
                        <div class="notification-panel-header flex items-center justify-between border-b px-5 py-4">
                            <h2 class="text-xl font-black">Notifications</h2>
                            <a class="text-xs font-bold text-blue-600 hover:text-blue-700" href="<?= e(app_url('index.php?page=documents')) ?>">View documents</a>
                        </div>
                        <div class="max-h-[70vh] overflow-y-auto p-3" data-notification-list>
                            <div class="space-y-3 p-2" data-notification-loading>
                                <?php for ($i = 0; $i < 6; $i++): ?>
                                    <div class="flex items-center gap-3">
                                        <span class="notification-skeleton-avatar h-12 w-12 rounded-full bg-slate-200"></span>
                                        <span class="notification-skeleton-line h-3 flex-1 rounded-full bg-slate-200"></span>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </section>
                </div>
            <?php endif; ?>
            <button class="theme-toggle inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50" type="button" data-theme-toggle aria-label="Toggle dark mode">
                <svg class="theme-icon-moon h-4 w-4" data-theme-moon aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12.8A9 9 0 1 1 11.2 3 7 7 0 0 0 21 12.8Z"></path>
                </svg>
                <svg class="theme-icon-sun hidden h-4 w-4" data-theme-sun aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="4"></circle>
                    <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"></path>
                </svg>
                <span class="hidden sm:inline" data-theme-label>Dark</span>
            </button>
            <span class="hidden text-slate-600 sm:inline"><?= e($user['name'] ?? 'Guest') ?></span>
            <a class="rounded-lg bg-slate-900 px-3 py-2 font-medium text-white hover:bg-slate-700" href="<?= app_url('logout.php') ?>">Logout</a>
        </div>
    </div>
</header>
