<?php
declare(strict_types=1);

require_once __DIR__ . '/../controllers/MedicalController.php';
require_role(['admin', 'sports_coordinator']);

$action = $_POST['action'] ?? 'save';
$controller = new MedicalController($pdo);

$result = match ($action) {
    'basics' => $controller->athleteBasics((int)($_POST['athlete_id'] ?? 0)),
    'delete' => $controller->delete((int)($_POST['id'] ?? 0)),
    default => $controller->save($_POST, $_FILES['certificate_file'] ?? null),
};

json_response($result);
