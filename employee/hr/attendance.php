<?php
/**
 * IFMS - HR Attendance Management
 * HR employees can manage and record attendance for all employees
 */
session_start();
require_once __DIR__ . '/../../config/auth.php';
requireLogin();
requireHRAccess();

$db = getDB();
$user = getCurrentUser();

// Today's date and holiday/weekend check for quick daily banner
$todayDate = date('Y-m-d');
$todayWeekday = (int)date('w', strtotime($todayDate));
$todayHolidayStmt = $db->prepare("SELECT id, title FROM holidays WHERE date = ? LIMIT 1");
$todayHolidayStmt->execute([$todayDate]);
$todayHolidayRow = $todayHolidayStmt->fetch();
$isHolidayToday = $todayHolidayRow ? true : false;
$holidayNameToday = $todayHolidayRow['title'] ?? '';
$isWeekendToday = ($todayWeekday === 0 || $todayWeekday === 6);

// Get filters
$filterMonth = $_GET['month'] ?? date('m');
$filterYear = $_GET['year'] ?? date('Y');
$filterDept = $_GET['dept'] ?? '';
$filterStatus = $_GET['status'] ?? '';

// Get attendance records
$query = "
    SELECT 
        a.*, 
        e.id as emp_id,
        e.employee_code,
        COALESCE(u.full_name, e.employee_code) as full_name, 
        COALESCE(d.name, '') as dept_name,
        COUNT(CASE WHEN a.status = 'present' THEN 1 END) as days_present,
        COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as days_absent,
        COUNT(CASE WHEN a.status = 'half_day' THEN 1 END) as days_half,
        COUNT(CASE WHEN a.status = 'late' THEN 1 END) as days_late
    FROM attendance a
    JOIN employees e ON a.employee_id = e.id
    LEFT JOIN users u ON e.user_id = u.id
    LEFT JOIN departments d ON e.department_id = d.id
    WHERE MONTH(a.date) = $filterMonth AND YEAR(a.date) = $filterYear
";

if ($filterDept) $query .= " AND e.department_id = " . intval($filterDept);
if ($filterStatus) $query .= " AND a.status = '" . $db->quote($filterStatus) . "'";

$query .= " GROUP BY e.id ORDER BY u.full_name";

$attendance = $db->query($query)->fetchAll();

// Get departments for filter
$departments = $db->query("SELECT id, name FROM departments ORDER BY name")->fetchAll();

