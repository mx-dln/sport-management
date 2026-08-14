<?php
require_once __DIR__ . '/../controllers/AthleteController.php';
require_role(['admin', 'sports_coordinator', 'coach', 'athlete']);
$controller = new AthleteController($pdo);
$action = $_POST['action'] ?? 'save';
if ($action === 'delete') {
    require_role(['admin', 'sports_coordinator']);
    json_response($controller->delete((int)($_POST['id'] ?? 0)));
}

$user = current_user();
if (($user['role'] ?? '') === 'athlete') {
    $stmt = $pdo->prepare('SELECT id FROM athletes WHERE user_id=? LIMIT 1');
    $stmt->execute([$user['id']]);
    $athleteId = (int)($stmt->fetchColumn() ?: 0);
    if ($athleteId <= 0) {
        json_response(['ok' => false, 'message' => 'Athlete profile not found.'], 404);
    }

    $_POST['id'] = (string)$athleteId;
    $_POST['user_id'] = (string)$user['id'];
    $_POST['athlete_status'] = 'Active';
    unset($_POST['team_id']);
}
$result = $controller->save($_POST, $_FILES['profile_photo'] ?? null);
json_response($result);
