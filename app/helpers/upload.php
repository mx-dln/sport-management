<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function upload_file(array $file, string $targetDir, array $allowedExt, int $maxMb = 5): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'message' => 'No file uploaded or upload failed.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return ['ok' => false, 'message' => 'Invalid file type.'];
    }

    if (($file['size'] ?? 0) > ($maxMb * 1024 * 1024)) {
        return ['ok' => false, 'message' => "File must not exceed {$maxMb}MB."];
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0775, true);
    }

    $safeName = bin2hex(random_bytes(8)) . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file['name']);
    $destination = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['ok' => false, 'message' => 'Unable to save uploaded file.'];
    }

    return ['ok' => true, 'path' => $destination, 'name' => $safeName, 'ext' => $ext];
}

