<?php
require_once __DIR__ . '/../controllers/AnnouncementController.php';
require_role(['admin', 'sports_coordinator', 'coach']);
$controller = new AnnouncementController($pdo);
$action = $_POST['action'] ?? 'save';
json_response(match ($action) {
    'delete' => $controller->delete((int)($_POST['id'] ?? 0)),
    default => $controller->save($_POST),
});
