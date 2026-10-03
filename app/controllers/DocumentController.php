<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/upload.php';

class DocumentController
{
    public function __construct(private PDO $pdo) { $this->ensureRequirementScopeColumns(); }

    private function ensureRequirementScopeColumns(): void
    {
        foreach ([
            "ALTER TABLE requirement_types ADD COLUMN sport_id INT NULL AFTER description",
            "ALTER TABLE requirement_types ADD COLUMN registration_initial TINYINT(1) NOT NULL DEFAULT 0 AFTER is_required",
            "ALTER TABLE requirement_types ADD INDEX idx_requirement_sport (sport_id)",
            "ALTER TABLE requirement_types DROP INDEX title",
            "ALTER TABLE requirement_types ADD UNIQUE KEY unique_requirement_scope (title, sport_id)",
        ] as $sql) {
            try { $this->pdo->exec($sql); } catch (Throwable $e) {}
        }
    }

    public function requirements(?int $sportId = null, bool $registrationOnly = false): array
    {
        $this->ensureRequirementScopeColumns();
        $where = [];
        $params = [];
        if ($sportId !== null) {
            $where[] = '(rt.sport_id IS NULL OR rt.sport_id=?)';
            $params[] = $sportId;
        }
        if ($registrationOnly) {
            $where[] = 'rt.registration_initial=1';
        }
        $sql = 'SELECT rt.*, s.name sport_name FROM requirement_types rt LEFT JOIN sports s ON s.id=rt.sport_id';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= " ORDER BY rt.registration_initial DESC, rt.is_required DESC, COALESCE(s.name, 'All Sports'), rt.title";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function sports(): array
    {
        return $this->pdo->query("SELECT id, name FROM sports WHERE status='active' ORDER BY name")->fetchAll();
    }
    public function templates(): array { return $this->pdo->query('SELECT * FROM form_templates ORDER BY uploaded_at DESC')->fetchAll(); }
    public function uploads(array $f = []): array
    {
        $this->ensureRequirementScopeColumns();
        $stmt = $this->pdo->query('SELECT ad.*, rt.title requirement_title, rt.sport_id requirement_sport_id, s.name requirement_sport_name, CONCAT(a.last_name,", ",a.first_name) athlete_name FROM athlete_documents ad JOIN requirement_types rt ON rt.id=ad.requirement_type_id LEFT JOIN sports s ON s.id=rt.sport_id JOIN athletes a ON a.id=ad.athlete_id ORDER BY ad.uploaded_at DESC');
        return $stmt->fetchAll();
    }

    public function latestUploadMarker(): int
    {
        return (int)$this->pdo->query('SELECT COALESCE(MAX(UNIX_TIMESTAMP(uploaded_at)), 0) FROM athlete_documents')->fetchColumn();
    }

    public function uploadsAfter(int $marker): array
    {
        $stmt = $this->pdo->prepare('SELECT ad.id, ad.athlete_id, ad.original_name, ad.file_path, ad.uploaded_at, UNIX_TIMESTAMP(ad.uploaded_at) upload_marker, rt.title requirement_title, CONCAT(a.last_name,", ",a.first_name) athlete_name FROM athlete_documents ad JOIN requirement_types rt ON rt.id=ad.requirement_type_id JOIN athletes a ON a.id=ad.athlete_id WHERE UNIX_TIMESTAMP(ad.uploaded_at)>? ORDER BY ad.uploaded_at DESC LIMIT 10');
        $stmt->execute([$marker]);
        return $stmt->fetchAll();
    }

    public function saveRequirement(array $d): array
    {
        if (!in_array((current_user()['role'] ?? ''), ['admin', 'sports_coordinator'], true)) {
            return ['ok' => false, 'message' => 'Only admin can manage requirements.'];
        }

        $this->ensureRequirementScopeColumns();
        $id = (int)($d['id'] ?? 0);
        $sportId = trim((string)($d['sport_id'] ?? '')) === '' ? null : (int)$d['sport_id'];
        $registrationInitial = !empty($d['registration_initial']) ? 1 : 0;
        if ($id > 0) {
            $stmt = $this->pdo->prepare('UPDATE requirement_types SET title=?, description=?, sport_id=?, is_required=?, registration_initial=? WHERE id=?');
            $stmt->execute([$d['title'], $d['description'] ?? '', $sportId, !empty($d['is_required']) ? 1 : 0, $registrationInitial, $id]);
        } else {
            $stmt = $this->pdo->prepare('INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE description=VALUES(description), is_required=VALUES(is_required), registration_initial=VALUES(registration_initial)');
            $stmt->execute([$d['title'], $d['description'] ?? '', $sportId, !empty($d['is_required']) ? 1 : 0, $registrationInitial]);
        }
        return ['ok' => true, 'message' => 'Requirement type saved.', 'reload' => true];
    }

    public function deleteRequirement(int $id): array
    {
        if (!in_array((current_user()['role'] ?? ''), ['admin', 'sports_coordinator'], true)) {
            return ['ok' => false, 'message' => 'Only admin can delete requirements.'];
        }

        $stmt = $this->pdo->prepare('DELETE FROM requirement_types WHERE id=?');
        $stmt->execute([$id]);
        return ['ok' => true, 'message' => 'Requirement deleted.', 'reload' => true];
    }

    public function uploadDocument(array $d, array $file): array
    {
        $athleteId = (int)($d['athlete_id'] ?? 0);
        $requirementId = (int)($d['requirement_type_id'] ?? 0);
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM athletes a JOIN requirement_types rt ON rt.id=? WHERE a.id=? AND (rt.sport_id IS NULL OR rt.sport_id=a.sport_id)');
        $stmt->execute([$requirementId, $athleteId]);
        if ((int)$stmt->fetchColumn() === 0) {
            return ['ok' => false, 'message' => 'This requirement does not apply to the selected athlete sport.'];
        }

        $upload = upload_file($file, __DIR__ . '/../../public/uploads/athlete_documents', ['pdf','jpg','jpeg','png'], 8);
        if (!$upload['ok']) return $upload;
        $filePath = 'uploads/athlete_documents/' . $upload['name'];
        $stmt = $this->pdo->prepare('INSERT INTO athlete_documents (athlete_id, requirement_type_id, file_path, original_name, status, remarks) VALUES (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE file_path=VALUES(file_path), original_name=VALUES(original_name), status="Submitted", remarks="", uploaded_at=CURRENT_TIMESTAMP');
        $stmt->execute([$athleteId, $requirementId, $filePath, $file['name'], 'Submitted', $d['remarks'] ?? '']);
        $this->syncProfilePhotoFromRequirement($athleteId, $requirementId, $filePath);
        return ['ok' => true, 'message' => 'Document uploaded.', 'reload' => true];
    }

    private function syncProfilePhotoFromRequirement(int $athleteId, int $requirementTypeId, string $filePath): void
    {
        if (!preg_match('/\.(jpe?g|png)$/i', $filePath)) {
            return;
        }

        $stmt = $this->pdo->prepare('SELECT title FROM requirement_types WHERE id=? LIMIT 1');
        $stmt->execute([$requirementTypeId]);
        $title = strtolower((string)$stmt->fetchColumn());

        if (!str_contains($title, '2x2') && !str_contains($title, 'picture') && !str_contains($title, 'photo')) {
            return;
        }

        $stmt = $this->pdo->prepare('UPDATE athletes SET profile_photo=? WHERE id=?');
        $stmt->execute([$filePath, $athleteId]);
    }
    public function updateStatus(int $id, string $status, string $remarks = ''): array
    {
        if (!in_array((current_user()['role'] ?? ''), ['admin', 'sports_coordinator'], true)) {
            return ['ok' => false, 'message' => 'Only admin can update document status.'];
        }

        $stmt = $this->pdo->prepare('UPDATE athlete_documents SET status=?, remarks=? WHERE id=?');
        $stmt->execute([$status, $remarks, $id]);
        return ['ok' => true, 'message' => 'Document status updated.'];
    }
    public function uploadTemplate(array $d, array $file): array
    {
        if (!in_array((current_user()['role'] ?? ''), ['admin', 'sports_coordinator'], true)) {
            return ['ok' => false, 'message' => 'Only admin can upload templates.'];
        }

        $upload = upload_file($file, __DIR__ . '/../../public/uploads/templates', ['pdf','doc','docx','jpg','jpeg','png'], 10);
        if (!$upload['ok']) return $upload;
        $stmt = $this->pdo->prepare('INSERT INTO form_templates (title,description,file_path,original_name,uploaded_by) VALUES (?,?,?,?,?)');
        $stmt->execute([$d['title'], $d['description'] ?? '', 'uploads/templates/' . $upload['name'], $file['name'], current_user()['id'] ?? null]);
        return ['ok' => true, 'message' => 'Template uploaded.', 'reload' => true];
    }
}
