<?php
declare(strict_types=1);

require_once __DIR__ . '/../controllers/CompetitionController.php';
require_role(['admin', 'sports_coordinator']);

$action = $_POST['action'] ?? 'save';
$controller = new CompetitionController($pdo);

$result = match ($action) {
    'delete' => $controller->delete((int)($_POST['id'] ?? 0)),
    'participant' => $controller->addParticipant($_POST),
    'remove_participant' => $controller->removeParticipant((int)($_POST['id'] ?? 0)),
    'result' => $controller->saveResult($_POST),
    'delete_result' => $controller->deleteResult((int)($_POST['id'] ?? 0)),
    'sms' => $controller->sendSms((string)($_POST['recipient_name'] ?? ''), (string)($_POST['phone_number'] ?? ''), (string)($_POST['message'] ?? '')),
    'sms_bulk' => $controller->sendSmsBulk($_POST),
    default => $controller->save($_POST),
};

json_response($result);
