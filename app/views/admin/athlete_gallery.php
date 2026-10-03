<?php
require_once __DIR__ . '/../../controllers/AthleteController.php';
require_once __DIR__ . '/../../controllers/SportController.php';
require_once __DIR__ . '/../../controllers/TeamController.php';
require_role(['admin', 'sports_coordinator', 'coach']);

$pageTitle = 'SCUAA Form 2 - Official Entry Form and Gallery';
$currentRole = current_user()['role'] ?? '';
$currentUserId = (int)(current_user()['id'] ?? 0);

$sportController = new SportController($pdo);
$teamController = new TeamController($pdo);
$sports = $sportController->all();
$allTeams = $teamController->all();

// Filter teams for coach if coach role
if ($currentRole === 'coach') {
    $teams = array_values(array_filter($allTeams, fn($t) => (int)($t['coach_id'] ?? 0) === $currentUserId));
} else {
    $teams = $allTeams;
}

// User-configurable parameters
$selectedSportId = isset($_GET['sport_id']) && $_GET['sport_id'] !== '' ? (int)$_GET['sport_id'] : 0;
$selectedTeamId = isset($_GET['team_id']) && $_GET['team_id'] !== '' ? (int)$_GET['team_id'] : 0;

// Tournament metadata (defaulting to the exact values in official SCUAA Form 2 sample)
$tournamentName = trim((string)($_GET['tournament_name'] ?? 'UNIVERSITY GAMES 2026'));
$venueDate = trim((string)($_GET['venue_date'] ?? '2-6 February 2026; Isabela State University - Cauayan Campus'));
$campusName = trim((string)($_GET['campus_name'] ?? 'Cauayan City'));
$schoolCampus = trim((string)($_GET['school_campus'] ?? 'ISU - CAUAYAN'));
$category = trim((string)($_GET['category'] ?? ''));

// Detect or fallback category and sport/event name
$selectedTeam = null;
if ($selectedTeamId > 0) {
    foreach ($allTeams as $t) {
        if ((int)$t['id'] === $selectedTeamId) {
            $selectedTeam = $t;
            if ($selectedSportId === 0 && !empty($t['sport_id'])) {
                $selectedSportId = (int)$t['sport_id'];
            }
            break;
        }
    }
}

$selectedSport = null;
if ($selectedSportId > 0) {
    foreach ($sports as $s) {
        if ((int)$s['id'] === $selectedSportId) {
            $selectedSport = $s;
            break;
        }
    }
}

// Auto-detect event name
$eventName = trim((string)($_GET['event_name'] ?? ''));
if ($eventName === '') {
    if ($selectedSport) {
        $eventName = $selectedSport['name'];
    } elseif ($selectedTeam) {
        $eventName = $selectedTeam['sport_name'] ?? $selectedTeam['name'];
    } else {
        $eventName = 'Softball'; // Default matching user's image
    }
}

// Auto-detect category (Women / Men / Mixed)
if ($category === '') {
    $sourceStr = ($selectedTeam['name'] ?? '') . ' ' . ($selectedSport['name'] ?? '');
    if (stripos($sourceStr, 'women') !== false || stripos($sourceStr, 'girls') !== false || stripos($sourceStr, 'female') !== false) {
        $category = 'Women';
    } elseif (stripos($sourceStr, 'men') !== false || stripos($sourceStr, 'boys') !== false || stripos($sourceStr, 'male') !== false) {
        $category = 'Men';
    } else {
        $category = 'Women'; // Default matching user's image
    }
}

