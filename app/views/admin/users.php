<?php
require_once __DIR__ . '/../../controllers/UserController.php';
require_role(['admin', 'sports_coordinator']);
$pageTitle = 'User Management';
$controller = new UserController($pdo);
$users = $controller->all($_GET['q'] ?? '');
$sports = $controller->sports();
$coachSportsMap = $controller->coachSportsMap();
$sportNamesById = array_column($sports, 'name', 'id');
require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72"><?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?>
<main class="p-4 lg:p-6">
<section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-col gap-3 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-black text-slate-950">Users</h2>
            <p class="mt-1 text-sm text-slate-500">Manage non-admin user accounts and access status.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= e(app_url('index.php?page=reports#coach-report')) ?>" class="smis-cmd-btn" title="View & Print Master List of Coaches">
                🖨️ Master List of Coaches
            </a>
            <button class="btn-primary" type="button" data-modal-open="#add-user-modal">+ Add User</button>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="table-th">Name</th>
                    <th class="table-th">Email</th>
                    <th class="table-th">Contact Number</th>
                    <th class="table-th">Role</th>
                    <th class="table-th">Delegated Sport(s)</th>
                    <th class="table-th">Status</th>
                    <th class="table-th text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td class="table-td font-semibold"><?= e($u['name']) ?></td>
                        <td class="table-td"><?= e($u['email']) ?></td>
                        <td class="table-td"><?= e($u['phone_number'] ?: '—') ?></td>
                        <td class="table-td"><?= e(str_replace('_', ' ', $u['role'])) ?></td>
                        <td class="table-td">
                            <?php if ($u['role'] === 'coach'): ?>
                                <?php $assignedSportIds = $coachSportsMap[(int)$u['id']] ?? []; ?>
                                <div class="flex flex-wrap gap-1.5">
                                    <?php foreach ($assignedSportIds as $sportId): ?>
                                        <span class="rounded-full bg-emerald-50 px-2 py-1 text-[11px] font-bold text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300"><?= e($sportNamesById[$sportId] ?? 'Sport #' . $sportId) ?></span>
                                    <?php endforeach; ?>
                                    <?php if (!$assignedSportIds): ?>
                                        <span class="text-xs font-semibold text-rose-500">No sport delegated</span>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-slate-400">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="table-td">
                            <select class="form-input" data-ajax-status data-url="<?= project_url('app/ajax/user_ajax.php') ?>" data-id="<?= e($u['id']) ?>" data-action="status">
                                <option value="active" <?= $u['status']==='active'?'selected':'' ?>>ACTIVE</option>
                                <option value="inactive" <?= $u['status']==='inactive'?'selected':'' ?>>INACTIVE</option>
                            </select>
                        </td>
                        <td class="table-td text-right">
                            <div class="flex justify-end gap-2">
                                <?php if ($u['role'] === 'coach'): ?>
                                    <button class="rounded-lg border border-blue-200 px-3 py-2 text-sm font-bold text-blue-700 hover:bg-blue-50" type="button" data-modal-open="#coach-sports-modal-<?= e((string)$u['id']) ?>">Sports</button>
                                <?php endif; ?>
                                <form method="post" action="<?= project_url('app/ajax/user_ajax.php') ?>" data-ajax-form data-confirm="Reset this user password to the default password123?">
                                    <input type="hidden" name="action" value="reset_password">
                                    <input type="hidden" name="id" value="<?= e((string)$u['id']) ?>">
                                    <button class="rounded-lg border border-amber-200 px-3 py-2 text-sm font-bold text-amber-700 hover:bg-amber-50" type="submit" data-loading-text="Resetting...">Reset Password</button>
                                </form>
                                <form method="post" action="<?= project_url('app/ajax/user_ajax.php') ?>" data-ajax-form data-confirm="Delete this user account?">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= e((string)$u['id']) ?>">
                                    <button class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-bold text-rose-600 hover:bg-rose-50" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$users): ?>
                    <tr><td class="table-td py-10 text-center text-slate-500" colspan="7">No users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<div id="add-user-modal" class="fixed inset-0 z-[70] hidden bg-slate-950/60 p-4 backdrop-blur-sm" data-modal>
    <div class="mx-auto flex min-h-full max-w-lg items-center">
        <section class="w-full overflow-hidden rounded-2xl bg-white shadow-2xl">
            <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">User Management</p>
                    <h2 class="text-lg font-black text-slate-950">Add User</h2>
                </div>
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" type="button" data-modal-close>Close</button>
            </header>
            <form class="p-5" method="post" action="<?= project_url('app/ajax/user_ajax.php') ?>" data-ajax-form data-validate>
                <label class="mb-3 block">
                    <span class="text-sm font-semibold text-slate-700">Full Name <span class="text-rose-600 font-bold">*</span></span>
                    <input class="form-input mt-1" name="name" required>
                </label>
                <label class="mb-3 block">
                    <span class="text-sm font-semibold text-slate-700">Email Address <span class="text-rose-600 font-bold">*</span></span>
                    <input class="form-input mt-1" name="email" type="email" required>
                </label>
                <label class="mb-3 block">
                    <span class="text-sm font-semibold text-slate-700">Contact Number (11 Digits)</span>
                    <input class="form-input mt-1" type="tel" name="phone_number" placeholder="09XXXXXXXXX (11 digits)" pattern="09[0-9]{9}" minlength="11" maxlength="11" inputmode="numeric" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 11)" data-phone-11>
                </label>
                <label class="mb-3 block">
                    <span class="text-sm font-medium">Password</span>
                    <input class="form-input mt-1" name="password" type="password" placeholder="Default: password123">
                </label>
                <label class="mb-3 block">
                    <span class="text-sm font-medium">Role</span>
                    <select class="form-input mt-1" name="role">
                        <option value="coach">Coach</option>
                        <option value="athlete">Athlete</option>
                    </select>
                </label>
                <fieldset class="mb-4 rounded-xl border border-slate-200 p-3" data-coach-sports-fieldset>
                    <legend class="px-1 text-sm font-bold text-slate-700">Coach Sport Delegation</legend>
                    <p class="mb-2 text-xs text-slate-500">Required for coach accounts. Coaches can only manage delegated sports.</p>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <?php foreach ($sports as $sport): ?>
                            <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">
                                <input type="checkbox" name="sport_ids[]" value="<?= e((string)$sport['id']) ?>">
                                <span><?= e($sport['name']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
                <label class="mb-4 block">
                    <span class="text-sm font-medium">Status</span>
                    <select class="form-input mt-1" name="status"><option value="active">ACTIVE</option><option value="inactive">INACTIVE</option></select>
                </label>
                <button class="btn-primary w-full" type="submit">Save User</button>
            </form>
        </section>
    </div>
</div>

<?php foreach ($users as $u): ?>
    <?php if ($u['role'] !== 'coach') continue; ?>
    <?php $assignedSportIds = $coachSportsMap[(int)$u['id']] ?? []; ?>
    <div id="coach-sports-modal-<?= e((string)$u['id']) ?>" class="fixed inset-0 z-[70] hidden bg-slate-950/60 p-4 backdrop-blur-sm" data-modal>
        <div class="mx-auto flex min-h-full max-w-xl items-center">
            <section class="w-full overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900">
                <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-blue-600">Coach Sport Delegation</p>
                        <h2 class="text-lg font-black text-slate-950 dark:text-white"><?= e($u['name']) ?></h2>
                    </div>
                    <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300" type="button" data-modal-close>Close</button>
                </header>
                <form class="p-5" method="post" action="<?= project_url('app/ajax/user_ajax.php') ?>" data-ajax-form>
                    <input type="hidden" name="action" value="coach_sports">
                    <input type="hidden" name="id" value="<?= e((string)$u['id']) ?>">
                    <div class="grid gap-2 sm:grid-cols-2">
                        <?php foreach ($sports as $sport): ?>
                            <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 dark:border-slate-800 dark:text-slate-300">
                                <input type="checkbox" name="sport_ids[]" value="<?= e((string)$sport['id']) ?>" <?= in_array((int)$sport['id'], $assignedSportIds, true) ? 'checked' : '' ?>>
                                <span><?= e($sport['name']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <button class="btn-primary mt-4 w-full" type="submit">Save Delegation</button>
                </form>
            </section>
        </div>
    </div>
<?php endforeach; ?>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

