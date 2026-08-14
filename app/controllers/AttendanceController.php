<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';

class AttendanceController
{
    public function __construct(private PDO $pdo) {}
    public function forSchedule(int $scheduleId): array
    {
        $where = 'ts.id=?';
        $params = [$scheduleId];
        if ((current_user()['role'] ?? '') === 'coach') {
            $where .= ' AND t.coach_id=?';
            $params[] = current_user()['id'] ?? 0;
        }

        $stmt = $this->pdo->prepare("SELECT a.id athlete_id, a.student_id, a.first_name, a.last_name, att.id attendance_id, att.status, att.remarks FROM training_schedules ts JOIN teams t ON t.id=ts.team_id JOIN team_members tm ON tm.team_id=ts.team_id JOIN athletes a ON a.id=tm.athlete_id LEFT JOIN attendance att ON att.schedule_id=ts.id AND att.athlete_id=a.id WHERE {$where} ORDER BY a.last_name");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    public function save(array $rows, int $scheduleId): array
    {
        if (!$this->canAccessSchedule($scheduleId)) {
            return ['ok' => false, 'message' => 'You can only mark attendance for your assigned teams.'];
        }

        if ($this->isCompletedSchedule($scheduleId)) {
            return ['ok' => false, 'message' => 'This schedule is completed. Attendance is view-only.'];
        }

        $stmt = $this->pdo->prepare('INSERT INTO attendance (schedule_id,athlete_id,status,remarks,marked_by) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE status=VALUES(status), remarks=VALUES(remarks), marked_by=VALUES(marked_by), marked_at=CURRENT_TIMESTAMP');
        foreach ($rows as $athleteId => $row) {
            $stmt->execute([$scheduleId, $athleteId, $row['status'] ?? 'Absent', $row['remarks'] ?? '', current_user()['id'] ?? null]);
        }

        $stmt = $this->pdo->prepare("UPDATE training_schedules SET status='Completed' WHERE id=? AND status <> 'Cancelled'");
        $stmt->execute([$scheduleId]);

        return ['ok' => true, 'message' => 'Attendance saved and schedule marked as completed.', 'reload' => true];
    }

    private function canAccessSchedule(int $scheduleId): bool
    {
        if ((current_user()['role'] ?? '') !== 'coach') {
            return true;
        }

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM training_schedules ts JOIN teams t ON t.id=ts.team_id WHERE ts.id=? AND t.coach_id=?');
        $stmt->execute([$scheduleId, current_user()['id'] ?? 0]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function isCompletedSchedule(int $scheduleId): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM training_schedules WHERE id=? AND status='Completed'");
        $stmt->execute([$scheduleId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
