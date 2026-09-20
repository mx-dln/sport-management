<?php
require_once __DIR__ . '/../../controllers/AthleteController.php';
require_once __DIR__ . '/../../controllers/SportController.php';
require_once __DIR__ . '/../../controllers/TeamController.php';
require_role(['admin', 'sports_coordinator', 'coach']);
$pageTitle = 'Athlete Profiles';
$currentRole = current_user()['role'] ?? '';
$canEditAthletes = in_array($currentRole, ['admin', 'sports_coordinator'], true);
$athleteController = new AthleteController($pdo);
$sports = (new SportController($pdo))->all();
$teams = (new TeamController($pdo))->all();
$athletes = $athleteController->all($_GET);
$selectedSport = (string) ($_GET['sport_id'] ?? '');
$selectedTeam = (string) ($_GET['team_id'] ?? '');
$selectedStatus = (string) ($_GET['athlete_status'] ?? '');
$search = (string) ($_GET['q'] ?? '');
$perPage = 10;
$currentPage = max(1, (int) ($_GET['p'] ?? 1));
$totalAthletes = count($athletes);
$totalPages = max(1, (int) ceil($totalAthletes / $perPage));
$currentPage = min($currentPage, $totalPages);
$pagedAthletes = array_slice($athletes, ($currentPage - 1) * $perPage, $perPage);
$pageUrl = function (int $pageNumber): string {
    $params = array_merge($_GET, ['page' => 'athletes', 'p' => $pageNumber]);
    return app_url('index.php?' . http_build_query($params));
};
require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72">
    <?php require __DIR__ . '/../../includes/sidebar.php';
    require __DIR__ . '/../../includes/navbar.php'; ?>
    <main class="p-4 lg:p-6">
        <!-- <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="text-lg font-bold">Add / Update Athlete Biodata</h2>
                <button
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                    type="button" data-toggle-panel data-target="#athlete-biodata-form">Hide</button>
            </div>
            <form id="athlete-biodata-form" class="grid gap-3 md:grid-cols-3" method="post"
                enctype="multipart/form-data" action="<?= project_url('app/ajax/athlete_ajax.php') ?>" data-ajax-form
                data-validate>
                <?php foreach (['student_id' => 'Student ID', 'first_name' => 'First Name', 'middle_name' => 'Middle Name', 'last_name' => 'Last Name', 'birthdate' => 'Birthdate', 'address' => 'Address', 'course' => 'Course', 'year_level' => 'Year Level', 'section' => 'Section', 'contact_number' => 'Contact No.', 'guardian_name' => 'Guardian', 'guardian_contact' => 'Guardian Contact', 'emergency_contact' => 'Emergency Contact', 'height' => 'Height', 'weight' => 'Weight', 'blood_type' => 'Blood Type', 'medical_condition' => 'Medical Condition'] as $name => $label): ?>
                    <input class="form-input" name="<?= e($name) ?>" placeholder="<?= e($label) ?>" <?= in_array($name, ['student_id', 'first_name', 'last_name'], true) ? 'required' : '' ?>     <?= $name === 'birthdate' ? 'type="date"' : '' ?>>
                <?php endforeach; ?>
                <select class="form-input" name="gender">
                    <option>Male</option>
                    <option>Female</option>
                </select>
                <select class="form-input" name="sport_id">
                    <option value="">Sport</option><?php foreach ($sports as $s): ?>
                        <option value="<?= e($s['id']) ?>"><?= e($s['name']) ?></option><?php endforeach; ?>
                </select>
                <select class="form-input" name="team_id">
                    <option value="">Team</option><?php foreach ($teams as $t): ?>
                        <option value="<?= e($t['id']) ?>"><?= e($t['name']) ?></option><?php endforeach; ?>
                </select>
                <select class="form-input" name="athlete_status">
                    <option value="Active">ACTIVE</option>
                    <option value="Inactive">INACTIVE</option>
                    <option value="Graduated">GRADUATED</option>
                    <option value="Injured">INJURED</option>
                </select>
                <input class="form-input" type="file" name="profile_photo" accept=".jpg,.jpeg,.png">
                <button class="btn-primary md:col-span-3" type="submit">Save Athlete</button>
            </form>
        </div> -->
        <section class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-5">
                <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <h2 class="text-lg font-black text-slate-950">Athlete Master List</h2>
                        <p class="mt-1 text-sm text-slate-500"><?= e((string) count($athletes)) ?>
                            record<?= count($athletes) === 1 ? '' : 's' ?> found</p>
                    </div>
                    <form class="grid gap-2 md:grid-cols-[minmax(220px,1fr)_180px_180px_160px_auto_auto]" method="get">
                        <input type="hidden" name="page" value="athletes">
                        <input type="hidden" name="p" value="1">
                        <input class="form-input" name="q" placeholder="Search student or name"
                            value="<?= e($search) ?>">
                        <select class="form-input" name="sport_id">
                            <option value="">All Sports</option>
                            <?php foreach ($sports as $s): ?>
                                <option value="<?= e((string) $s['id']) ?>" <?= $selectedSport === (string) $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option><?php endforeach; ?>
                        </select>
                        <select class="form-input" name="team_id">
                            <option value="">All Teams</option>
                            <?php foreach ($teams as $t): ?>
                                <option value="<?= e((string) $t['id']) ?>" <?= $selectedTeam === (string) $t['id'] ? 'selected' : '' ?>><?= e($t['name']) ?></option><?php endforeach; ?>
                        </select>
                        <select class="form-input" name="athlete_status">
                            <option value="">All Status</option>
                            <?php foreach (['Active', 'Inactive', 'Graduated', 'Injured'] as $status): ?>
                                <option value="<?= e($status) ?>" <?= $selectedStatus === $status ? 'selected' : '' ?>>
                                    <?= e(strtoupper($status)) ?></option><?php endforeach; ?>
                        </select>
                        <button class="btn-primary" type="submit">Filter</button>
                        <a class="btn-muted text-center" href="<?= e(app_url('index.php?page=athletes')) ?>">Reset</a>
                    </form>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm" data-enhance-table="false">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="table-th">Student</th>
                            <th class="table-th">Name</th>
                            <th class="table-th">Sport</th>
                            <th class="table-th">Team</th>
                            <th class="table-th">Status</th>
                            <th class="table-th text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pagedAthletes as $a): ?>
                            <?php
                            $statusClass = match ($a['athlete_status'] ?? '') {
                                'Active' => 'status-active',
                                'Inactive' => 'status-inactive',
                                default => 'status-neutral',
                            };
                            ?>
                            <tr>
                                <td class="table-td font-semibold"><?= e($a['student_id']) ?></td>
                                <td class="table-td">
                                    <p class="font-bold text-slate-950"><?= e($a['last_name'] . ', ' . $a['first_name']) ?>
                                    </p>
                                    <p class="text-xs text-slate-500"><?= e($a['course'] ?? '') ?></p>
                                </td>
                                <td class="table-td"><?= e($a['sport_name'] ?: 'Unassigned') ?></td>
                                <td class="table-td"><?= e($a['team_name'] ?: 'Unassigned') ?></td>
                                <td class="table-td"><span
                                        class="status-pill <?= e($statusClass) ?>"><?= e(strtoupper((string)$a['athlete_status'])) ?></span>
                                </td>
                                <td class="table-td text-right">
                                    <div class="flex justify-end gap-2">
                                        <a class="font-semibold text-blue-600" href="<?= e(app_url('index.php?page=athlete_print&id=' . $a['id'])) ?>">View Profile</a>
                                        <form method="post" action="<?= project_url('app/ajax/athlete_ajax.php') ?>" data-ajax-form data-confirm="Reset this athlete password to the default password123?">
                                            <input type="hidden" name="action" value="reset_password">
                                            <input type="hidden" name="id" value="<?= e((string)$a['id']) ?>">
                                            <button class="rounded-lg border border-amber-200 px-3 py-2 text-sm font-bold text-amber-700 hover:bg-amber-50" type="submit" data-loading-text="Resetting...">Reset Password</button>
                                        </form>
                                        <?php if ($canEditAthletes): ?>
                                            <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50" type="button" data-modal-open="#athlete-edit-modal-<?= e((string)$a['id']) ?>">Edit</button>
                                            <form method="post" action="<?= project_url('app/ajax/athlete_ajax.php') ?>" data-ajax-form data-confirm="Delete this athlete? Documents, medical records, attendance, and competition entries will also be removed.">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= e((string)$a['id']) ?>">
                                                <button class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-bold text-rose-600 hover:bg-rose-50" type="submit">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$pagedAthletes): ?>
                            <tr>
                                <td class="table-td py-10 text-center text-slate-500" colspan="6">No athletes match your
                                    filters.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($totalAthletes > 0): ?>
                <div
                    class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-500">
                        Showing <?= e((string) ((($currentPage - 1) * $perPage) + 1)) ?>
                        to <?= e((string) min($currentPage * $perPage, $totalAthletes)) ?>
                        of <?= e((string) $totalAthletes) ?> athletes
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <a class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold <?= $currentPage <= 1 ? 'pointer-events-none opacity-40' : 'hover:bg-slate-50' ?>"
                            href="<?= e($pageUrl($currentPage - 1)) ?>">Previous</a>
                        <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                            <a class="rounded-lg border px-3 py-2 text-sm font-semibold <?= $i === $currentPage ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-300 text-slate-700 hover:bg-slate-50' ?>"
                                href="<?= e($pageUrl($i)) ?>"><?= e((string) $i) ?></a>
                        <?php endfor; ?>
                        <a class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold <?= $currentPage >= $totalPages ? 'pointer-events-none opacity-40' : 'hover:bg-slate-50' ?>"
                            href="<?= e($pageUrl($currentPage + 1)) ?>">Next</a>
                    </div>
                </div>
            <?php endif; ?>
        </section>

        <?php if ($canEditAthletes): ?>
        <?php foreach ($pagedAthletes as $a): ?>
            <div id="athlete-edit-modal-<?= e((string)$a['id']) ?>" class="fixed inset-0 z-[70] hidden bg-slate-950/60 p-4 backdrop-blur-sm" data-modal>
                <div class="mx-auto flex min-h-full max-w-3xl items-center">
                    <section class="max-h-[86vh] w-full overflow-hidden rounded-2xl bg-white shadow-2xl">
                        <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-blue-600">Athlete Biodata</p>
                                <h2 class="text-lg font-black text-slate-950">Edit Athlete</h2>
                                <p class="text-sm text-slate-500"><?= e($a['student_id'] . ' — ' . $a['last_name'] . ', ' . $a['first_name']) ?></p>
                            </div>
                            <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" type="button" data-modal-close>Close</button>
                        </header>
                        <form class="grid max-h-[70vh] gap-3 overflow-y-auto p-5 md:grid-cols-3" method="post" enctype="multipart/form-data" action="<?= project_url('app/ajax/athlete_ajax.php') ?>" data-ajax-form data-validate data-athlete-address-form data-existing-address="<?= e($a['address'] ?? '') ?>">
                            <input type="hidden" name="id" value="<?= e((string)$a['id']) ?>">
                            <input type="hidden" name="user_id" value="<?= e((string)($a['user_id'] ?? '')) ?>">
                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">Student ID</span>
                                <input class="form-input mt-1" name="student_id" value="<?= e($a['student_id']) ?>" required>
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">First Name</span>
                                <input class="form-input mt-1" name="first_name" value="<?= e($a['first_name']) ?>" required>
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">Middle Name</span>
                                <input class="form-input mt-1" name="middle_name" value="<?= e($a['middle_name'] ?? '') ?>">
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">Last Name</span>
                                <input class="form-input mt-1" name="last_name" value="<?= e($a['last_name']) ?>" required>
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">Gender</span>
                                <select class="form-input mt-1" name="gender">
                                    <option value="Male" <?= ($a['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= ($a['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                                </select>
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">Birthdate</span>
                                <input class="form-input mt-1" type="date" name="birthdate" value="<?= e($a['birthdate'] ?? '') ?>">
                            </label>
                            <div class="grid gap-3 md:col-span-3 md:grid-cols-3">
                                <label class="block">
                                    <span class="text-sm font-semibold text-slate-700">Province</span>
                                    <select class="form-input mt-1" name="address_province" data-address-province required>
                                        <option value="Isabela" selected>Isabela</option>
                                    </select>
                                </label>
                                <label class="block">
                                    <span class="text-sm font-semibold text-slate-700">Municipality / City</span>
                                    <select class="form-input mt-1" name="address_municipality" data-address-municipality required>
                                        <option value="">Loading municipalities...</option>
                                    </select>
                                </label>
                                <label class="block">
                                    <span class="text-sm font-semibold text-slate-700">Barangay</span>
                                    <select class="form-input mt-1" name="address_barangay" data-address-barangay required disabled>
                                        <option value="">Select municipality first</option>
                                    </select>
                                </label>
                            </div>
                            <input type="hidden" name="address" data-address-combined value="<?= e($a['address'] ?? '') ?>">
                            <label class="block md:col-span-3">
                                <span class="text-sm font-semibold text-slate-700">Address</span>
                                <input class="form-input mt-1 bg-slate-50 text-slate-600" data-address-preview readonly placeholder="Select barangay, municipality, and province" value="<?= e($a['address'] ?? '') ?>">
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">Course</span>
                                <input class="form-input mt-1" name="course" value="<?= e($a['course'] ?? '') ?>">
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">Year Level</span>
                                <input class="form-input mt-1" name="year_level" value="<?= e($a['year_level'] ?? '') ?>">
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">Section</span>
                                <input class="form-input mt-1" name="section" value="<?= e($a['section'] ?? '') ?>">
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">Contact No.</span>
                                <input class="form-input mt-1" name="contact_number" value="<?= e($a['contact_number'] ?? '') ?>">
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">Guardian</span>
                                <input class="form-input mt-1" name="guardian_name" value="<?= e($a['guardian_name'] ?? '') ?>">
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">Guardian Contact</span>
                                <input class="form-input mt-1" name="guardian_contact" value="<?= e($a['guardian_contact'] ?? '') ?>">
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">Emergency Contact</span>
                                <input class="form-input mt-1" name="emergency_contact" value="<?= e($a['emergency_contact'] ?? '') ?>">
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">Height</span>
                                <input class="form-input mt-1" name="height" value="<?= e($a['height'] ?? '') ?>">
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">Weight</span>
                                <input class="form-input mt-1" name="weight" value="<?= e($a['weight'] ?? '') ?>">
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">Blood Type</span>
                                <input class="form-input mt-1" name="blood_type" value="<?= e($a['blood_type'] ?? '') ?>">
                            </label>
                            <label class="block md:col-span-3">
                                <span class="text-sm font-semibold text-slate-700">Medical Condition</span>
                                <input class="form-input mt-1" name="medical_condition" value="<?= e($a['medical_condition'] ?? '') ?>">
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">Sport</span>
                                <select class="form-input mt-1" name="sport_id">
                                    <option value="">Unassigned</option>
                                    <?php foreach ($sports as $sport): ?>
                                        <option value="<?= e($sport['id']) ?>" <?= (string)($a['sport_id'] ?? '') === (string)$sport['id'] ? 'selected' : '' ?>><?= e($sport['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">Team</span>
                                <select class="form-input mt-1" name="team_id">
                                    <option value="">Unassigned</option>
                                    <?php foreach ($teams as $team): ?>
                                        <option value="<?= e($team['id']) ?>" <?= (string)($a['team_id'] ?? '') === (string)$team['id'] ? 'selected' : '' ?>><?= e($team['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">Athlete Status</span>
                                <select class="form-input mt-1" name="athlete_status">
                                    <?php foreach (['Active', 'Inactive', 'Graduated', 'Injured'] as $status): ?>
                                        <option value="<?= e($status) ?>" <?= ($a['athlete_status'] ?? '') === $status ? 'selected' : '' ?>><?= e(strtoupper($status)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">Profile Photo</span>
                                <input class="form-input mt-1" type="file" name="profile_photo" accept=".jpg,.jpeg,.png">
                            </label>
                            <button class="btn-primary md:col-span-3">Save Changes</button>
                        </form>
                    </section>
                </div>
            </div>
        <?php endforeach; ?>
        <?php endif; ?>
        <script>
        (() => {
            const forms = Array.from(document.querySelectorAll('[data-athlete-address-form]'));
            if (!forms.length) return;

            const addressDataUrl = <?= json_encode(app_url('assets/data/isabela-addresses.json')) ?>;
            let isabelaAddressData = null;

            function resetOptions(select, placeholder) {
                select.innerHTML = '';
                const option = document.createElement('option');
                option.value = '';
                option.textContent = placeholder;
                select.appendChild(option);
            }

            function parseAddress(address) {
                const parts = (address || '').split(',').map((part) => part.trim()).filter(Boolean);
                return {
                    barangay: parts[0] || '',
                    municipality: parts[1] || '',
                    province: parts[2] || 'Isabela',
                };
            }

            function updateCombinedAddress(form) {
                const province = form.querySelector('[data-address-province]');
                const municipality = form.querySelector('[data-address-municipality]');
                const barangay = form.querySelector('[data-address-barangay]');
                const combined = form.querySelector('[data-address-combined]');
                const preview = form.querySelector('[data-address-preview]');
                const value = [barangay.value, municipality.value, province.value].filter(Boolean).join(', ');
                combined.value = value;
                preview.value = value;
            }

            function populateMunicipalities(form, selectedMunicipality = '') {
                const municipalityField = form.querySelector('[data-address-municipality]');
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

            function populateBarangays(form, selectedBarangay = '') {
                const municipalityField = form.querySelector('[data-address-municipality]');
                const barangayField = form.querySelector('[data-address-barangay]');
                const municipality = isabelaAddressData?.municipalities.find((item) => item.name === municipalityField.value);
                resetOptions(barangayField, municipality ? 'Select barangay' : 'Select municipality first');
                barangayField.disabled = !municipality;

                if (!municipality) {
                    updateCombinedAddress(form);
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
                updateCombinedAddress(form);
            }

            function initForm(form) {
                const parsed = parseAddress(form.dataset.existingAddress || '');
                populateMunicipalities(form, parsed.municipality);
                populateBarangays(form, parsed.barangay);
                form.querySelector('[data-address-municipality]').addEventListener('change', () => populateBarangays(form));
                form.querySelector('[data-address-barangay]').addEventListener('change', () => updateCombinedAddress(form));
                form.querySelector('[data-address-province]').addEventListener('change', () => updateCombinedAddress(form));
                form.addEventListener('submit', () => updateCombinedAddress(form));
            }

            fetch(addressDataUrl)
                .then((response) => {
                    if (!response.ok) throw new Error('Address data failed to load.');
                    return response.json();
                })
                .then((data) => {
                    isabelaAddressData = data;
                    forms.forEach(initForm);
                })
                .catch(() => {
                    forms.forEach((form) => {
                        resetOptions(form.querySelector('[data-address-municipality]'), 'Unable to load municipalities');
                        resetOptions(form.querySelector('[data-address-barangay]'), 'Unable to load barangays');
                        form.querySelector('[data-address-municipality]').disabled = true;
                        form.querySelector('[data-address-barangay]').disabled = true;
                    });
                });
        })();
        </script>
        <?php require __DIR__ . '/../../includes/footer.php'; ?>
