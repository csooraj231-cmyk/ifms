<?php
/**
 * IFMS - HR: Employee Management
 * HR employees can manage employees (except administrators)
 */
session_start();
require_once __DIR__ . '/../../config/auth.php';
requireLogin();
requireHRAccess();

$db = getDB();
$pageTitle = 'Employee Management';

$employees = $db->query("
    SELECT e.*, u.full_name, u.email, u.phone, u.role as user_role, d.name AS department_name,
           sd.full_name as senior_full_name
    FROM employees e 
    JOIN users u ON e.user_id = u.id 
    JOIN departments d ON e.department_id = d.id 
    LEFT JOIN employees se ON e.senior_developer_id = se.id
    LEFT JOIN users sd ON se.user_id = sd.id
    ORDER BY e.employee_code ASC
")->fetchAll();

$departments = $db->query("SELECT * FROM departments ORDER BY name ASC")->fetchAll();
$seniorDevs = $db->query("
    SELECT e.id, u.full_name 
    FROM employees e
    JOIN users u ON e.user_id = u.id
    WHERE u.role = 'employee' 
    AND e.designation LIKE '%Senior%Developer%'
    AND e.is_active = 1
    ORDER BY u.full_name
")->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h2 class="text-2xl font-black text-gray-900 tracking-tight">Employees</h2>
        <p class="text-sm text-gray-500 font-medium">Manage employee records and details.</p>
    </div>
    <button onclick="openModal('add-employee-modal')" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-2xl font-bold text-sm hover:bg-indigo-700 shadow-xl shadow-indigo-500/20 transition-all active:scale-95">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Add Employee
    </button>
</div>

<!-- Filters -->
<div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm mb-6 flex flex-wrap gap-4 items-center">
    <div class="flex-1 min-w-[200px] relative">
        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" id="searchInput" placeholder="Search employees..." class="w-full pl-10 pr-4 py-2 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20" onkeyup="filterTable()">
    </div>
    <select id="deptFilter" class="bg-gray-50 border-none rounded-xl text-sm px-4 py-2 focus:ring-2 focus:ring-indigo-500/20 min-w-[150px]" onchange="filterTable()">
        <option value="">All Departments</option>
        <?php foreach ($departments as $d): ?><option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option><?php endforeach; ?>
    </select>
    <select id="statusFilter" class="bg-gray-50 border-none rounded-xl text-sm px-4 py-2 focus:ring-2 focus:ring-indigo-500/20 min-w-[120px]" onchange="filterTable()">
        <option value="">All Status</option>
        <option value="1">Active</option>
        <option value="0">Inactive</option>
    </select>
</div>

<!-- Table -->
<div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full" id="employeeTable">
            <thead>
                <tr class="text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.15em] bg-gray-50/50">
                    <th class="px-6 py-4">Employee</th>
                    <th class="px-6 py-4">Department</th>
                    <th class="px-6 py-4">Role</th>
                    <th class="px-6 py-4 text-right">Salary</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($employees as $emp): ?>
                <tr class="hover:bg-gray-50/50 transition-colors group employee-row" data-dept="<?= $emp['department_id'] ?>" data-status="<?= $emp['is_active'] ?>" data-name="<?= strtolower($emp['full_name']) ?>" data-code="<?= strtolower($emp['employee_code']) ?>">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-black text-xs">
                                <?= strtoupper(substr($emp['full_name'], 0, 1)) ?>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900 leading-none"><?= htmlspecialchars($emp['full_name']) ?></p>
                                <p class="text-xs text-indigo-500 font-mono mt-1"><?= $emp['employee_code'] ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-medium text-gray-600"><?= htmlspecialchars($emp['department_name']) ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-bold text-gray-500"><?= $emp['designation'] ?></span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <span class="text-sm font-black text-gray-900">₹<?= number_format($emp['base_salary']) ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter <?= $emp['is_active'] ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' ?>">
                            <?= $emp['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex gap-2 justify-end opacity-0 group-hover:opacity-100 transition-all">
                            <button onclick="openEditEmployeeModal(<?= $emp['id'] ?>)" title="Edit" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536M9 11l6-6 3 3-6 6H9v-3z"/></svg>
                            </button>
                            <button onclick="confirmDeleteEmployee(<?= $emp['id'] ?>, '<?= htmlspecialchars($emp['full_name'], ENT_QUOTES) ?>')" title="Delete" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Employee Modal -->
<div id="add-employee-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl max-h-screen overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-100 p-6 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Add New Employee</h2>
            <button onclick="closeModal('add-employee-modal')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <form id="add-employee-form" class="p-6 space-y-4">
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Full Name</label>
                <input type="text" name="full_name" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Email Address</label>
                <input type="email" name="email" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Password</label>
                <input type="password" name="password" required minlength="6" placeholder="Temporary password" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                <p class="text-xs text-gray-500 mt-2">Set a temporary password. Employee should change on first login.</p>
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Phone Number</label>
                <input type="tel" name="phone" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Department</label>
                <select name="department_id" id="add-department" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">Select Department</option>
                    <?php foreach ($departments as $d): ?><option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Designation</label>
                <select name="designation" id="add-designation" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">Select Designation (choose department first)</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Base Salary</label>
                <input type="number" name="base_salary" step="0.01" min="0" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeModal('add-employee-modal')" class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-200 transition-all">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all">Add Employee</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-employee-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-3xl max-w-sm w-full shadow-2xl">
        <div class="p-6">
            <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 0v2m6-8a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h2 class="text-center text-xl font-bold text-gray-900 mb-2">Delete Employee?</h2>
            <p class="text-center text-sm text-gray-600 mb-6">
                You are about to permanently remove <span id="delete-emp-name" class="font-bold">this employee</span>. This action cannot be undone. Their account will be deactivated.
            </p>
            <div class="flex gap-3">
                <button onclick="closeModal('delete-employee-modal')" class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-200 transition-all">Cancel</button>
                <button onclick="deleteEmployeeConfirmed()" class="flex-1 px-4 py-3 bg-red-600 text-white rounded-xl font-bold text-sm hover:bg-red-700 transition-all">Delete Employee</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Employee Modal -->
<div id="edit-employee-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl max-h-screen overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-100 p-6 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Edit Employee</h2>
            <button onclick="closeModal('edit-employee-modal')" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form id="edit-employee-form" class="p-6 space-y-4">
            <input type="hidden" name="employee_id" id="edit-employee-id">
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Full Name</label>
                <input type="text" name="full_name" id="edit-full-name" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Email Address</label>
                <input type="email" name="email" id="edit-email" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Phone Number</label>
                <input type="tel" name="phone" id="edit-phone" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Department</label>
                <select name="department_id" id="edit-department" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">Select Department</option>
                    <?php foreach ($departments as $d): ?><option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Designation</label>
                <select name="designation" id="edit-designation-select" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">Select Designation</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Base Salary</label>
                <input type="number" name="base_salary" id="edit-base-salary" step="0.01" min="0" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeModal('edit-employee-modal')" class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-200 transition-all">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
// Load designations when department changes
async function loadDesignations(deptId, selectId = 'add-designation') {
    const select = document.getElementById(selectId);
    select.innerHTML = '<option value="">Loading...</option>';
    
    if (!deptId) {
        select.innerHTML = '<option value="">Select Designation (choose department first)</option>';
        return;
    }

    try {
        const res = await fetch('/ifms/api/designations.php?dept_id=' + deptId);
        const json = await res.json();
        if (json.success && json.designations && json.designations.length > 0) {
            select.innerHTML = '<option value="">Select Designation</option>';
            json.designations.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d;
                opt.text = d;
                select.appendChild(opt);
            });
        } else {
            select.innerHTML = '<option value="">No designations for this department</option>';
        }
    } catch (err) {
        select.innerHTML = '<option value="">Error loading designations</option>';
        console.error('Error:', err);
    }
}

document.getElementById('add-department').addEventListener('change', (e) => {
    loadDesignations(e.target.value, 'add-designation');
});

document.getElementById('edit-department').addEventListener('change', (e) => {
    loadDesignations(e.target.value, 'edit-designation-select');
});

document.getElementById('add-employee-form').onsubmit = async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target));
    try {
        const res = await fetch('/ifms/api/employees.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'create', ...data })
        });
        const json = await res.json();
        if (json.success) {
            showToast('Employee added successfully!');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(json.error || json.message || 'Error adding employee', 'error');
        }
    } catch (err) {
        showToast(err?.message || 'Error adding employee', 'error');
    }
};

