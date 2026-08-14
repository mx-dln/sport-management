<?php
$role = current_user()['role'] ?? '';

function nav_icon(string $name): string
{
    $icons = [
        'dashboard' => '<path d="M4 13h6V4H4v9Zm10 7h6v-9h-6v9ZM4 20h6v-5H4v5Zm10-11h6V4h-6v5Z"/>',
        'users' => '<path d="M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0ZM4 20a8 8 0 0 1 16 0"/>',
        'athletes' => '<path d="M12 3l3 6 6 .9-4.5 4.4 1.1 6.2L12 17.6l-5.6 2.9 1.1-6.2L3 9.9 9 9l3-6Z"/>',
        'sports' => '<path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm-7-9h14M12 3c2.5 2.6 2.5 15.4 0 18M12 3C9.5 5.6 9.5 18.4 12 21"/>',
        'teams' => '<path d="M8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm8 0a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM3 20a5 5 0 0 1 10 0M11 20a5 5 0 0 1 10 0"/>',
        'documents' => '<path d="M7 3h7l5 5v13H7V3Zm7 0v5h5M10 13h6M10 17h6"/>',
        'medical' => '<path d="M4 7c4 0 4 3 8 3s4-3 8-3v10c-4 0-4-3-8-3s-4 3-8 3V7Zm8 2V4"/>',
        'competition' => '<path d="M8 21h8M12 17v4M7 4h10v4a5 5 0 0 1-10 0V4Zm3 4h4l1-2H9l1 2Zm5 4h8v1a4 4 0 0 1-8 0v-1ZM1 12h8v1a4 4 0 0 1-8 0v-1Z"/>',
        'history' => '<path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm-8-7.5 3.5 3.5L12 7l4.5 4.5L20 8v12H4V8Z"/>',
//        'templates' => '<path d="M5 4h14v16H5V4Zm4 4h6M9 12h6M9 16h3"/>',
        'schedules' => '<path d="M7 3v4M17 3v4M4 8h16M5 5h14v16H5V5Zm3 7h3v3H8v-3Z"/>',
        'attendance' => '<path d="m4 12 4 4L20 4M4 20h16"/>',
        'announcements' => '<path d="M4 11v4h4l8 4V7l-8 4H4Zm12-1 4-2v10l-4-2"/>',
        'sms' => '<path d="M4 5h16v11H7l-3 3V5Zm4 5h8M8 13h5"/>',
        'reports' => '<path d="M5 20V4h14v16H5Zm4-4v-5M12 16V8M15 16v-3"/>',
        'settings' => '<path d="M12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5Zm0-12 1.2 2.2 2.5.4.4 2.5L18.3 10 17 12l1.3 2-2.2 1.4-.4 2.5-2.5.4L12 20.5l-1.2-2.2-2.5-.4-.4-2.5L5.7 14 7 12l-1.3-2 2.2-1.4.4-2.5 2.5-.4L12 3.5Z"/>',
        'profile' => '<path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 9a7 7 0 0 1 14 0"/>',
    ];

    return '<svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($icons[$name] ?? $icons['dashboard']) . '</svg>';
}

