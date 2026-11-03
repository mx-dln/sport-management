<?php
require_once __DIR__ . '/../../controllers/AthleteController.php';
require_role(['admin', 'sports_coordinator', 'coach', 'athlete']);
$pageTitle = 'Print Athlete Profile';
$controller = new AthleteController($pdo);
$athlete = $controller->find((int)($_GET['id'] ?? 0));
if (!$athlete) exit('Athlete not found.');
$role = current_user()['role'] ?? '';
if ($role === 'athlete' && (int)($athlete['user_id'] ?? 0) !== (int)current_user()['id']) {
    http_response_code(403);
    exit('Unauthorized access.');
}
if ($role === 'coach' && !$controller->canAccessAthlete((int)$athlete['id'])) {
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
/* Biodata Screen Styling: University Forest Green Gradient Banner with Crisp White Typography */
.biodata-header {
    background: linear-gradient(135deg, var(--theme-color, #006738) 0%, #064e3b 45%, #0f172a 100%) !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    color: #ffffff !important;
    box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.15), 0 2px 6px -2px rgba(0, 0, 0, 0.08);
}
.biodata-header * {
    color: #ffffff;
}
.biodata-header-app {
    color: rgba(255, 255, 255, 0.85) !important;
    font-weight: 700 !important;
    letter-spacing: 0.04em !important;
}
.biodata-header-title {
    color: #ffffff !important;
    font-weight: 900 !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
}
.biodata-header-sub {
    color: rgba(255, 255, 255, 0.9) !important;
    font-weight: 500 !important;
}
.biodata-header-box {
    background: rgba(255, 255, 255, 0.14) !important;
    border: 1px solid rgba(255, 255, 255, 0.25) !important;
    backdrop-filter: blur(6px);
}
.biodata-header-box-lbl {
    color: rgba(255, 255, 255, 0.75) !important;
    font-weight: 700 !important;
}
.biodata-header-box-date {
    color: #ffffff !important;
    font-weight: 900 !important;
}

@media print {
    @page { size: A4; margin: 8mm; }
    body { background: #fff !important; color: #0f172a !important; }
    .biodata-page { max-width: none !important; padding: 0 !important; }
    .biodata-sheet {
        border: 1px solid #cbd5e1 !important;
        box-shadow: none !important;
        padding: 8mm 10mm !important;
        overflow: hidden !important;
        min-height: auto !important;
        background: #ffffff !important;
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
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
    }
    .biodata-photo {
        width: 34mm !important;
        height: 34mm !important;
        margin-left: auto !important;
        margin-right: auto !important;
        border: 1px solid #cbd5e1 !important;
        background: #ffffff !important;
    }
    .biodata-profile h2 { font-size: 13pt !important; margin-top: 4mm !important; color: #0f172a !important; font-weight: 900 !important; }
    .biodata-profile p,
    .biodata-profile div { font-size: 8.5pt !important; color: #334155 !important; }
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
    .biodata-info-row span:first-child {
        font-weight: 700 !important;
        color: #0f172a !important;
    }
    .biodata-info-row span:last-child {
        color: #334155 !important;
    }
    .biodata-header {
        margin-bottom: 8mm !important;
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-top: 3.5px solid #006738 !important;
        color: #0f172a !important;
        box-shadow: none !important;
        padding: 5mm 6mm !important;
        display: block !important;
        break-inside: avoid !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .biodata-header-app {
        color: #006738 !important;
        font-size: 8pt !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: .04em !important;
    }
    .biodata-header-title {
        color: #0f172a !important;
        font-size: 15pt !important;
        font-weight: 900 !important;
        margin-top: 1mm !important;
    }
    .biodata-header-sub {
        color: #475569 !important;
        font-size: 8.5pt !important;
        margin-top: 0.5mm !important;
    }
    .biodata-header-box {
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        padding: 2.5mm 4mm !important;
        text-align: right !important;
    }
    .biodata-header-box-lbl {
        color: #64748b !important;
        font-size: 7pt !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
    }
    .biodata-header-box-date {
        color: #0f172a !important;
        font-size: 9.5pt !important;
        font-weight: 900 !important;
    }
    .biodata-status-badge {
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        font-size: 8pt !important;
        font-weight: 800 !important;
    }
    .biodata-status-badge.status-active {
        color: #15803d !important;
        border-color: #86efac !important;
        background: #f0fdf4 !important;
    }
    .biodata-status-badge.status-inactive {
        color: #b91c1c !important;
        border-color: #fca5a5 !important;
        background: #fef2f2 !important;
    }
    .biodata-watermark { display: none !important; }
    .biodata-signatures {
        display: grid !important;
        break-inside: avoid !important;
        margin-top: 8mm !important;
        border-top: 1px solid #cbd5e1 !important;
        color: #0f172a !important;
    }
    .biodata-signatures p {
        color: #0f172a !important;
        font-weight: 700 !important;
    }
    .biodata-signatures div div {
        color: #64748b !important;
        border-top: 1px solid #94a3b8 !important;
    }
    .biodata-attachments-card { display: none !important; }
}
</style>
<main class="biodata-page mx-auto max-w-6xl p-4 lg:p-8">
    <div class="no-print mb-5 flex justify-end gap-2">
        <button class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700" type="button" onclick="history.length > 1 ? history.back() : window.location.href='<?= e(app_url('index.php?page=profile')) ?>'">Back</button>
        <button class="btn-primary" data-print>Print Profile</button>
    </div>

    <section class="biodata-sheet relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:p-8">
        <div class="biodata-watermark pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full opacity-5" style="background: var(--theme-color);"></div>

        <header class="biodata-header relative rounded-2xl p-6 shadow-xs">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <?php if (app_icon_url()): ?>
                        <img class="h-16 w-16 rounded-2xl border border-emerald-100 bg-white object-contain p-1 shadow-xs dark:border-slate-700 dark:bg-slate-800" src="<?= e(app_icon_url()) ?>" alt="<?= e(app_setting('app_name')) ?> icon">
                    <?php else: ?>
                        <div class="grid h-16 w-16 place-items-center rounded-2xl bg-[#006738] text-xl font-black text-white shadow-xs"><?= e(substr(app_setting('app_short_name', 'SM'), 0, 4)) ?></div>
                    <?php endif; ?>
                    <div>
                        <p class="biodata-header-app text-xs font-bold uppercase tracking-wider"><?= e(app_setting('app_name')) ?></p>
                        <h1 class="biodata-header-title text-2xl font-black tracking-tight"><?= e(school_name()) ?></h1>
                        <p class="biodata-header-sub text-sm font-semibold">Athlete Biodata and Attachments</p>
                    </div>
                </div>
                <div class="biodata-header-box rounded-2xl px-4 py-3 text-right">
                    <p class="biodata-header-box-lbl text-[10px] font-bold uppercase tracking-wider">Generated</p>
                    <p class="biodata-header-box-date text-sm font-black"><?= e(date('F j, Y')) ?></p>
                </div>
            </div>
        </header>

        <section class="biodata-main mt-6 grid gap-6 lg:grid-cols-[230px_1fr]">
            <div class="biodata-profile biodata-section rounded-2xl border border-slate-200 bg-slate-50 p-5 text-center dark:border-slate-800 dark:bg-slate-800/50">
                <div class="biodata-photo mx-auto h-48 w-48 overflow-hidden rounded-2xl border-4 border-white bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                    <?php if ($profilePhoto): ?>
                        <img class="h-full w-full object-cover" src="<?= e(app_url($profilePhoto)) ?>" alt="Profile photo">
                    <?php else: ?>
                        <div class="grid h-full w-full place-items-center bg-slate-100 text-sm font-semibold text-slate-400 dark:bg-slate-800 dark:text-slate-500">No Photo</div>
                    <?php endif; ?>
                </div>
                <h2 class="mt-4 text-xl font-black text-slate-950 dark:text-white"><?= e(trim(($athlete['first_name'] ?? '') . ' ' . ($athlete['last_name'] ?? ''))) ?></h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400"><?= e($athlete['student_id'] ?? '') ?></p>
                
                <?php
                $athStatus = $athlete['athlete_status'] ?: 'Active';
                $athStatusLower = strtolower($athStatus);
                $athStatusPillClass = match ($athStatusLower) {
                    'inactive', 'rejected', 'suspended' => 'status-inactive bg-rose-100 text-rose-800 border-rose-300 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800',
                    'injured' => 'status-injured bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800',
                    default => 'status-active bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800',
                };
                ?>
                <span class="biodata-status-badge mt-4 inline-flex items-center rounded-full border px-3 py-1 text-xs font-bold shadow-xs <?= $athStatusPillClass ?>"><?= e($athStatus) ?></span>
                
                <div class="mt-5 space-y-3 border-t border-slate-200 pt-4 text-left text-sm dark:border-slate-700">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Course</p>
                        <p class="mt-1 font-semibold text-slate-700 dark:text-slate-300"><?= e($athlete['course'] ?: 'Not specified') ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Address</p>
                        <p class="mt-1 leading-6 text-slate-700 dark:text-slate-300"><?= e($athlete['address'] ?: 'Not specified') ?></p>
                    </div>
                </div>
            </div>

            <div class="biodata-section rounded-2xl border border-slate-200 p-5 dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <h2 class="text-lg font-black text-slate-950 dark:text-white">Personal Information</h2>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Biodata</p>
                </div>
                <div class="biodata-info-grid grid gap-x-8 gap-y-3 text-sm md:grid-cols-2">
                    <div class="space-y-3">
                        <?php foreach ($fieldsLeft as $key => $label): ?>
                            <p class="biodata-info-row grid grid-cols-[140px_1fr] gap-3"><span class="font-bold text-slate-700 dark:text-slate-300"><?= e($label) ?></span><span class="text-slate-700 dark:text-slate-300"><?= e((string)($athlete[$key] ?? '')) ?></span></p>
                        <?php endforeach; ?>
                    </div>
                    <div class="space-y-3">
                        <?php foreach ($fieldsRight as $key => $label): ?>
                            <p class="biodata-info-row grid grid-cols-[140px_1fr] gap-3"><span class="font-bold text-slate-700 dark:text-slate-300"><?= e($label) ?></span><span class="text-slate-700 dark:text-slate-300"><?= e((string)($athlete[$key] ?? '')) ?></span></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <div class="biodata-signatures mt-8 grid gap-6 border-t border-slate-200 pt-6 text-sm dark:border-slate-800 sm:grid-cols-2">
            <div>
                <p class="font-bold text-slate-700 dark:text-slate-300">Athlete Signature</p>
                <div class="mt-10 border-t border-slate-300 pt-2 text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400">Signature over printed name</div>
            </div>
            <div>
                <p class="font-bold text-slate-700 dark:text-slate-300">Verified By</p>
                <div class="mt-10 border-t border-slate-300 pt-2 text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400">Sports office / coordinator</div>
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
