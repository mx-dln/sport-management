<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';

class TeamController
{
    public function __construct(private PDO $pdo) {}
    public function all(array $f = []): array
    {
        if ((current_user()['role'] ?? '') === 'coach') {
            $f['coach_id'] = current_user()['id'] ?? 0;
        }

        $where = !empty($f['coach_id']) ? 'WHERE t.coach_id=?' : 'WHERE 1=1';
        $params = !empty($f['coach_id']) ? [$f['coach_id']] : [];
        if (!empty($f['sport_id'])) { $where .= ' AND t.sport_id=?'; $params[] = $f['sport_id']; }
        $stmt = $this->pdo->prepare("SELECT t.*, s.name sport_name, u.name coach_name FROM teams t LEFT JOIN sports s ON s.id=t.sport_id LEFT JOIN users u ON u.id=t.coach_id {$where} ORDER BY t.name");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    public function save(array $d): array
    {
        $id = (int)($d['id'] ?? 0);
        if ($id) {
            $stmt = $this->pdo->prepare('UPDATE teams SET sport_id=?, coach_id=?, name=?, description=?, status=? WHERE id=?');
            $stmt->execute([$d['sport_id'], $d['coach_id'] ?: null, $d['name'], $d['description'] ?? '', $d['status'] ?? 'active', $id]);
        } else {
            $stmt = $this->pdo->prepare('INSERT INTO teams (sport_id,coach_id,name,description,status) VALUES (?,?,?,?,?)');
            $stmt->execute([$d['sport_id'], $d['coach_id'] ?: null, $d['name'], $d['description'] ?? '', $d['status'] ?? 'active']);
        }
        return ['ok' => true, 'message' => 'Team saved.', 'reload' => true];
    }

    public function delete(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM team_members WHERE team_id=?');
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            return ['ok' => false, 'message' => 'Remove all athletes from the team first before deleting it.'];
        }

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM training_schedules WHERE team_id=?');
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            return ['ok' => false, 'message' => 'Cannot delete a team that has training schedules.'];
        }

        $stmt = $this->pdo->prepare('DELETE FROM teams WHERE id=?');
        $stmt->execute([$id]);
        return ['ok' => true, 'message' => 'Team deleted.', 'reload' => true];
    }

    public function assignCoach(int $teamId, ?int $coachId): array
    {
        if (!in_array((current_user()['role'] ?? ''), ['admin', 'sports_coordinator'], true)) {
            return ['ok' => false, 'message' => 'Only admin can assign teams to coaches.'];
        }

        if ($coachId) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE id=? AND role='coach' AND status='active'");
            $stmt->execute([$coachId]);
            if ((int)$stmt->fetchColumn() === 0) {
                return ['ok' => false, 'message' => 'Please select an active coach.'];
            }
        }

        $stmt = $this->pdo->prepare('UPDATE teams SET coach_id=? WHERE id=?');
        $stmt->execute([$coachId ?: null, $teamId]);
        return ['ok' => true, 'message' => 'Coach assignment updated.', 'reload' => true];
    }

    public function roster(int $teamId): array
    {
        $stmt = $this->pdo->prepare('SELECT a.* FROM team_members tm JOIN athletes a ON a.id=tm.athlete_id WHERE tm.team_id=? ORDER BY a.last_name, a.first_name');
        $stmt->execute([$teamId]);
        return $stmt->fetchAll();
    }

    public function assignMember(int $teamId, int $athleteId): array
    {
        $this->assertTeamAccess($teamId);
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM team_members WHERE team_id=? AND athlete_id=?');
        $stmt->execute([$teamId, $athleteId]);
        if ((int)$stmt->fetchColumn() > 0) {
            return ['ok' => false, 'message' => 'This athlete is already assigned to this team.'];
        }

        $stmt = $this->pdo->prepare('INSERT IGNORE INTO team_members (team_id, athlete_id) VALUES (?, ?)');
        $stmt->execute([$teamId, $athleteId]);
        return ['ok' => true, 'message' => 'Athlete assigned to team.', 'reload' => true];
    }

    public function removeMember(int $teamId, int $athleteId): array
    {
        $this->assertTeamAccess($teamId);
        $stmt = $this->pdo->prepare('DELETE FROM team_members WHERE team_id=? AND athlete_id=?');
        $stmt->execute([$teamId, $athleteId]);
        return ['ok' => true, 'message' => 'Athlete removed from team.', 'reload' => true];
    }

    private function assertTeamAccess(int $teamId): void
    {
        if ((current_user()['role'] ?? '') !== 'coach') {
            return;
        }

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM teams WHERE id=? AND coach_id=?');
        $stmt->execute([$teamId, current_user()['id'] ?? 0]);
        if ((int)$stmt->fetchColumn() === 0) {
            throw new RuntimeException('You can only assign athletes to your own teams.');
        }
    }
}
