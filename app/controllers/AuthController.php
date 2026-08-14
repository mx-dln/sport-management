<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/upload.php';

class AuthController
{
    public function __construct(private PDO $pdo) {}

    public function sports(): array
    {
        return $this->pdo->query("SELECT id, name FROM sports WHERE status='active' ORDER BY name")->fetchAll();
    }

    public function teams(): array
    {
        return $this->pdo->query("SELECT id, name FROM teams WHERE status='active' ORDER BY name")->fetchAll();
    }

    public function requirements(): array
    {
        return $this->pdo->query('SELECT id, title, description, is_required FROM requirement_types ORDER BY is_required DESC, title')->fetchAll();
    }

    public function login(array $data): void
    {
        $email = trim($data['email'] ?? '');
        $password = (string)($data['password'] ?? '');

        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ? AND status = "active" LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            flash('error', 'Invalid login credentials or inactive account.');
            redirect(app_url('login.php'));
        }

        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];

        redirect(dashboard_path($user['role']));
    }

    public function registerAthlete(array $data, array $files = []): void
    {
        $studentId = trim($data['student_id'] ?? '');
        $firstName = trim($data['first_name'] ?? '');
        $middleName = trim($data['middle_name'] ?? '');
        $lastName = trim($data['last_name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = (string)($data['password'] ?? '');
        $confirmPassword = (string)($data['confirm_password'] ?? '');
        $contactNumber = trim($data['contact_number'] ?? '');
        $sportId = trim((string)($data['sport_id'] ?? ''));
        $birthdate = trim($data['birthdate'] ?? '');
        $gender = trim($data['gender'] ?? '');
        $address = trim($data['address'] ?? '');
        $course = trim($data['course'] ?? '');
        $yearLevel = trim($data['year_level'] ?? '');
        $section = trim($data['section'] ?? '');
        $guardianName = trim($data['guardian_name'] ?? '');
        $guardianContact = trim($data['guardian_contact'] ?? '');
        $emergencyContact = trim($data['emergency_contact'] ?? '');
        $height = trim($data['height'] ?? '');
        $weight = trim($data['weight'] ?? '');
        $bloodType = trim($data['blood_type'] ?? '');
        $medicalCondition = trim($data['medical_condition'] ?? '');
        $position = trim($data['position'] ?? '');

        if ($studentId === '' || $firstName === '' || $lastName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Student ID, name, and valid email are required.');
            redirect(app_url('register.php'));
        }

        if (strlen($password) < 6 || $password !== $confirmPassword) {
            flash('error', 'Password must be at least 6 characters and match the confirmation.');
            redirect(app_url('register.php'));
        }

        $exists = $this->pdo->prepare('SELECT 1 FROM users WHERE email=? UNION SELECT 1 FROM athletes WHERE student_id=? LIMIT 1');
        $exists->execute([$email, $studentId]);
        if ($exists->fetchColumn()) {
            flash('error', 'Email or student ID is already registered.');
            redirect(app_url('register.php'));
        }

        $this->pdo->beginTransaction();
        try {
            $fullName = trim($firstName . ' ' . $lastName);
            $stmt = $this->pdo->prepare('INSERT INTO users (name,email,password,role,status) VALUES (?,?,?,?,?)');
            $stmt->execute([$fullName, $email, password_hash($password, PASSWORD_DEFAULT), 'athlete', 'active']);
            $userId = (int)$this->pdo->lastInsertId();

            $stmt = $this->pdo->prepare('INSERT INTO athletes (user_id,student_id,first_name,middle_name,last_name,gender,birthdate,age,address,course,year_level,section,contact_number,guardian_name,guardian_contact,emergency_contact,height,weight,blood_type,medical_condition,sport_id,team_id,position,athlete_status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([
                $userId,
                $studentId,
                $firstName,
                $middleName,
                $lastName,
                $gender,
                $birthdate,
                age_from_birthdate($birthdate),
                $address,
                $course,
                $yearLevel,
                $section,
                $contactNumber,
                $guardianName,
                $guardianContact,
                $emergencyContact,
                $height,
                $weight,
                $bloodType,
                $medicalCondition,
                $sportId === '' ? null : (int)$sportId,
                null,
                $position,
                'Active',
            ]);
            $athleteId = (int)$this->pdo->lastInsertId();

            $documentFiles = $files['documents'] ?? null;
            $profilePhotoPath = '';
            if (is_array($documentFiles['name'] ?? null)) {
                foreach ($documentFiles['name'] as $requirementId => $originalName) {
                    if (($documentFiles['error'][$requirementId] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }

                    $file = [
                        'name' => $originalName,
                        'type' => $documentFiles['type'][$requirementId] ?? '',
                        'tmp_name' => $documentFiles['tmp_name'][$requirementId] ?? '',
                        'error' => $documentFiles['error'][$requirementId] ?? UPLOAD_ERR_NO_FILE,
                        'size' => $documentFiles['size'][$requirementId] ?? 0,
                    ];
                    $upload = upload_file($file, __DIR__ . '/../../public/uploads/athlete_documents', ['pdf','jpg','jpeg','png'], 8);
                    if (!$upload['ok']) {
                        throw new RuntimeException($upload['message']);
                    }

                    $stmt = $this->pdo->prepare('INSERT INTO athlete_documents (athlete_id, requirement_type_id, file_path, original_name, status, remarks) VALUES (?,?,?,?,?,?)');
                    $filePath = 'uploads/athlete_documents/' . $upload['name'];
                    $stmt->execute([$athleteId, (int)$requirementId, $filePath, $originalName, 'Submitted', 'Uploaded during registration']);

                    if ($profilePhotoPath === '' && preg_match('/\.(jpe?g|png)$/i', $filePath)) {
                        $stmt = $this->pdo->prepare('SELECT title FROM requirement_types WHERE id=? LIMIT 1');
                        $stmt->execute([(int)$requirementId]);
                        $title = strtolower((string)$stmt->fetchColumn());
                        if (str_contains($title, '2x2') || str_contains($title, 'picture') || str_contains($title, 'photo')) {
                            $profilePhotoPath = $filePath;
                        }
                    }
                }
            }

            if ($profilePhotoPath !== '') {
                $stmt = $this->pdo->prepare('UPDATE athletes SET profile_photo=? WHERE id=?');
                $stmt->execute([$profilePhotoPath, $athleteId]);
            }

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            flash('error', 'Registration failed. ' . $e->getMessage());
            redirect(app_url('register.php'));
        }

        $_SESSION['user'] = [
            'id' => $userId,
            'name' => $fullName,
            'email' => $email,
            'role' => 'athlete',
        ];

        redirect(app_url('register_success.php'));
    }
}
