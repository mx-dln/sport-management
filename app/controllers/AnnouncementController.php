<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/ScheduleController.php';

class AnnouncementController
{
    public function __construct(private PDO $pdo) {}
    public function all(array $f = []): array
    {
        $stmt = $this->pdo->query('SELECT an.*, s.name sport_name, t.name team_name, u.name created_by_name FROM announcements an LEFT JOIN sports s ON s.id=an.sport_id LEFT JOIN teams t ON t.id=an.team_id LEFT JOIN users u ON u.id=an.created_by ORDER BY an.created_at DESC');
        return $stmt->fetchAll();
    }
    public function save(array $d): array
    {
        $id = (int)($d['id'] ?? 0);

        $sportId = ($d['sport_id'] ?? '') !== '' ? (int)$d['sport_id'] : null;
        $teamId = ($d['team_id'] ?? '') !== '' ? (int)$d['team_id'] : null;

        if ($id > 0) {
            if (!$this->canManage((int)$id)) {
                return ['ok' => false, 'message' => 'You can only edit your own announcements.'];
            }
            $stmt = $this->pdo->prepare('UPDATE announcements SET title=?,body=?,sport_id=?,team_id=? WHERE id=?');
            $stmt->execute([$d['title'], $d['body'], $sportId, $teamId, $id]);
        } else {
            $stmt = $this->pdo->prepare('INSERT INTO announcements (title,body,sport_id,team_id,created_by) VALUES (?,?,?,?,?)');
            $stmt->execute([$d['title'], $d['body'], $sportId, $teamId, current_user()['id'] ?? null]);
        }
        if (!empty($d['send_sms']) && !empty($d['team_id'])) {
            (new ScheduleController($this->pdo))->notifyTeam((int)$d['team_id'], $d['title'] . ': ' . $d['body']);
        }
        return ['ok' => true, 'message' => 'Announcement saved.', 'reload' => true];
    }

    public function delete(int $id): array
    {
        if (!$this->canManage($id)) {
            return ['ok' => false, 'message' => 'You can only delete your own announcements.'];
        }
        $stmt = $this->pdo->prepare('DELETE FROM announcements WHERE id=?');
        $stmt->execute([$id]);
        return ['ok' => true, 'message' => 'Announcement deleted.', 'reload' => true];
    }

    private function canManage(int $id): bool
    {
        if (in_array((current_user()['role'] ?? ''), ['admin', 'sports_coordinator'], true)) {
            return true;
        }
        $stmt = $this->pdo->prepare('SELECT created_by FROM announcements WHERE id=?');
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn() === (int)(current_user()['id'] ?? 0);
    }
}