// Get all employees for marking attendance
$allEmployees = $db->query("
    SELECT e.id, e.employee_code, COALESCE(u.full_name, e.employee_code) as full_name, COALESCE(d.name, '') as dept_name
    FROM employees e
    LEFT JOIN users u ON e.user_id = u.id
    LEFT JOIN departments d ON e.department_id = d.id
    WHERE e.is_active = 1
    ORDER BY COALESCE(u.full_name, e.employee_code)
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management - IFMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/ifms/assets/js/app.js"></script>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
        
        <div class="flex-1 overflow-auto">
            <?php include __DIR__ . '/../../includes/header.php'; ?>
            
            <div class="p-6 max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Attendance Management</h1>
                        <p class="text-gray-600 mt-2">Record and manage employee attendance</p>
                        <?php if ($isHolidayToday || $isWeekendToday): ?>
                            <div class="mt-3 bg-cyan-50 border border-cyan-100 rounded-xl p-3 text-cyan-800 flex items-start gap-3">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3M16 7V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <div>
                                    <div class="font-bold">Holiday — No Deductions</div>
                                    <div class="text-sm">Today is a holiday<?= $isHolidayToday && $holidayNameToday ? ': ' . htmlspecialchars($holidayNameToday) : '' ?>. Attendance will be recorded as paid leave for this date.</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button onclick="openModal('mark-attendance-modal')" class="btn-primary px-6 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-all">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Record Attendance
                    </button>
                    <button id="hr-bulk-all" onclick="hrOpenBulkAll()" class="ml-3 px-4 py-2 bg-emerald-50 text-emerald-700 rounded-xl font-bold hover:bg-emerald-100 transition-all">Apply</button>
                    <button id="hr-clear-btn" onclick="hrOpenClearModal()" class="ml-3 px-4 py-2 bg-red-50 text-red-700 rounded-xl font-bold hover:bg-red-100 transition-all">Clear</button>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 mb-6">
                    <form method="GET" class="flex flex-wrap gap-4">
                        <div>
                            <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Month</label>
                            <select name="month" class="px-4 py-2 bg-gray-50 border-none rounded-lg text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>" <?= ($filterMonth == str_pad($m, 2, '0', STR_PAD_LEFT)) ? 'selected' : '' ?>>
                                    <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Year</label>
                            <select name="year" class="px-4 py-2 bg-gray-50 border-none rounded-lg text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                                <?php for ($y = date('Y') - 2; $y <= date('Y'); $y++): ?>
                                <option value="<?= $y ?>" <?= ($filterYear == $y) ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Department</label>
                            <select name="dept" class="px-4 py-2 bg-gray-50 border-none rounded-lg text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                                <option value="">All Departments</option>
                                <?php foreach ($departments as $d): ?>
                                <option value="<?= $d['id'] ?>" <?= ($filterDept == $d['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($d['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="btn-primary px-4 py-2 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 transition-all">Filter</button>
                            <a href="?month=<?= date('m') ?>&year=<?= date('Y') ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-bold hover:bg-gray-300 transition-all">Reset</a>
                        </div>
                    </form>
                </div>

                <!-- Statistics -->
                <div class="grid grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                        <p class="text-sm font-bold text-gray-500 mb-2">Total Present</p>
                        <p class="text-3xl font-bold text-green-600">
                            <?= count(array_filter($attendance, fn($a) => $a['days_present'] > 0)) ?>
                        </p>
                    </div>
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                        <p class="text-sm font-bold text-gray-500 mb-2">Total Absent</p>
                        <p class="text-3xl font-bold text-red-600">
                            <?= count(array_filter($attendance, fn($a) => $a['days_absent'] > 0)) ?>
                        </p>
                    </div>
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                        <p class="text-sm font-bold text-gray-500 mb-2">Half Days</p>
                        <p class="text-3xl font-bold text-yellow-600">
                            <?= count(array_filter($attendance, fn($a) => $a['days_half'] > 0)) ?>
                        </p>
                    </div>
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                        <p class="text-sm font-bold text-gray-500 mb-2">Late Arrivals</p>
                        <p class="text-3xl font-bold text-orange-600">
                            <?= count(array_filter($attendance, fn($a) => $a['days_late'] > 0)) ?>
                        </p>
                    </div>
                </div>

                <!-- Attendance Table -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                        <tr class="bg-gray-50 border-b border-gray-100">
                                            <th class="px-4 py-4 text-left"><input type="checkbox" id="select-all-emps" onclick="toggleSelectAll(this)"></th>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Employee</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Department</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-widest">Present</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-widest">Absent</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-widest">Half Day</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-widest">Late</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($attendance as $record): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-4">
                                        <input type="checkbox" class="emp-select" value="<?= $record['emp_id'] ?>">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="font-bold text-gray-900"><?= htmlspecialchars($record['full_name']) ?></p>
                                            <p class="text-sm text-gray-500"><?= $record['employee_code'] ?></p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($record['dept_name']) ?></td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-bold"><?= $record['days_present'] ?></span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-bold"><?= $record['days_absent'] ?></span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-bold"><?= $record['days_half'] ?></span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-sm font-bold"><?= $record['days_late'] ?></span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="#" onclick="openEmployeeAttendanceModal(<?= $record['emp_id'] ?>, '<?= htmlspecialchars($record['full_name']) ?>', '<?= $record['employee_code'] ?>')" class="text-indigo-600 hover:text-indigo-700 font-bold">Details</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Bulk Actions -->
                <div class="bg-white rounded-3xl p-4 shadow-sm border border-gray-100 mt-4">
                    <h3 class="text-sm font-bold text-gray-700 mb-2">Bulk Actions</h3>
                    <div class="flex flex-wrap gap-3 items-end">
                        <div>
                            <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Date</label>
                            <input type="date" id="bulk-date" value="<?= date('Y-m-d') ?>" class="px-4 py-2 bg-gray-50 border-none rounded-lg text-sm font-bold">
                        </div>
                        <div>
                            <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Status</label>
                            <select id="bulk-status" class="px-4 py-2 bg-gray-50 border-none rounded-lg text-sm font-bold">
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="half_day">Half Day</option>
                                <option value="late">Late Arrival</option>
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Notes (Optional)</label>
                            <input id="bulk-notes" class="w-full px-4 py-2 bg-gray-50 border-none rounded-lg text-sm font-bold" placeholder="Optional notes">
                        </div>
                        <div>
                            <button id="apply-bulk" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-bold" onclick="applyBulkAttendance()">Apply to selected</button>
                        </div>
                    </div>
                </div>
                        <!-- Clear Modal (HR) -->
                        <div id="clear-modal-hr" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
                            <div class="bg-white rounded-2xl p-6 w-full max-w-md">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-bold">Clear Attendance for Selected</h3>
                                    <button onclick="closeClearModalHR()" class="text-gray-400 hover:text-gray-600">✕</button>
                                </div>
                                <p class="text-sm text-gray-600 mb-4">This will delete attendance records for the selected employees.</p>
                                <div class="mb-4">
                                    <label class="text-xs font-black text-gray-400 block mb-2">Date</label>
                                    <input id="clear-modal-hr-date" type="date" class="w-full px-3 py-2 border rounded" value="<?= $todayDate ?>" />
                                </div>
                                <div class="mb-4">
                                    <label class="text-xs font-black text-gray-400 block mb-2">Notes (optional)</label>
                                    <textarea id="clear-modal-hr-notes" class="w-full px-3 py-2 border rounded" rows="3"></textarea>
                                </div>
                                <div class="flex justify-end gap-3">
                                    <button onclick="closeClearModalHR()" class="px-4 py-2 bg-gray-100 rounded">Cancel</button>
                                    <button id="clear-modal-hr-confirm" onclick="submitClearModalHR()" class="px-4 py-2 bg-red-600 text-white rounded">Clear</button>
                                </div>
                            </div>
                        </div>
            </div>
        </div>
    </div>

    <!-- Mark Attendance Modal -->
    <div id="mark-attendance-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="modal-content bg-white rounded-3xl max-w-md w-full shadow-2xl max-h-screen overflow-y-auto">
            <div class="sticky top-0 bg-white border-b border-gray-100 p-6 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900">Record Attendance</h2>
                <button onclick="closeModal('mark-attendance-modal')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <form id="attendance-form" class="p-6 space-y-4">
                <div>
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Employee</label>
                    <select name="employee_code" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                        <option value="">Select Employee</option>
                        <?php foreach ($allEmployees as $emp): ?>
                        <option value="<?= htmlspecialchars($emp['employee_code']) ?>">
                            <?= htmlspecialchars($emp['full_name']) ?> (<?= $emp['employee_code'] ?>) - <?= htmlspecialchars($emp['dept_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Date</label>
                    <input type="date" name="date" value="<?= date('Y-m-d') ?>" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                </div>

                <div>
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Status</label>
                    <select name="status" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                        <option value="">Select Status</option>
                        <option value="present">Present</option>
                        <option value="absent">Absent</option>
                        <option value="half_day">Half Day</option>
                        <option value="late">Late Arrival</option>
                    </select>
                </div>

                <div>
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Notes (Optional)</label>
                    <textarea name="notes" rows="3" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20"></textarea>
                </div>

                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="closeModal('mark-attendance-modal')" class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-200 transition-all">Cancel</button>
                    <button type="submit" class="btn-primary flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all">Record</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.getElementById('attendance-form').onsubmit = async (e) => {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(e.target));
        try {
            const res = await fetch('/ifms/api/attendance.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'mark', ...data })
            });
            const json = await res.json();
            if (json.success) {
                showToast('Attendance recorded successfully!');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(json.error || json.message || 'Error recording attendance', 'error');
            }
        } catch (err) {
            showToast('Error recording attendance', 'error');
        }
    };

    function editAttendance(empCode, empName) {
        // Populate form and open modal
        document.querySelector('select[name="employee_code"]').value = empCode;
        openModal('mark-attendance-modal');
    }

    function toggleSelectAll(checkbox) {
        const checked = checkbox.checked;
        document.querySelectorAll('.emp-select').forEach(cb => cb.checked = checked);
    }

    async function applyBulkAttendance() {
        const selected = Array.from(document.querySelectorAll('.emp-select:checked')).map(i => i.value);
        if (selected.length === 0) { showToast('No employees selected', 'warning'); return; }
        const date = document.getElementById('bulk-date').value;
        const status = document.getElementById('bulk-status').value;
        try {
            const res = await fetch('/ifms/api/attendance.php?action=bulk_mark', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ employee_ids: selected, date: date, status: status })
            });
            const json = await res.json();
            if (json.success) {
                showToast('Bulk attendance applied');
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(json.error || json.message || 'Error applying bulk attendance', 'error');
            }
        } catch (err) {
            showToast('Error applying bulk attendance', 'error');
        }
    }

    // HR: Open bulk modal to apply to selected employees
    function hrOpenBulkAll() {
        const selected = Array.from(document.querySelectorAll('.emp-select:checked')).map(i => i.value);
        if (selected.length === 0) { alert('No employees selected'); return; }
        document.getElementById('bulk-modal-hr-count').textContent = selected.length;
        document.getElementById('bulk-modal-hr-date').value = '<?= $todayDate ?>';
        document.getElementById('bulk-modal-hr-status').value = <?= ($isHolidayToday || $isWeekendToday) ? "'paid_leave'" : "'present'" ?>;
        document.getElementById('bulk-modal-hr-notes').value = '';
        document.getElementById('bulk-modal-hr').dataset.selected = JSON.stringify(selected);
        document.getElementById('bulk-modal-hr').classList.remove('hidden');
    }

    function hrOpenClearModal() {
        const selected = Array.from(document.querySelectorAll('.emp-select:checked')).map(i => i.value);
        if (selected.length === 0) { alert('No employees selected'); return; }
        document.getElementById('clear-modal-hr').dataset.selected = JSON.stringify(selected);
        document.getElementById('clear-modal-hr').classList.remove('hidden');
    }

    function closeClearModalHR() { document.getElementById('clear-modal-hr').classList.add('hidden'); }

    async function submitClearModalHR() {
        const modal = document.getElementById('clear-modal-hr');
        const selected = JSON.parse(modal.dataset.selected || '[]');
        if (!selected.length) { alert('No employees selected'); return; }
        const date = document.getElementById('clear-modal-hr-date').value;
        const notes = document.getElementById('clear-modal-hr-notes').value;
        const todayStr = new Date().toISOString().slice(0,10);
        if (date > todayStr) { showToast('Cannot clear attendance for future dates', 'error'); return; }
        if (!confirm(`Delete attendance for ${selected.length} employees on ${date}?`)) return;
        try {
            const res = await fetch('/ifms/api/attendance.php?action=clear', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ employee_ids: selected, date: date, notes: notes })
            });
            const json = await res.json();
            if (json.success) {
                showToast('Attendance cleared');
                setTimeout(() => location.reload(), 800);
            } else { showToast(json.error || json.message || 'Error clearing attendance', 'error'); }
        } catch (err) { showToast('Error clearing attendance', 'error'); }
    }

    async function submitBulkModalHR() {
        const modal = document.getElementById('bulk-modal-hr');
        const selected = JSON.parse(modal.dataset.selected || '[]');
        if (!selected.length) { alert('No employees selected'); return; }
        const date = document.getElementById('bulk-modal-hr-date').value;
        const status = document.getElementById('bulk-modal-hr-status').value;
        const notes = document.getElementById('bulk-modal-hr-notes').value;
        // validation
        const allowed = ['present','late','half_day','absent','paid_leave'];
        if (!allowed.includes(status)) { showToast('Invalid status selected', 'error'); return; }
        // prevent future date
        const todayStr = new Date().toISOString().slice(0,10);
        if (date > todayStr) { showToast('Cannot mark attendance for future dates', 'error'); return; }
        document.getElementById('bulk-modal-hr-confirm').classList.remove('hidden');
        document.getElementById('bulk-modal-hr-confirm-yes').onclick = async function() {
            document.getElementById('bulk-modal-hr-confirm').classList.add('hidden');
            document.getElementById('bulk-modal-hr-apply').disabled = true;
            try {
                const res = await fetch('/ifms/api/attendance.php?action=bulk_mark', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ employee_ids: selected, date: date, status: status, notes: notes })
                });
                const json = await res.json();
                if (json.success) {
                    showToast('Bulk attendance applied');
                    setTimeout(() => location.reload(), 800);
                } else {
                    showToast(json.error || json.message || 'Error applying bulk attendance', 'error');
                }
            } catch (err) {
                showToast('Error applying bulk attendance', 'error');
            } finally { document.getElementById('bulk-modal-hr-apply').disabled = false; }
        };
        return;
    }

    function closeBulkModalHR() {
        document.getElementById('bulk-modal-hr').classList.add('hidden');
    }

