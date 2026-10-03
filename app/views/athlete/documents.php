<?php
require_once __DIR__ . '/../../controllers/DocumentController.php';
require_once __DIR__ . '/../../controllers/AthleteController.php';
require_role(['athlete']);
$pageTitle = 'My Requirement Documents';
$stmt = $pdo->prepare('SELECT * FROM athletes WHERE user_id=? LIMIT 1');
$stmt->execute([current_user()['id']]);
$athlete = $stmt->fetch();
$docs = new DocumentController($pdo);
$requirements = $docs->requirements();
$myDocs = $athlete ? (new AthleteController($pdo))->documents((int)$athlete['id']) : [];

$docsById = [];
foreach ($myDocs as $d) {
    $docsById[(int)$d['requirement_type_id']] = $d;
}

require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72">
<?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?>
<main class="p-4 lg:p-6">

<!-- Header Section -->
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-2xl font-black text-slate-950 dark:text-white">Document Compliance &amp; Verification</h2>
        <p class="text-sm text-slate-500">Scan and upload your mandatory requirements (PSA Birth Certificate, COG / Grade Slip, and Clearances).</p>
    </div>
    <div class="flex items-center gap-2">
        <button type="button" class="smis-cmd-btn primary" data-modal-open="#scan-upload-modal">
            <span>📷</span>
            <span>Scan / Upload Document</span>
        </button>
    </div>
</div>

<!-- Priority Cards for PSA Birth Certificate and COG -->
<div class="mb-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
    <?php
    $priorityReqs = [
        ['type' => 'birth', 'title' => 'PSA Birth Certificate', 'icon' => '📄', 'desc' => 'Official PSA or local civil registry birth certificate for age verification.'],
        ['type' => 'cog', 'title' => 'Certificate of Grades (COG)', 'icon' => '📑', 'desc' => 'Current Certificate of Grades or COR for varsity academic eligibility.'],
        ['type' => 'medical', 'title' => 'Medical Certificate', 'icon' => '🩺', 'desc' => 'Signed physician clearance certifying athlete is fit for training and competition.'],
    ];

    foreach ($priorityReqs as $pReq):
        $matchedDoc = null;
        $matchedReqId = null;
        foreach ($requirements as $r) {
            if ($pReq['type'] === 'birth' && (stripos($r['title'], 'Birth') !== false || stripos($r['title'], 'PSA') !== false)) {
                $matchedReqId = (int)$r['id'];
                $matchedDoc = $docsById[$matchedReqId] ?? null;
                break;
            } elseif ($pReq['type'] === 'cog' && (stripos($r['title'], 'Grade') !== false || stripos($r['title'], 'COG') !== false)) {
                $matchedReqId = (int)$r['id'];
                $matchedDoc = $docsById[$matchedReqId] ?? null;
                break;
            } elseif ($pReq['type'] === 'medical' && stripos($r['title'], 'Medical') !== false) {
                $matchedReqId = (int)$r['id'];
                $matchedDoc = $docsById[$matchedReqId] ?? null;
                break;
            }
        }
        $status = $matchedDoc['status'] ?? 'Missing';
        $statusColor = match ($status) {
            'Approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
            'Submitted' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
            'Rejected' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
            default => 'bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-800/40 dark:text-slate-300 dark:border-slate-700',
        };
    ?>
        <div class="rounded-2xl border p-5 flex flex-col justify-between <?= $statusColor ?>">
            <div>
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <span class="text-2xl"><?= $pReq['icon'] ?></span>
                        <div>
                            <h3 class="font-bold"><?= $pReq['title'] ?></h3>
                            <span class="text-[10px] font-black uppercase tracking-wider opacity-75">Mandatory Requirement</span>
                        </div>
                    </div>
                    <span class="status-pill <?= $status === 'Approved' ? 'status-active' : ($status === 'Submitted' ? 'status-pending' : 'status-inactive') ?>">
                        <?= e($status) ?>
                    </span>
                </div>
                <p class="mt-3 text-xs leading-relaxed opacity-90"><?= $pReq['desc'] ?></p>
            </div>
            <div class="mt-4 pt-3 border-t border-current/15 flex items-center justify-between">
                <?php if ($matchedDoc && !empty($matchedDoc['file_path'])): ?>
                    <a href="<?= e(app_url($matchedDoc['file_path'])) ?>" data-attachment-preview data-attachment-url="<?= e(app_url($matchedDoc['file_path'])) ?>" data-attachment-name="<?= e($matchedDoc['original_name'] ?? $pReq['title']) ?>" class="text-xs font-bold underline">
                        View Uploaded File
                    </a>
                <?php else: ?>
                    <span class="text-xs italic opacity-75">Not uploaded yet</span>
                <?php endif; ?>
                <button type="button" class="rounded-lg bg-white/90 px-3 py-1.5 text-xs font-bold text-slate-900 shadow-2xs hover:bg-white dark:bg-slate-800 dark:text-white" data-modal-open="#scan-upload-modal" onclick="preselectRequirement(<?= e((string)($matchedReqId ?? 0)) ?>)">
                    <?= $status === 'Approved' ? 'Replace Scan' : 'Scan / Upload' ?>
                </button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Upload / Replace Form -->
