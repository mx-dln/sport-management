<?php
require_once __DIR__ . '/../app/controllers/AuthController.php';

$controller = new AuthController($pdo);
if (is_post()) {
    $controller->registerAthlete($_POST, $_FILES);
}
$sports = $controller->sports();
$requirements = $controller->requirements();
$pageTitle = 'Athlete Registration';
require __DIR__ . '/../app/includes/header.php';
?>
<main class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-slate-100 px-4 py-8">
    <section class="mx-auto w-full max-w-4xl rounded-2xl border border-slate-200 bg-white p-6 shadow-xl lg:p-8">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <?php if (app_icon_url()): ?>
                    <img class="mb-3 h-12 w-12 rounded-2xl object-cover" src="<?= e(app_icon_url()) ?>" alt="<?= e(app_setting('app_name')) ?> icon">
                <?php else: ?>
                    <div class="mb-3 grid h-12 w-12 place-items-center rounded-2xl text-lg font-black text-white" style="background: var(--theme-color);"><?= e(substr(app_setting('app_short_name', 'SM'), 0, 4)) ?></div>
                <?php endif; ?>
                <h1 class="text-2xl font-bold">Athlete Registration</h1>
                <p class="mt-1 text-sm text-slate-500">Complete your account, profile, and requirement documents.</p>
            </div>
            <a class="text-sm font-semibold text-blue-600 hover:text-blue-700" href="<?= e(app_url('login.php')) ?>">Back to login</a>
        </div>
        <?php require __DIR__ . '/../app/includes/alerts.php'; ?>
        <div class="grid grid-cols-3 gap-2 text-center text-xs font-semibold">
            <button type="button" class="register-step-tab rounded-lg bg-blue-600 px-2 py-2 text-white" data-step-target="1">1. Account</button>
            <button type="button" class="register-step-tab rounded-lg bg-slate-100 px-2 py-2 text-slate-600" data-step-target="2">2. Profile</button>
            <button type="button" class="register-step-tab rounded-lg bg-slate-100 px-2 py-2 text-slate-600" data-step-target="3">3. Documents</button>
        </div>
        <div class="mb-4 mt-4 flex flex-wrap items-center justify-between gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-semibold text-slate-600">
            <span>Fields marked with <span class="font-bold text-rose-600">*</span> are mandatory</span>
            <span class="text-blue-600 font-bold">11-digit mobile numbers only (example: 0912 3456 789)</span>
        </div>
        <form method="post" enctype="multipart/form-data" data-validate id="athlete-register-form">
            <input type="hidden" name="auth_action" value="register_athlete">
            <section class="register-step grid gap-3 sm:grid-cols-2" data-step="1">
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Student ID <span class="text-rose-600 font-bold">*</span></span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="student_id" placeholder="e.g. 2026-0001" required>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Email Address <span class="text-rose-600 font-bold">*</span></span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" type="email" name="email" placeholder="name@domain.com" required>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Password <span class="text-rose-600 font-bold">*</span></span>
                    <div class="mt-1 flex overflow-hidden rounded-lg border border-slate-300 focus-within:border-blue-500">
                        <input class="w-full border-0 px-3 py-2 focus:outline-none" type="password" name="password" required minlength="6" data-password-field>
                        <button class="border-l border-slate-300 px-3 text-sm font-semibold text-slate-600 hover:bg-slate-50" type="button" data-password-toggle>Show</button>
                    </div>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Confirm Password <span class="text-rose-600 font-bold">*</span></span>
                    <div class="mt-1 flex overflow-hidden rounded-lg border border-slate-300 focus-within:border-blue-500">
                        <input class="w-full border-0 px-3 py-2 focus:outline-none" type="password" name="confirm_password" required minlength="6" data-password-field>
                        <button class="border-l border-slate-300 px-3 text-sm font-semibold text-slate-600 hover:bg-slate-50" type="button" data-password-toggle>Show</button>
                    </div>
                </label>
            </section>
            <section class="register-step hidden grid gap-3 sm:grid-cols-2" data-step="2">
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">First Name <span class="text-rose-600 font-bold">*</span></span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="first_name" pattern="[A-Za-z ]+" title="Use letters and spaces only." data-name-only required>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Last Name <span class="text-rose-600 font-bold">*</span></span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="last_name" pattern="[A-Za-z ]+" title="Use letters and spaces only." data-name-only required>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Middle Name <span class="text-xs font-normal text-slate-400">(Optional)</span></span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="middle_name" pattern="[A-Za-z ]+" title="Use letters and spaces only." data-name-only>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Contact Number (11 Digits) <span class="text-rose-600 font-bold">*</span></span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" type="tel" name="contact_number" placeholder="0912 3456 789" pattern="[0-9]{11}" minlength="11" maxlength="11" inputmode="numeric" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 11)" title="Must be exactly 11 digits (e.g. 0912 3456 789)" data-phone-11 required>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Gender <span class="text-rose-600 font-bold">*</span></span>
                    <select class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="gender" required>
                        <option value="">Select gender</option>
                        <option>Male</option>
                        <option>Female</option>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Birthdate <span class="text-rose-600 font-bold">*</span></span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" type="date" name="birthdate" required>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Course <span class="text-rose-600 font-bold">*</span></span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="course" placeholder="e.g. BS Information Technology" required>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Year Level <span class="text-rose-600 font-bold">*</span></span>
                    <select class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="year_level" required>
                        <option value="">Select year level</option>
                        <option>1st Year</option>
                        <option>2nd Year</option>
                        <option>3rd Year</option>
                        <option>4th Year</option>
                        <option>5th Year</option>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Section <span class="text-rose-600 font-bold">*</span></span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="section" placeholder="e.g. 3A" required>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Guardian Name <span class="text-rose-600 font-bold">*</span></span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="guardian_name" required>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Guardian Contact (11 Digits) <span class="text-rose-600 font-bold">*</span></span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" type="tel" name="guardian_contact" placeholder="0912 3456 789" pattern="[0-9]{11}" minlength="11" maxlength="11" inputmode="numeric" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 11)" title="Must be exactly 11 digits (e.g. 0912 3456 789)" data-phone-11 required>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Emergency Contact (11 Digits) <span class="text-rose-600 font-bold">*</span></span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" type="tel" name="emergency_contact" placeholder="0912 3456 789" pattern="[0-9]{11}" minlength="11" maxlength="11" inputmode="numeric" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 11)" title="Must be exactly 11 digits (e.g. 0912 3456 789)" data-phone-11 required>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Height <span class="text-rose-600 font-bold">*</span></span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="height" placeholder="175 cm" required>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Weight <span class="text-rose-600 font-bold">*</span></span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="weight" placeholder="68 kg" required>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Blood Type <span class="text-rose-600 font-bold">*</span></span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="blood_type" placeholder="e.g. O+, A+, B+" required>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Medical Condition <span class="text-rose-600 font-bold">*</span></span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="medical_condition" placeholder="None or state specific condition" required>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Sport <span class="text-xs font-normal text-slate-400">(Optional)</span></span>
                    <select class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="sport_id">
                        <option value="">Select later / Any</option>
                        <?php foreach ($sports as $sport): ?>
                            <option value="<?= e((string)$sport['id']) ?>"><?= e($sport['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <div class="rounded-lg bg-slate-50 p-3 text-sm text-slate-500">Team assignment is handled by the sports office or coach after registration.</div>
                <div class="grid gap-3 sm:col-span-2 sm:grid-cols-3">
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Province <span class="text-rose-600 font-bold">*</span></span>
                        <select class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="address_province" data-address-province required>
                            <option value="Isabela" selected>Isabela</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Municipality / City <span class="text-rose-600 font-bold">*</span></span>
                        <select class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="address_municipality" data-address-municipality required>
                            <option value="">Loading municipalities...</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Barangay <span class="text-rose-600 font-bold">*</span></span>
                        <select class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" name="address_barangay" data-address-barangay required disabled>
                            <option value="">Select municipality first</option>
                        </select>
                    </label>
                </div>
                <input type="hidden" name="address" data-address-combined>
                <label class="block sm:col-span-2">
                    <span class="text-sm font-semibold text-slate-700">Full Address (Auto-Generated)</span>
                    <input class="mt-1 w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-slate-600 focus:outline-none" data-address-preview readonly placeholder="Select barangay, municipality, and province">
                </label>
            </section>
            <section class="register-step hidden" data-step="3">
                <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                    <p class="font-bold">Scan &amp; Upload Documents</p>
                    <p class="mt-1 text-xs text-blue-700">You may upload scanned PDF or image copies (PSA Birth Certificate, COG/Grade Slip, Medical Clearance, etc.) now, or upload them later via your athlete dashboard.</p>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <?php foreach ($requirements as $requirement): ?>
                        <?php 
                        $isBirthCert = stripos($requirement['title'], 'Birth Certificate') !== false;
                        $isCOG = stripos($requirement['title'], 'Grade') !== false || stripos($requirement['title'], 'COG') !== false;
                        ?>
                        <label class="block rounded-xl border <?= ($isBirthCert || $isCOG) ? 'border-blue-300 bg-blue-50/40 shadow-sm' : 'border-slate-200' ?> p-3.5 transition hover:border-blue-400">
                            <span class="flex items-center justify-between gap-3 text-sm font-semibold">
                                <span class="flex items-center gap-1.5">
                                    <?php if ($isBirthCert): ?>
                                        <span class="rounded bg-indigo-600 px-1.5 py-0.5 text-[10px] font-black uppercase text-white">PSA Scan</span>
                                    <?php elseif ($isCOG): ?>
                                        <span class="rounded bg-emerald-600 px-1.5 py-0.5 text-[10px] font-black uppercase text-white">COG Scan</span>
                                    <?php endif; ?>
                                    <span><?= e($requirement['title']) ?></span>
                                </span>
                                <span class="text-xs font-bold <?= $requirement['is_required'] ? 'text-rose-600' : 'text-slate-400' ?>"><?= $requirement['is_required'] ? '* Required' : 'Optional' ?></span>
                            </span>
                            <?php if (!empty($requirement['description'])): ?>
                                <span class="mt-1 block text-xs text-slate-500"><?= e($requirement['description']) ?></span>
                            <?php endif; ?>
                            <div class="mt-2.5 flex items-center gap-2">
                                <input class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs focus:border-blue-500 focus:outline-none" type="file" name="documents[<?= e((string)$requirement['id']) ?>]" accept=".pdf,image/*">
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </section>
            <div class="mt-6 flex items-center justify-between gap-3">
                <button type="button" class="register-prev hidden rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Back</button>
                <button type="button" class="register-next ml-auto rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Next</button>
                <button class="register-submit hidden rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Submit Registration</button>
            </div>
        </form>
    </section>
</main>
<script>
let registerStep = 1;
let isabelaAddressData = null;
const addressDataUrl = <?= json_encode(app_url('assets/data/isabela-addresses.json')) ?>;
const provinceField = document.querySelector('[data-address-province]');
const municipalityField = document.querySelector('[data-address-municipality]');
const barangayField = document.querySelector('[data-address-barangay]');
const combinedAddressField = document.querySelector('[data-address-combined]');
const addressPreview = document.querySelector('[data-address-preview]');


function markRequiredRegisterFields() {
    document.querySelectorAll('#athlete-register-form [required]').forEach((field) => {
        if (field.type === 'hidden') return;
        const label = field.closest('label');
        if (!label || label.querySelector('[data-required-marker], .text-rose-600')) return;
        const caption = label.querySelector('span.text-sm, span.flex, span');
        if (!caption) return;
        caption.insertAdjacentHTML('beforeend', ' <span class="text-rose-600 font-bold" data-required-marker>*</span>');
    });
}

function showRegisterStep(step) {
    registerStep = Math.max(1, Math.min(3, step));
    document.querySelectorAll('.register-step').forEach((panel) => {
        panel.classList.toggle('hidden', Number(panel.dataset.step) !== registerStep);
    });
    document.querySelectorAll('.register-step-tab').forEach((tab) => {
        const active = Number(tab.dataset.stepTarget) === registerStep;
        tab.classList.toggle('bg-blue-600', active);
        tab.classList.toggle('text-white', active);
        tab.classList.toggle('bg-slate-100', !active);
        tab.classList.toggle('text-slate-600', !active);
    });
    document.querySelector('.register-prev').classList.toggle('hidden', registerStep === 1);
    document.querySelector('.register-next').classList.toggle('hidden', registerStep === 3);
    document.querySelector('.register-submit').classList.toggle('hidden', registerStep !== 3);
}

function resetOptions(select, placeholder) {
    select.innerHTML = '';
    const option = document.createElement('option');
    option.value = '';
    option.textContent = placeholder;
    select.appendChild(option);
}

function populateMunicipalities() {
    resetOptions(municipalityField, 'Select municipality / city');
    isabelaAddressData.municipalities.forEach((municipality) => {
        const option = document.createElement('option');
        option.value = municipality.name;
        option.textContent = municipality.name;
        option.dataset.code = municipality.code;
        municipalityField.appendChild(option);
    });
}

function populateBarangays() {
    const municipality = isabelaAddressData?.municipalities.find((item) => item.name === municipalityField.value);
    resetOptions(barangayField, municipality ? 'Select barangay' : 'Select municipality first');
    barangayField.disabled = !municipality;

    if (!municipality) {
        updateCombinedAddress();
        return;
    }

    municipality.barangays.forEach((barangay) => {
        const option = document.createElement('option');
        option.value = barangay.name;
        option.textContent = barangay.name;
        option.dataset.code = barangay.code;
        barangayField.appendChild(option);
    });
    updateCombinedAddress();
}

function updateCombinedAddress() {
    const parts = [barangayField.value, municipalityField.value, provinceField.value].filter(Boolean);
    const combined = parts.join(', ');
    combinedAddressField.value = combined;
    addressPreview.value = combined;
}

async function loadAddressData() {
    try {
        const response = await fetch(addressDataUrl);
        if (!response.ok) throw new Error('Address data failed to load.');
        isabelaAddressData = await response.json();
        populateMunicipalities();
    } catch (error) {
        resetOptions(municipalityField, 'Unable to load municipalities');
        resetOptions(barangayField, 'Unable to load barangays');
        municipalityField.disabled = true;
        barangayField.disabled = true;
    }
}

function validatePasswordConfirmation() {
    const password = document.querySelector('[name="password"]');
    const confirmPassword = document.querySelector('[name="confirm_password"]');
    if (!password || !confirmPassword) return true;

    const mismatch = password.value && confirmPassword.value && password.value !== confirmPassword.value;
    confirmPassword.setCustomValidity(mismatch ? 'Passwords do not match.' : '');
    return !mismatch;
}

function currentStepIsValid() {
    validatePasswordConfirmation();
    updateCombinedAddress();
    const fields = document.querySelectorAll(`.register-step[data-step="${registerStep}"] input, .register-step[data-step="${registerStep}"] select`);
    return Array.from(fields).every((field) => field.reportValidity());
}


document.addEventListener('input', (event) => {
    const input = event.target;
    if (!input || !input.matches('[data-name-only]')) return;
    const cleaned = input.value.replace(/[^A-Za-z ]+/g, '').replace(/\s{2,}/g, ' ');
    if (input.value !== cleaned) {
        input.value = cleaned;
    }
});

document.querySelector('[name="password"]').addEventListener('input', validatePasswordConfirmation);
document.querySelector('[name="confirm_password"]').addEventListener('input', validatePasswordConfirmation);
municipalityField.addEventListener('change', populateBarangays);
barangayField.addEventListener('change', updateCombinedAddress);
provinceField.addEventListener('change', updateCombinedAddress);
document.getElementById('athlete-register-form').addEventListener('submit', updateCombinedAddress);

document.querySelector('.register-next').addEventListener('click', () => {
    if (currentStepIsValid()) showRegisterStep(registerStep + 1);
});
document.querySelector('.register-prev').addEventListener('click', () => showRegisterStep(registerStep - 1));
document.querySelectorAll('.register-step-tab').forEach((tab) => {
    tab.addEventListener('click', () => {
        const target = Number(tab.dataset.stepTarget);
        if (target < registerStep || currentStepIsValid()) showRegisterStep(target);
    });
});
markRequiredRegisterFields();
showRegisterStep(1);
loadAddressData();

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const field = button.parentElement.querySelector('[data-password-field]');
        const showing = field.type === 'text';
        field.type = showing ? 'password' : 'text';
        button.textContent = showing ? 'Show' : 'Hide';
    });
});
</script>
</body>
</html>