function filterTable() {
    const searchVal = document.getElementById('searchInput').value.toLowerCase();
    const deptVal = document.getElementById('deptFilter').value;
    const statusVal = document.getElementById('statusFilter').value;
    
    document.querySelectorAll('.employee-row').forEach(row => {
        const name = row.dataset.name || '';
        const code = row.dataset.code || '';
        const dept = row.dataset.dept || '';
        const status = row.dataset.status || '';
        
        const matchSearch = name.includes(searchVal) || code.includes(searchVal);
        const matchDept = !deptVal || dept === deptVal;
        const matchStatus = !statusVal || status === statusVal;
        
        row.style.display = (matchSearch && matchDept && matchStatus) ? '' : 'none';
    });
}

async function openEditEmployeeModal(empId) {
    try {
        const res = await fetch('/ifms/api/employees.php?action=get&id=' + empId);
        const json = await res.json();
        if (!json || !json.success) {
            showToast(json?.error || 'Failed to load employee', 'error');
            return;
        }
        const e = json.data;
        document.getElementById('edit-employee-id').value = e.id || '';
        document.getElementById('edit-full-name').value = e.full_name || '';
        document.getElementById('edit-email').value = e.email || '';
        document.getElementById('edit-phone').value = e.phone || '';
        document.getElementById('edit-department').value = e.department_id || '';
        document.getElementById('edit-base-salary').value = e.base_salary || '';
        
        if (e.department_id) {
            await loadDesignations(e.department_id, 'edit-designation-select');
            setTimeout(() => {
                document.getElementById('edit-designation-select').value = e.designation || '';
            }, 300);
        }
        
        openModal('edit-employee-modal');
    } catch (err) {
        showToast(err?.message || 'Error fetching employee', 'error');
    }
}

document.getElementById('edit-employee-form').onsubmit = async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target));
    try {
        const res = await fetch('/ifms/api/employees.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'update', ...data })
        });
        const json = await res.json();
        if (json.success) {
            showToast('Employee updated successfully!');
            closeModal('edit-employee-modal');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(json?.error || json?.message || 'Update failed', 'error');
        }
    } catch (err) {
        showToast(err?.message || 'Error updating employee', 'error');
    }
};

let deleteEmployeeId = null;

function confirmDeleteEmployee(empId, empName) {
    deleteEmployeeId = empId;
    document.getElementById('delete-emp-name').textContent = empName;
    openModal('delete-employee-modal');
}

async function deleteEmployeeConfirmed() {
    if (!deleteEmployeeId) return;
    
    try {
        const res = await fetch('/ifms/api/employees.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'deactivate', employee_id: deleteEmployeeId })
        });
        const json = await res.json();
        if (json.success) {
            showToast('Employee deactivated successfully!');
            closeModal('delete-employee-modal');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(json?.error || json?.message || 'Delete failed', 'error');
        }
    } catch (err) {
        showToast(err?.message || 'Error deleting employee', 'error');
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
