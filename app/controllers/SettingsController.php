<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/upload.php';

class SettingsController
{
    private string $settingsPath;

    public function __construct()
    {
        $this->settingsPath = __DIR__ . '/../config/system_settings.json';
    }


    private function decodeSecret(string $value): string
    {
        return str_starts_with($value, 'base64:') ? (string)base64_decode(substr($value, 7)) : $value;
    }

    private function encodeSecret(string $value): string
    {
        return $value === '' ? '' : 'base64:' . base64_encode($value);
    }

    private function smsConfig(): array
    {
        $configPath = __DIR__ . '/../config/sms.php';
        $examplePath = __DIR__ . '/../config/sms.example.php';
        $config = require (is_file($configPath) ? $configPath : $examplePath);
        return is_array($config) ? $config : [];
    }

    public function current(): array
    {
        $settings = app_settings();
        $rawSettings = [];
        if (is_file($this->settingsPath)) {
            $decoded = json_decode((string)file_get_contents($this->settingsPath), true);
            $rawSettings = is_array($decoded) ? $decoded : [];
        }
        $smsConfig = $this->smsConfig();

        if (($settings['semaphore_api_key'] ?? '') === '' && !empty($smsConfig['api_key'])) {
            $settings['semaphore_api_key'] = (string)$smsConfig['api_key'];
        }
        if (($settings['semaphore_sender_name'] ?? '') === '' && !empty($smsConfig['sender_name'])) {
            $settings['semaphore_sender_name'] = (string)$smsConfig['sender_name'];
        }
        if (($settings['semaphore_endpoint'] ?? '') === '' && !empty($smsConfig['endpoint'])) {
            $settings['semaphore_endpoint'] = (string)$smsConfig['endpoint'];
        }
        if (isset($smsConfig['enabled']) && !array_key_exists('semaphore_enabled', $rawSettings)) {
            $settings['semaphore_enabled'] = !empty($smsConfig['enabled']) ? '1' : ($settings['semaphore_enabled'] ?? '0');
        }
        if (!empty($smsConfig['provider'])) {
            $settings['semaphore_provider'] = (string)$smsConfig['provider'];
        }

        $settings['semaphore_api_key_display'] = $this->decodeSecret((string)($settings['semaphore_api_key'] ?? ''));
        $settings['semaphore_sender_name_display'] = $this->decodeSecret((string)($settings['semaphore_sender_name'] ?? ''));
        $settings['smtp_password_display'] = $this->decodeSecret((string)($settings['smtp_password'] ?? ''));

        return $settings;
    }

