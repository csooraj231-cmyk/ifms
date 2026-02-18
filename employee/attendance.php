<?php
/**
 * IFMS - Employee: My Attendance
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('employee');

$db = getDB();
$user = getCurrentUser();
$empId = $user['employee_id'];
$pageTitle = 'My Attendance';

$month = $_GET['month'] ?? date('n');
$year = $_GET['year'] ?? date('Y');

$attendance = $db->query("
    SELECT * FROM attendance 
    WHERE employee_id = {$empId} AND MONTH(date) = {$month} AND YEAR(date) = {$year}
    ORDER BY date DESC
")->fetchAll();

$stats = $db->query("
    SELECT 
        COUNT(CASE WHEN status = 'present' THEN 1 END) as present,
        COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent,
        COUNT(CASE WHEN status = 'late' THEN 1 END) as late,
        SUM(work_hours) as total_hours
    FROM attendance 
    WHERE employee_id = {$empId} AND MONTH(date) = {$month} AND YEAR(date) = {$year}
")->fetch();

include __DIR__ . '/../includes/header.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h2 class="text-2xl font-black text-gray-900 tracking-tight">Attendance Record</h2>
        <p class="text-sm text-gray-500 font-medium">Monthly oversight of your work hours and status.</p>
    </div>
    <select onchange="location.href='?year=<?= $year ?>&month='+this.value" class="bg-white border border-gray-100 rounded-xl px-4 py-2 text-sm font-bold shadow-sm">
        <?php for($i=1;$i<=12;$i++): ?>
            <option value="<?= $i ?>" <?= $i == $month ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$i,1)) ?></option>
        <?php endfor; ?>
    </select>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-emerald-500 p-6 rounded-2xl text-white">
        <p class="text-[10px] font-black uppercase opacity-80">Days Present</p>
        <p class="text-3xl font-black mt-2"><?= $stats['present'] + $stats['late'] ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm text-center">
        <p class="text-[10px] font-black text-gray-400 uppercase">Avg. Hours</p>
        <p class="text-3xl font-black text-gray-900 mt-2"><?= number_format(($stats['total_hours'] ?? 0) / (max(1, $stats['present'] + $stats['late'])), 1) ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm text-center text-red-500">
        <p class="text-[10px] font-black text-gray-400 uppercase">Absents</p>
        <p class="text-3xl font-black mt-2"><?= $stats['absent'] ?></p>
    </div>
    <div class="bg-indigo-600 p-6 rounded-2xl text-white text-center">
        <p class="text-[10px] font-black uppercase opacity-80">Total Hours</p>
        <p class="text-3xl font-black mt-2"><?= number_format($stats['total_hours'] ?? 0, 1) ?></p>
    </div>
</div>

<div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="text-left text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/50">
                <th class="px-6 py-4">Date</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4">Check In</th>
                <th class="px-6 py-4">Check Out</th>
                <th class="px-6 py-4 text-right">Hours</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            <?php foreach ($attendance as $a): ?>
            <tr class="hover:bg-gray-50/50 transition-all">
                <td class="px-6 py-4 text-sm font-bold text-gray-900"><?= date('d M, Y', strtotime($a['date'])) ?></td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-black uppercase <?php echo match($a['status']) { 'present' => 'bg-emerald-100 text-emerald-700', 'late' => 'bg-amber-100 text-amber-700', 'absent' => 'bg-red-100 text-red-700', default => 'bg-gray-100 text-gray-600' }; ?>">
                        <?= $a['status'] ?>
                    </span>
                </td>
                <td class="px-6 py-4 text-xs font-mono font-bold"><?= $a['check_in'] ?? '--:--' ?></td>
                <td class="px-6 py-4 text-xs font-mono font-bold"><?= $a['check_out'] ?? '--:--' ?></td>
                <td class="px-6 py-4 text-right text-sm font-black text-gray-900"><?= number_format($a['work_hours'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>