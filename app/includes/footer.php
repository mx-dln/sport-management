<footer class="mt-8 border-t border-slate-200 py-4 text-center text-xs text-slate-500">
    &copy; <?= date('Y') ?> <?= e(app_setting('app_name')) ?>
</footer>
<script src="<?= app_url('assets/js/app.js?v=' . filemtime(__DIR__ . '/../../public/assets/js/app.js')) ?>"></script>
<script src="<?= app_url('assets/js/ajax.js?v=' . filemtime(__DIR__ . '/../../public/assets/js/ajax.js')) ?>"></script>
<script src="<?= app_url('assets/js/validations.js?v=' . filemtime(__DIR__ . '/../../public/assets/js/validations.js')) ?>"></script>
<?php if (in_array((current_user()['role'] ?? ''), ['admin', 'sports_coordinator'], true)): ?>
    <div
        data-admin-document-notifications
        data-endpoint="<?= e(project_url('app/ajax/document_notification_ajax.php')) ?>"
        data-sound="<?= e(app_url('sound/notif.wav')) ?>"
        data-documents-url="<?= e(app_url('index.php?page=documents')) ?>"
        hidden
    ></div>
    <script src="<?= app_url('assets/js/admin_notifications.js?v=' . filemtime(__DIR__ . '/../../public/assets/js/admin_notifications.js')) ?>"></script>
<?php endif; ?>
</body>
</html>
