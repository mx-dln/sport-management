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

    public function delete(int $id): array
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id=? AND id<>?');
        $stmt->execute([$id, current_user()['id'] ?? 0]);
        return ['ok' => true, 'message' => 'User deleted.', 'reload' => true];
    }
}
