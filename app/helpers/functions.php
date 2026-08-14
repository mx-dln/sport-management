<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header("Location: {$path}");
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

function age_from_birthdate(?string $birthdate): int
{
    if (!$birthdate) {
        return 0;
    }
    return (int)date_diff(date_create($birthdate), date_create('today'))->y;
}

function format_time_12(?string $time): string
{
    if (!$time) {
        return '';
    }

    $timestamp = strtotime($time);
    return $timestamp ? date('g:i A', $timestamp) : $time;
}

function format_time_range(?string $start, ?string $end): string
{
    $startText = format_time_12($start);
    $endText = format_time_12($end);

    if ($startText && $endText) {
        return "{$startText} - {$endText}";
    }

    return $startText ?: $endText;
}

function format_datetime_12(?string $datetime): string
{
    if (!$datetime) {
        return '';
    }

    $timestamp = strtotime($datetime);
    return $timestamp ? date('M j, Y g:i A', $timestamp) : $datetime;
}

function app_url(string $path = ''): string
{
    $root = project_root_url();
    return $root . '/' . ltrim($path, '/');
}

function project_url(string $path = ''): string
{
    $root = project_root_url();
    return $root . '/' . ltrim($path, '/');
}

function project_root_url(): string
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $root = preg_replace('#/(public|app)(/.*)?$#', '', $script);

    if ($root === $script) {
        $root = preg_replace('#/[^/]*\.php$#', '', $script);
    }

    return $root ?: '';
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function school_name(): string
{
    return app_setting('school_name', 'School / Client Name');
}

function app_settings(): array
{
    static $settings = null;
    if ($settings !== null) {
        return $settings;
    }

    $defaults = [
        'app_name' => 'Sports Management Information System',
        'app_short_name' => 'SMIS',
        'app_version' => '1.9.0',
        'school_name' => 'School / Client Name',
        'theme_color' => '#2563eb',
        'icon_path' => '',
    ];
    $path = __DIR__ . '/../config/system_settings.json';
    if (!is_file($path)) {
        return $settings = $defaults;
    }

    $data = json_decode((string)file_get_contents($path), true);
    return $settings = array_merge($defaults, is_array($data) ? $data : []);
}

function app_setting(string $key, string $default = ''): string
{
    $settings = app_settings();
    return (string)($settings[$key] ?? $default);
}

function app_version(): string
{
    return app_setting('app_version', '1.8.0');
}

function app_icon_url(): string
{
    $icon = app_setting('icon_path');
    return $icon !== '' ? app_url($icon) : '';
}
