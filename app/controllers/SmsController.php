<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/sms_helper.php';

class SmsController
{
    public function __construct(private PDO $pdo) {}
    public function logs(): array { return $this->pdo->query('SELECT sl.*, u.name sent_by_name FROM sms_logs sl LEFT JOIN users u ON u.id=sl.sent_by ORDER BY sent_at DESC')->fetchAll(); }
    public function send(string $name, string $phone, string $message): array
    {
        $result = sendSMS($phone, $message);
        $stmt = $this->pdo->prepare('INSERT INTO sms_logs (recipient_name, phone_number, message, status, sent_by) VALUES (?,?,?,?,?)');
        $stmt->execute([$name, $phone, $message, $result['status'], current_user()['id'] ?? null]);
        return [
            'ok' => $result['success'],
            'message' => $result['success'] ? 'SMS sent and logged.' : 'SMS failed but was logged.',
            'status' => $result['status'],
            'response' => $result['response'],
        ];
    }
}
