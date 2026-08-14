<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/SmsController.php';

class ScheduleController
{
    public function __construct(private PDO $pdo) {}

    public function autoCompletePastSchedules(): void
    {
        $stmt = $this->pdo->prepare("UPDATE training_schedules
            SET status='Completed'
            WHERE status IN ('Scheduled','Updated')
              AND TIMESTAMP(training_date, end_time) < NOW()");
        $stmt->execute();
    }

    public function all(array $f = []): array
    {
        $this->autoCompletePastSchedules();

        $where = ['1=1']; $params = [];
        if ((current_user()['role'] ?? '') === 'coach') {
            $f['coach_id'] = current_user()['id'] ?? 0;
        }

        foreach (['sport_id','team_id','status'] as $field) {
            if (!empty($f[$field])) { $where[] = "ts.{$field}=?"; $params[] = $f[$field]; }
        }
        if (!empty($f['coach_id'])) {
            $where[] = 't.coach_id=?';
            $params[] = $f['coach_id'];
        }
        if (!empty($f['date_from'])) { $where[] = 'ts.training_date>=?'; $params[] = $f['date_from']; }
        if (!empty($f['date_to'])) { $where[] = 'ts.training_date<=?'; $params[] = $f['date_to']; }
        $stmt = $this->pdo->prepare('SELECT ts.*, s.name sport_name, t.name team_name, u.name coach_name FROM training_schedules ts LEFT JOIN sports s ON s.id=ts.sport_id LEFT JOIN teams t ON t.id=ts.team_id LEFT JOIN users u ON u.id=ts.coach_id WHERE ' . implode(' AND ', $where) . ' ORDER BY ts.training_date DESC, ts.start_time');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    public function save(array $d, bool $notify = true): array
    {
        $id = (int)($d['id'] ?? 0);
        if ($id) {
            $this->assertScheduleAccess($id);
            $stmt = $this->pdo->prepare('UPDATE training_schedules SET sport_id=?,team_id=?,coach_id=?,training_date=?,start_time=?,end_time=?,venue=?,description=?,status=? WHERE id=?');
            $stmt->execute([$d['sport_id'],$d['team_id'],$d['coach_id'],$d['training_date'],$d['start_time'],$d['end_time'],$d['venue'],$d['description'] ?? '',$d['status'] ?? 'Scheduled',$id]);
        } else {
            $stmt = $this->pdo->prepare('INSERT INTO training_schedules (sport_id,team_id,coach_id,training_date,start_time,end_time,venue,description,status,created_by) VALUES (?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$d['sport_id'],$d['team_id'],$d['coach_id'],$d['training_date'],$d['start_time'],$d['end_time'],$d['venue'],$d['description'] ?? '',$d['status'] ?? 'Scheduled',current_user()['id'] ?? null]);
            $id = (int)$this->pdo->lastInsertId();
        }
        $smsResult = null;
        if ($notify && !empty($d['send_sms'])) {
            $timeText = format_time_12($d['start_time'] ?? '');
            $smsResult = $this->notifyTeam((int)$d['team_id'], "Training {$d['status']} on {$d['training_date']} at {$timeText} in {$d['venue']}.");
        }

        $message = 'Training schedule saved.';
        if ($smsResult) {
            $message .= " SMS sent: {$smsResult['sent']}/{$smsResult['attempted']}.";
            if ($smsResult['attempted'] === 0) {
                $message .= ' No athlete or guardian phone numbers found for this team.';
            } elseif ($smsResult['failed'] > 0) {
                $message .= ' Check SMS Logs for failed recipients.';
            }
        }

        return ['ok' => true, 'message' => $message, 'reload' => true];
    }
    public function delete(int $id): array
    {
        $this->assertScheduleAccess($id);
        $stmt = $this->pdo->prepare('DELETE FROM attendance WHERE schedule_id=?');
        $stmt->execute([$id]);
        $stmt = $this->pdo->prepare('DELETE FROM training_schedules WHERE id=?');
        $stmt->execute([$id]);
        return ['ok' => true, 'message' => 'Training schedule deleted.', 'reload' => true];
    }

    private function assertScheduleAccess(int $scheduleId): void
    {
        if ((current_user()['role'] ?? '') !== 'coach') {
            return;
        }

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM training_schedules ts JOIN teams t ON t.id=ts.team_id WHERE ts.id=? AND t.coach_id=?');
        $stmt->execute([$scheduleId, current_user()['id'] ?? 0]);
        if ((int)$stmt->fetchColumn() === 0) {
            throw new RuntimeException('You can only manage schedules for your own teams.');
        }
    }

    public function notifyTeam(int $teamId, string $message): array
    {
        $sms = new SmsController($this->pdo);
        $stmt = $this->pdo->prepare('SELECT a.first_name,a.last_name,a.contact_number,a.guardian_contact FROM team_members tm JOIN athletes a ON a.id=tm.athlete_id WHERE tm.team_id=?');
        $stmt->execute([$teamId]);
        $attempted = 0;
        $sent = 0;
        $failed = 0;

        foreach ($stmt->fetchAll() as $a) {
            $name = trim($a['first_name'] . ' ' . $a['last_name']);
            foreach ([
                [$name, $a['contact_number']],
                [$name . ' Guardian', $a['guardian_contact']],
            ] as [$recipientName, $phone]) {
                if (!$phone) {
                    continue;
                }

                $attempted++;
                $result = $sms->send($recipientName, $phone, $message);
                if ($result['ok']) {
                    $sent++;
                } else {
                    $failed++;
                }
            }
        }

        return ['attempted' => $attempted, 'sent' => $sent, 'failed' => $failed];
    }
}
