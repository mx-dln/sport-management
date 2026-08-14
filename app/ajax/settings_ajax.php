<?php
require_once __DIR__ . '/../controllers/SettingsController.php';
require_role(['admin']);

$controller = new SettingsController();
json_response($controller->save($_POST, $_FILES['app_icon'] ?? null));
