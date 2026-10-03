<?php
$cmdUser = current_user();
$cmdRole = $cmdUser['role'] ?? '';
$cmdSports = $pdo->query("SELECT id, name FROM sports WHERE status='active' ORDER BY name")->fetchAll();
$cmdTeams = $pdo->query("SELECT id, name, sport_id FROM teams WHERE status='active' ORDER BY name")->fetchAll();
$cmdReqTypes = $pdo->query("SELECT id, title, is_required FROM requirement_types ORDER BY is_required DESC, title")->fetchAll();
$cmdAthletes = [];
if (in_array($cmdRole, ['admin', 'sports_coordinator', 'coach'], true)) {
    $cmdAthletes = $pdo->query("SELECT id, student_id, first_name, last_name FROM athletes ORDER BY last_name, first_name")->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT id, student_id, first_name, last_name FROM athletes WHERE user_id=? LIMIT 1");
    $stmt->execute([$cmdUser['id'] ?? 0]);
    $cmdAthletes = $stmt->fetchAll();
}
?>

<!-- ================= CONSTRUCT MESSAGE MODAL ================= -->
<div id="construct-message-modal" class="fixed inset-0 z-[80] hidden bg-slate-950/70 p-4 backdrop-blur-sm" data-modal>
    <div class="mx-auto flex min-h-full max-w-2xl items-center">
        <section class="w-full overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900 dark:border dark:border-slate-800">
            <header class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-950 dark:text-blue-400">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="m22 6-10 7L2 6"/></svg>
                    </span>
                    <div>
                        <h2 class="text-lg font-black text-slate-950 dark:text-white">Construct Message</h2>
                        <p class="text-xs text-slate-500">Draft and dispatch SMS notifications &amp; system bulletins.</p>
                    </div>
                </div>
                <button class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" type="button" data-modal-close>Close</button>
            </header>
            
            <form id="construct-message-form" class="space-y-4 p-6" method="post" action="<?= project_url('app/ajax/sms_ajax.php') ?>" data-ajax-form>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Quick Templates</label>
                    <div class="flex flex-wrap gap-2 text-xs font-semibold">
                        <button type="button" class="template-chip rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-slate-700 hover:border-blue-500 hover:bg-blue-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300" data-template="URGENT: Please scan and upload your pending requirements (PSA Birth Certificate / Certificate of Grades (COG)) via your athlete portal.">📄 Missing PSA / COG</button>
                        <button type="button" class="template-chip rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-slate-700 hover:border-blue-500 hover:bg-blue-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300" data-template="Reminder: Varsity training scheduled for tomorrow at the gymnasium. Please be in complete sports attire on time.">🏀 Training Reminder</button>
                        <button type="button" class="template-chip rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-slate-700 hover:border-blue-500 hover:bg-blue-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300" data-template="Notice: Medical clearance is required before participating in upcoming official league games. Check your medical status.">🩺 Medical Clearance</button>
                        <button type="button" class="template-chip rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-slate-700 hover:border-blue-500 hover:bg-blue-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300" data-template="Attention: Competition schedule and athlete roster have been updated. Review your event assignment.">🏆 Event Notice</button>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Recipient Name / Group</span>
                        <input id="construct-recipient-name" class="form-input mt-1 w-full" name="recipient_name" placeholder="e.g. All Athletes / Basketball Team" required>
                    </label>
                    <label class="block">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Recipient Phone (11 Digits)</span>
                        <input id="construct-recipient-phone" class="form-input mt-1 w-full" type="tel" name="phone_number" placeholder="09XXXXXXXXX (11 digits)" pattern="09[0-9]{9}" minlength="11" maxlength="11" data-phone-11 oninput="this.value = this.value.replace(/\D/g, '').slice(0, 11)" required>
                    </label>
                </div>

                <div>
                    <div class="mb-1 flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Message Content</span>
                        <span id="char-counter" class="text-xs font-semibold text-slate-500">0 / 160 (1 SMS)</span>
                    </div>
                    <textarea id="construct-message-body" class="form-input w-full" name="message" rows="4" placeholder="Type your announcement or notification message here..." required></textarea>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs dark:border-slate-800 dark:bg-slate-800/50">
                    <span class="font-bold text-slate-600 dark:text-slate-400">📱 Mobile Preview:</span>
                    <p id="construct-preview-text" class="mt-1 italic text-slate-500 dark:text-slate-400">Message preview will show here...</p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300" data-modal-close>Cancel</button>
                    <button type="submit" class="btn-primary">Send Message</button>
                </div>
            </form>
        </section>
    </div>