// enable/disable Apply button in HR modal based on status selection
document.addEventListener('change', function(e){
    if (e.target && e.target.id === 'bulk-modal-hr-status') {
        const applyBtn = document.getElementById('bulk-modal-hr-apply');
        if (!applyBtn) return;
        if (e.target.value && ['present','late','half_day','absent','paid_leave'].includes(e.target.value)) {
            applyBtn.disabled = false; applyBtn.classList.remove('opacity-60');
        } else { applyBtn.disabled = true; applyBtn.classList.add('opacity-60'); }
    }
});

    let currentEmpData = { id: null, code: null, month: null, year: null };

    async function openEmployeeAttendanceModal(empId, empName, empCode) {
        currentEmpData = { id: empId, code: empCode, month: '<?= $filterMonth ?>', year: '<?= $filterYear ?>' };
        document.getElementById('emp-name-title').textContent = empName;
        try {
            const res = await fetch(`/ifms/api/attendance.php?action=list&employee_id=${empId}&month=${currentEmpData.month}&year=${currentEmpData.year}`);
            const json = await res.json();
            if (!json.success) { showToast('Error loading attendance records', 'error'); return; }
            
            const records = json.data || [];
            const grid = document.getElementById('emp-attendance-grid');
            const daysInMonth = new Date(currentEmpData.year, currentEmpData.month, 0).getDate();
            const todayStr = new Date().toISOString().slice(0,10);
            
            let html = '';
            for (let day = 1; day <= daysInMonth; day++) {
                const dayNum = String(day).padStart(2, '0');
                const dateStr = `${currentEmpData.year}-${currentEmpData.month}-${dayNum}`;
                const record = records.find(r => r.date === dateStr);
                const status = record?.status || 'absent';
                const statusLabel = status.replace(/_/g, ' ').toUpperCase();
                const checkIn = record?.check_in ? record.check_in.substring(0, 5) : '';
                const checkOut = record?.check_out ? record.check_out.substring(0, 5) : '';
                const isFutureDay = dateStr > todayStr;
                
                html += `<div class="bg-gray-50 rounded-lg p-4 border" data-date="${dateStr}">
                    <div class="font-bold text-sm mb-2">${day} ${new Date(dateStr).toLocaleDateString('en-IN', { weekday: 'short' })}</div>
                    <div class="space-y-2">
                        <select class="w-full text-xs px-2 py-1 bg-white border rounded" data-field="status" data-value="${status}" onchange="updateAttendanceRecord(this, '${dateStr}')" ${isFutureDay ? 'disabled' : ''}>
                            <option value="present" ${status === 'present' ? 'selected' : ''}>Present</option>
                            <option value="absent" ${status === 'absent' ? 'selected' : ''}>Absent</option>
                            <option value="half_day" ${status === 'half_day' ? 'selected' : ''}>Half Day</option>
                            <option value="late" ${status === 'late' ? 'selected' : ''}>Late</option>
                            <option value="paid_leave" ${status === 'paid_leave' ? 'selected' : ''}>Paid Leave</option>
                        </select>
                        <div class="flex gap-2">
                            <input type="time" class="flex-1 text-xs px-2 py-1 bg-white border rounded" data-field="check_in" value="${checkIn}" placeholder="Check-in" ${isFutureDay ? 'disabled' : ''}>
                            <input type="time" class="flex-1 text-xs px-2 py-1 bg-white border rounded" data-field="check_out" value="${checkOut}" placeholder="Check-out" ${isFutureDay ? 'disabled' : ''}>
                        </div>
                    </div>
                </div>`;
            }
            grid.innerHTML = html;
        } catch (err) {
            showToast('Error loading attendance', 'error');
        }
        openModal('emp-attendance-modal');
    }

    function updateAttendanceRecord(select, dateStr) {
        // Mark for save
        const container = document.querySelector(`[data-date="${dateStr}"]`);
        if (container) container.classList.add('border-indigo-400', 'bg-indigo-50');
    }

    async function saveEmployeeAttendance() {
        const changes = [];
        const containers = document.querySelectorAll('[data-date]');
        containers.forEach(container => {
            const dateStr = container.getAttribute('data-date');
            const statusSelect = container.querySelector('[data-field="status"]');
            const checkInInput = container.querySelector('[data-field="check_in"]');
            const checkOutInput = container.querySelector('[data-field="check_out"]');
            
            if (container.classList.contains('border-indigo-400')) {
                changes.push({
                    date: dateStr,
                    status: statusSelect.value,
                    check_in: checkInInput.value,
                    check_out: checkOutInput.value
                });
            }
        });
        
        if (changes.length === 0) { showToast('No changes made', 'warning'); return; }
        
        try {
            for (const change of changes) {
                await fetch('/ifms/api/attendance.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'mark', employee_code: currentEmpData.code, ...change })
                });
            }
            showToast('Attendance updated successfully!', 'success');
            closeModal('emp-attendance-modal');
            setTimeout(() => location.reload(), 1000);
        } catch (err) {
            showToast('Error saving attendance', 'error');
        }
    }
    </script>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>

