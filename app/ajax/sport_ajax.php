<?php
require_once __DIR__ . '/../controllers/SportController.php';
require_role(['admin', 'sports_coordinator']);
$controller = new SportController($pdo);
$action = $_POST['action'] ?? 'save';
json_response(match ($action) {
    'delete' => $controller->delete((int)($_POST['id'] ?? 0)),
    default => $controller->save($_POST),
});
