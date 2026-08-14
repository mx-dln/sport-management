<?php
require_once __DIR__ . '/../app/helpers/auth.php';

if (!is_logged_in()) {
    redirect(app_url('login.php'));
}

$role = current_user()['role'];
$page = preg_replace('/[^a-z_]/', '', $_GET['page'] ?? 'dashboard');

$routes = [
    'admin' => [
        'dashboard' => 'admin/dashboard.php',
        'users' => 'admin/users.php',
        'athletes' => 'admin/athletes.php',
        'athlete_print' => 'admin/athlete_print.php',
        'sports' => 'admin/sports.php',
        'teams' => 'admin/teams.php',
        'documents' => 'admin/documents.php',
        'templates' => 'admin/templates.php',
        'schedules' => 'admin/schedules.php',
        'attendance' => 'admin/attendance.php',
        'announcements' => 'admin/announcements.php',
        'sms' => 'admin/sms.php',
        'medical' => 'admin/medical.php',
        'competition' => 'admin/competition.php',
        'competition_manage' => 'admin/competition_manage.php',
        'history' => 'admin/history.php',
        'reports' => 'admin/reports.php',
        'settings' => 'admin/settings.php',
    ],
    'sports_coordinator' => [
        'dashboard' => 'admin/dashboard.php',
        'athletes' => 'admin/athletes.php',
        'athlete_print' => 'admin/athlete_print.php',
        'sports' => 'admin/sports.php',
        'teams' => 'admin/teams.php',
        'documents' => 'admin/documents.php',
        'schedules' => 'admin/schedules.php',
        'medical' => 'admin/medical.php',
        'competition' => 'admin/competition.php',
        'competition_manage' => 'admin/competition_manage.php',
        'history' => 'admin/history.php',
        'reports' => 'admin/reports.php',
    ],
    'coach' => [
        'dashboard' => 'coach/dashboard.php',
        'teams' => 'coach/teams.php',
        'schedules' => 'coach/schedules.php',
        'attendance' => 'coach/attendance.php',
        'announcements' => 'coach/announcements.php',
    ],
    'athlete' => [
        'dashboard' => 'athlete/dashboard.php',
        'profile' => 'athlete/profile.php',
        'athlete_print' => 'admin/athlete_print.php',
        'documents' => 'athlete/documents.php',
        'medical' => 'athlete/medical.php',
        'history' => 'athlete/history.php',
        'schedules' => 'athlete/schedules.php',
        'announcements' => 'athlete/announcements.php',
    ],
];

if (!isset($routes[$role], $routes[$role][$page])) {
    http_response_code(404);
    exit('Page not found.');
}

require __DIR__ . '/../app/views/' . $routes[$role][$page];
