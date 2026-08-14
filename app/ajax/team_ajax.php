<?php
require_once __DIR__ . '/../controllers/TeamController.php';
require_role(['admin', 'sports_coordinator', 'coach']);
$controller = new TeamController($pdo);
$action = $_POST['action'] ?? 'save';
try {
    $result = match ($action) {
        'assign_member' => $controller->assignMember((int)$_POST['team_id'], (int)$_POST['athlete_id']),
        'remove_member' => $controller->removeMember((int)$_POST['team_id'], (int)$_POST['athlete_id']),
        'assign_coach' => $controller->assignCoach((int)$_POST['team_id'], ($_POST['coach_id'] ?? '') === '' ? null : (int)$_POST['coach_id']),
        'delete' => (current_user()['role'] ?? '') === 'coach'
            ? ['ok' => false, 'message' => 'Coaches cannot delete teams.']
            : $controller->delete((int)($_POST['id'] ?? 0)),
        default => (current_user()['role'] ?? '') === 'coach'
            ? ['ok' => false, 'message' => 'Coaches can assign athletes only to their own teams.']
            : $controller->save($_POST),
    };
} catch (Throwable $e) {
    $result = ['ok' => false, 'message' => $e->getMessage()];
}
json_response($result);