    public function save(array $data, ?array $iconFile = null): array
    {
        $settings = $this->current();
        $themeColor = trim((string)($data['theme_color'] ?? $settings['theme_color']));

        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $themeColor)) {
            return ['ok' => false, 'message' => 'Theme color must be a valid hex color.'];
        }

        $settings['app_name'] = trim((string)($data['app_name'] ?? '')) ?: $settings['app_name'];
        $settings['app_short_name'] = trim((string)($data['app_short_name'] ?? '')) ?: $settings['app_short_name'];
        $settings['school_name'] = trim((string)($data['school_name'] ?? '')) ?: $settings['school_name'];
        $settings['theme_color'] = $themeColor;

        $settings['semaphore_enabled'] = !empty($data['semaphore_enabled']) ? '1' : '0';
        $settings['semaphore_provider'] = trim((string)($data['semaphore_provider'] ?? 'Semaphore')) ?: 'Semaphore';
        $settings['semaphore_endpoint'] = trim((string)($data['semaphore_endpoint'] ?? '')) ?: 'https://api.semaphore.co/api/v4/messages';
        $settings['semaphore_sender_name'] = $this->encodeSecret(trim((string)($data['semaphore_sender_name'] ?? '')));
        $semaphoreApiKey = trim((string)($data['semaphore_api_key'] ?? ''));
        if ($semaphoreApiKey !== '') {
            $settings['semaphore_api_key'] = $this->encodeSecret($semaphoreApiKey);
        } elseif (!isset($settings['semaphore_api_key'])) {
            $settings['semaphore_api_key'] = '';
        }

        $settings['smtp_enabled'] = !empty($data['smtp_enabled']) ? '1' : '0';
        $settings['smtp_host'] = trim((string)($data['smtp_host'] ?? ''));
        $settings['smtp_port'] = (string)max(1, min(65535, (int)($data['smtp_port'] ?? 587)));
        $settings['smtp_encryption'] = in_array(($data['smtp_encryption'] ?? 'tls'), ['tls', 'ssl', 'none'], true) ? (string)$data['smtp_encryption'] : 'tls';
        $settings['smtp_username'] = trim((string)($data['smtp_username'] ?? ''));
        $smtpPassword = trim((string)($data['smtp_password'] ?? ''));
        if ($smtpPassword !== '') {
            $settings['smtp_password'] = $this->encodeSecret($smtpPassword);
        } elseif (!isset($settings['smtp_password'])) {
            $settings['smtp_password'] = '';
        }
        $settings['smtp_from_email'] = trim((string)($data['smtp_from_email'] ?? ''));
        $settings['smtp_from_name'] = trim((string)($data['smtp_from_name'] ?? ''));

        if ($settings['smtp_from_email'] !== '' && !filter_var($settings['smtp_from_email'], FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'message' => 'SMTP from email must be a valid email address.'];
        }

        if ($iconFile && ($iconFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $upload = upload_file($iconFile, __DIR__ . '/../../public/uploads/branding', ['jpg', 'jpeg', 'png', 'webp', 'ico'], 2);
            if (!$upload['ok']) {
                return $upload;
            }
            $settings['icon_path'] = 'uploads/branding/' . $upload['name'];
        }

        unset($settings['semaphore_api_key_display'], $settings['semaphore_sender_name_display'], $settings['smtp_password_display']);

        $json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false || file_put_contents($this->settingsPath, $json . PHP_EOL) === false) {
            return ['ok' => false, 'message' => 'Unable to save settings.'];
        }

        return ['ok' => true, 'message' => 'System settings saved.', 'reload' => true];
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($digits, '63')) {
            return $digits;
        }
        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            return '63' . substr($digits, 1);
        }
        return $digits;
    }

    public function testSms(array $data): array
    {
        $save = $this->save($data, null);
        if (!$save['ok']) {
            return $save;
        }

        $phone = $this->normalizePhone((string)($data['test_sms_number'] ?? ''));
        if ($phone === '') {
            return ['ok' => false, 'message' => 'Enter a test phone number first.'];
        }

        $apiKey = trim((string)($data['semaphore_api_key'] ?? ''));
        $senderName = trim((string)($data['semaphore_sender_name'] ?? ''));
        $endpoint = trim((string)($data['semaphore_endpoint'] ?? '')) ?: 'https://api.semaphore.co/api/v4/messages';

        if ($apiKey === '') {
            return ['ok' => false, 'message' => 'Semaphore API key is required for the test SMS.'];
        }
        if (empty($data['semaphore_enabled'])) {
            return ['ok' => false, 'message' => 'Enable SMS before sending a test message.'];
        }

        if (!function_exists('curl_init')) {
            return ['ok' => false, 'message' => 'Test SMS failed: PHP cURL extension is not enabled.'];
        }

        $payload = http_build_query([
            'apikey' => $apiKey,
            'number' => $phone,
            'message' => 'SMIS test SMS sent on ' . date('M j, Y g:i A') . '.',
            'sendername' => $senderName,
        ]);

        $ch = curl_init($endpoint);
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
            return ['ok' => false, 'message' => 'Test SMS failed: ' . $error];
        }

        return [
            'ok' => $code >= 200 && $code < 300,
            'message' => $code >= 200 && $code < 300 ? 'Test SMS sent successfully.' : 'Test SMS failed. Semaphore returned HTTP ' . $code . ': ' . (string)$response,
        ];
    }

    private function smtpRead($socket): string
    {
        $data = '';
        while (($line = fgets($socket, 515)) !== false) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $data;
    }

    private function smtpCommand($socket, string $command, array $expected): string
    {
        fwrite($socket, $command . "\r\n");
        $response = $this->smtpRead($socket);
        $code = (int)substr($response, 0, 3);
        if (!in_array($code, $expected, true)) {
            throw new RuntimeException(trim($response) ?: 'SMTP command failed.');
        }
        return $response;
    }

    public function testEmail(array $data): array
    {
        $save = $this->save($data, null);
        if (!$save['ok']) {
            return $save;
        }

        if (empty($data['smtp_enabled'])) {
            return ['ok' => false, 'message' => 'Enable SMTP before sending a test email.'];
        }

        $to = trim((string)($data['test_email_to'] ?? ''));
        $host = trim((string)($data['smtp_host'] ?? ''));
        $port = (int)($data['smtp_port'] ?? 587);
        $encryption = (string)($data['smtp_encryption'] ?? 'tls');
        $username = trim((string)($data['smtp_username'] ?? ''));
        $password = trim((string)($data['smtp_password'] ?? ''));
        $fromEmail = trim((string)($data['smtp_from_email'] ?? '')) ?: $username;
        $fromName = trim((string)($data['smtp_from_name'] ?? '')) ?: app_setting('app_name');

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'message' => 'Enter a valid test recipient email first.'];
        }
        if ($host === '' || $port < 1 || $fromEmail === '' || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'message' => 'SMTP host, port, and valid from email are required.'];
        }

        if (!function_exists('stream_socket_client')) {
            return ['ok' => false, 'message' => 'Test email failed: PHP stream sockets are not available.'];
        }

        $remote = ($encryption === 'ssl' ? 'ssl://' : '') . $host;
        $errno = 0;
        $errstr = '';
        $socket = @stream_socket_client($remote . ':' . $port, $errno, $errstr, 20, STREAM_CLIENT_CONNECT);
        if (!$socket) {
            return ['ok' => false, 'message' => 'SMTP connection failed: ' . ($errstr ?: 'Unable to connect.')];
        }

        stream_set_timeout($socket, 20);

        try {
            $greeting = $this->smtpRead($socket);
            if ((int)substr($greeting, 0, 3) !== 220) {
                throw new RuntimeException(trim($greeting) ?: 'SMTP server did not send a greeting.');
            }

            $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
            $this->smtpCommand($socket, 'EHLO ' . $serverName, [250]);

            if ($encryption === 'tls') {
                $this->smtpCommand($socket, 'STARTTLS', [220]);
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new RuntimeException('Unable to start TLS encryption.');
                }
                $this->smtpCommand($socket, 'EHLO ' . $serverName, [250]);
            }

            if ($username !== '' || $password !== '') {
                $this->smtpCommand($socket, 'AUTH LOGIN', [334]);
                $this->smtpCommand($socket, base64_encode($username), [334]);
                $this->smtpCommand($socket, base64_encode($password), [235]);
            }

            $this->smtpCommand($socket, 'MAIL FROM:<' . $fromEmail . '>', [250]);
            $this->smtpCommand($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
            $this->smtpCommand($socket, 'DATA', [354]);

            $subject = 'SMIS test email';
            $body = 'This is a test email from ' . app_setting('app_name') . ' sent on ' . date('M j, Y g:i A') . '.';
            $headers = [
                'From: ' . sprintf('"%s" <%s>', addslashes($fromName), $fromEmail),
                'To: <' . $to . '>',
                'Subject: ' . $subject,
                'MIME-Version: 1.0',
                'Content-Type: text/plain; charset=UTF-8',
            ];
            fwrite($socket, implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.\r\n");
            $response = $this->smtpRead($socket);
            if ((int)substr($response, 0, 3) !== 250) {
                throw new RuntimeException(trim($response) ?: 'SMTP server rejected the test email.');
            }
            $this->smtpCommand($socket, 'QUIT', [221, 250]);
            fclose($socket);

            return ['ok' => true, 'message' => 'Test email sent successfully.'];
        } catch (Throwable $error) {
            if (is_resource($socket)) {
                fclose($socket);
            }
            return ['ok' => false, 'message' => 'Test email failed: ' . $error->getMessage()];
        }
    }

}
