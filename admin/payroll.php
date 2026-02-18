<?php
/**
 * IFMS - Admin: Payroll Management
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('admin');

$db = getDB();
$pageTitle = 'Payroll Management';

$month = $_GET['month'] ?? date('n');
$year = $_GET['year'] ?? date('Y');

$payroll = $db->query("
    SELECT p.*, u.full_name, e.employee_code, e.designation
    FROM payroll p
    JOIN employees e ON p.employee_id = e.id
    JOIN users u ON e.user_id = u.id
    WHERE p.month = {$month} AND p.year = {$year}
    ORDER BY u.full_name ASC
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h2 class="text-2xl font-black text-gray-900 tracking-tight">Payroll</h2>
        <p class="text-sm text-gray-500 font-medium">Monthly salary processing and distribution.</p>
    </div>
    <div class="flex items-center gap-3">
        <select onchange="location.href='?year=<?= $year ?>&month='+this.value" class="bg-white border border-gray-100 rounded-xl px-4 py-2 text-sm font-bold shadow-sm">
            <?php for($i=1;$i<=12;$i++): ?>
                <option value="<?= $i ?>" <?= $i == $month ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$i,1)) ?></option>
            <?php endfor; ?>
        </select>
        <button onclick="generatePayroll()" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-2xl font-bold text-sm hover:bg-indigo-700 shadow-xl shadow-indigo-500/20 active:scale-95">
            Auto-Generate
        </button>
    </div>
</div>

<div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/50">
                    <th class="px-6 py-4">Employee</th>
                    <th class="px-6 py-4">Worked/Total</th>
                    <th class="px-6 py-4 text-right">Gross</th>
                    <th class="px-6 py-4 text-right">Deductions</th>
                    <th class="px-6 py-4 text-right">Net Salary</th>
                    <th class="px-6 py-4">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($payroll as $p): ?>
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <p class="text-sm font-bold text-gray-900"><?= htmlspecialchars($p['full_name']) ?></p>
                        <p class="text-[10px] text-indigo-500 font-mono"><?= $p['employee_code'] ?></p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-xs font-bold text-gray-600"><?= $p['days_present'] ?> / <?= $p['total_working_days'] ?> Days</p>
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-bold text-gray-900">₹<?= number_format($p['gross_salary']) ?></td>
                    <td class="px-6 py-4 text-right text-sm font-bold text-red-500">₹<?= number_format($p['total_deductions']) ?></td>
                    <td class="px-6 py-4 text-right text-sm font-black text-emerald-600">₹<?= number_format($p['net_salary']) ?></td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter <?php
                            echo match($p['status']) {
                                'paid' => 'bg-emerald-50 text-emerald-600',
                                'generated' => 'bg-indigo-50 text-indigo-600',
                                default => 'bg-gray-100 text-gray-400'
                            };
                        ?>"><?= $p['status'] ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
async function generatePayroll() {
    const res = await api('/ifms/api/payroll.php', 'POST', { action: 'generate', month: <?= $month ?>, year: <?= $year ?> });
    if (res.success) { showToast(res.message); setTimeout(() => location.reload(), 1000); }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>