$groups = [
    'admin' => [
        'Main' => [
            ['Dashboard', 'dashboard.php', 'dashboard'],
        ],
        'Records' => [
            ['Athletes', 'athletes.php', 'athletes'],
            ['Sports', 'sports.php', 'sports'],
            ['Teams', 'teams.php', 'teams'],
            ['Documents', 'documents.php', 'documents'],
//            ['Templates', 'templates.php', 'templates'],
            ['Medical Records', 'medical.php', 'medical'],
            ['Competitions', 'competition.php', 'competition'],
            ['Athletic History', 'history.php', 'history'],
            ['Schedules', 'schedules.php', 'schedules'],
            ['Attendance', 'attendance.php', 'attendance'],
            ['Announcements', 'announcements.php', 'announcements'],
            ['SMS Logs', 'sms.php', 'sms'],
            ['Reports', 'reports.php', 'reports'],
        ],
        'Users' => [
            ['Users', 'users.php', 'users'],
        ],
        'Settings' => [
            ['System Settings', 'settings.php', 'settings'],
        ],
    ],
    'sports_coordinator' => [
        'Main' => [
            ['Dashboard', 'dashboard.php', 'dashboard'],
        ],
        'Records' => [
            ['Athletes', 'athletes.php', 'athletes'],
            ['Sports', 'sports.php', 'sports'],
            ['Teams', 'teams.php', 'teams'],
            ['Documents', 'documents.php', 'documents'],
            ['Medical Records', 'medical.php', 'medical'],
            ['Competitions', 'competition.php', 'competition'],
            ['Athletic History', 'history.php', 'history'],
            ['Schedules', 'schedules.php', 'schedules'],
            ['Reports', 'reports.php', 'reports'],
        ],
    ],
    'coach' => [
        'Main' => [
            ['Dashboard', 'dashboard.php', 'dashboard'],
        ],
        'Records' => [
            ['My Teams', 'teams.php', 'teams'],
            ['Schedules', 'schedules.php', 'schedules'],
            ['Attendance', 'attendance.php', 'attendance'],
            ['Announcements', 'announcements.php', 'announcements'],
        ],
    ],
    'athlete' => [
        'Main' => [
            ['Dashboard', 'dashboard.php', 'dashboard'],
            ['My Profile', 'profile.php', 'profile'],
        ],
        'Records' => [
            ['Documents', 'documents.php', 'documents'],
            ['Medical Records', 'medical.php', 'medical'],
            ['Athletic History', 'history.php', 'history'],
            ['Schedules', 'schedules.php', 'schedules'],
            ['Announcements', 'announcements.php', 'announcements'],
        ],
    ],
];

$activePage = $_GET['page'] ?? 'dashboard';
?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.querySelector('[data-sidebar]');
    const toggleBtn = document.querySelector('[data-sidebar-toggle]');

    if (!sidebar || !toggleBtn) return;

    let overlay = document.querySelector('[data-sidebar-overlay]');

    if (!overlay) {
        overlay = document.createElement('div');
        overlay.setAttribute('data-sidebar-overlay', '');
        overlay.className = 'fixed inset-0 z-20 hidden bg-black/50 lg:hidden';
        document.body.appendChild(overlay);
    }

    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        sidebar.classList.add('translate-x-0');
        overlay.classList.remove('hidden');
    }

    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('translate-x-0');
        overlay.classList.add('hidden');
    }

    toggleBtn.addEventListener('click', function () {
        if (sidebar.classList.contains('-translate-x-full')) {
            openSidebar();
        } else {
            closeSidebar();
        }
    });

    overlay.addEventListener('click', closeSidebar);

    document.querySelectorAll('[data-sidebar] a').forEach(function (link) {
        link.addEventListener('click', closeSidebar);
    });
});
</script>
<aside class="fixed inset-y-0 left-0 z-30 w-72 -translate-x-full border-r border-slate-200 bg-white transition lg:translate-x-0" data-sidebar>
    <div class="flex h-16 items-center border-b border-slate-200 px-5">
        <?php if (app_icon_url()): ?>
            <img class="h-11 w-11 rounded-xl object-cover" src="<?= e(app_icon_url()) ?>" alt="<?= e(app_setting('app_name')) ?> icon">
        <?php else: ?>
            <div class="rounded-xl px-3 py-2 font-black text-white" style="background: var(--theme-color);"><?= e(app_setting('app_short_name', 'SMIS')) ?></div>
        <?php endif; ?>
        <div class="ml-3 min-w-0">
            <p class="truncate font-bold"><?= e(app_setting('app_short_name', 'SMIS')) ?></p>
            <p class="text-xs capitalize text-slate-500"><?= e(str_replace('_', ' ', $role)) ?></p>
        </div>
    </div>
    <nav class="space-y-5 overflow-y-auto p-4">
        <?php foreach (($groups[$role] ?? []) as $groupLabel => $items): ?>
            <section>
                <p class="mb-2 px-3 text-xs font-black uppercase tracking-wide text-slate-400"><?= e($groupLabel) ?></p>
                <div class="space-y-1">
                    <?php foreach ($items as [$label, $href, $icon]): ?>
                        <?php $page = basename($href, '.php'); $active = $activePage === $page; ?>
                        <a class="nav-link flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold <?= $active ? 'bg-blue-50 text-blue-600' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' ?>" href="<?= e(app_url('index.php?page=' . $page)) ?>">
                            <?= nav_icon($icon) ?>
                            <span><?= e($label) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </nav>
</aside>