// Query athletes
if ($selectedTeamId > 0) {
    $stmt = $pdo->prepare("
        SELECT a.*, t.name as team_name, s.name as sport_name
        FROM team_members tm
        JOIN athletes a ON a.id = tm.athlete_id
        JOIN teams t ON t.id = tm.team_id
        LEFT JOIN sports s ON s.id = t.sport_id
        WHERE tm.team_id = ?
        ORDER BY a.last_name ASC, a.first_name ASC
    ");
    $stmt->execute([$selectedTeamId]);
    $rawAthletes = $stmt->fetchAll();
} elseif ($selectedSportId > 0) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT a.*, s.name as sport_name
        FROM athletes a
        LEFT JOIN sports s ON s.id = a.sport_id
        LEFT JOIN team_members tm ON tm.athlete_id = a.id
        LEFT JOIN teams t ON t.id = tm.team_id
        WHERE (a.sport_id = ? OR t.sport_id = ?)
        ORDER BY a.last_name ASC, a.first_name ASC
    ");
    $stmt->execute([$selectedSportId, $selectedSportId]);
    $rawAthletes = $stmt->fetchAll();
} else {
    // If coach, default to coach's first team if exists, else all
    if ($currentRole === 'coach' && !empty($teams)) {
        $firstTeamId = (int)$teams[0]['id'];
        $selectedTeamId = $firstTeamId;
        $stmt = $pdo->prepare("
            SELECT a.*, t.name as team_name, s.name as sport_name
            FROM team_members tm
            JOIN athletes a ON a.id = tm.athlete_id
            JOIN teams t ON t.id = tm.team_id
            LEFT JOIN sports s ON s.id = t.sport_id
            WHERE tm.team_id = ?
            ORDER BY a.last_name ASC, a.first_name ASC
        ");
        $stmt->execute([$firstTeamId]);
        $rawAthletes = $stmt->fetchAll();
    } else {
        $stmt = $pdo->query("
            SELECT a.*, s.name as sport_name
            FROM athletes a
            LEFT JOIN sports s ON s.id = a.sport_id
            WHERE a.athlete_status = 'Active'
            ORDER BY a.last_name ASC, a.first_name ASC
            LIMIT 28
        ");
        $rawAthletes = $stmt->fetchAll();
    }
}

// Resolve profile photos from athlete_documents if not set in profile_photo
$docStmt = $pdo->prepare("
    SELECT ad.file_path, rt.title
    FROM athlete_documents ad
    LEFT JOIN requirement_types rt ON rt.id = ad.requirement_type_id
    WHERE ad.athlete_id = ? AND ad.file_path IS NOT NULL AND ad.file_path != ''
    ORDER BY ad.id DESC
");

$formattedAthletes = [];
foreach ($rawAthletes as $ath) {
    $photoUrl = '';
    if (!empty($ath['profile_photo'])) {
        $photoUrl = app_url($ath['profile_photo']);
    } else {
        $docStmt->execute([(int)$ath['id']]);
        $docs = $docStmt->fetchAll();
        foreach ($docs as $d) {
            $t = strtolower((string)$d['title']);
            $f = (string)$d['file_path'];
            if (preg_match('/\.(jpe?g|png|webp)$/i', $f) && (str_contains($t, '2x2') || str_contains($t, 'photo') || str_contains($t, 'picture') || str_contains($t, 'id') || str_contains($t, 'profile'))) {
                $photoUrl = app_url($f);
                break;
            }
        }
    }

    // Name format: SURNAME, FIRSTNAME M.I.
    $lName = strtoupper(trim((string)($ath['last_name'] ?? '')));
    $fName = strtoupper(trim((string)($ath['first_name'] ?? '')));
    $mName = trim((string)($ath['middle_name'] ?? ''));
    $mi = $mName !== '' ? ' ' . strtoupper(substr($mName, 0, 1)) . '.' : '';
    $fullName = trim($lName . ', ' . $fName . $mi);

    // DOB format: MARCH 29, 2006
    $dob = '';
    if (!empty($ath['birthdate'])) {
        $ts = strtotime($ath['birthdate']);
        if ($ts) {
            $dob = strtoupper(date('F d, Y', $ts));
        }
    }

    // Course & Year: BSEMC 2
    $course = strtoupper(trim((string)($ath['course'] ?? '')));
    $year = strtoupper(trim((string)($ath['year_level'] ?? '')));
    $courseYear = trim($course . ' ' . $year);

    $formattedAthletes[] = [
        'id' => $ath['id'],
        'name' => $fullName,
        'dob' => $dob,
        'course_year' => $courseYear,
        'school_campus' => $schoolCampus,
        'photo_url' => $photoUrl,
    ];
}

// 14 athletes per page (2 rows of 7)
$pages = [];
$totalAthleteCount = count($formattedAthletes);
if ($totalAthleteCount === 0) {
    // 1 empty sheet with 14 blank athlete cells
    $pages[] = [
        'row1' => array_fill(0, 7, null),
        'row2' => array_fill(0, 7, null),
    ];
} else {
    $chunks = array_chunk($formattedAthletes, 14);
    foreach ($chunks as $chunk) {
        // Pad chunk to 14 slots
        while (count($chunk) < 14) {
            $chunk[] = null;
        }
        $pages[] = [
            'row1' => array_slice($chunk, 0, 7),
            'row2' => array_slice($chunk, 7, 7),
        ];
    }
}
$totalPages = count($pages);

require __DIR__ . '/../../includes/header.php';
?>

<style>
/* Reset and Container Styling for Form 2 */
.scuaa-table {
    width: 100%;
    border-collapse: collapse;
    border: 1.5px solid #000;
    table-layout: fixed;
}
.scuaa-table th,
.scuaa-table td {
    border: 1px solid #000;
    padding: 0;
    vertical-align: middle;
}
.scuaa-col-label {
    width: 13.5%;
}
.scuaa-col-athlete {
    width: 12.35%;
}
.scuaa-label-cell {
    font-weight: 700;
    font-size: 11px;
    padding: 3px 6px !important;
    background-color: #ffffff;
    color: #000000;
    white-space: nowrap;
}
.scuaa-val-cell {
    font-size: 10px;
    padding: 3px 2px !important;
    text-align: center;
    color: #000000;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.15;
}
.scuaa-photo-frame {
    width: 96px;
    height: 126px;
    margin: 4px auto;
    border: 1px solid #94a3b8;
    background-color: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.scuaa-photo-frame img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.scuaa-screen-wrapper {
    border-radius: 1.5rem;
    background: linear-gradient(135deg, rgba(15, 23, 42, 0.04), rgba(148, 163, 184, 0.18));
    padding: 1.25rem;
}
.scuaa-sheet {
    overflow: hidden;
}
html.dark .scuaa-screen-wrapper {
    background: linear-gradient(135deg, rgba(2, 6, 23, 0.68), rgba(30, 41, 59, 0.48));
    border: 1px solid #1e293b;
}


html.dark .scuaa-sheet {
    background: #111827 !important;
    color: #f8fafc !important;
    border-color: #334155 !important;
}
html.dark .scuaa-sheet header,
html.dark .scuaa-sheet footer {
    background: #111827 !important;
    color: #f8fafc !important;
    border-color: #475569 !important;
}
html.dark .scuaa-sheet table,
html.dark .scuaa-sheet tr,
html.dark .scuaa-sheet th,
html.dark .scuaa-sheet td {
    background: #0f172a !important;
    color: #f8fafc !important;
    border-color: #475569 !important;
}
html.dark .scuaa-sheet .scuaa-label-cell,
html.dark .scuaa-sheet .scuaa-val-cell {
    background: #0f172a !important;
    color: #f8fafc !important;
}
html.dark .scuaa-sheet .scuaa-photo-frame {
    background: #1e293b !important;
    border-color: #64748b !important;
}
html.dark .scuaa-sheet .bg-white {
    background: #1e293b !important;
}
html.dark .scuaa-sheet .text-black,
html.dark .scuaa-sheet .text-slate-700,
html.dark .scuaa-sheet .text-slate-800,
html.dark .scuaa-sheet .text-slate-900 {
    color: #f8fafc !important;
}

/* Print Specific Styles */
@media print {
    @page {
        size: landscape;
        margin: 6mm 6mm;
    }
    html, body {
        background: #ffffff !important;
        color: #000000 !important;
        margin: 0 !important;
        padding: 0 !important;
        font-family: Arial, "Helvetica Neue", Helvetica, sans-serif !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .no-print, header:not(.scuaa-sheet-header), aside, nav, footer, .banner-ticker {
        display: none !important;
    }
    .min-h-screen.lg\:pl-72 {
        padding-left: 0 !important;
    }
    main {
        padding: 0 !important;
        margin: 0 !important;
    }
    .scuaa-screen-wrapper {
        padding: 0 !important;
        margin: 0 !important;
        background: transparent !important;
    }
    .scuaa-sheet {
        page-break-after: always !important;
        page-break-inside: avoid !important;
        break-after: page !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 4mm !important;
        border: none !important;
        box-shadow: none !important;
        background: #ffffff !important;
        color: #000000 !important;
    }
    .scuaa-sheet:last-child {
        page-break-after: avoid !important;
        break-after: avoid !important;
    }
    .scuaa-sheet-header {
        display: flex !important;
        align-items: flex-start !important;
        justify-content: space-between !important;
        break-inside: avoid !important;
        page-break-inside: avoid !important;
        margin-bottom: 2.5mm !important;
        padding-bottom: 2mm !important;
        border-bottom: 2px solid #000000 !important;
        background: #ffffff !important;
        color: #000000 !important;
    }
    .scuaa-sheet-header img,
    .scuaa-sheet-header svg {
        display: block !important;
        visibility: visible !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .scuaa-sheet > footer {
        display: flex !important;
        align-items: flex-end !important;
        justify-content: space-between !important;
        break-inside: avoid !important;
        page-break-inside: avoid !important;
        background: #ffffff !important;
        color: #000000 !important;
    }
    .scuaa-photo-frame {
        border-color: #cbd5e1 !important;
        background-color: #f1f5f9 !important;
    }
    html.dark .scuaa-screen-wrapper {
        background: transparent !important;
        border: none !important;
    }
    html.dark .scuaa-sheet,
    html.dark .scuaa-sheet header,
    html.dark .scuaa-sheet footer,
    html.dark .scuaa-sheet table,
    html.dark .scuaa-sheet tr,
    html.dark .scuaa-sheet th,
    html.dark .scuaa-sheet td,
    html.dark .scuaa-sheet .scuaa-label-cell,
    html.dark .scuaa-sheet .scuaa-val-cell,
    html.dark .scuaa-sheet .bg-white {
        background: #ffffff !important;
        color: #000000 !important;
        border-color: #000000 !important;
    }
    html.dark .scuaa-sheet .text-black,
    html.dark .scuaa-sheet .text-slate-700,
    html.dark .scuaa-sheet .text-slate-800,
    html.dark .scuaa-sheet .text-slate-900 {
        color: #000000 !important;
    }
    html.dark .scuaa-sheet .scuaa-photo-frame {
        background: #f1f5f9 !important;
        border-color: #cbd5e1 !important;
    }
}
</style>

<div class="min-h-screen lg:pl-72">
    <?php require __DIR__ . '/../../includes/sidebar.php'; require __DIR__ . '/../../includes/navbar.php'; ?>
    <main class="p-4 lg:p-6">

        <!-- No-Print Control Toolbar -->
        <section class="no-print mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-black uppercase tracking-wider text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                            SCUAA Form 2 Standard
                        </span>
                        <span class="text-xs text-slate-400">Official Tournament Entry Gallery</span>
                    </div>
                    <h2 class="mt-1.5 text-2xl font-black text-slate-950 dark:text-white">
                        Official Entry Form &amp; Gallery of Athletes
                    </h2>
                    <p class="text-xs text-slate-500">
                        Exact 7-column SCUAA Form 2 gallery table with dual university &amp; games logo, category header, and landscape print formatting.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <button type="button" onclick="document.getElementById('customize-panel').classList.toggle('hidden')" class="rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        ⚙️ Customize Form Fields
                    </button>
                    <a href="<?= e(app_url('index.php?page=teams')) ?>" class="rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        ← Back to Teams
                    </a>
                    <button type="button" onclick="window.print()" class="smis-cmd-btn primary shadow-md">
                        <span>🖨️</span>
                        <span>Print SCUAA Form 2 (Landscape)</span>
                    </button>
                </div>
            </div>

            <!-- Filtering Bar -->
            <form method="get" action="<?= e(app_url('index.php')) ?>" class="mt-4 grid gap-3 border-t border-slate-100 pt-4 sm:grid-cols-2 lg:grid-cols-4 dark:border-slate-800">
                <input type="hidden" name="page" value="athlete_gallery">
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Sport Filter</label>
                    <select name="sport_id" class="form-input text-xs" onchange="this.form.submit()">
                        <option value="">All Sports</option>
                        <?php foreach ($sports as $s): ?>
                            <option value="<?= e((string)$s['id']) ?>" <?= $selectedSportId === (int)$s['id'] ? 'selected' : '' ?>>
                                <?= e($s['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Team Roster</label>
                    <select name="team_id" class="form-input text-xs" onchange="this.form.submit()">
                        <option value="">Select Team (All Athletes)</option>
                        <?php foreach ($teams as $t): ?>
                            <option value="<?= e((string)$t['id']) ?>" <?= $selectedTeamId === (int)$t['id'] ? 'selected' : '' ?>>
                                <?= e($t['name']) ?> (<?= e($t['sport_name'] ?? 'Sport') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Category</label>
                    <select name="category" class="form-input text-xs" onchange="this.form.submit()">
                        <option value="Women" <?= $category === 'Women' ? 'selected' : '' ?>>Women</option>
                        <option value="Men" <?= $category === 'Men' ? 'selected' : '' ?>>Men</option>
                        <option value="Mixed" <?= $category === 'Mixed' ? 'selected' : '' ?>>Mixed</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="btn-primary w-full text-xs">
                        Filter Roster
                    </button>
                    <a href="<?= e(app_url('index.php?page=athlete_gallery')) ?>" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-800 dark:text-slate-400">
                        Reset
                    </a>
                </div>
            </form>

            <!-- Collapsible Customize Form Fields -->
            <div id="customize-panel" class="hidden mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40">
                <form method="get" action="<?= e(app_url('index.php')) ?>" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <input type="hidden" name="page" value="athlete_gallery">
                    <input type="hidden" name="sport_id" value="<?= e((string)$selectedSportId) ?>">
                    <input type="hidden" name="team_id" value="<?= e((string)$selectedTeamId) ?>">
                    <input type="hidden" name="category" value="<?= e($category) ?>">

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Event / Sport Name in Box</label>
                        <input type="text" name="event_name" value="<?= e($eventName) ?>" class="form-input text-xs" placeholder="e.g. Softball, Basketball">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Tournament Name</label>
                        <input type="text" name="tournament_name" value="<?= e($tournamentName) ?>" class="form-input text-xs" placeholder="e.g. UNIVERSITY GAMES 2026">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Dates &amp; Venue</label>
                        <input type="text" name="venue_date" value="<?= e($venueDate) ?>" class="form-input text-xs" placeholder="e.g. 2-6 February 2026; ISU - Cauayan Campus">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Host Campus Line</label>
                        <input type="text" name="campus_name" value="<?= e($campusName) ?>" class="form-input text-xs" placeholder="e.g. Cauayan City">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">School/Campus Cell Label</label>
                        <input type="text" name="school_campus" value="<?= e($schoolCampus) ?>" class="form-input text-xs" placeholder="e.g. ISU - CAUAYAN">
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="btn-primary w-full text-xs">
                            Apply Custom Details
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Printable Document Sheets Canvas -->
        <div class="scuaa-screen-wrapper">
            <?php foreach ($pages as $pageIdx => $pageData): ?>
                <?php 
                $row1 = $pageData['row1'];
                $row2 = $pageData['row2'];
                ?>
                <article class="scuaa-sheet mx-auto mb-8 max-w-[1150px] rounded-lg border border-slate-300 bg-white p-6 shadow-md transition dark:border-slate-800">
                    
                    <!-- Official SCUAA Form 2 Header Block -->
                    <header class="scuaa-sheet-header mb-2.5 flex items-start justify-between border-b-2 border-black pb-2 text-black">
                        <!-- Left Header Column -->
                        <div class="w-[36%] pr-2">
                            <h1 class="text-[17px] font-black tracking-tight leading-tight uppercase text-black font-sans">
                                <?= e($tournamentName) ?>
                            </h1>
                            <p class="text-[11px] leading-snug mt-0.5 text-slate-900 font-sans">
                                <?= e($venueDate) ?>
                            </p>
                            <p class="text-[11px] font-bold mt-1 text-black font-sans">
                                Campus: <span class="font-normal"><?= e($campusName) ?></span>
                            </p>
                        </div>

                        <!-- Center Column: Dual Logos (University Logo + Official SCUAA Emblem) -->
                        <div class="w-[28%] flex items-center justify-center gap-4">
                            <?php if (app_icon_url()): ?>
                                <img src="<?= e(app_icon_url()) ?>" alt="University Seal" class="h-16 w-16 object-contain" style="max-height: 64px;">
                            <?php else: ?>
                                <div class="h-16 w-16 rounded-full border-2 border-emerald-800 grid place-items-center text-center p-1 text-[9px] font-black text-emerald-900 leading-none">
                                    ISU SEAL
                                </div>
                            <?php endif; ?>

                            <!-- Official SCUAA National Games Emblem -->
                            <img src="<?= e(app_url('assets/images/scuaa-national-games-emblem.png')) ?>" alt="SCUAA National Games Emblem" class="h-16 w-16 object-contain" style="max-height: 64px;">
                        </div>

                        <!-- Right Header Column: SCUAA Form 2, Event Box & Category -->
                        <div class="w-[36%] pl-2 text-right">
                            <div class="text-[13px] font-black tracking-wider text-black font-sans uppercase">
                                SCUAA Form 2
                            </div>
                            <div class="text-[10.5px] font-bold uppercase tracking-tight mt-0.5 text-black font-sans">
                                OFFICIAL ENTRY FORM AND GALLERY OF
                            </div>
                            
                            <!-- Event Title & Pagination Box -->
                            <div class="my-1 flex items-center justify-between border-2 border-black bg-white px-2 py-0.5 text-[11px] font-bold text-black font-sans">
                                <span><?= e($eventName) ?> (Event)</span>
                                <span>page <?= $pageIdx + 1 ?> of <?= $totalPages ?></span>
                            </div>

                            <p class="text-[8.5px] italic text-slate-800 leading-tight">
                                Note: Submission of documents must be at least one (1) week before the opening of the games.
                            </p>
                            <div class="mt-0.5 text-[11px] font-bold text-black font-sans">
                                Category: <span class="font-bold underline"><?= e($category) ?></span>
                            </div>
                        </div>
                    </header>

                    <!-- Official SCUAA Form 2 Table (Exactly 7 Athlete Columns per row) -->
                    <table class="scuaa-table" data-enhance-table="false">
                        <colgroup>
                            <col class="scuaa-col-label">
                            <col class="scuaa-col-athlete">
                            <col class="scuaa-col-athlete">
                            <col class="scuaa-col-athlete">
                            <col class="scuaa-col-athlete">
                            <col class="scuaa-col-athlete">
                            <col class="scuaa-col-athlete">
                            <col class="scuaa-col-athlete">
                        </colgroup>
                        <tbody>
                            <!-- ================= FIRST 7-ATHLETE BLOCK ================= -->
                            <!-- Header Row: ATHLETE -->
                            <tr>
                                <th class="scuaa-label-cell"></th>
                                <?php for ($i = 0; $i < 7; $i++): ?>
                                    <th class="border border-black bg-white py-1 px-1 text-center font-bold text-[11px] text-black uppercase tracking-wider font-sans">
                                        ATHLETE
                                    </th>
                                <?php endfor; ?>
                            </tr>

                            <!-- Photo Box Row -->
                            <tr>
                                <td class="scuaa-label-cell"></td>
                                <?php foreach ($row1 as $ath): ?>
                                    <td class="text-center align-middle p-1 h-[134px]">
                                        <div class="scuaa-photo-frame">
                                            <?php if (!empty($ath['photo_url'])): ?>
                                                <img src="<?= e($ath['photo_url']) ?>" alt="<?= e($ath['name'] ?? 'Athlete') ?>">
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                <?php endforeach; ?>
                            </tr>

                            <!-- Name Row -->
                            <tr>
                                <td class="scuaa-label-cell">Name:</td>
                                <?php foreach ($row1 as $ath): ?>
                                    <td class="scuaa-val-cell font-bold uppercase">
                                        <?= e($ath['name'] ?? '') ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>

                            <!-- Date of Birth Row -->
                            <tr>
                                <td class="scuaa-label-cell">Date of Birth:</td>
                                <?php foreach ($row1 as $ath): ?>
                                    <td class="scuaa-val-cell uppercase">
                                        <?= e($ath['dob'] ?? '') ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>

                            <!-- Course & Year Row -->
                            <tr>
                                <td class="scuaa-label-cell">Course &amp; Year:</td>
                                <?php foreach ($row1 as $ath): ?>
                                    <td class="scuaa-val-cell uppercase">
                                        <?= e($ath['course_year'] ?? '') ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>

                            <!-- School / Campus Row -->
                            <tr>
                                <td class="scuaa-label-cell">School/Campus:</td>
                                <?php foreach ($row1 as $ath): ?>
                                    <td class="scuaa-val-cell uppercase">
                                        <?= e($schoolCampus) ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>

                            <!-- ================= SECOND 7-ATHLETE BLOCK ================= -->
                            <!-- Header Row: ATHLETE -->
                            <tr>
                                <th class="scuaa-label-cell"></th>
                                <?php for ($i = 0; $i < 7; $i++): ?>
                                    <th class="border border-black bg-white py-1 px-1 text-center font-bold text-[11px] text-black uppercase tracking-wider font-sans">
                                        ATHLETE
                                    </th>
                                <?php endfor; ?>
                            </tr>

                            <!-- Photo Box Row -->
                            <tr>
                                <td class="scuaa-label-cell"></td>
                                <?php foreach ($row2 as $ath): ?>
                                    <td class="text-center align-middle p-1 h-[134px]">
                                        <div class="scuaa-photo-frame">
                                            <?php if (!empty($ath['photo_url'])): ?>
                                                <img src="<?= e($ath['photo_url']) ?>" alt="<?= e($ath['name'] ?? 'Athlete') ?>">
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                <?php endforeach; ?>
                            </tr>

                            <!-- Name Row -->
                            <tr>
                                <td class="scuaa-label-cell">Name:</td>
                                <?php foreach ($row2 as $ath): ?>
                                    <td class="scuaa-val-cell font-bold uppercase">
                                        <?= e($ath['name'] ?? '') ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>

                            <!-- Date of Birth Row -->
                            <tr>
                                <td class="scuaa-label-cell">Date of Birth:</td>
                                <?php foreach ($row2 as $ath): ?>
                                    <td class="scuaa-val-cell uppercase">
                                        <?= e($ath['dob'] ?? '') ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>

                            <!-- Course & Year Row -->
                            <tr>
                                <td class="scuaa-label-cell">Course &amp; Year:</td>
                                <?php foreach ($row2 as $ath): ?>
                                    <td class="scuaa-val-cell uppercase">
                                        <?= e($ath['course_year'] ?? '') ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>

                            <!-- School / Campus Row -->
                            <tr>
                                <td class="scuaa-label-cell">School/Campus:</td>
                                <?php foreach ($row2 as $ath): ?>
                                    <td class="scuaa-val-cell uppercase">
                                        <?= e($schoolCampus) ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Optional Footer Verification / Signature Section -->
                    <footer class="mt-4 pt-2 border-t border-slate-300 text-[10px] text-black flex justify-between items-end">
                        <div class="text-left">
                            <p class="font-bold">Prepared by:</p>
                            <div class="mt-6 border-b border-black w-48 text-center font-bold">
                                <?= e(current_user()['name'] ?? 'Coach / Team In-Charge') ?>
                            </div>
                            <p class="text-[9px] text-slate-700">Coach / Sports Coordinator</p>
                        </div>
                        <div class="text-center">
                            <p class="font-bold">Screened &amp; Verified by:</p>
                            <div class="mt-6 border-b border-black w-48 text-center font-bold">
                                Screening Committee
                            </div>
                            <p class="text-[9px] text-slate-700">Official Tournament Secretariat</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold">Noted by:</p>
                            <div class="mt-6 border-b border-black w-48 text-center font-bold">
                                Campus Executive Officer
                            </div>
                            <p class="text-[9px] text-slate-700">Campus Sports Director</p>
                        </div>
                    </footer>
                </article>
            <?php endforeach; ?>
        </div>

    </main>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
