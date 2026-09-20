<?php
require_once __DIR__ . '/../../controllers/AthleteController.php';
require_once __DIR__ . '/../../controllers/SportController.php';
require_once __DIR__ . '/../../controllers/AthleteHistoryController.php';
require_role(['athlete']);
$pageTitle = 'My Profile';
$stmt = $pdo->prepare('SELECT * FROM athletes WHERE user_id=? LIMIT 1');
$stmt->execute([current_user()['id']]);
$athlete = $stmt->fetch();
$sports = (new SportController($pdo))->all();
$historyStats = $athlete ? (new AthleteHistoryController($pdo))->stats((int)$athlete['id']) : [];
require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72"><?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?>
<main class="p-4 lg:p-6">
<?php require __DIR__ . '/../../includes/alerts.php'; ?>
<?php if (!$athlete): ?>
    <div class="rounded-xl bg-white p-5 shadow-sm">Your athlete biodata is not yet linked. Please contact the sports office.</div>
<?php else: ?>
    <section class="mb-6 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-black text-slate-950">Edit Biodata</h2>
            <p class="mt-1 text-sm text-slate-500">Update the information shown on your athlete profile and printable biodata.</p>
        </div>
        <a class="btn-primary text-center" href="<?= e(app_url('index.php?page=athlete_print&id=' . $athlete['id'])) ?>">View Printable Profile</a>
    </section>

    <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-black text-slate-950">Athletic Achievements</h2>
                <p class="mt-1 text-sm text-slate-500">Your previous competitions, medals, and placings.</p>
            </div>
            <a class="btn-muted text-center" href="<?= e(app_url('index.php?page=history')) ?>">Manage Athletic History</a>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4 xl:grid-cols-8">
            <?php
            $profileStatCards = [
                ['Competitions', $historyStats['total'] ?? 0, '🏆'],
                ['Gold', $historyStats['gold'] ?? 0, '🥇'],
                ['Silver', $historyStats['silver'] ?? 0, '🥈'],
                ['Bronze', $historyStats['bronze'] ?? 0, '🥉'],
                ['Total Medals', $historyStats['medals'] ?? 0, '🏅'],
                ['1st Place', $historyStats['first_place'] ?? 0, '🥇'],
                ['2nd Place', $historyStats['second_place'] ?? 0, '🥈'],
                ['3rd Place', $historyStats['third_place'] ?? 0, '🥉'],
            ];
            ?>
            <?php foreach ($profileStatCards as [$label, $value, $emoji]): ?>
                <div class="rounded-xl bg-slate-50 p-3 text-center">
                    <p class="text-lg"><?= e($emoji) ?></p>
                    <p class="text-xl font-black text-slate-950"><?= e((string)$value) ?></p>
                    <p class="text-xs font-semibold text-slate-500"><?= e($label) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <form class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" method="post" enctype="multipart/form-data" action="<?= project_url('app/ajax/athlete_ajax.php') ?>" data-ajax-form data-validate>
        <input type="hidden" name="id" value="<?= e((string)$athlete['id']) ?>">
        <input type="hidden" name="user_id" value="<?= e((string)current_user()['id']) ?>">
        <input type="hidden" name="athlete_status" value="<?= e($athlete['athlete_status'] ?: 'Active') ?>">
        <div class="grid gap-4 md:grid-cols-3">
            <label class="block">
                <span class="text-sm font-medium">Student ID</span>
                <input class="form-input mt-1" name="student_id" required value="<?= e($athlete['student_id']) ?>">
            </label>
            <label class="block">
                <span class="text-sm font-medium">First Name</span>
                <input class="form-input mt-1" name="first_name" pattern="[A-Za-z .'-]+" title="Use letters only. Spaces, hyphens, apostrophes, and periods are allowed." required value="<?= e($athlete['first_name']) ?>">
            </label>
            <label class="block">
                <span class="text-sm font-medium">Middle Name</span>
                <input class="form-input mt-1" name="middle_name" pattern="[A-Za-z .'-]+" title="Use letters only. Spaces, hyphens, apostrophes, and periods are allowed." value="<?= e($athlete['middle_name']) ?>">
            </label>
            <label class="block">
                <span class="text-sm font-medium">Last Name</span>
                <input class="form-input mt-1" name="last_name" pattern="[A-Za-z .'-]+" title="Use letters only. Spaces, hyphens, apostrophes, and periods are allowed." required value="<?= e($athlete['last_name']) ?>">
            </label>
            <label class="block">
                <span class="text-sm font-medium">Gender</span>
                <select class="form-input mt-1" name="gender">
                    <option value="">Select gender</option>
                    <?php foreach (['Male','Female'] as $gender): ?><option <?= $athlete['gender'] === $gender ? 'selected' : '' ?>><?= e($gender) ?></option><?php endforeach; ?>
                </select>
            </label>
            <label class="block">
                <span class="text-sm font-medium">Birthdate</span>
                <input class="form-input mt-1" type="date" name="birthdate" value="<?= e($athlete['birthdate']) ?>">
            </label>
            <label class="block">
                <span class="text-sm font-medium">Course</span>
                <input class="form-input mt-1" name="course" required value="<?= e($athlete['course']) ?>">
            </label>
            <label class="block">
                <span class="text-sm font-medium">Year Level</span>
                <select class="form-input mt-1" name="year_level" required>
                    <option value="">Select year level</option>
                    <?php foreach (['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'] as $year): ?>
                        <option value="<?= e($year) ?>" <?= ($athlete['year_level'] ?? '') === $year ? 'selected' : '' ?>><?= e($year) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <?php foreach (['section'=>'Section','contact_number'=>'Contact Number','guardian_name'=>'Guardian Name','guardian_contact'=>'Guardian Contact','emergency_contact'=>'Emergency Contact','height'=>'Height','weight'=>'Weight','blood_type'=>'Blood Type','medical_condition'=>'Medical Condition'] as $name=>$label): ?>
                <label class="block">
                    <span class="text-sm font-medium"><?= e($label) ?></span>
                    <input class="form-input mt-1" name="<?= e($name) ?>" required value="<?= e($athlete[$name]) ?>">
                </label>
            <?php endforeach; ?>
            <input type="hidden" name="position" value="<?= e($athlete['position'] ?? '') ?>">
            <label class="block">
                <span class="text-sm font-medium">Sport</span>
                <select class="form-input mt-1" name="sport_id">
                    <option value="">Select sport</option>
                    <?php foreach ($sports as $sport): ?><option value="<?= e((string)$sport['id']) ?>" <?= (int)$athlete['sport_id'] === (int)$sport['id'] ? 'selected' : '' ?>><?= e($sport['name']) ?></option><?php endforeach; ?>
                </select>
            </label>
            <div class="rounded-lg bg-slate-50 p-3 text-sm text-slate-500">Team assignment is managed by the sports office or your coach.</div>
            <label class="block">
                <span class="text-sm font-medium">Profile Photo / 2x2</span>
                <input class="form-input mt-1" type="file" name="profile_photo" accept="image/*" capture="environment">
            </label>
            <div class="grid gap-3 md:col-span-3 md:grid-cols-3">
                <label class="block">
                    <span class="text-sm font-medium">Province</span>
                    <select class="form-input mt-1" name="address_province" data-address-province required>
                        <option value="Isabela" selected>Isabela</option>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-medium">Municipality / City</span>
                    <select class="form-input mt-1" name="address_municipality" data-address-municipality required>
                        <option value="">Loading municipalities...</option>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-medium">Barangay</span>
                    <select class="form-input mt-1" name="address_barangay" data-address-barangay required disabled>
                        <option value="">Select municipality first</option>
                    </select>
                </label>
            </div>
            <input type="hidden" name="address" data-address-combined value="<?= e($athlete['address']) ?>">
            <label class="block md:col-span-3">
                <span class="text-sm font-medium">Address</span>
                <input class="form-input mt-1 bg-slate-50 text-slate-600" data-address-preview readonly placeholder="Select barangay, municipality, and province" value="<?= e($athlete['address']) ?>">
            </label>
        </div>
        <div class="mt-5 flex justify-end">
            <button class="btn-primary" type="submit">Save Profile</button>
        </div>
    </form>
    <script>
    (() => {
        let isabelaAddressData = null;
        const addressDataUrl = <?= json_encode(app_url('assets/data/isabela-addresses.json')) ?>;
        const provinceField = document.querySelector('[data-address-province]');
        const municipalityField = document.querySelector('[data-address-municipality]');
        const barangayField = document.querySelector('[data-address-barangay]');
        const combinedAddressField = document.querySelector('[data-address-combined]');
        const addressPreview = document.querySelector('[data-address-preview]');
        const existingAddress = <?= json_encode((string)($athlete['address'] ?? '')) ?>;

        function resetOptions(select, placeholder) {
            select.innerHTML = '';
            const option = document.createElement('option');
            option.value = '';
            option.textContent = placeholder;
            select.appendChild(option);
        }

        function parseExistingAddress() {
            const parts = existingAddress.split(',').map((part) => part.trim()).filter(Boolean);
            return {
                barangay: parts[0] || '',
                municipality: parts[1] || '',
                province: parts[2] || 'Isabela',
            };
        }

        function populateMunicipalities(selectedMunicipality = '') {
            resetOptions(municipalityField, 'Select municipality / city');
            isabelaAddressData.municipalities.forEach((municipality) => {
                const option = document.createElement('option');
                option.value = municipality.name;
                option.textContent = municipality.name;
                option.dataset.code = municipality.code;
                option.selected = municipality.name === selectedMunicipality;
                municipalityField.appendChild(option);
            });
        }

        function populateBarangays(selectedBarangay = '') {
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
                option.selected = barangay.name === selectedBarangay;
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
                const parsed = parseExistingAddress();
                populateMunicipalities(parsed.municipality);
                populateBarangays(parsed.barangay);
            } catch (error) {
                resetOptions(municipalityField, 'Unable to load municipalities');
                resetOptions(barangayField, 'Unable to load barangays');
                municipalityField.disabled = true;
                barangayField.disabled = true;
            }
        }

        municipalityField.addEventListener('change', () => populateBarangays());
        barangayField.addEventListener('change', updateCombinedAddress);
        provinceField.addEventListener('change', updateCombinedAddress);
        document.querySelector('form[action$="athlete_ajax.php"]').addEventListener('submit', updateCombinedAddress);
        loadAddressData();
    })();
    </script>
<?php endif; ?>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
