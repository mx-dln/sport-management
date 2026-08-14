<?php
require_once __DIR__ . '/../controllers/DocumentController.php';
require_role(['admin', 'sports_coordinator', 'athlete']);
$controller = new DocumentController($pdo);
$action = $_POST['action'] ?? 'upload_document';
$result = match ($action) {
    'requirement' => $controller->saveRequirement($_POST),
    'delete_requirement' => $controller->deleteRequirement((int)$_POST['id']),
    'status' => $controller->updateStatus((int)$_POST['id'], $_POST['status'], $_POST['remarks'] ?? ''),
    'template' => $controller->uploadTemplate($_POST, $_FILES['template_file']),
    default => $controller->uploadDocument($_POST, $_FILES['document_file']),
};
json_response($result);
