<?php
/**
 * IFMS - HR Payroll View
 * HR employees can view/download payslips for all employees
 */
require_once __DIR__ . '/../../config/auth.php';
requireLogin();
requireHRAccess();

$db = getDB();

$filterMonth = $_GET['month'] ?? date('m');
$filterYear = $_GET['year'] ?? date('Y');

$payrolls = $db->query("SELECT p.*, e.id as emp_id, e.employee_code, u.full_name, d.name as dept_name FROM payroll p JOIN employees e ON p.employee_id = e.id JOIN users u ON e.user_id = u.id JOIN departments d ON e.department_id = d.id WHERE p.month = {$filterMonth} AND p.year = {$filterYear} ORDER BY u.full_name")->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>

<div class="p-6">
    <h2 class="text-2xl font-black">HR - Payroll Records</h2>
    <p class="text-sm text-gray-500">View and download payslips for employees.</p>

    <div class="mt-6 bg-white rounded-2xl p-4">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 text-left">
                    <th class="px-4 py-2">Employee</th>
                    <th class="px-4 py-2">Code</th>
                    <th class="px-4 py-2">Gross</th>
                    <th class="px-4 py-2">Deductions</th>
                    <th class="px-4 py-2">Net</th>
                    <th class="px-4 py-2">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payrolls as $p): ?>
                <tr>
                    <td class="px-4 py-2"><?= htmlspecialchars($p['full_name']) ?></td>
                    <td class="px-4 py-2"><?= $p['employee_code'] ?></td>
                    <td class="px-4 py-2">₹<?= number_format($p['gross_salary']) ?></td>
                    <td class="px-4 py-2">₹<?= number_format($p['total_deductions']) ?></td>
                    <td class="px-4 py-2">₹<?= number_format($p['net_salary']) ?></td>
                    <td class="px-4 py-2"><a href="/ifms/api/payroll.php?action=download&id=<?= $p['id'] ?>" class="text-indigo-600">Download</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>