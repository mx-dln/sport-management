<?php
require_once __DIR__ . '/../../controllers/CompetitionController.php';
require_role(['admin', 'sports_coordinator']);
$pageTitle = 'Competition Management';
$competitionController = new CompetitionController($pdo);

$competitionId = (int)($_GET['id'] ?? 0);
$competition = $competitionController->find($competitionId);
if (!$competition) {
    http_response_code(404);
    exit('Competition not found.');
}

$participants = $competitionController->participants($competitionId);
$availableAthletes = $competitionController->availableAthletes($competitionId);
$results = $competitionController->results($competitionId);
$coaches = $competitionController->coaches();
$smsCoaches = $competitionController->smsCoaches();
$smsTeams = $competitionController->smsTeams();
$smsLogs = $competitionController->smsLogs();

$medals = ['Gold', 'Silver', 'Bronze', 'None'];
$resultStatuses = ['Winner', 'Qualified', 'Eliminated'];

require __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen lg:pl-72"><?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?><main class="p-4 lg:p-6">
<div class="mb-6 flex flex-wrap items-start justify-between gap-3">
    <div>
        <a class="mb-2 inline-flex items-center gap-1 text-sm font-bold text-slate-500 hover:text-slate-700" href="<?= e(app_url('index.php?page=competition')) ?>">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"></path></svg>
            Back to Competitions
        </a>
        <h2 class="text-2xl font-black text-slate-950"><?= e($competition['name']) ?></h2>
        <p class="mt-1 text-sm text-slate-500">
            Competition #<?= e((string)$competition['id']) ?> · <?= e($competition['sport_name'] ?: 'Any sport') ?> · <?= e($competition['category']) ?> · <?= e($competition['level']) ?> · <?= e($competition['status']) ?>
        </p>
        <p class="mt-1 text-sm text-slate-500">
            <?= e(($competition['start_date'] ?: '—') . ' to ' . ($competition['end_date'] ?: '—')) ?> · Venue: <?= e($competition['venue'] ?: '—') ?> · Organizer: <?= e($competition['organizer'] ?: '—') ?>
        </p>
    </div>
    <span class="status-pill <?= $competition['status'] === 'Ongoing' ? 'status-active' : ($competition['status'] === 'Completed' ? 'status-neutral' : 'status-pending') ?>"><?= e($competition['status']) ?></span>
</div>

