<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/sms_helper.php';

class CompetitionController
{
    public function __construct(private PDO $pdo) {}

    public function all(array $filter = []): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filter['q'])) {
            $where[] = 'c.name LIKE ?';
            $params[] = '%' . $filter['q'] . '%';
        }
        if (!empty($filter['sport_id'])) {
            $where[] = 'c.sport_id = ?';
            $params[] = (int)$filter['sport_id'];
        }
        if (!empty($filter['level'])) {
            $where[] = 'c.level = ?';
            $params[] = $filter['level'];
        }
        if (!empty($filter['status'])) {
            $where[] = 'c.status = ?';
            $params[] = $filter['status'];
        }

        $sql = 'SELECT c.*, s.name AS sport_name,
                       (SELECT COUNT(*) FROM competition_participants cp WHERE cp.competition_id = c.id) AS participant_count
                FROM competitions c
                LEFT JOIN sports s ON s.id = c.sport_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY c.start_date DESC, c.id DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT c.*, s.name AS sport_name
                                     FROM competitions c
                                     LEFT JOIN sports s ON s.id = c.sport_id
                                     WHERE c.id = ? LIMIT 1');
        $stmt->execute([$id]);
        $competition = $stmt->fetch();
        return $competition ?: null;
    }

    public function save(array $d): array
    {
        $id = (int)($d['id'] ?? 0);
        $name = trim((string)($d['name'] ?? ''));

        if ($name === '') {
            return ['ok' => false, 'message' => 'Competition name is required.'];
        }

        $values = [
            'name' => $name,
            'sport_id' => trim((string)($d['sport_id'] ?? '')) !== '' ? (int)$d['sport_id'] : null,
            'category' => trim((string)($d['category'] ?? 'Individual')) ?: 'Individual',
            'event_type' => trim((string)($d['event_type'] ?? '')),
            'venue' => trim((string)($d['venue'] ?? '')),
            'organizer' => trim((string)($d['organizer'] ?? '')),
            'level' => trim((string)($d['level'] ?? 'School')) ?: 'School',
            'start_date' => trim((string)($d['start_date'] ?? '')) ?: null,
            'end_date' => trim((string)($d['end_date'] ?? '')) ?: null,
            'registration_deadline' => trim((string)($d['registration_deadline'] ?? '')) ?: null,
            'status' => trim((string)($d['status'] ?? 'Upcoming')) ?: 'Upcoming',
        ];

        if ($id > 0) {
            $stmt = $this->pdo->prepare('UPDATE competitions SET
                name=?, sport_id=?, category=?, event_type=?, venue=?, organizer=?, level=?,
                start_date=?, end_date=?, registration_deadline=?, status=? WHERE id=?');
            $stmt->execute([
                $values['name'], $values['sport_id'], $values['category'], $values['event_type'],
                $values['venue'], $values['organizer'], $values['level'], $values['start_date'],
                $values['end_date'], $values['registration_deadline'], $values['status'], $id,
            ]);
        } else {
            $stmt = $this->pdo->prepare('INSERT INTO competitions
                (name, sport_id, category, event_type, venue, organizer, level, start_date, end_date, registration_deadline, status, created_by)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([
                $values['name'], $values['sport_id'], $values['category'], $values['event_type'],
                $values['venue'], $values['organizer'], $values['level'], $values['start_date'],
                $values['end_date'], $values['registration_deadline'], $values['status'], current_user()['id'] ?? null,
            ]);
        }

        return ['ok' => true, 'message' => 'Competition saved.', 'reload' => true];
    }

    public function delete(int $id): array
    {
        $stmt = $this->pdo->prepare('DELETE FROM competition_results WHERE competition_id = ?');
        $stmt->execute([$id]);
        $stmt = $this->pdo->prepare('DELETE FROM competition_participants WHERE competition_id = ?');
        $stmt->execute([$id]);
        $stmt = $this->pdo->prepare('DELETE FROM competitions WHERE id = ?');
        $stmt->execute([$id]);
        return ['ok' => true, 'message' => 'Competition deleted.', 'reload' => true];
    }

    public function participants(int $competitionId): array
    {
        $stmt = $this->pdo->prepare('SELECT cp.*, a.student_id, a.first_name, a.middle_name, a.last_name,
                                            a.section, a.contact_number, t.name AS team_name, u.name AS coach_name
                                     FROM competition_participants cp
                                     JOIN athletes a ON a.id = cp.athlete_id
                                     LEFT JOIN teams t ON t.id = a.team_id
                                     LEFT JOIN users u ON u.id = cp.coach_id
                                     WHERE cp.competition_id = ?
                                     ORDER BY a.last_name, a.first_name');
        $stmt->execute([$competitionId]);
        return $stmt->fetchAll();
    }

    public function availableAthletes(int $competitionId): array
    {
        $stmt = $this->pdo->prepare('SELECT a.id, a.student_id, a.first_name, a.middle_name, a.last_name, a.section
                                     FROM athletes a
                                     WHERE a.athlete_status = "Active"
                                       AND a.id NOT IN (
                                           SELECT athlete_id FROM competition_participants WHERE competition_id = ?
                                       )
                                     ORDER BY a.last_name, a.first_name');
        $stmt->execute([$competitionId]);
        return $stmt->fetchAll();
    }

    public function addParticipant(array $d): array
    {
        $competitionId = (int)($d['competition_id'] ?? 0);
        $athleteId = (int)($d['athlete_id'] ?? 0);

        if ($competitionId <= 0 || $athleteId <= 0) {
            return ['ok' => false, 'message' => 'Select a competition and an athlete.'];
        }

        $check = $this->pdo->prepare('SELECT 1 FROM competition_participants WHERE competition_id = ? AND athlete_id = ? LIMIT 1');
        $check->execute([$competitionId, $athleteId]);
        if ($check->fetchColumn()) {
            return ['ok' => false, 'message' => 'Athlete is already added to this competition.'];
        }

        $stmt = $this->pdo->prepare('INSERT INTO competition_participants (competition_id, athlete_id, event_name, jersey_bib, coach_id) VALUES (?,?,?,?,?)');
        $stmt->execute([
            $competitionId,
            $athleteId,
            trim((string)($d['event_name'] ?? '')),
            trim((string)($d['jersey_bib'] ?? '')),
            trim((string)($d['coach_id'] ?? '')) !== '' ? (int)$d['coach_id'] : null,
        ]);
        return ['ok' => true, 'message' => 'Athlete added to the competition.', 'reload' => true];
    }

    public function removeParticipant(int $id): array
    {
        $stmt = $this->pdo->prepare('DELETE FROM competition_participants WHERE id = ?');
        $stmt->execute([$id]);
        return ['ok' => true, 'message' => 'Participant removed.', 'reload' => true];
    }

    public function results(int $competitionId): array
    {
        $stmt = $this->pdo->prepare('SELECT r.*, a.student_id, a.first_name, a.middle_name, a.last_name
                                     FROM competition_results r
                                     JOIN athletes a ON a.id = r.athlete_id
                                     WHERE r.competition_id = ?
                                     ORDER BY CASE r.medal WHEN "Gold" THEN 1 WHEN "Silver" THEN 2 WHEN "Bronze" THEN 3 ELSE 4 END, r.rank_place');
        $stmt->execute([$competitionId]);
        return $stmt->fetchAll();
    }

    public function saveResult(array $d): array
    {
        $competitionId = (int)($d['competition_id'] ?? 0);
        $athleteId = (int)($d['athlete_id'] ?? 0);
        $id = (int)($d['id'] ?? 0);

        if ($competitionId <= 0 || $athleteId <= 0) {
            return ['ok' => false, 'message' => 'Select an athlete for the result.'];
        }

        $rank = trim((string)($d['rank_place'] ?? ''));
        $medal = trim((string)($d['medal'] ?? 'None')) ?: 'None';
        $score = trim((string)($d['score_time'] ?? ''));
        $status = trim((string)($d['result_status'] ?? 'Winner')) ?: 'Winner';
        $remarks = trim((string)($d['remarks'] ?? ''));

        if ($id > 0) {
            $stmt = $this->pdo->prepare('UPDATE competition_results SET rank_place=?, medal=?, score_time=?, result_status=?, remarks=?, updated_by=? WHERE id=?');
            $stmt->execute([$rank, $medal, $score, $status, $remarks, current_user()['id'] ?? null, $id]);
        } else {
            $check = $this->pdo->prepare('SELECT 1 FROM competition_results WHERE competition_id = ? AND athlete_id = ? LIMIT 1');
            $check->execute([$competitionId, $athleteId]);
            if ($check->fetchColumn()) {
                return ['ok' => false, 'message' => 'This athlete already has a result. Edit the existing entry instead.'];
            }
            $stmt = $this->pdo->prepare('INSERT INTO competition_results (competition_id, athlete_id, rank_place, medal, score_time, result_status, remarks, updated_by) VALUES (?,?,?,?,?,?,?,?)');
            $stmt->execute([$competitionId, $athleteId, $rank, $medal, $score, $status, $remarks, current_user()['id'] ?? null]);
        }

        return ['ok' => true, 'message' => 'Competition result saved.', 'reload' => true];
    }

    public function deleteResult(int $id): array
    {
        $stmt = $this->pdo->prepare('DELETE FROM competition_results WHERE id = ?');
        $stmt->execute([$id]);
        return ['ok' => true, 'message' => 'Competition result deleted.', 'reload' => true];
    }

    public function sendSms(string $name, string $phone, string $message): array
    {
        $name = trim($name);
        $phone = trim($phone);
        $message = trim($message);

        if ($name === '' || $phone === '' || $message === '') {
            return ['ok' => false, 'message' => 'Recipient, contact number, and message are required.'];
        }

        $result = sendSMS($phone, $message);
        $stmt = $this->pdo->prepare('INSERT INTO sms_logs (recipient_name, phone_number, message, status, sent_by, source) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$name, $phone, $message, $result['status'], current_user()['id'] ?? null, 'competition']);

        return [
            'ok' => $result['success'],
            'message' => $result['success'] ? 'SMS sent and logged.' : 'SMS failed but was logged.',
            'status' => $result['status'],
        ];
    }

    public function smsRecipients(string $type, int $id, int $competitionId): array
    {
        return match ($type) {
            'athlete' => $id > 0 ? $this->smsRecipientAthlete($id) : [],
            'participants' => $this->smsRecipientParticipants($competitionId),
            'coach' => $id > 0 ? $this->smsRecipientCoach($id) : [],
            'coaches' => $this->smsRecipientCoaches(),
            'team' => $id > 0 ? $this->smsRecipientTeam($id) : [],
            default => [],
        };
    }

    private function smsRecipientAthlete(int $athleteId): array
    {
        $stmt = $this->pdo->prepare('SELECT id, student_id, first_name, middle_name, last_name, contact_number FROM athletes WHERE id = ? LIMIT 1');
        $stmt->execute([$athleteId]);
        $athlete = $stmt->fetch();
        if (!$athlete || !trim((string)$athlete['contact_number'])) {
            return [];
        }
        return [[
            'name' => trim(($athlete['first_name'] ?? '') . ' ' . ($athlete['last_name'] ?? '')) . ' (' . $athlete['student_id'] . ')',
            'phone' => trim((string)$athlete['contact_number']),
        ]];
    }

    private function smsRecipientParticipants(int $competitionId): array
    {
        $stmt = $this->pdo->prepare('SELECT a.id, a.student_id, a.first_name, a.middle_name, a.last_name, a.contact_number
                                     FROM competition_participants cp
                                     JOIN athletes a ON a.id = cp.athlete_id
                                     WHERE cp.competition_id = ? AND a.contact_number IS NOT NULL AND a.contact_number <> ""
                                     ORDER BY a.last_name, a.first_name');
        $stmt->execute([$competitionId]);
        return array_map(static function (array $athlete): array {
            return [
                'name' => trim(($athlete['first_name'] ?? '') . ' ' . ($athlete['last_name'] ?? '')) . ' (' . $athlete['student_id'] . ')',
                'phone' => trim((string)$athlete['contact_number']),
            ];
        }, $stmt->fetchAll());
    }

    private function smsRecipientCoach(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT id, name, phone_number FROM users WHERE id = ? AND role = 'coach' AND status = 'active' LIMIT 1");
        $stmt->execute([$userId]);
        $coach = $stmt->fetch();
        if (!$coach || !trim((string)$coach['phone_number'])) {
            return [];
        }
        return [['name' => $coach['name'], 'phone' => trim((string)$coach['phone_number'])]];
    }

    private function smsRecipientCoaches(): array
    {
        $stmt = $this->pdo->query("SELECT id, name, phone_number FROM users WHERE role = 'coach' AND status = 'active' AND phone_number IS NOT NULL AND phone_number <> '' ORDER BY name");
        return array_map(static fn (array $coach): array => ['name' => $coach['name'], 'phone' => trim((string)$coach['phone_number'])], $stmt->fetchAll());
    }

    private function smsRecipientTeam(int $teamId): array
    {
        $stmt = $this->pdo->prepare('SELECT t.name, u.phone_number
                                     FROM teams t
                                     LEFT JOIN users u ON u.id = t.coach_id
                                     WHERE t.id = ? AND t.status = "active" LIMIT 1');
        $stmt->execute([$teamId]);
        $team = $stmt->fetch();
        if (!$team || !trim((string)$team['phone_number'])) {
            return [];
        }
        return [['name' => $team['name'] . ' (Team)', 'phone' => trim((string)$team['phone_number'])]];
    }

    public function sendSmsBulk(array $d): array
    {
        $type = trim((string)($d['recipient_type'] ?? ''));
        $id = (int)($d['recipient_id'] ?? 0);
        $competitionId = (int)($d['competition_id'] ?? 0);
        $message = trim((string)($d['message'] ?? ''));

        if ($message === '') {
            return ['ok' => false, 'message' => 'Notification message is required.'];
        }

        $recipients = $this->smsRecipients($type, $id, $competitionId);
        if (!$recipients) {
            return ['ok' => false, 'message' => 'No recipients with a contact number found for this selection.'];
        }

        $stmt = $this->pdo->prepare('INSERT INTO sms_logs (recipient_name, phone_number, message, status, sent_by, source) VALUES (?,?,?,?,?,?)');
        $sent = 0;
        $failed = 0;
        $currentUser = current_user()['id'] ?? null;

        foreach ($recipients as $recipient) {
            $result = sendSMS($recipient['phone'], $message);
            $stmt->execute([$recipient['name'], $recipient['phone'], $message, $result['status'], $currentUser, 'competition']);
            if ($result['success']) {
                $sent++;
            } else {
                $failed++;
            }
        }

        return [
            'ok' => $failed === 0,
            'message' => 'SMS sent to ' . $sent . ' recipient(s)' . ($failed > 0 ? ', ' . $failed . ' failed but were logged.' : '.'),
            'reload' => true,
        ];
    }

    public function smsLogs(): array
    {
        return $this->pdo->query("SELECT * FROM sms_logs WHERE source = 'competition' ORDER BY sent_at DESC LIMIT 50")->fetchAll();
    }

    public function smsAthletes(): array
    {
        return $this->pdo->query("SELECT id, student_id, first_name, middle_name, last_name, contact_number
                                  FROM athletes
                                  WHERE athlete_status = 'Active'
                                  ORDER BY last_name, first_name")->fetchAll();
    }

    public function smsCoaches(): array
    {
        return $this->pdo->query("SELECT id, name, phone_number
                                  FROM users
                                  WHERE role = 'coach' AND status = 'active'
                                  ORDER BY name")->fetchAll();
    }

    public function smsTeams(): array
    {
        return $this->pdo->query("SELECT t.id, t.name, u.phone_number
                                  FROM teams t
                                  LEFT JOIN users u ON u.id = t.coach_id
                                  WHERE t.status = 'active'
                                  ORDER BY t.name")->fetchAll();
    }

    public function coaches(): array
    {
        return $this->pdo->query("SELECT id, name FROM users WHERE role = 'coach' AND status = 'active' ORDER BY name")->fetchAll();
    }

    public function sports(): array
    {
        return $this->pdo->query("SELECT id, name FROM sports WHERE status = 'active' ORDER BY name")->fetchAll();
    }
}
