<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/upload.php';

class MedicalController
{
    public function __construct(private PDO $pdo) {}

    public function records(array $filter = []): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filter['q'])) {
            $where[] = '(a.student_id LIKE ? OR a.first_name LIKE ? OR a.last_name LIKE ?)';
            $params = array_merge($params, array_fill(0, 3, '%' . $filter['q'] . '%'));
        }
        if (!empty($filter['clearance_status'])) {
            $where[] = 'm.clearance_status = ?';
            $params[] = $filter['clearance_status'];
        }
        if (!empty($filter['athlete_id'])) {
            $where[] = 'm.athlete_id = ?';
            $params[] = (int)$filter['athlete_id'];
        }

        $sql = 'SELECT m.*, a.student_id, a.first_name, a.middle_name, a.last_name,
                       s.name AS sport_name, t.name AS team_name
                FROM medical_records m
                JOIN athletes a ON a.id = m.athlete_id
                LEFT JOIN sports s ON s.id = a.sport_id
                LEFT JOIN teams t ON t.id = a.team_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY m.exam_date DESC, m.id DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function record(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM medical_records WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    public function athleteBasics(int $athleteId): array
    {
        $stmt = $this->pdo->prepare('SELECT height, weight, blood_type, medical_condition FROM athletes WHERE id = ? LIMIT 1');
        $stmt->execute([$athleteId]);
        $athlete = $stmt->fetch();
        return ['ok' => true, 'athlete' => $athlete ?: null];
    }

    public function save(array $d, ?array $file = null): array
    {
        $id = (int)($d['id'] ?? 0);
        $athleteId = (int)($d['athlete_id'] ?? 0);

        if ($athleteId <= 0) {
            return ['ok' => false, 'message' => 'Select an athlete.'];
        }

        $values = [
            'exam_date' => trim((string)($d['exam_date'] ?? '')) ?: null,
            'height' => trim((string)($d['height'] ?? '')),
            'weight' => trim((string)($d['weight'] ?? '')),
            'blood_type' => trim((string)($d['blood_type'] ?? '')),
            'blood_pressure' => trim((string)($d['blood_pressure'] ?? '')),
            'heart_rate' => trim((string)($d['heart_rate'] ?? '')),
            'allergies' => trim((string)($d['allergies'] ?? '')),
            'medical_conditions' => trim((string)($d['medical_conditions'] ?? '')),
            'medications' => trim((string)($d['medications'] ?? '')),
            'injury_history' => trim((string)($d['injury_history'] ?? '')),
            'recent_injury' => trim((string)($d['recent_injury'] ?? '')),
            'fitness_status' => trim((string)($d['fitness_status'] ?? '')),
            'clearance_status' => trim((string)($d['clearance_status'] ?? '')) ?: null,
            'physician_name' => trim((string)($d['physician_name'] ?? '')),
            'physician_remarks' => trim((string)($d['physician_remarks'] ?? '')),
            'next_checkup_date' => trim((string)($d['next_checkup_date'] ?? '')) ?: null,
        ];

        $existing = null;
        if ($id > 0) {
            $existing = $this->record($id);
            if (!$existing) {
                return ['ok' => false, 'message' => 'Medical record not found.'];
            }
            $athleteId = (int)$existing['athlete_id'];
        }

        $certificatePath = $existing['certificate_path'] ?? null;
        $certificateName = $existing['certificate_name'] ?? null;

        if (!empty($file['name'])) {
            $upload = upload_file($file, __DIR__ . '/../../public/uploads/medical_certificates', ['pdf', 'jpg', 'jpeg', 'png'], 8);
            if (!$upload['ok']) {
                return ['ok' => false, 'message' => $upload['message']];
            }
            $certificatePath = 'uploads/medical_certificates/' . $upload['name'];
            $certificateName = $file['name'];
        }

        if ($id > 0) {
            $stmt = $this->pdo->prepare('UPDATE medical_records SET
                athlete_id=?, exam_date=?, height=?, weight=?, blood_type=?, blood_pressure=?, heart_rate=?,
                allergies=?, medical_conditions=?, medications=?, injury_history=?, recent_injury=?,
                fitness_status=?, clearance_status=?, certificate_path=?, certificate_name=?,
                physician_name=?, physician_remarks=?, next_checkup_date=?
                WHERE id=?');
            $stmt->execute([
                $athleteId, $values['exam_date'], $values['height'], $values['weight'], $values['blood_type'],
                $values['blood_pressure'], $values['heart_rate'], $values['allergies'], $values['medical_conditions'],
                $values['medications'], $values['injury_history'], $values['recent_injury'], $values['fitness_status'],
                $values['clearance_status'], $certificatePath, $certificateName, $values['physician_name'],
                $values['physician_remarks'], $values['next_checkup_date'], $id,
            ]);
        } else {
            $stmt = $this->pdo->prepare('INSERT INTO medical_records
                (athlete_id, exam_date, height, weight, blood_type, blood_pressure, heart_rate,
                 allergies, medical_conditions, medications, injury_history, recent_injury,
                 fitness_status, clearance_status, certificate_path, certificate_name,
                 physician_name, physician_remarks, next_checkup_date, recorded_by)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([
                $athleteId, $values['exam_date'], $values['height'], $values['weight'], $values['blood_type'],
                $values['blood_pressure'], $values['heart_rate'], $values['allergies'], $values['medical_conditions'],
                $values['medications'], $values['injury_history'], $values['recent_injury'], $values['fitness_status'],
                $values['clearance_status'], $certificatePath, $certificateName, $values['physician_name'],
                $values['physician_remarks'], $values['next_checkup_date'], current_user()['id'] ?? null,
            ]);
        }

        return ['ok' => true, 'message' => 'Medical record saved.', 'reload' => true];
    }

    public function delete(int $id): array
    {
        $stmt = $this->pdo->prepare('DELETE FROM medical_records WHERE id = ?');
        $stmt->execute([$id]);
        return ['ok' => true, 'message' => 'Medical record deleted.', 'reload' => true];
    }

    public function forAthlete(int $athleteId): array
    {
        $stmt = $this->pdo->prepare('SELECT m.*, a.student_id, a.first_name, a.middle_name, a.last_name,
                                            s.name AS sport_name, t.name AS team_name
                                     FROM medical_records m
                                     JOIN athletes a ON a.id = m.athlete_id
                                     LEFT JOIN sports s ON s.id = a.sport_id
                                     LEFT JOIN teams t ON t.id = a.team_id
                                     WHERE m.athlete_id = ?
                                     ORDER BY m.exam_date DESC, m.id DESC');
        $stmt->execute([$athleteId]);
        return $stmt->fetchAll();
    }
}
