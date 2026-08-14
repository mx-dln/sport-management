<?php
require_once __DIR__ . '/../controllers/DocumentController.php';
require_role(['admin', 'sports_coordinator']);

$controller = new DocumentController($pdo);
$marker = max(0, (int)($_GET['marker'] ?? 0));
$uploads = $marker > 0 ? $controller->uploadsAfter($marker) : [];
$recent = !empty($_GET['recent']) ? array_slice($controller->uploads(), 0, 10) : [];
$latest = $controller->latestUploadMarker();

json_response([
    'ok' => true,
    'latest_marker' => $latest,
    'uploads' => $uploads,
    'recent' => $recent,
]);
