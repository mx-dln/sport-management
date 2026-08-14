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

    public function current(): array
    {
        return app_settings();
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

        if ($iconFile && ($iconFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $upload = upload_file($iconFile, __DIR__ . '/../../public/uploads/branding', ['jpg', 'jpeg', 'png', 'webp', 'ico'], 2);
            if (!$upload['ok']) {
                return $upload;
            }
            $settings['icon_path'] = 'uploads/branding/' . $upload['name'];
        }

        $json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false || file_put_contents($this->settingsPath, $json . PHP_EOL) === false) {
            return ['ok' => false, 'message' => 'Unable to save settings.'];
        }

        return ['ok' => true, 'message' => 'System settings saved.', 'reload' => true];
    }
}
