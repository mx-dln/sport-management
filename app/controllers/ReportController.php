<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';

class ReportController
{
    public function __construct(private PDO $pdo) {}
    public function dashboardCounts(): array
    {
        return [
            'athletes' => (int)$this->pdo->query('SELECT COUNT(*) FROM athletes')->fetchColumn(),
            'sports' => (int)$this->pdo->query('SELECT COUNT(*) FROM sports')->fetchColumn(),
            'teams' => (int)$this->pdo->query('SELECT COUNT(*) FROM teams')->fetchColumn(),
            'coaches' => (int)$this->pdo->query("SELECT COUNT(*) FROM users WHERE role='coach'")->fetchColumn(),
            'pending_documents' => (int)$this->pdo->query("SELECT COUNT(*) FROM athlete_documents WHERE status IN ('Pending','Submitted')")->fetchColumn(),
            'sms_sent' => (int)$this->pdo->query('SELECT COUNT(*) FROM sms_logs')->fetchColumn(),
        ];
    }
    public function missingRequirements(): array
    {
        return $this->pdo->query('SELECT a.id, a.student_id, CONCAT(a.last_name,", ",a.first_name) athlete_name, rt.title requirement_title FROM athletes a CROSS JOIN requirement_types rt LEFT JOIN athlete_documents ad ON ad.athlete_id=a.id AND ad.requirement_type_id=rt.id WHERE rt.is_required=1 AND ad.id IS NULL ORDER BY athlete_name, rt.title')->fetchAll();
    }
}
