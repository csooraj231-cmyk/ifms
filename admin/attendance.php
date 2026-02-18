<?php
/**
 * IFMS - Admin: Attendance Management
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('admin');

$db = getDB();
$pageTitle = 'Attendance Management';

$date = $_GET['date'] ?? date('Y-m-d');
// Determine if the selected date is a holiday or weekend (Saturday/Sunday)
$weekday = (int)date('w', strtotime($date)); // 0=Sun,6=Sat
$holidayStmt = $db->prepare("SELECT id, title FROM holidays WHERE date = ? LIMIT 1");
$holidayStmt->execute([$date]);
$holidayRow = $holidayStmt->fetch();
$isHoliday = $holidayRow ? true : false;
$holidayName = $holidayRow['title'] ?? '';
$isWeekend = ($weekday === 0 || $weekday === 6);
// Prevent marking for future dates
$isFuture = (strtotime($date) > strtotime(date('Y-m-d')));

$employees = $db->query("
        SELECT e.*, COALESCE(u.full_name, e.employee_code) AS full_name, COALESCE(d.name, '') AS department_name, 
            a.status AS attendance_status, a.check_in, a.check_out
        FROM employees e
        LEFT JOIN users u ON e.user_id = u.id
        LEFT JOIN departments d ON e.department_id = d.id
        LEFT JOIN attendance a ON e.id = a.employee_id AND a.date = '{$date}'
    ORDER BY u.full_name ASC
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h2 class="text-2xl font-black text-gray-900 tracking-tight">Daily Attendance</h2>
        <p class="text-sm text-gray-500 font-medium">Track and mark attendance for <?= date('d M, Y', strtotime($date)) ?></p>
        <?php if ($isHoliday || $isWeekend): ?>
            <p class="text-sm text-amber-600 font-bold mt-2">Today is a holiday<?= $isHoliday && $holidayName ? ': ' . htmlspecialchars($holidayName) : '' ?></p>
        <?php endif; ?>
    </div>
    <div class="flex items-center gap-3">
        <button id="bulk-all-btn" <?= $isFuture ? 'disabled' : '' ?> onclick="openBulkAllModal()" class="px-4 py-2 bg-emerald-50 text-emerald-700 rounded-xl font-bold hover:bg-emerald-100 transition-all <?= $isFuture ? 'opacity-50 cursor-not-allowed' : '' ?>">Apply</button>
        <button id="clear-all-btn" <?= $isFuture ? 'disabled' : '' ?> onclick="openClearModalAdmin()" class="px-4 py-2 bg-red-50 text-red-700 rounded-xl font-bold hover:bg-red-100 transition-all <?= $isFuture ? 'opacity-50 cursor-not-allowed' : '' ?>">Clear</button>
        <button onclick="location.href='?date=<?= date('Y-m-d', strtotime($date . ' - 1 day')) ?>'" class="px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl font-bold hover:bg-indigo-100 transition-all">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Previous Day
        </button>
        <input type="date" value="<?= $date ?>" onchange="location.href='?date='+this.value" class="bg-white border border-gray-100 rounded-xl px-4 py-2 text-sm font-bold shadow-sm focus:ring-2 focus:ring-indigo-500/20">
        <button onclick="location.href='?date=<?= date('Y-m-d', strtotime($date . ' + 1 day')) ?>'" class="px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl font-bold hover:bg-indigo-100 transition-all">
            Next Day
            <svg class="w-4 h-4 inline ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
    </div>
</div>

<div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/50">
                    <th class="px-4 py-4"><input type="checkbox" id="select-all-admin" onclick="toggleSelectAllAdmin(this)"></th>
                    <th class="px-6 py-4">Employee</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Check In</th>
                    <th class="px-6 py-4">Check Out</th>
                    <th class="px-6 py-4">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($employees as $emp): ?>
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-4 py-4">
                        <input type="checkbox" class="emp-select" value="<?= $emp['id'] ?>">
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-bold text-gray-900"><?= htmlspecialchars($emp['full_name']) ?></p>
                        <p class="text-[10px] text-gray-400 font-bold uppercase"><?= $emp['department_name'] ?></p>
                    </td>
                    <td class="px-6 py-4">
                        <?php if ($isHoliday || $isWeekend): ?>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter bg-cyan-50 text-cyan-700">PAID LEAVE</span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter <?php
                                echo match($emp['attendance_status']) {
                                    'present' => 'bg-emerald-50 text-emerald-600',
                                    'absent' => 'bg-red-50 text-red-600',
                                    'late' => 'bg-amber-50 text-amber-600',
                                    'half_day' => 'bg-orange-50 text-orange-600',
                                    default => 'bg-gray-100 text-gray-400'
                                };
                            ?>"><?= $emp['attendance_status'] ?? 'Not Marked' ?></span>
                        <?php endif; ?>
                    </td>
                    <?php if ($isHoliday || $isWeekend): ?>
                        <td class="px-6 py-4 text-sm font-mono text-gray-600">-</td>
                        <td class="px-6 py-4 text-sm font-mono text-gray-600">-</td>
                        <td class="px-6 py-4"><span class="text-xs text-gray-500">Holiday</span></td>
                    <?php else: ?>
                        <td class="px-6 py-4 text-sm font-mono text-gray-600">
                            <input type="time" value="<?= substr($emp['check_in'] ?? '00:00', 0, 5) ?>" <?= $isFuture ? 'disabled' : '' ?> class="px-2 py-1 bg-gray-50 border border-gray-200 rounded-lg text-sm font-mono" onchange="updateTime('<?= $emp['employee_code'] ?>', 'check_in', this.value)">
                        </td>
                        <td class="px-6 py-4 text-sm font-mono text-gray-600">
                            <input type="time" value="<?= substr($emp['check_out'] ?? '00:00', 0, 5) ?>" <?= $isFuture ? 'disabled' : '' ?> class="px-2 py-1 bg-gray-50 border border-gray-200 rounded-lg text-sm font-mono" onchange="updateTime('<?= $emp['employee_code'] ?>', 'check_out', this.value)">
                        </td>
                        <td class="px-6 py-4">
                            <select onchange="markAttendance('<?= $emp['employee_code'] ?>', this.value)" <?= $isFuture ? 'disabled' : '' ?> class="text-[10px] font-bold text-indigo-600 bg-indigo-50 border-none rounded-lg px-2 py-1 focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                                <option value="">Mark as...</option>
                                <option value="present">Present</option>
                                <option value="late">Late</option>
                                <option value="half_day">Half Day</option>
                                <option value="absent">Absent</option>
                            </select>
                        </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
async function markAttendance(code, status) {
    if (!status) return;
    const res = await api('/ifms/api/attendance.php', 'POST', { action: 'mark', employee_code: code, status: status, date: '<?= $date ?>' });
    if (res.success) { showToast('Attendance updated'); setTimeout(() => location.reload(), 500); }
}

async function updateTime(code, type, time) {
    const res = await api('/ifms/api/attendance.php', 'POST', { action: 'update_time', employee_code: code, type: type, time: time, date: '<?= $date ?>' });
    if (res.success) { showToast('Time updated'); }
    else { showToast('Error updating time', 'error'); }
}

function toggleSelectAllAdmin(cb) {
    const checked = cb.checked;
    document.querySelectorAll('.emp-select').forEach(i => i.checked = checked);
}

function openBulkAllModal() {
    const selected = Array.from(document.querySelectorAll('.emp-select:checked')).map(i => i.value);
    if (selected.length === 0) { alert('No employees selected'); return; }
    // populate modal
    document.getElementById('bulk-modal-admin-count').textContent = selected.length;
    document.getElementById('bulk-modal-admin-status').value = '';
    document.getElementById('bulk-modal-admin-notes').value = '';
    document.getElementById('bulk-modal-admin').dataset.selected = JSON.stringify(selected);
    document.getElementById('bulk-modal-admin').classList.remove('hidden');
}

function openClearModalAdmin() {
    const selected = Array.from(document.querySelectorAll('.emp-select:checked')).map(i => i.value);
    if (selected.length === 0) { alert('No employees selected'); return; }
    document.getElementById('clear-modal-admin-count').textContent = selected.length;
    document.getElementById('clear-modal-admin-notes').value = '';
    document.getElementById('clear-modal-admin').dataset.selected = JSON.stringify(selected);
    document.getElementById('clear-modal-admin').classList.remove('hidden');
}

function closeClearModalAdmin() { document.getElementById('clear-modal-admin').classList.add('hidden'); }

async function submitClearModalAdmin() {
    const modal = document.getElementById('clear-modal-admin');
    const selected = JSON.parse(modal.dataset.selected || '[]');
    if (!selected.length) { alert('No employees selected'); return; }
    const notes = document.getElementById('clear-modal-admin-notes').value;
    if (!confirm(`Delete attendance for ${selected.length} employees on <?= $date ?>?`)) return;
    try {
        const res = await fetch('/ifms/api/attendance.php?action=clear', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ employee_ids: selected, date: '<?= $date ?>', notes: notes })
        });
        const json = await res.json();
        if (json.success) {
            showToast('Attendance cleared');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(json.error || json.message || 'Error clearing attendance', 'error');
        }
    } catch (err) { showToast('Error clearing attendance', 'error'); }
}

async function submitBulkModalAdmin() {
    const modal = document.getElementById('bulk-modal-admin');
    const selected = JSON.parse(modal.dataset.selected || '[]');
    if (!selected.length) { alert('No employees selected'); return; }
    const status = document.getElementById('bulk-modal-admin-status').value;
    const notes = document.getElementById('bulk-modal-admin-notes').value;
        // validation
        const allowed = ['present','late','half_day','absent','paid_leave'];
        if (!allowed.includes(status)) { showToast('Invalid status selected', 'error'); return; }
        // show inline confirmation
        document.getElementById('bulk-modal-admin-confirm').classList.remove('hidden');
        document.getElementById('bulk-modal-admin-confirm-yes').onclick = async function() {
            document.getElementById('bulk-modal-admin-confirm').classList.add('hidden');
            document.getElementById('bulk-modal-admin-apply').disabled = true;
            try {
                const res = await fetch('/ifms/api/attendance.php?action=bulk_mark', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ employee_ids: selected, date: '<?= $date ?>', status: status, notes: notes })
                });
                const json = await res.json();
                if (json.success) {
                    showToast('Bulk attendance applied to selected employees');
                    setTimeout(() => location.reload(), 800);
                } else {
                    showToast(json.error || json.message || 'Error applying bulk attendance', 'error');
                }
            } catch (err) {
                showToast('Error applying bulk attendance', 'error');
            } finally { document.getElementById('bulk-modal-admin-apply').disabled = false; }
        };
        return;
    try {
        const res = await fetch('/ifms/api/attendance.php?action=bulk_mark', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ employee_ids: selected, date: '<?= $date ?>', status: status, notes: notes })
        });
        const json = await res.json();
        if (json.success) {
            showToast('Bulk attendance applied to selected employees');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(json.error || json.message || 'Error applying bulk attendance', 'error');
        }
    } catch (err) {
        showToast('Error applying bulk attendance', 'error');
    }
}

function closeBulkModalAdmin() {
    document.getElementById('bulk-modal-admin').classList.add('hidden');
}

// enable/disable Apply button based on status selection
document.addEventListener('change', function(e){
    if (e.target && e.target.id === 'bulk-modal-admin-status') {
        const applyBtn = document.getElementById('bulk-modal-admin-apply');
        if (!applyBtn) return;
        if (e.target.value && ['present','late','half_day','absent','paid_leave'].includes(e.target.value)) {
            applyBtn.disabled = false; applyBtn.classList.remove('opacity-60');
        } else { applyBtn.disabled = true; applyBtn.classList.add('opacity-60'); }
    }
});
</script>

<!-- Bulk Modal (Admin) -->
<div id="bulk-modal-admin" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold">Apply Attendance to Selected</h3>
            <button onclick="closeBulkModalAdmin()" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <p class="text-sm text-gray-600 mb-4">Applying to <span id="bulk-modal-admin-count">0</span> selected employees.</p>
        <div class="mb-4">
            <label class="text-xs font-black text-gray-400 block mb-2">Status</label>
            <select id="bulk-modal-admin-status" class="w-full px-3 py-2 border rounded">
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
            <textarea id="bulk-modal-admin-notes" class="w-full px-3 py-2 border rounded" rows="3"></textarea>
        </div>
        <div class="flex justify-end gap-3">
            <button onclick="closeBulkModalAdmin()" class="px-4 py-2 bg-gray-100 rounded">Cancel</button>
            <button id="bulk-modal-admin-apply" onclick="submitBulkModalAdmin()" disabled class="px-4 py-2 bg-indigo-600 text-white rounded opacity-60">Apply</button>
        </div>
        <div class="mb-3 text-sm text-gray-600 hidden" id="bulk-modal-admin-confirm">Are you sure? <button id="bulk-modal-admin-confirm-yes" class="ml-3 px-3 py-1 bg-indigo-600 text-white rounded">Yes, apply</button> <button onclick="document.getElementById('bulk-modal-admin-confirm').classList.add('hidden')" class="ml-2 px-3 py-1 bg-gray-100 rounded">Cancel</button></div>
    </div>
</div>

<!-- Clear Modal (Admin) -->
<div id="clear-modal-admin" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold">Clear Attendance for Selected</h3>
            <button onclick="closeClearModalAdmin()" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <p class="text-sm text-gray-600 mb-4">This will delete attendance records for the selected employees on <strong><?= htmlspecialchars($date) ?></strong>.</p>
        <p class="text-sm text-gray-600 mb-4">Selected: <span id="clear-modal-admin-count">0</span></p>
        <div class="mb-4">
            <label class="text-xs font-black text-gray-400 block mb-2">Notes (optional)</label>
            <textarea id="clear-modal-admin-notes" class="w-full px-3 py-2 border rounded" rows="3"></textarea>
        </div>
        <div class="flex justify-end gap-3">
            <button onclick="closeClearModalAdmin()" class="px-4 py-2 bg-gray-100 rounded">Cancel</button>
            <button id="clear-modal-admin-confirm" onclick="submitClearModalAdmin()" class="px-4 py-2 bg-red-600 text-white rounded">Clear</button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>