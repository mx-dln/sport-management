<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/functions.php';

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect(app_url('login.php'));
    }
}

function require_role(array $roles): void
{
    require_login();
    $role = current_user()['role'] ?? '';
    if (!in_array($role, $roles, true)) {
        http_response_code(403);
        exit('Unauthorized access.');
    }
}

function dashboard_path(string $role): string
{
    return match ($role) {
        'admin', 'sports_coordinator', 'coach', 'athlete' => app_url('index.php?page=dashboard'),
        default => app_url('login.php'),
    };
}
