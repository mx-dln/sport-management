<?php
require_once __DIR__ . '/../controllers/SmsController.php';
require_role(['admin', 'sports_coordinator', 'coach']);
$controller = new SmsController($pdo);
json_response($controller->send($_POST['recipient_name'], $_POST['phone_number'], $_POST['message']));

