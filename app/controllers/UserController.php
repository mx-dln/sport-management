<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';

class UserController
{
    public function __construct(private PDO $pdo) {}

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

        if ($id > 0) {
            $stmt = $this->pdo->prepare('UPDATE users SET name=?, email=?, phone_number=?, role=?, status=? WHERE id=?');
            $stmt->execute([$name, $email, $phone, $role, $status, $id]);
        } else {
            $password = password_hash($data['password'] ?: 'password123', PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare('INSERT INTO users (name,email,phone_number,password,role,status) VALUES (?,?,?,?,?,?)');
            $stmt->execute([$name, $email, $phone, $password, $role, $status]);
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