</div>

<!-- ================= SCAN & UPLOAD DOCUMENT MODAL ================= -->
<div id="scan-upload-modal" class="fixed inset-0 z-[80] hidden bg-slate-950/70 p-4 backdrop-blur-sm" data-modal>
    <div class="mx-auto flex min-h-full max-w-xl items-center">
        <section class="w-full overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900 dark:border dark:border-slate-800">
            <header class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0-5 5M4 16v4m0 0h4m-4 0 5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                    </span>
                    <div>
                        <h2 class="text-lg font-black text-slate-950 dark:text-white">Scan &amp; Upload Document</h2>
                        <p class="text-xs text-slate-500">Scan PSA Birth Certificate, COG / Grade Slip, and Clearances.</p>
                    </div>
                </div>
                <button class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" type="button" data-modal-close onclick="stopScannerCamera()">Close</button>
            </header>

            <form class="space-y-4 p-6" method="post" enctype="multipart/form-data" action="<?= project_url('app/ajax/document_ajax.php') ?>" data-ajax-form>
                <input type="hidden" name="action" value="upload_document">
                
                <?php if (count($cmdAthletes) === 1): ?>
                    <input type="hidden" name="athlete_id" value="<?= e((string)$cmdAthletes[0]['id']) ?>">
                    <div class="rounded-xl bg-slate-50 p-3 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        Athlete: <span class="font-bold text-slate-900 dark:text-white"><?= e($cmdAthletes[0]['first_name'] . ' ' . $cmdAthletes[0]['last_name']) ?></span> (<?= e($cmdAthletes[0]['student_id']) ?>)
                    </div>
                <?php else: ?>
                    <label class="block">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Select Athlete</span>
                        <select class="form-input mt-1 w-full" name="athlete_id" required>
                            <option value="">Choose Athlete</option>
                            <?php foreach ($cmdAthletes as $ath): ?>
                                <option value="<?= e((string)$ath['id']) ?>"><?= e($ath['last_name'] . ', ' . $ath['first_name'] . ' (' . $ath['student_id'] . ')') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                <?php endif; ?>

                <div>
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Target Document</span>
                    <select id="scan-requirement-select" class="form-input mt-1 w-full" name="requirement_type_id" required>
                        <option value="">Select Document Requirement</option>
                        <?php foreach ($cmdReqTypes as $r): ?>
                            <option value="<?= e((string)$r['id']) ?>"><?= e($r['title']) ?><?= $r['is_required'] ? ' (* Mandatory)' : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Camera Scanner Section -->
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">Option 1: Camera Scanner</span>
                        <button type="button" id="btn-toggle-camera" class="rounded-lg bg-emerald-600 px-3 py-1 text-xs font-bold text-white hover:bg-emerald-700" onclick="toggleScannerCamera()">📷 Open Scanner</button>
                    </div>
                    <div id="camera-scanner-container" class="mt-3 hidden overflow-hidden rounded-lg bg-black text-center">
                        <video id="camera-scanner-video" class="h-56 w-full object-cover" autoplay playsinline></video>
                        <canvas id="camera-scanner-canvas" class="hidden"></canvas>
                        <div class="flex items-center justify-center gap-2 p-2 bg-slate-900">
                            <button type="button" class="rounded bg-white px-3 py-1 text-xs font-bold text-slate-900 hover:bg-slate-200" onclick="captureDocumentPhoto()">Capture Photo</button>
                            <button type="button" class="rounded border border-slate-700 px-3 py-1 text-xs font-semibold text-slate-300 hover:bg-slate-800" onclick="stopScannerCamera()">Cancel Camera</button>
                        </div>
                    </div>
                    <div id="camera-snapshot-preview" class="mt-2 hidden text-center">
                        <p class="text-xs font-bold text-emerald-600">✓ Scanned Photo Ready for Upload</p>
                        <img id="camera-snapshot-img" class="mx-auto mt-1 max-h-36 rounded-lg border border-slate-200 shadow-sm" src="" alt="Scanned Document">
                    </div>
                </div>

                <!-- File Picker Section -->
                <div>
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Option 2: Choose File / PDF / Scanned Image</span>
                    <input id="document-file-input" class="form-input mt-1 w-full text-xs" type="file" name="document_file" accept=".pdf,.jpg,.jpeg,.png">
                    <p class="mt-1 text-[11px] text-slate-400">Accepted: PSA Birth Certificate or COG PDF / High-res JPG / PNG (Max 8MB)</p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300" data-modal-close onclick="stopScannerCamera()">Cancel</button>
                    <button type="submit" class="btn-primary">Upload Document</button>
                </div>
            </form>
        </section>
    </div>
