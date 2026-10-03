<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';

class UserController
{
    public function __construct(private PDO $pdo) { $this->ensureCoachSportsTable(); }

    private function ensureCoachSportsTable(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS coach_sports (
            coach_id INT NOT NULL,
            sport_id INT NOT NULL,
            assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (coach_id, sport_id),
            INDEX idx_coach_sports_sport (sport_id)
        )");
        $this->pdo->exec("INSERT IGNORE INTO coach_sports (coach_id, sport_id)
            SELECT DISTINCT coach_id, sport_id FROM teams WHERE coach_id IS NOT NULL AND sport_id IS NOT NULL");
    }

    public function sports(): array
    {
        return $this->pdo->query("SELECT id, name FROM sports WHERE status='active' ORDER BY name")->fetchAll();
    }

    public function coachSportIds(int $coachId): array
    {
        $stmt = $this->pdo->prepare('SELECT sport_id FROM coach_sports WHERE coach_id=? ORDER BY sport_id');
        $stmt->execute([$coachId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    public function coachSportsMap(): array
    {
        $rows = $this->pdo->query('SELECT coach_id, sport_id FROM coach_sports ORDER BY coach_id, sport_id')->fetchAll();
        $map = [];
        foreach ($rows as $row) {
            $map[(int)$row['coach_id']][] = (int)$row['sport_id'];
        }
        return $map;
    }

    public function saveCoachSports(int $coachId, array $sportIds): array
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE id=? AND role='coach'");
        $stmt->execute([$coachId]);
        if ((int)$stmt->fetchColumn() === 0) {
            return ['ok' => false, 'message' => 'Please select a valid coach account.'];
        }

        $sportIds = array_values(array_unique(array_filter(array_map('intval', $sportIds), static fn (int $id): bool => $id > 0)));
        $this->pdo->beginTransaction();
        try {
            $delete = $this->pdo->prepare('DELETE FROM coach_sports WHERE coach_id=?');
            $delete->execute([$coachId]);
            if ($sportIds) {
                $insert = $this->pdo->prepare('INSERT INTO coach_sports (coach_id, sport_id) VALUES (?, ?)');
                foreach ($sportIds as $sportId) {
                    $insert->execute([$coachId, $sportId]);
                }
            }
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
        return ['ok' => true, 'message' => 'Coach sport delegation updated.', 'reload' => true];
    }

    public function all(string $search = ''): array
    {
        $sql = 'SELECT * FROM users WHERE role <> "admin" AND (name LIKE ? OR email LIKE ? OR role LIKE ?) ORDER BY created_at DESC';
        $term = "%{$search}%";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$term, $term, $term]);
        return $stmt->fetchAll();
    }

    public function save(array $data): array
    {
        $id = (int)($data['id'] ?? 0);
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone_number'] ?? '');
        $role = trim($data['role'] ?? 'athlete');
        $status = trim($data['status'] ?? 'active');

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'message' => 'Name and valid email are required.'];
        }

        if ($phone !== '' && !preg_match('/^\d{11}$/', $phone)) {
            return ['ok' => false, 'message' => 'Phone number must be exactly 11 digits (e.g. 0912 3456 789).'];
        }

        if ($id > 0) {
            $stmt = $this->pdo->prepare('UPDATE users SET name=?, email=?, phone_number=?, role=?, status=? WHERE id=?');
            $stmt->execute([$name, $email, $phone, $role, $status, $id]);
        } else {
            $password = password_hash($data['password'] ?: 'password123', PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare('INSERT INTO users (name,email,phone_number,password,role,status) VALUES (?,?,?,?,?,?)');
            $stmt->execute([$name, $email, $phone, $password, $role, $status]);
            $id = (int)$this->pdo->lastInsertId();
        }

        if ($role === 'coach') {
            $this->saveCoachSports($id, $data['sport_ids'] ?? []);
        } else {
            $this->pdo->prepare('DELETE FROM coach_sports WHERE coach_id=?')->execute([$id]);
        }

        return ['ok' => true, 'message' => 'User saved successfully.', 'reload' => true];
    }

    public function status(int $id, string $status): array
    {
        $stmt = $this->pdo->prepare('UPDATE users SET status=? WHERE id=?');
        $stmt->execute([$status, $id]);
        return ['ok' => true, 'message' => 'User status updated.'];
    }

    public function resetPassword(int $id, string $defaultPassword = 'password123'): array
    {
        if ($id === (int)(current_user()['id'] ?? 0)) {
            return ['ok' => false, 'message' => 'You cannot reset your own password here.'];
        }

        $stmt = $this->pdo->prepare('UPDATE users SET password=? WHERE id=? AND role <> "admin"');
        $stmt->execute([password_hash($defaultPassword, PASSWORD_DEFAULT), $id]);

        if ($stmt->rowCount() < 1) {
            return ['ok' => false, 'message' => 'Unable to reset password for this user.'];
        }

        return ['ok' => true, 'message' => 'User password reset to default: ' . $defaultPassword];
    }

    public function delete(int $id): array
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id=? AND id<>?');
        $stmt->execute([$id, current_user()['id'] ?? 0]);
        return ['ok' => true, 'message' => 'User deleted.', 'reload' => true];
    }
}
