<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/upload.php';

class AthleteController
{
    public function __construct(private PDO $pdo) {}

    public function all(array $filter = []): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filter['sport_id'])) {
            $where[] = 'EXISTS (SELECT 1 FROM team_members tmf JOIN teams tf ON tf.id=tmf.team_id WHERE tmf.athlete_id=a.id AND tf.sport_id=?)';
            $params[] = $filter['sport_id'];
        }
        if (!empty($filter['team_id'])) {
            $where[] = 'EXISTS (SELECT 1 FROM team_members tmf WHERE tmf.athlete_id=a.id AND tmf.team_id=?)';
            $params[] = $filter['team_id'];
        }
        if (!empty($filter['athlete_status'])) {
            $where[] = 'a.athlete_status = ?';
            $params[] = $filter['athlete_status'];
        }
        if (!empty($filter['q'])) {
            $where[] = '(a.student_id LIKE ? OR a.first_name LIKE ? OR a.last_name LIKE ?)';
            $params = array_merge($params, array_fill(0, 3, '%' . $filter['q'] . '%'));
        }
        $sql = 'SELECT a.*,
                       COALESCE(NULLIF(GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR ", "), ""), ps.name) sport_name,
                       COALESCE(NULLIF(GROUP_CONCAT(DISTINCT t.name ORDER BY t.name SEPARATOR ", "), ""), pt.name) team_name
                FROM athletes a
                LEFT JOIN team_members tm ON tm.athlete_id=a.id
                LEFT JOIN teams t ON t.id=tm.team_id
                LEFT JOIN sports s ON s.id=t.sport_id
                LEFT JOIN sports ps ON ps.id=a.sport_id
                LEFT JOIN teams pt ON pt.id=a.team_id
                WHERE ' . implode(' AND ', $where) . '
                GROUP BY a.id
                ORDER BY a.last_name, a.first_name';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT a.*,
                       COALESCE(NULLIF(GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR ", "), ""), ps.name) sport_name,
                       COALESCE(NULLIF(GROUP_CONCAT(DISTINCT t.name ORDER BY t.name SEPARATOR ", "), ""), pt.name) team_name
                FROM athletes a
                LEFT JOIN team_members tm ON tm.athlete_id=a.id
                LEFT JOIN teams t ON t.id=tm.team_id
                LEFT JOIN sports s ON s.id=t.sport_id
                LEFT JOIN sports ps ON ps.id=a.sport_id
                LEFT JOIN teams pt ON pt.id=a.team_id
                WHERE a.id=?
                GROUP BY a.id');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function save(array $data, ?array $file = null): array
    {
        $id = (int)($data['id'] ?? 0);
        $fields = ['user_id','student_id','first_name','middle_name','last_name','gender','birthdate','address','course','year_level','section','contact_number','guardian_name','guardian_contact','emergency_contact','height','weight','blood_type','medical_condition','sport_id','team_id','position','athlete_status'];
        $values = [];
        foreach ($fields as $field) {
            $values[$field] = trim((string)($data[$field] ?? ''));
        }
        foreach (['user_id', 'sport_id', 'team_id'] as $nullableId) {
            $values[$nullableId] = $values[$nullableId] === '' ? null : (int)$values[$nullableId];
        }
        $values['age'] = age_from_birthdate($values['birthdate']);

        $photoSql = '';
        $photoParam = [];
        if ($file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $upload = upload_file($file, __DIR__ . '/../../public/uploads/profile_photos', ['jpg', 'jpeg', 'png'], 3);
            if (!$upload['ok']) {
                return $upload;
            }
            $photoSql = ', profile_photo=?';
            $photoParam[] = 'uploads/profile_photos/' . $upload['name'];
        }

        if ($id > 0) {
            $set = implode('=?, ', array_keys($values)) . '=?' . $photoSql;
            $stmt = $this->pdo->prepare("UPDATE athletes SET {$set} WHERE id=?");
            $stmt->execute([...array_values($values), ...$photoParam, $id]);
        } else {
            $keys = array_keys($values);
            if ($photoParam) {
                $keys[] = 'profile_photo';
                $values['profile_photo'] = $photoParam[0];
            }
            $stmt = $this->pdo->prepare('INSERT INTO athletes (' . implode(',', $keys) . ') VALUES (' . rtrim(str_repeat('?,', count($keys)), ',') . ')');
            $stmt->execute(array_values($values));
            $id = (int)$this->pdo->lastInsertId();
        }

        if (!empty($values['team_id']) && $id > 0) {
            $stmt = $this->pdo->prepare('INSERT IGNORE INTO team_members (team_id, athlete_id) VALUES (?, ?)');
            $stmt->execute([(int)$values['team_id'], $id]);
        }

        return ['ok' => true, 'message' => 'Athlete profile saved.', 'reload' => true];
    }

    public function delete(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT user_id FROM athletes WHERE id=? LIMIT 1');
        $stmt->execute([$id]);
        $userId = (int)($stmt->fetchColumn() ?: 0);

        foreach ([
            'DELETE FROM athlete_documents WHERE athlete_id=?',
            'DELETE FROM medical_records WHERE athlete_id=?',
            'DELETE FROM attendance WHERE athlete_id=?',
            'DELETE FROM team_members WHERE athlete_id=?',
            'DELETE FROM competition_participants WHERE athlete_id=?',
            'DELETE FROM competition_results WHERE athlete_id=?',
            'DELETE FROM athlete_histories WHERE athlete_id=?',
        ] as $sql) {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
        }

        $stmt = $this->pdo->prepare('DELETE FROM athletes WHERE id=?');
        $stmt->execute([$id]);

        if ($userId > 0) {
            $stmt = $this->pdo->prepare('DELETE FROM users WHERE id=?');
            $stmt->execute([$userId]);
        }

        return ['ok' => true, 'message' => 'Athlete and linked records deleted.', 'reload' => true];
    }

    public function documents(int $athleteId): array
    {
        $stmt = $this->pdo->prepare('SELECT rt.title, ad.* FROM requirement_types rt LEFT JOIN athlete_documents ad ON ad.requirement_type_id=rt.id AND ad.athlete_id=? ORDER BY rt.title');
        $stmt->execute([$athleteId]);
        return $stmt->fetchAll();
    }
}