<!-- Bulk Modal (HR) -->
<div id="bulk-modal-hr" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold">Apply Attendance to Selected</h3>
            <button onclick="closeBulkModalHR()" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <p class="text-sm text-gray-600 mb-4">Applying to <span id="bulk-modal-hr-count">0</span> selected employees.</p>
        <div class="mb-4">
            <label class="text-xs font-black text-gray-400 block mb-2">Date</label>
            <input id="bulk-modal-hr-date" type="date" class="w-full px-3 py-2 border rounded" />
        </div>
        <div class="mb-4">
            <label class="text-xs font-black text-gray-400 block mb-2">Status</label>
            <select id="bulk-modal-hr-status" class="w-full px-3 py-2 border rounded">
                <option value="">-- Select status --</option>
                <option value="present">Present</option>
                <option value="late">Late</option>
                <option value="half_day">Half Day</option>
                <option value="absent">Absent</option>
                <option value="paid_leave">Paid Leave</option>
            </select>
        </div>
        <div class="mb-4">
            <label class="text-xs font-black text-gray-400 block mb-2">Notes (optional)</label>
            <textarea id="bulk-modal-hr-notes" class="w-full px-3 py-2 border rounded" rows="3"></textarea>
        </div>
        <div class="flex justify-end gap-3">
            <button onclick="closeBulkModalHR()" class="px-4 py-2 bg-gray-100 rounded">Cancel</button>
            <button id="bulk-modal-hr-apply" onclick="submitBulkModalHR()" disabled class="px-4 py-2 bg-indigo-600 text-white rounded opacity-60">Apply</button>
        </div>
        <div class="mb-3 text-sm text-gray-600 hidden" id="bulk-modal-hr-confirm">Are you sure? <button id="bulk-modal-hr-confirm-yes" class="ml-3 px-3 py-1 bg-indigo-600 text-white rounded">Yes, apply</button> <button onclick="document.getElementById('bulk-modal-hr-confirm').classList.add('hidden')" class="ml-2 px-3 py-1 bg-gray-100 rounded">Cancel</button></div>
    </div>
</div>