</div>

<script>
// Construct Message Scripts
document.addEventListener('DOMContentLoaded', () => {
    const msgBody = document.getElementById('construct-message-body');
    const charCounter = document.getElementById('char-counter');
    const previewText = document.getElementById('construct-preview-text');

    if (msgBody && charCounter && previewText) {
        msgBody.addEventListener('input', () => {
            const len = msgBody.value.length;
            const segments = Math.max(1, Math.ceil(len / 160));
            charCounter.textContent = `${len} / 160 (${segments} SMS)`;
            previewText.textContent = msgBody.value.trim() || 'Message preview will show here...';
        });

        document.querySelectorAll('.template-chip').forEach(btn => {
            btn.addEventListener('click', () => {
                msgBody.value = btn.dataset.template || '';
                msgBody.dispatchEvent(new Event('input'));
            });
        });
    }
});

// Camera Scanner Scripts
let scannerStream = null;

async function toggleScannerCamera() {
    const container = document.getElementById('camera-scanner-container');
    const video = document.getElementById('camera-scanner-video');
    const btn = document.getElementById('btn-toggle-camera');

    if (scannerStream) {
        stopScannerCamera();
        return;
    }

    try {
        scannerStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: { ideal: 'environment' } }
        });
        video.srcObject = scannerStream;
        container.classList.remove('hidden');
        btn.textContent = '❌ Close Camera';
    } catch (err) {
        alert('Could not access camera: ' + err.message + '. Please upload a scanned file instead.');
    }
}

function stopScannerCamera() {
    const container = document.getElementById('camera-scanner-container');
    const video = document.getElementById('camera-scanner-video');
    const btn = document.getElementById('btn-toggle-camera');

    if (scannerStream) {
        scannerStream.getTracks().forEach(track => track.stop());
        scannerStream = null;
    }
    if (video) video.srcObject = null;
    if (container) container.classList.add('hidden');
    if (btn) btn.textContent = '📷 Open Scanner';
}

function captureDocumentPhoto() {
    const video = document.getElementById('camera-scanner-video');
    const canvas = document.getElementById('camera-scanner-canvas');
    const imgPreview = document.getElementById('camera-snapshot-img');
    const previewContainer = document.getElementById('camera-snapshot-preview');
    const fileInput = document.getElementById('document-file-input');

    if (!video || !canvas) return;

    canvas.width = video.videoWidth || 640;
    canvas.height = video.videoHeight || 480;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    canvas.toBlob((blob) => {
        if (!blob) return;
        const file = new File([blob], 'scanned_document_' + Date.now() + '.jpg', { type: 'image/jpeg' });
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        fileInput.files = dataTransfer.files;

        imgPreview.src = URL.createObjectURL(blob);
        previewContainer.classList.remove('hidden');
        stopScannerCamera();
    }, 'image/jpeg', 0.92);
}
</script>
