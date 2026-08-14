<?php
require_once __DIR__ . '/../controllers/UserController.php';
require_role(['admin', 'sports_coordinator']);
$controller = new UserController($pdo);
$action = $_POST['action'] ?? 'save';
$result = match ($action) {
    'status' => $controller->status((int)$_POST['id'], $_POST['status']),
    'delete' => $controller->delete((int)$_POST['id']),
    default => $controller->save($_POST),
};
json_response($result);

