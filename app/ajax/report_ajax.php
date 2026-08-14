<?php
require_once __DIR__ . '/../controllers/ReportController.php';
require_role(['admin', 'sports_coordinator', 'coach']);
$controller = new ReportController($pdo);
json_response(['ok' => true, 'missing' => $controller->missingRequirements()]);