<?php if ($athlete): ?>
<section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="mb-3">
        <h3 class="text-sm font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300">Quick Document Uploader</h3>
        <p class="text-xs text-slate-500">Select any required document to upload a scanned PDF or photo copy.</p>
    </div>
    <form class="grid gap-3 sm:grid-cols-3" method="post" enctype="multipart/form-data" action="<?= project_url('app/ajax/document_ajax.php') ?>" data-ajax-form data-validate>
        <input type="hidden" name="action" value="upload_document">
        <input type="hidden" name="athlete_id" value="<?= e($athlete['id']) ?>">
        <select id="quick-upload-req-select" class="form-input" name="requirement_type_id" required>
            <option value="">Select Requirement</option>
            <?php foreach ($requirements as $r): ?>
                <option value="<?= e($r['id']) ?>">
                    <?= stripos($r['title'], 'Birth') !== false ? '📄 ' : (stripos($r['title'], 'Grade') !== false ? '📑 ' : '') ?>
                    <?= e($r['title']) ?><?= $r['is_required'] ? ' (* Mandatory)' : '' ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input class="form-input text-xs" type="file" name="document_file" accept=".pdf,image/*" required>
        <button class="btn-primary">Upload Document</button>
    </form>
</section>
<?php endif; ?>

<!-- All Requirements Status Table -->
<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="border-b border-slate-100 p-5 dark:border-slate-800 flex items-center justify-between">
        <div>
            <h3 class="font-bold text-slate-950 dark:text-white">All Document Requirements</h3>
            <p class="text-xs text-slate-500">Official checklist of athletic credentials.</p>
        </div>
        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
            <?= count($myDocs) ?> of <?= count($requirements) ?> submitted
        </span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800">
                <tr>
                    <th class="table-th">Requirement Title</th>
                    <th class="table-th">Type</th>
                    <th class="table-th">Status</th>
                    <th class="table-th">Submitted File</th>
                    <th class="table-th">Remarks</th>
                    <th class="table-th text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requirements as $r): ?>
                    <?php
                    $doc = $docsById[(int)$r['id']] ?? null;
                    $status = $doc['status'] ?? 'Missing';
                    $sClass = match ($status) {
                        'Approved' => 'status-active',
                        'Submitted' => 'status-pending',
                        'Rejected' => 'status-inactive',
                        default => 'status-neutral',
                    };
                    $isPriority = (stripos($r['title'], 'Birth') !== false || stripos($r['title'], 'Grade') !== false || stripos($r['title'], 'COG') !== false);
                    ?>
                    <tr class="border-t border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                        <td class="table-td">
                            <div class="flex items-center gap-2">
                                <?php if ($isPriority): ?>
                                    <span class="rounded bg-blue-100 px-1.5 py-0.5 text-[10px] font-black uppercase text-blue-700 dark:bg-blue-950 dark:text-blue-300">Priority</span>
                                <?php endif; ?>
                                <span class="font-bold text-slate-900 dark:text-white"><?= e($r['title']) ?></span>
                            </div>
                            <?php if (!empty($r['description'])): ?>
                                <p class="text-xs text-slate-500 mt-0.5"><?= e($r['description']) ?></p>
                            <?php endif; ?>
                        </td>
                        <td class="table-td">
                            <span class="text-xs font-semibold <?= $r['is_required'] ? 'text-rose-600 font-bold' : 'text-slate-400' ?>">
                                <?= $r['is_required'] ? '* Mandatory' : 'Optional' ?>
                            </span>
                        </td>
                        <td class="table-td">
                            <span class="status-pill <?= e($sClass) ?>"><?= e($status) ?></span>
                        </td>
                        <td class="table-td">
                            <?php if ($doc && !empty($doc['file_path'])): ?>
                                <a class="font-semibold text-blue-600 hover:underline flex items-center gap-1" href="#attachment-preview" data-attachment-preview data-attachment-url="<?= e(app_url($doc['file_path'])) ?>" data-attachment-name="<?= e($doc['original_name'] ?? $r['title']) ?>">
                                    <span>👁️ View File</span>
                                </a>
                            <?php else: ?>
                                <span class="text-xs text-slate-400">None</span>
                            <?php endif; ?>
                        </td>
                        <td class="table-td text-xs text-slate-500">
                            <?= e($doc['remarks'] ?? '—') ?>
                        </td>
                        <td class="table-td text-right">
                            <button type="button" class="smis-cmd-btn text-xs" data-modal-open="#scan-upload-modal" onclick="preselectRequirement(<?= e((string)$r['id']) ?>)">
                                <?= $doc ? 'Replace' : 'Scan / Upload' ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<script>
function preselectRequirement(reqId) {
    if (!reqId) return;
    const modalSelect = document.getElementById('scan-requirement-select');
    const quickSelect = document.getElementById('quick-upload-req-select');
    if (modalSelect) modalSelect.value = String(reqId);
    if (quickSelect) quickSelect.value = String(reqId);
}
</script>

</main>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
