<?php
declare(strict_types=1);

require_once __DIR__ . '/../controllers/AthleteHistoryController.php';
require_role(['admin', 'sports_coordinator', 'athlete']);
$controller = new AthleteHistoryController($pdo);
$action = $_POST['action'] ?? 'save';
json_response(match ($action) {
    'delete' => $controller->delete((int)($_POST['id'] ?? 0)),
    default => $controller->save($_POST, $_FILES['proof_file'] ?? null),
});
