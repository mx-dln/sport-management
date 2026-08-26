<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function normalize_phone_number(string $phone): string
{
    $digits = preg_replace('/\D+/', '', $phone);

    if (str_starts_with($digits, '63')) {
        return $digits;
    }

    if (str_starts_with($digits, '0') && strlen($digits) === 11) {
        return '63' . substr($digits, 1);
    }

    return $digits;
}

function decode_secret(string $value): string
{
    return str_starts_with($value, 'base64:') ? (string)base64_decode(substr($value, 7)) : $value;
}

function sendSMS(string $phone, string $message): array
{
    $configPath = __DIR__ . '/../config/sms.php';
    $examplePath = __DIR__ . '/../config/sms.example.php';
    $config = require (is_file($configPath) ? $configPath : $examplePath);

    if (!$config['enabled']) {
        return [
            'success' => true,
            'status' => 'queued-placeholder',
            'response' => 'Semaphore integration is disabled. Message was logged only.',
        ];
    }

    $message = trim($message);

    if ($phone === '' || $message === '') {
        return [
            'success' => false,
            'status' => 'invalid-input',
            'response' => 'Phone number and message are required.',
        ];
    }

    $phone = normalize_phone_number($phone);

    $payload = http_build_query([
        'apikey' => decode_secret($config['api_key']),
        'number' => $phone,
        'message' => $message,
        'sendername' => decode_secret($config['sender_name']),
    ]);

    $ch = curl_init($config['endpoint']);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    ]);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($error !== '') {
        return [
            'success' => false,
            'status' => 'curl-failed',
            'response' => $error,
        ];
    }

    $decoded = json_decode((string)$response, true);
    $apiError = is_array($decoded) && isset($decoded['message']) ? (string)$decoded['message'] : '';

    return [
        'success' => $code >= 200 && $code < 300,
        'status' => $code >= 200 && $code < 300 ? "sent-http-{$code}" : "failed-http-{$code}",
        'response' => $apiError !== '' ? $apiError : (string)$response,
    ];
}
