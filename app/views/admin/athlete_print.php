<?php
require_once __DIR__ . '/../../controllers/AthleteController.php';
require_role(['admin', 'sports_coordinator', 'coach', 'athlete']);
$pageTitle = 'Print Athlete Profile';
$controller = new AthleteController($pdo);
$athlete = $controller->find((int)($_GET['id'] ?? 0));
if (!$athlete) exit('Athlete not found.');
if ((current_user()['role'] ?? '') === 'athlete' && (int)($athlete['user_id'] ?? 0) !== (int)current_user()['id']) {
    http_response_code(403);
    exit('Unauthorized access.');
}
$documents = $controller->documents((int)$athlete['id']);
$profilePhoto = $athlete['profile_photo'] ?? '';
if ($profilePhoto === '') {
    foreach ($documents as $document) {
        $title = strtolower((string)($document['title'] ?? ''));
        $filePath = (string)($document['file_path'] ?? '');
        if ($filePath !== '' && preg_match('/\.(jpe?g|png)$/i', $filePath) && (str_contains($title, '2x2') || str_contains($title, 'picture') || str_contains($title, 'photo'))) {
            $profilePhoto = $filePath;
            break;
        }
    }
}
$fieldsLeft = [
    'student_id' => 'Student ID',
    'middle_name' => 'Middle Name',
    'gender' => 'Gender',
    'age' => 'Age',
    'year_level' => 'Year',
    'contact_number' => 'Contact',
    'guardian_contact' => 'Guardian Contact',
    'height' => 'Height',
    'blood_type' => 'Blood Type',
    'sport_name' => 'Sport',
    'position' => 'Position',
];
$fieldsRight = [
    'first_name' => 'First Name',
    'last_name' => 'Last Name',
    'birthdate' => 'Birthdate',
    'section' => 'Section',
    'guardian_name' => 'Guardian',
    'emergency_contact' => 'Emergency',
    'weight' => 'Weight',
    'medical_condition' => 'Medical',
    'team_name' => 'Team',
    'athlete_status' => 'Status',
];
require __DIR__ . '/../../includes/header.php';
?>
<style>
@media print {
    @page { size: A4; margin: 8mm; }
    body { background: #fff !important; color: #0f172a !important; }
    .biodata-page { max-width: none !important; padding: 0 !important; }
    .biodata-sheet {
        border: 1px solid #dbe4ef !important;
        box-shadow: none !important;
        padding: 10mm !important;
        overflow: hidden !important;
        min-height: auto !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .biodata-main {
        display: grid !important;
        grid-template-columns: 45mm 1fr !important;
        gap: 8mm !important;
        align-items: start !important;
    }
    .biodata-profile {
        padding: 4mm !important;
        text-align: center !important;
    }
    .biodata-photo {
        width: 34mm !important;
        height: 34mm !important;
        margin-left: auto !important;
        margin-right: auto !important;
    }
    .biodata-profile h2 { font-size: 13pt !important; margin-top: 4mm !important; }
    .biodata-profile p,
    .biodata-profile div { font-size: 8.5pt !important; }
    .biodata-info-grid {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 5mm !important;
        font-size: 8.5pt !important;
    }
    .biodata-info-row {
        display: grid !important;
        grid-template-columns: 30mm 1fr !important;
        gap: 3mm !important;
        margin-bottom: 2.2mm !important;
    }
    .biodata-header { margin-bottom: 8mm !important; }
    .biodata-header,
    .biodata-profile,
    .biodata-section,
    .biodata-photo {
        display: block !important;
        break-inside: avoid !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .biodata-header * { color: inherit !important; }
    .biodata-watermark { display: block !important; }
    .biodata-signatures {
        display: grid !important;
        break-inside: avoid !important;
        margin-top: 10mm !important;
    }
    .biodata-attachments-card { display: none !important; }
}
</style>
<main class="biodata-page mx-auto max-w-6xl p-4 lg:p-8">
    <div class="no-print mb-5 flex justify-end gap-2">
        <button class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" type="button" onclick="history.length > 1 ? history.back() : window.location.href='<?= e(app_url('index.php?page=profile')) ?>'">Back</button>
        <button class="btn-primary" data-print>Print Profile</button>
    </div>

    <section class="biodata-sheet relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:p-8">
        <div class="biodata-watermark pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full opacity-10" style="background: var(--theme-color);"></div>

        <header class="biodata-header relative rounded-2xl p-6 text-white" style="background: linear-gradient(135deg, var(--theme-color), #0f172a);">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <?php if (app_icon_url()): ?>
                        <img class="h-16 w-16 rounded-2xl bg-white object-cover p-1" src="<?= e(app_icon_url()) ?>" alt="<?= e(app_setting('app_name')) ?> icon">
                    <?php else: ?>
                        <div class="grid h-16 w-16 place-items-center rounded-2xl bg-white/15 text-xl font-black"><?= e(substr(app_setting('app_short_name', 'SM'), 0, 4)) ?></div>
                    <?php endif; ?>
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-white/75"><?= e(app_setting('app_name')) ?></p>
                        <h1 class="text-2xl font-black tracking-tight"><?= e(school_name()) ?></h1>
                        <p class="text-sm text-white/80">Athlete Biodata and Attachments</p>
                    </div>
                </div>
                <div class="rounded-2xl bg-white/10 px-4 py-3 text-right">
                    <p class="text-xs uppercase tracking-wide text-white/70">Generated</p>
                    <p class="font-bold"><?= e(date('F j, Y')) ?></p>
                </div>
            </div>
        </header>

        <section class="biodata-main mt-6 grid gap-6 lg:grid-cols-[230px_1fr]">
            <div class="biodata-profile biodata-section rounded-2xl border border-slate-200 bg-slate-50 p-5 text-center">
                <div class="biodata-photo mx-auto h-48 w-48 overflow-hidden rounded-2xl border-4 border-white bg-white shadow-sm">
                    <?php if ($profilePhoto): ?>
                        <img class="h-full w-full object-cover" src="<?= e(app_url($profilePhoto)) ?>" alt="Profile photo">
                    <?php else: ?>
                        <div class="grid h-full w-full place-items-center bg-slate-100 text-sm font-semibold text-slate-400">No Photo</div>
                    <?php endif; ?>
                </div>
                <h2 class="mt-4 text-xl font-black text-slate-950"><?= e(trim(($athlete['first_name'] ?? '') . ' ' . ($athlete['last_name'] ?? ''))) ?></h2>
                <p class="mt-1 text-sm text-slate-500"><?= e($athlete['student_id'] ?? '') ?></p>
                <span class="mt-4 inline-flex rounded-full px-3 py-1 text-xs font-bold text-white" style="background: var(--theme-color);"><?= e($athlete['athlete_status'] ?: 'Active') ?></span>
                <div class="mt-5 space-y-3 border-t border-slate-200 pt-4 text-left text-sm">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Course</p>
                        <p class="mt-1 font-semibold text-slate-700"><?= e($athlete['course'] ?: 'Not specified') ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Address</p>
                        <p class="mt-1 leading-6 text-slate-700"><?= e($athlete['address'] ?: 'Not specified') ?></p>
                    </div>
                </div>
            </div>

            <div class="biodata-section rounded-2xl border border-slate-200 p-5">
                <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-lg font-black text-slate-950">Personal Information</h2>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Biodata</p>
                </div>
                <div class="biodata-info-grid grid gap-x-8 gap-y-3 text-sm md:grid-cols-2">
                    <div class="space-y-3">
                        <?php foreach ($fieldsLeft as $key => $label): ?>
                            <p class="biodata-info-row grid grid-cols-[140px_1fr] gap-3"><span class="font-bold text-slate-700"><?= e($label) ?></span><span class="text-slate-700"><?= e((string)($athlete[$key] ?? '')) ?></span></p>
                        <?php endforeach; ?>
                    </div>
                    <div class="space-y-3">
                        <?php foreach ($fieldsRight as $key => $label): ?>
                            <p class="biodata-info-row grid grid-cols-[140px_1fr] gap-3"><span class="font-bold text-slate-700"><?= e($label) ?></span><span class="text-slate-700"><?= e((string)($athlete[$key] ?? '')) ?></span></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <div class="biodata-signatures mt-8 grid gap-6 border-t border-slate-200 pt-6 text-sm sm:grid-cols-2">
            <div>
                <p class="font-bold text-slate-700">Athlete Signature</p>
                <div class="mt-10 border-t border-slate-300 pt-2 text-xs text-slate-500">Signature over printed name</div>
            </div>
            <div>
                <p class="font-bold text-slate-700">Verified By</p>
                <div class="mt-10 border-t border-slate-300 pt-2 text-xs text-slate-500">Sports office / coordinator</div>
            </div>
        </div>
    </section>

    <section class="biodata-attachments-card biodata-section no-print mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-lg font-black text-slate-950">Submitted Requirements</h2>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Attachments</p>
            </div>
            <div class="biodata-table overflow-hidden rounded-lg border border-slate-200">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="table-th">Requirement</th>
                            <th class="table-th">Status</th>
                            <th class="table-th">Attachment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $d): ?>
                            <?php
                            $attachmentPath = (string)($d['file_path'] ?? '');
                            $attachmentName = (string)($d['original_name'] ?? '');
                            $attachmentExt = strtolower(pathinfo($attachmentName !== '' ? $attachmentName : $attachmentPath, PATHINFO_EXTENSION));
                            $isImageAttachment = in_array($attachmentExt, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true);
                            $isPdfAttachment = $attachmentExt === 'pdf';
                            ?>
                            <tr>
                                <td class="table-td font-semibold text-slate-700"><?= e($d['title']) ?></td>
                                <td class="table-td">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold <?= ($d['status'] ?? 'Missing') === 'Approved' ? 'bg-green-100 text-green-700' : ((($d['status'] ?? 'Missing') === 'Submitted') ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-500') ?>"><?= e($d['status'] ?? 'Missing') ?></span>
                                </td>
                                <td class="table-td">
                                    <?php if ($attachmentPath !== ''): ?>
                                        <?php if ($isImageAttachment): ?>
                                            <a class="inline-flex items-center gap-2 font-semibold text-blue-600" href="#attachment-preview" data-attachment-preview data-attachment-url="<?= e(app_url($attachmentPath)) ?>" data-attachment-name="<?= e($attachmentName) ?>">
                                                <img class="h-8 w-8 rounded object-cover" src="<?= e(app_url($attachmentPath)) ?>" alt="<?= e($attachmentName) ?>">
                                                <span>Preview image</span>
                                            </a>
                                        <?php elseif ($isPdfAttachment): ?>
                                            <a class="font-semibold text-red-700 hover:text-red-800" href="#attachment-preview" data-attachment-preview data-attachment-url="<?= e(app_url($attachmentPath)) ?>" data-attachment-name="<?= e($attachmentName) ?>">Preview PDF</a>
                                        <?php else: ?>
                                            <a class="font-semibold text-slate-600 hover:text-slate-800" href="#attachment-preview" data-attachment-preview data-attachment-url="<?= e(app_url($attachmentPath)) ?>" data-attachment-name="<?= e($attachmentName) ?>">File preview unavailable</a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-slate-400">No attachment</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
    </section>
</main>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
