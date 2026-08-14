<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/upload.php';

class AthleteHistoryController
{
    public const LEVELS = ['School', 'Municipal', 'Provincial', 'Regional', 'National', 'International', 'Other'];
    public const MEDALS = ['None', 'Gold', 'Silver', 'Bronze', 'Other'];

    public function __construct(private PDO $pdo) {}

    public function all(array $filter = []): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filter['athlete_id'])) {
            $where[] = 'h.athlete_id = ?';
            $params[] = (int)$filter['athlete_id'];
        }
        if (!empty($filter['year'])) {
            $where[] = 'h.competition_year = ?';
            $params[] = (int)$filter['year'];
        }
        if (!empty($filter['sport_id'])) {
            $where[] = 'h.sport_id = ?';
            $params[] = (int)$filter['sport_id'];
        }
        if (!empty($filter['level'])) {
            $where[] = 'h.competition_level = ?';
            $params[] = $filter['level'];
        }
        if (!empty($filter['medal'])) {
            $where[] = 'h.medal = ?';
            $params[] = $filter['medal'];
        }
        if (!empty($filter['result'])) {
            $where[] = 'h.result LIKE ?';
            $params[] = '%' . $filter['result'] . '%';
        }
        if (!empty($filter['q'])) {
            $where[] = '(h.competition_name LIKE ? OR a.first_name LIKE ? OR a.last_name LIKE ? OR a.student_id LIKE ? OR h.event_name LIKE ?)';
            $params = array_merge($params, array_fill(0, 5, '%' . $filter['q'] . '%'));
        }

        $sql = 'SELECT h.*, a.student_id, a.first_name, a.middle_name, a.last_name, s.name AS sport_name
                FROM athlete_histories h
                JOIN athletes a ON a.id = h.athlete_id
                LEFT JOIN sports s ON s.id = h.sport_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY h.competition_year DESC, h.id DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function forAthlete(int $athleteId): array
    {
        return $this->all(['athlete_id' => $athleteId]);
    }

    public function stats(int $athleteId): array
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) AS total,
                                            SUM(medal = "Gold") AS gold,
                                            SUM(medal = "Silver") AS silver,
                                            SUM(medal = "Bronze") AS bronze,
                                            SUM(result LIKE "1st%") AS first_place,
                                            SUM(result LIKE "2nd%") AS second_place,
                                            SUM(result LIKE "3rd%") AS third_place
                                     FROM athlete_histories WHERE athlete_id = ?');
        $stmt->execute([$athleteId]);
        $row = $stmt->fetch() ?: [];
        $total = (int)($row['total'] ?? 0);
        $gold = (int)($row['gold'] ?? 0);
        $silver = (int)($row['silver'] ?? 0);
        $bronze = (int)($row['bronze'] ?? 0);
        return [
            'total' => $total,
            'gold' => $gold,
            'silver' => $silver,
            'bronze' => $bronze,
            'medals' => $gold + $silver + $bronze,
            'first_place' => (int)($row['first_place'] ?? 0),
            'second_place' => (int)($row['second_place'] ?? 0),
            'third_place' => (int)($row['third_place'] ?? 0),
        ];
    }

    public function athleteIdForUser(int $userId): int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM athletes WHERE user_id = ? LIMIT 1');
        $stmt->execute([$userId]);
        return (int)($stmt->fetchColumn() ?: 0);
    }

    public function save(array $d, ?array $file = null): array
    {
        $id = (int)($d['id'] ?? 0);
        $athleteId = (int)($d['athlete_id'] ?? 0);
        $isAthlete = (current_user()['role'] ?? '') === 'athlete';

        if ($isAthlete) {
            $athleteId = $this->athleteIdForUser((int)(current_user()['id'] ?? 0));
            if ($athleteId <= 0) {
                return ['ok' => false, 'message' => 'Athlete profile not found.'];
            }
            if ($id > 0) {
                $stmt = $this->pdo->prepare('SELECT athlete_id FROM athlete_histories WHERE id = ? LIMIT 1');
                $stmt->execute([$id]);
                if ((int)$stmt->fetchColumn() !== $athleteId) {
                    return ['ok' => false, 'message' => 'You can only modify your own history records.'];
                }
            }
        } elseif ($athleteId <= 0) {
            return ['ok' => false, 'message' => 'Select an athlete.'];
        }

        $competitionName = trim((string)($d['competition_name'] ?? ''));
        $level = trim((string)($d['competition_level'] ?? ''));
        $sportId = trim((string)($d['sport_id'] ?? ''));
        $year = trim((string)($d['competition_year'] ?? ''));
        $medal = trim((string)($d['medal'] ?? 'None'));

        if ($competitionName === '') {
            return ['ok' => false, 'message' => 'Competition name is required.'];
        }
        if ($level === '' || !in_array($level, self::LEVELS, true)) {
            return ['ok' => false, 'message' => 'Select a valid competition level.'];
        }
        if ($medal === '' || !in_array($medal, self::MEDALS, true)) {
            return ['ok' => false, 'message' => 'Select a valid medal value.'];
        }
        if ($year !== '') {
            if (!ctype_digit($year) || (int)$year < 1900 || (int)$year > (int)date('Y') + 1) {
                return ['ok' => false, 'message' => 'Enter a valid competition year.'];
            }
            $year = (string)(int)$year;
        }
        if ($sportId !== '') {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM sports WHERE id = ?');
            $stmt->execute([(int)$sportId]);
            if ((int)$stmt->fetchColumn() === 0) {
                return ['ok' => false, 'message' => 'Select a valid sport.'];
            }
        }

        $values = [
            'athlete_id' => $athleteId,
            'competition_name' => $competitionName,
            'competition_level' => $level,
            'sport_id' => $sportId === '' ? null : (int)$sportId,
            'event_name' => trim((string)($d['event_name'] ?? '')),
            'competition_year' => $year === '' ? null : (int)$year,
            'organization' => trim((string)($d['organization'] ?? '')),
            'location' => trim((string)($d['location'] ?? '')),
            'result' => trim((string)($d['result'] ?? '')),
            'medal' => $medal,
            'description' => trim((string)($d['description'] ?? '')),
        ];

        $proofPath = null;
        $proofName = null;
        if ($id > 0) {
            $stmt = $this->pdo->prepare('SELECT proof_file, proof_name FROM athlete_histories WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            $existing = $stmt->fetch() ?: [];
            $proofPath = $existing['proof_file'] ?? null;
            $proofName = $existing['proof_name'] ?? null;
        }

        if ($file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $upload = upload_file($file, __DIR__ . '/../../public/uploads/athlete_history', ['pdf', 'jpg', 'jpeg', 'png'], 8);
            if (!$upload['ok']) {
                return ['ok' => false, 'message' => $upload['message']];
            }
            $proofPath = 'uploads/athlete_history/' . $upload['name'];
            $proofName = $file['name'];
        }

        if ($id > 0) {
            $stmt = $this->pdo->prepare('UPDATE athlete_histories SET
                athlete_id=?, competition_name=?, competition_level=?, sport_id=?, event_name=?,
                competition_year=?, organization=?, location=?, result=?, medal=?, description=?,
                proof_file=?, proof_name=? WHERE id=?');
            $stmt->execute([
                $values['athlete_id'], $values['competition_name'], $values['competition_level'], $values['sport_id'],
                $values['event_name'], $values['competition_year'], $values['organization'], $values['location'],
                $values['result'], $values['medal'], $values['description'], $proofPath, $proofName, $id,
            ]);
        } else {
            $stmt = $this->pdo->prepare('INSERT INTO athlete_histories
                (athlete_id, competition_name, competition_level, sport_id, event_name, competition_year,
                 organization, location, result, medal, description, proof_file, proof_name, created_by)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([
                $values['athlete_id'], $values['competition_name'], $values['competition_level'], $values['sport_id'],
                $values['event_name'], $values['competition_year'], $values['organization'], $values['location'],
                $values['result'], $values['medal'], $values['description'], $proofPath, $proofName,
                current_user()['id'] ?? null,
            ]);
        }

        return ['ok' => true, 'message' => 'Athletic history saved.', 'reload' => true];
    }

    public function delete(int $id): array
    {
        $isAthlete = (current_user()['role'] ?? '') === 'athlete';
        if ($isAthlete) {
            $athleteId = $this->athleteIdForUser((int)(current_user()['id'] ?? 0));
            $stmt = $this->pdo->prepare('SELECT athlete_id FROM athlete_histories WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            if ((int)$stmt->fetchColumn() !== $athleteId) {
                return ['ok' => false, 'message' => 'You can only delete your own history records.'];
            }
        }

        $stmt = $this->pdo->prepare('DELETE FROM athlete_histories WHERE id = ?');
        $stmt->execute([$id]);
        return ['ok' => true, 'message' => 'Athletic history deleted.', 'reload' => true];
    }

    public function athleteAchievements(): array
    {
        return $this->pdo->query('SELECT a.id, a.student_id, CONCAT(a.last_name, ", ", a.first_name) AS athlete_name,
                                        COUNT(h.id) AS competitions,
                                        SUM(h.medal = "Gold") AS gold,
                                        SUM(h.medal = "Silver") AS silver,
                                        SUM(h.medal = "Bronze") AS bronze,
                                        SUM(h.medal IN ("Gold","Silver","Bronze")) AS medals,
                                        MAX(h.competition_level) AS top_level
                                 FROM athletes a
                                 LEFT JOIN athlete_histories h ON h.athlete_id = a.id
                                 GROUP BY a.id
                                 HAVING competitions > 0
                                 ORDER BY medals DESC, competitions DESC, athlete_name')->fetchAll();
    }

    public function medalsBySport(): array
    {
        return $this->pdo->query('SELECT COALESCE(s.name, "Other") AS sport_name,
                                        COUNT(h.id) AS total,
                                        SUM(h.medal = "Gold") AS gold,
                                        SUM(h.medal = "Silver") AS silver,
                                        SUM(h.medal = "Bronze") AS bronze
                                 FROM athlete_histories h
                                 LEFT JOIN sports s ON s.id = h.sport_id
                                 GROUP BY h.sport_id
                                 ORDER BY total DESC, sport_name')->fetchAll();
    }

    public function medalsByYear(): array
    {
        return $this->pdo->query('SELECT h.competition_year AS year,
                                        COUNT(h.id) AS total,
                                        SUM(h.medal = "Gold") AS gold,
                                        SUM(h.medal = "Silver") AS silver,
                                        SUM(h.medal = "Bronze") AS bronze
                                 FROM athlete_histories h
                                 WHERE h.competition_year IS NOT NULL
                                 GROUP BY h.competition_year
                                 ORDER BY h.competition_year DESC')->fetchAll();
    }
}