<section class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <button class="flex w-full items-center justify-between gap-3 p-4 text-left" type="button" data-toggle-panel data-target="#participants-panel" data-chevron data-show-label="Show participants" data-hide-label="Hide participants">
        <div>
            <h3 class="font-bold">Athlete Participation</h3>
            <p class="text-sm text-slate-500"><?= e((string)count($participants)) ?> athlete(s) enrolled in this competition.</p>
        </div>
        <svg class="h-5 w-5 transition" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"></path></svg>
    </button>
    <div id="participants-panel" class="hidden border-t border-slate-200 p-5">
        <form class="mb-4 grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-2 xl:grid-cols-5" method="post" action="<?= project_url('app/ajax/competition_ajax.php') ?>" data-ajax-form data-validate>
            <input type="hidden" name="action" value="participant">
            <input type="hidden" name="competition_id" value="<?= e((string)$competitionId) ?>">
            <label class="block xl:col-span-2">
                <span class="text-sm font-semibold text-slate-700">Athlete</span>
                <select class="form-input mt-1" name="athlete_id" required>
                    <option value="">Select athlete</option>
                    <?php foreach ($availableAthletes as $athlete): ?>
                        <option value="<?= e($athlete['id']) ?>"><?= e($athlete['student_id'] . ' — ' . $athlete['last_name'] . ', ' . $athlete['first_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="block">
                <span class="text-sm font-semibold text-slate-700">Assigned Event</span>
                <input class="form-input mt-1" name="event_name" placeholder="e.g. 100m Sprint">
            </label>
            <label class="block">
                <span class="text-sm font-semibold text-slate-700">Jersey / Bib No.</span>
                <input class="form-input mt-1" name="jersey_bib" placeholder="Optional">
            </label>
            <label class="block">
                <span class="text-sm font-semibold text-slate-700">Coach</span>
                <select class="form-input mt-1" name="coach_id">
                    <option value="">—</option>
                    <?php foreach ($coaches as $coach): ?>
                        <option value="<?= e($coach['id']) ?>"><?= e($coach['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="btn-primary xl:col-span-5">Add Participant</button>
        </form>
        <div class="overflow-x-auto">
            <table class="w-full text-sm" data-enhance-table="false">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="table-th">Athlete ID</th>
                        <th class="table-th">Athlete</th>
                        <th class="table-th">Team / Section</th>
                        <th class="table-th">Coach</th>
                        <th class="table-th">Assigned Event</th>
                        <th class="table-th">Jersey / Bib</th>
                        <th class="table-th">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($participants as $participant): ?>
                        <tr>
                            <td class="table-td font-semibold text-slate-500"><?= e($participant['student_id']) ?></td>
                            <td class="table-td font-semibold"><?= e($participant['last_name'] . ', ' . $participant['first_name']) ?></td>
                            <td class="table-td"><?= e(($participant['team_name'] ?: '—') . ($participant['section'] ? ' · ' . $participant['section'] : '')) ?></td>
                            <td class="table-td"><?= e($participant['coach_name'] ?: '—') ?></td>
                            <td class="table-td"><?= e($participant['event_name'] ?: '—') ?></td>
                            <td class="table-td"><?= e($participant['jersey_bib'] ?: '—') ?></td>
                            <td class="table-td">
                                <form method="post" action="<?= project_url('app/ajax/competition_ajax.php') ?>" data-ajax-form data-confirm="Remove this athlete from the competition?">
                                    <input type="hidden" name="action" value="remove_participant">
                                    <input type="hidden" name="id" value="<?= e((string)$participant['id']) ?>">
                                    <button class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-bold text-rose-600 hover:bg-rose-50" type="submit">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$participants): ?>
                        <tr>
                            <td class="table-td py-10 text-center text-slate-500" colspan="7">No athletes added yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <button class="flex w-full items-center justify-between gap-3 p-4 text-left" type="button" data-toggle-panel data-target="#results-panel" data-chevron data-show-label="Show results" data-hide-label="Hide results">
        <div>
            <h3 class="font-bold">Competition Results</h3>
            <p class="text-sm text-slate-500"><?= e((string)count($results)) ?> result(s) recorded.</p>
        </div>
        <svg class="h-5 w-5 transition" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"></path></svg>
    </button>
    <div id="results-panel" class="hidden border-t border-slate-200 p-5">
        <div class="mb-4 flex justify-end">
            <button class="btn-primary" type="button" data-modal-open="#result-add-modal">Add Result</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm" data-enhance-table="false">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="table-th">Athlete</th>
                        <th class="table-th">Rank / Place</th>
                        <th class="table-th">Medal</th>
                        <th class="table-th">Score / Time</th>
                        <th class="table-th">Result Status</th>
                        <th class="table-th">Remarks</th>
                        <th class="table-th">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $result): ?>
                        <tr>
                            <td class="table-td font-semibold"><?= e($result['last_name'] . ', ' . $result['first_name']) ?></td>
                            <td class="table-td"><?= e($result['rank_place'] ?: '—') ?></td>
                            <td class="table-td">
                                <?php if ($result['medal'] && $result['medal'] !== 'None'): ?>
                                    <span class="status-pill <?= match ($result['medal']) { 'Gold' => 'status-active', 'Silver' => 'status-pending', default => 'status-submitted' } ?>"><?= e($result['medal']) ?></span>
                                <?php else: ?>
                                    <span class="text-slate-400">None</span>
                                <?php endif; ?>
                            </td>
                            <td class="table-td"><?= e($result['score_time'] ?: '—') ?></td>
                            <td class="table-td">
                                <span class="status-pill <?= match ($result['result_status']) { 'Winner' => 'status-active', 'Qualified' => 'status-pending', default => 'status-rejected' } ?>"><?= e($result['result_status']) ?></span>
                            </td>
                            <td class="table-td"><?= e($result['remarks'] ?: '—') ?></td>
                            <td class="table-td">
                                <div class="flex flex-wrap gap-2">
                                    <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50" type="button" data-modal-open="#result-edit-modal-<?= e((string)$result['id']) ?>">Edit</button>
                                    <form method="post" action="<?= project_url('app/ajax/competition_ajax.php') ?>" data-ajax-form data-confirm="Delete this result?">
                                        <input type="hidden" name="action" value="delete_result">
                                        <input type="hidden" name="id" value="<?= e((string)$result['id']) ?>">
                                        <button class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-bold text-rose-600 hover:bg-rose-50" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$results): ?>
                        <tr>
                            <td class="table-td py-10 text-center text-slate-500" colspan="7">No results recorded yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <button class="flex w-full items-center justify-between gap-3 p-4 text-left" type="button" data-toggle-panel data-target="#sms-panel" data-chevron data-show-label="Show SMS notifications" data-hide-label="Hide SMS notifications">
        <div>
            <h3 class="font-bold">SMS Notification</h3>
            <p class="text-sm text-slate-500">Send competition updates to athletes, coaches, or teams.</p>
        </div>
        <svg class="h-5 w-5 transition" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"></path></svg>
    </button>
    <div id="sms-panel" class="hidden border-t border-slate-200 p-5">
        <form class="mb-4 grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-3" method="post" action="<?= project_url('app/ajax/competition_ajax.php') ?>" data-ajax-form data-validate data-sms-participant-count="<?= e((string)count($participants)) ?>" data-sms-coach-count="<?= e((string)count(array_filter($smsCoaches, static fn (array $c): bool => trim((string)($c['phone_number'] ?? '')) !== ''))) ?>">
            <input type="hidden" name="action" value="sms_bulk">
            <input type="hidden" name="competition_id" value="<?= e((string)$competitionId) ?>">
            <label class="block">
                <span class="text-sm font-semibold text-slate-700">Send To</span>
                <select class="form-input mt-1" name="recipient_type" data-sms-type required>
                    <option value="athlete">Individual Athlete</option>
                    <option value="participants">All Participants</option>
                    <option value="coach">Individual Coach</option>
                    <option value="coaches">All Coaches</option>
                    <option value="team">Team</option>
                </select>
            </label>
            <label class="block md:col-span-2" data-sms-recipient-field>
                <span class="text-sm font-semibold text-slate-700" data-sms-recipient-label>Recipient</span>
                <select class="form-input mt-1" name="recipient_id" data-sms-recipient data-sms-recipient-type="athlete" required>
                    <option value="">Select athlete</option>
                    <?php foreach ($participants as $participant): ?>
                        <option value="<?= e($participant['athlete_id']) ?>" data-phone="<?= e($participant['contact_number'] ?? '') ?>"><?= e($participant['last_name'] . ', ' . $participant['first_name'] . ' (' . $participant['student_id'] . ')') ?><?= $participant['contact_number'] ? ' — ' . e($participant['contact_number']) : ' — no number' ?></option>
                    <?php endforeach; ?>
                </select>
                <select class="form-input mt-1 hidden" name="recipient_id" data-sms-recipient data-sms-recipient-type="coach">
                    <option value="">Select coach</option>
                    <?php foreach ($smsCoaches as $smsCoach): ?>
                        <option value="<?= e($smsCoach['id']) ?>" data-phone="<?= e($smsCoach['phone_number'] ?? '') ?>"><?= e($smsCoach['name']) ?><?= $smsCoach['phone_number'] ? ' — ' . e($smsCoach['phone_number']) : ' — no number' ?></option>
                    <?php endforeach; ?>
                </select>
                <select class="form-input mt-1 hidden" name="recipient_id" data-sms-recipient data-sms-recipient-type="team">
                    <option value="">Select team</option>
                    <?php foreach ($smsTeams as $smsTeam): ?>
                        <option value="<?= e($smsTeam['id']) ?>" data-phone="<?= e($smsTeam['phone_number'] ?? '') ?>"><?= e($smsTeam['name']) ?><?= $smsTeam['phone_number'] ? ' — ' . e($smsTeam['phone_number']) : ' — no number' ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="mt-1 hidden text-sm font-semibold text-slate-600" data-sms-bulk-note></p>
            </label>
            <label class="block" data-sms-phone-field>
                <span class="text-sm font-semibold text-slate-700">Contact Number</span>
                <input class="form-input mt-1" name="phone_number" data-sms-phone placeholder="Auto-fills, editable" required>
            </label>
            <label class="block md:col-span-2">
                <span class="text-sm font-semibold text-slate-700">Notification Message</span>
                <textarea class="form-input mt-1" name="message" rows="2" placeholder="Competition schedule, updates, etc." required></textarea>
            </label>
            <div class="flex items-end md:col-span-3">
                <button class="btn-primary w-full" type="submit" data-sms-submit>Send SMS</button>
            </div>
        </form>
        <div class="overflow-x-auto">
            <table class="w-full text-sm" data-enhance-table="false">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="table-th">Recipient</th>
                        <th class="table-th">Contact Number</th>
                        <th class="table-th">Notification Message</th>
                        <th class="table-th">Date &amp; Time Sent</th>
                        <th class="table-th">SMS Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($smsLogs as $log): ?>
                        <tr>
                            <td class="table-td font-semibold"><?= e($log['recipient_name']) ?></td>
                            <td class="table-td"><?= e($log['phone_number']) ?></td>
                            <td class="table-td"><?= e($log['message']) ?></td>
                            <td class="table-td"><?= e(format_datetime_12($log['sent_at'] ?? '')) ?></td>
                            <td class="table-td">
                                <span class="status-pill <?= str_starts_with((string)$log['status'], 'sent') || str_starts_with((string)$log['status'], 'queued') ? 'status-active' : (str_starts_with((string)$log['status'], 'failed') ? 'status-rejected' : 'status-pending') ?>"><?= e($log['status']) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$smsLogs): ?>
                        <tr>
                            <td class="table-td py-10 text-center text-slate-500" colspan="5">No SMS notifications sent yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<div id="result-add-modal" class="fixed inset-0 z-[70] hidden bg-slate-950/60 p-4 backdrop-blur-sm" data-modal>
    <div class="mx-auto flex min-h-full max-w-xl items-center">
        <section class="w-full overflow-hidden rounded-2xl bg-white shadow-2xl">
            <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-blue-600">Competition Results</p>
                    <h2 class="text-lg font-black text-slate-950">Add Result</h2>
                </div>
                <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" type="button" data-modal-close>Close</button>
            </header>
            <form class="grid gap-3 p-5 md:grid-cols-2" method="post" action="<?= project_url('app/ajax/competition_ajax.php') ?>" data-ajax-form data-validate>
                <input type="hidden" name="action" value="result">
                <input type="hidden" name="competition_id" value="<?= e((string)$competitionId) ?>">
                <label class="block md:col-span-2">
                    <span class="text-sm font-semibold text-slate-700">Athlete</span>
                    <select class="form-input mt-1" name="athlete_id" required>
                        <option value="">Select participant</option>
                        <?php foreach ($participants as $participant): ?>
                            <option value="<?= e($participant['athlete_id']) ?>"><?= e($participant['student_id'] . ' — ' . $participant['last_name'] . ', ' . $participant['first_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Rank / Place</span>
                    <input class="form-input mt-1" name="rank_place" placeholder="e.g. 1st, 2nd">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Medal</span>
                    <select class="form-input mt-1" name="medal">
                        <?php foreach ($medals as $medal): ?>
                            <option value="<?= e($medal) ?>"><?= e($medal) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Score / Time</span>
                    <input class="form-input mt-1" name="score_time" placeholder="e.g. 10.5s, 89 points">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Result Status</span>
                    <select class="form-input mt-1" name="result_status">
                        <?php foreach ($resultStatuses as $status): ?>
                            <option value="<?= e($status) ?>"><?= e($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block md:col-span-2">
                    <span class="text-sm font-semibold text-slate-700">Remarks</span>
                    <textarea class="form-input mt-1" name="remarks" rows="2"></textarea>
                </label>
                <button class="btn-primary md:col-span-2">Save Result</button>
            </form>
        </section>
    </div>
</div>

<?php foreach ($results as $result): ?>
    <div id="result-edit-modal-<?= e((string)$result['id']) ?>" class="fixed inset-0 z-[70] hidden bg-slate-950/60 p-4 backdrop-blur-sm" data-modal>
        <div class="mx-auto flex min-h-full max-w-xl items-center">
            <section class="w-full overflow-hidden rounded-2xl bg-white shadow-2xl">
                <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-blue-600">Competition Results</p>
                        <h2 class="text-lg font-black text-slate-950">Edit Result</h2>
                    </div>
                    <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" type="button" data-modal-close>Close</button>
                </header>
                <form class="grid gap-3 p-5 md:grid-cols-2" method="post" action="<?= project_url('app/ajax/competition_ajax.php') ?>" data-ajax-form data-validate>
                    <input type="hidden" name="action" value="result">
                    <input type="hidden" name="competition_id" value="<?= e((string)$competitionId) ?>">
                    <input type="hidden" name="id" value="<?= e((string)$result['id']) ?>">
                    <input type="hidden" name="athlete_id" value="<?= e((string)$result['athlete_id']) ?>">
                    <p class="text-sm font-bold text-slate-700 md:col-span-2"><?= e($result['last_name'] . ', ' . $result['first_name']) ?></p>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Rank / Place</span>
                        <input class="form-input mt-1" name="rank_place" value="<?= e($result['rank_place'] ?? '') ?>">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Medal</span>
                        <select class="form-input mt-1" name="medal">
                            <?php foreach ($medals as $medal): ?>
                                <option value="<?= e($medal) ?>" <?= ($result['medal'] ?? '') === $medal ? 'selected' : '' ?>><?= e($medal) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Score / Time</span>
                        <input class="form-input mt-1" name="score_time" value="<?= e($result['score_time'] ?? '') ?>">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Result Status</span>
                        <select class="form-input mt-1" name="result_status">
                            <?php foreach ($resultStatuses as $status): ?>
                                <option value="<?= e($status) ?>" <?= ($result['result_status'] ?? '') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block md:col-span-2">
                        <span class="text-sm font-semibold text-slate-700">Remarks</span>
                        <textarea class="form-input mt-1" name="remarks" rows="2"><?= e($result['remarks'] ?? '') ?></textarea>
                    </label>
                    <button class="btn-primary md:col-span-2">Save Changes</button>
                </form>
            </section>
        </div>
    </div>
<?php endforeach; ?>

<?php
$smsParticipantCount = count($participants);
$smsCoachCount = count(array_filter($smsCoaches, static fn (array $c): bool => trim((string)($c['phone_number'] ?? '')) !== ''));
?>

<script>
document.querySelectorAll('[data-sms-type]').forEach((typeSelect) => {
    const form = typeSelect.closest('form');
    const recipientField = form.querySelector('[data-sms-recipient-field]');
    const recipientSelects = form.querySelectorAll('[data-sms-recipient]');
    const recipientLabel = form.querySelector('[data-sms-recipient-label]');
    const phoneField = form.querySelector('[data-sms-phone-field]');
    const phoneInput = form.querySelector('[data-sms-phone]');
    const bulkNote = form.querySelector('[data-sms-bulk-note]');
    const submitButton = form.querySelector('[data-sms-submit]');
    const participantCount = parseInt(form.dataset.smsParticipantCount || '0', 10);
    const coachCount = parseInt(form.dataset.smsCoachCount || '0', 10);

    const labels = { athlete: 'Athlete', coach: 'Coach', team: 'Team' };
    const notes = {
        participants: `Will send to all ${participantCount} participant(s) that have a contact number.`,
        coaches: `Will send to all ${coachCount} coach(es) that have a contact number.`,
    };

    const fillPhone = (select) => {
        const option = select.options[select.selectedIndex];
        phoneInput.value = option?.dataset?.phone ?? '';
    };

    const syncRecipients = () => {
        const type = typeSelect.value;
        const isIndividual = ['athlete', 'coach', 'team'].includes(type);
        const isBulk = ['participants', 'coaches'].includes(type);

        recipientField.classList.toggle('hidden', !isIndividual);
        phoneField.classList.toggle('hidden', !isIndividual);
        bulkNote.classList.toggle('hidden', !isBulk);

        recipientSelects.forEach((select) => {
            const isActive = select.dataset.smsRecipientType === type;
            select.classList.toggle('hidden', !isActive);
            select.disabled = !isActive;
            if (isActive && !select.value) {
                select.value = select.querySelector('option')?.value ?? '';
                fillPhone(select);
            }
        });

        if (isIndividual) {
            recipientLabel.textContent = labels[type];
            phoneInput.required = true;
            phoneInput.placeholder = 'Auto-fills, editable';
            bulkNote.textContent = '';
            submitButton.textContent = 'Send SMS';
        } else {
            phoneInput.required = false;
            phoneInput.value = '';
            phoneInput.placeholder = '';
            bulkNote.textContent = notes[type] || '';
            submitButton.textContent = 'Send SMS to All';
        }
    };

    typeSelect.addEventListener('change', syncRecipients);
    recipientSelects.forEach((select) => select.addEventListener('change', () => fillPhone(select)));
    syncRecipients();
});
</script>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
