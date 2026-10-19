<?php
require_once __DIR__ . '/../controllers/SettingsController.php';
require_role(['admin']);

$controller = new SettingsController();
$action = $_POST['action'] ?? 'save';

$result = match ($action) {
    'test_sms' => $controller->testSms($_POST),
    'test_email' => $controller->testEmail($_POST),
    default => $controller->save($_POST, $_FILES['app_icon'] ?? null),
};

json_response($result);
