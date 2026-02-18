<?php
/**
 * IFMS - Finance Payroll Management
 * Finance employees can manage payroll and generate payslips
 */
session_start();
require_once __DIR__ . '/../../config/auth.php';
requireLogin();
requireFinanceAccess();

$db = getDB();
$user = getCurrentUser();

// Get filters
$filterMonth = $_GET['month'] ?? date('m');
$filterYear = $_GET['year'] ?? date('Y');
$filterDept = $_GET['dept'] ?? '';
$filterStatus = $_GET['status'] ?? '';

// Get payroll records
$query = "
    SELECT 
        p.*, 
        e.id as emp_id,
        e.employee_code,
        u.full_name,
        d.name as dept_name,
        e.base_salary,
        e.hra
    FROM payroll p
    JOIN employees e ON p.employee_id = e.id
    JOIN users u ON e.user_id = u.id
    JOIN departments d ON e.department_id = d.id
    WHERE p.month = $filterMonth AND p.year = $filterYear AND e.is_active = 1
";

if ($filterDept) $query .= " AND e.department_id = " . intval($filterDept);
if ($filterStatus) $query .= " AND p.status = '" . $db->quote($filterStatus) . "'";

$query .= " ORDER BY u.full_name";

$payrolls = $db->query($query)->fetchAll();

// Get departments
$departments = $db->query("SELECT id, name FROM departments ORDER BY name")->fetchAll();

// Get employees for salary setup
$employees = $db->query("
    SELECT e.id, e.employee_code, u.full_name, d.name as dept_name, e.base_salary, e.hra
    FROM employees e
    JOIN users u ON e.user_id = u.id
    JOIN departments d ON e.department_id = d.id
    WHERE e.is_active = 1
    ORDER BY u.full_name
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Management - IFMS</title>
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
                        <h1 class="text-3xl font-bold text-gray-900">Payroll Management</h1>
                        <p class="text-gray-600 mt-2">Generate and manage employee payroll</p>
                    </div>
                    <button onclick="openModal('generate-payroll-modal')" class="btn-primary px-6 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-all">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Generate Payroll
                    </button>
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
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                        <p class="text-sm font-bold text-gray-500 mb-2">Total Payroll</p>
                        <p class="text-3xl font-bold text-indigo-600">
                            ₹<?= number_format(array_sum(array_column($payrolls, 'gross_salary')), 0) ?>
                        </p>
                    </div>
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                        <p class="text-sm font-bold text-gray-500 mb-2">Total Deductions</p>
                        <p class="text-3xl font-bold text-red-600">
                            ₹<?= number_format(array_sum(array_column($payrolls, 'total_deductions')), 0) ?>
                        </p>
                    </div>
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                        <p class="text-sm font-bold text-gray-500 mb-2">Net Payroll</p>
                        <p class="text-3xl font-bold text-green-600">
                            ₹<?= number_format(array_sum(array_column($payrolls, 'net_salary')), 0) ?>
                        </p>
                    </div>
                </div>

                <!-- Payroll Table -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100">
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Employee</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Department</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Gross Salary</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Deductions</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Net Salary</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Status</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($payrolls as $payroll): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="font-bold text-gray-900"><?= htmlspecialchars($payroll['full_name']) ?></p>
                                            <p class="text-sm text-gray-500"><?= $payroll['employee_code'] ?></p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($payroll['dept_name']) ?></td>
                                    <td class="px-6 py-4 text-right font-bold text-gray-900">
                                        ₹<?= number_format($payroll['gross_salary'], 0) ?>
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-red-600">
                                        ₹<?= number_format($payroll['total_deductions'], 0) ?>
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-green-600">
                                        ₹<?= number_format($payroll['net_salary'], 0) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-bold">
                                            <?= ucfirst($payroll['status'] ?? 'pending') ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="#" onclick="viewPayslip(<?= $payroll['id'] ?>)" class="text-indigo-600 hover:text-indigo-700 font-bold">View</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (empty($payrolls)): ?>
                    <div class="p-8 text-center">
                        <p class="text-gray-600">No payroll records found. Generate payroll to get started.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Generate Payroll Modal -->
    <div id="generate-payroll-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="modal-content bg-white rounded-3xl max-w-md w-full shadow-2xl max-h-screen overflow-y-auto">
            <div class="sticky top-0 bg-white border-b border-gray-100 p-6 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900">Generate Payroll</h2>
                <button onclick="closeModal('generate-payroll-modal')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <form id="payroll-form" class="p-6 space-y-4">
                <div>
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Month</label>
                    <select name="month" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                        <option value="">Select Month</option>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>">
                            <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div>
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Year</label>
                    <select name="year" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                        <option value="<?= date('Y') ?>"><?= date('Y') ?></option>
                        <option value="<?= date('Y') - 1 ?>"><?= date('Y') - 1 ?></option>
                    </select>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <p class="text-sm text-blue-700">
                        <strong>Warning:</strong> This will generate payroll for all active employees for the selected month.
                    </p>
                </div>

                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="closeModal('generate-payroll-modal')" class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-200 transition-all">Cancel</button>
                    <button type="submit" class="btn-primary flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all">Generate</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.getElementById('payroll-form').onsubmit = async (e) => {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(e.target));
        try {
            const res = await fetch('/ifms/api/payroll.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'generate', ...data })
            });
            const json = await res.json();
            if (json.success) {
                showToast('Payroll generated successfully!');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(json.error || json.message || 'Error generating payroll', 'error');
            }
        } catch (err) {
            showToast('Error generating payroll', 'error');
        }
    };

    function viewPayslip(payrollId) {
        // Open downloadable payslip in new tab (API handles auth)
        if (!payrollId) { showToast('Payslip ID missing', 'error'); return; }
        window.open('/ifms/api/payroll.php?action=download&id=' + payrollId, '_blank');
    }
    </script>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>
