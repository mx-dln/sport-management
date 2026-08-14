<?php
require_once __DIR__ . '/../../controllers/UserController.php';
require_role(['admin', 'sports_coordinator']);
$pageTitle = 'User Management';
$controller = new UserController($pdo);
$users = $controller->all($_GET['q'] ?? '');
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
        <button class="btn-primary" type="button" data-modal-open="#add-user-modal">Add User</button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="table-th">Name</th>
                    <th class="table-th">Email</th>
                    <th class="table-th">Contact Number</th>
                    <th class="table-th">Role</th>
                    <th class="table-th">Status</th>
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
                            <select class="form-input" data-ajax-status data-url="<?= project_url('app/ajax/user_ajax.php') ?>" data-id="<?= e($u['id']) ?>" data-action="status">
                                <option <?= $u['status']==='active'?'selected':'' ?>>active</option>
                                <option <?= $u['status']==='inactive'?'selected':'' ?>>inactive</option>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$users): ?>
                    <tr><td class="table-td py-10 text-center text-slate-500" colspan="5">No users found.</td></tr>
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
                    <span class="text-sm font-medium">Full Name</span>
                    <input class="form-input mt-1" name="name" required>
                </label>
                <label class="mb-3 block">
                    <span class="text-sm font-medium">Email</span>
                    <input class="form-input mt-1" name="email" type="email" required>
                </label>
                <label class="mb-3 block">
                    <span class="text-sm font-medium">Contact Number</span>
                    <input class="form-input mt-1" name="phone_number" placeholder="Used for SMS notifications">
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
                <label class="mb-4 block">
                    <span class="text-sm font-medium">Status</span>
                    <select class="form-input mt-1" name="status"><option>active</option><option>inactive</option></select>
                </label>
                <button class="btn-primary w-full" type="submit">Save User</button>
            </form>
        </section>
    </div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
