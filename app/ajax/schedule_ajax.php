<?php
require_once __DIR__ . '/../controllers/ScheduleController.php';
require_role(['admin', 'sports_coordinator', 'coach']);
$controller = new ScheduleController($pdo);
$action = $_POST['action'] ?? 'save';
try {
    $result = match ($action) {
        'delete' => $controller->delete((int)($_POST['id'] ?? 0)),
        default => $controller->save($_POST),
    };
} catch (Throwable $e) {
    $result = ['ok' => false, 'message' => $e->getMessage()];
}
json_response($result);
