<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';

class SportController
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
    }
    public function all(array $filter = []): array
    {
        if (!empty($filter['coach_id'])) {
            $stmt = $this->pdo->prepare('SELECT s.* FROM sports s JOIN coach_sports cs ON cs.sport_id=s.id WHERE cs.coach_id=? ORDER BY s.name');
            $stmt->execute([(int)$filter['coach_id']]);
            return $stmt->fetchAll();
        }
        return $this->pdo->query('SELECT * FROM sports ORDER BY name')->fetchAll();
    }
    public function save(array $d): array
    {
        $id = (int)($d['id'] ?? 0);
        $name = trim($d['name'] ?? '');
        if ($name === '') return ['ok' => false, 'message' => 'Sport name is required.'];
        if ($id) {
            $stmt = $this->pdo->prepare('UPDATE sports SET name=?, description=?, status=? WHERE id=?');
            $stmt->execute([$name, $d['description'] ?? '', $d['status'] ?? 'active', $id]);
        } else {
            $stmt = $this->pdo->prepare('INSERT INTO sports (name,description,status) VALUES (?,?,?)');
            $stmt->execute([$name, $d['description'] ?? '', $d['status'] ?? 'active']);
        }
        return ['ok' => true, 'message' => 'Sport saved.', 'reload' => true];
    }

    public function delete(int $id): array
    {
        $references = [
            ['SELECT COUNT(*) FROM teams WHERE sport_id=?', 'teams'],
            ['SELECT COUNT(*) FROM athletes WHERE sport_id=?', 'athletes'],
            ['SELECT COUNT(*) FROM training_schedules WHERE sport_id=?', 'training schedules'],
            ['SELECT COUNT(*) FROM competitions WHERE sport_id=?', 'competitions'],
        ];
        foreach ($references as [$sql, $label]) {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            if ((int)$stmt->fetchColumn() > 0) {
                return ['ok' => false, 'message' => "Cannot delete this sport because it is used by {$label}."];
            }
        }

        $stmt = $this->pdo->prepare('DELETE FROM sports WHERE id=?');
        $stmt->execute([$id]);
        return ['ok' => true, 'message' => 'Sport deleted.', 'reload' => true];
    }
}
