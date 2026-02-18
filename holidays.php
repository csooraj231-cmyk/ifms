<?php
/**
 * IFMS - View Holidays
 * All employees can view company holidays
 */
session_start();
require_once __DIR__ . '/config/auth.php';
requireLogin();

$db = getDB();
$pageTitle = 'Company Holidays';

// Fetch all upcoming and past holidays
$holidays = $db->query("
    SELECT * FROM holidays 
    ORDER BY date DESC
")->fetchAll();

// Group by year for better organization
$holidaysByYear = [];
foreach ($holidays as $h) {
    $year = date('Y', strtotime($h['date']));
    if (!isset($holidaysByYear[$year])) {
        $holidaysByYear[$year] = [];
    }
    $holidaysByYear[$year][] = $h;
}
krsort($holidaysByYear);

include __DIR__ . '/includes/header.php';
?>

<div class="mb-8">
    <h2 class="text-2xl font-black text-gray-900 tracking-tight">Company Holidays</h2>
    <p class="text-sm text-gray-500 font-medium mt-2">View all company holidays and special days off.</p>
</div>

<?php foreach ($holidaysByYear as $year => $year_holidays): ?>
<div class="mb-12">
    <h3 class="text-xl font-bold text-gray-900 mb-4"><?= $year ?></h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($year_holidays as $holiday): ?>
        <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h4 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($holiday['title']) ?></h4>
                    <p class="text-sm text-gray-500 mt-1"><?= date('l, F d', strtotime($holiday['date'])) ?></p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-tighter <?= $holiday['type'] === 'national' ? 'bg-blue-50 text-blue-600' : 'bg-emerald-50 text-emerald-600' ?>">
                    <?= ucfirst($holiday['type']) ?>
                </span>
            </div>
            <div class="flex items-center gap-2 text-gray-400 text-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <?php 
                $daysUntil = round((strtotime($holiday['date']) - time()) / (24 * 3600));
                if ($daysUntil > 0) {
                    echo $daysUntil . ' day' . ($daysUntil > 1 ? 's' : '') . ' away';
                } elseif ($daysUntil == 0) {
                    echo 'Today';
                } else {
                    echo abs($daysUntil) . ' day' . (abs($daysUntil) > 1 ? 's' : '') . ' ago';
                }
                ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>

<?php if (count($holidays) === 0): ?>
<div class="bg-white rounded-3xl border border-gray-100 p-12 text-center shadow-sm">
    <p class="text-gray-500">No holidays scheduled yet.</p>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
