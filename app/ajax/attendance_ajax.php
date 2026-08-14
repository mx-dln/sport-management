<?php
require_once __DIR__ . '/../controllers/AttendanceController.php';
require_role(['admin', 'sports_coordinator', 'coach']);
$controller = new AttendanceController($pdo);
json_response($controller->save($_POST['attendance'] ?? [], (int)$_POST['schedule_id']));

