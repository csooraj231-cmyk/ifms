<?php
/**
 * IFMS - Employee: My Payslips
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('employee');

$db = getDB();
$user = getCurrentUser();
$empId = $user['employee_id'];
$pageTitle = 'My Payslips';

$payslips = $db->query("
    SELECT * FROM payroll 
    WHERE employee_id = {$empId} AND status IN ('generated', 'approved', 'paid')
    ORDER BY year DESC, month DESC
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="mb-8">
    <h2 class="text-2xl font-black text-gray-900 tracking-tight">Salary Slips</h2>
    <p class="text-sm text-gray-500 font-medium">Download and view your monthly compensation details.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <?php foreach ($payslips as $ps): ?>
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 hover:shadow-xl transition-all group">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h3 class="text-xl font-black text-gray-900"><?= date('F Y', mktime(0,0,0,$ps['month'],1, $ps['year'])) ?></h3>
                <span class="inline-block mt-2 px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-black uppercase tracking-widest"><?= $ps['status'] ?></span>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>

        <div class="space-y-4 mb-8">
            <div class="flex justify-between text-sm">
                <span class="text-gray-400 font-bold uppercase tracking-widest text-[10px]">Gross Salary</span>
                <span class="text-gray-900 font-black">₹<?= number_format($ps['gross_salary']) ?></span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-400 font-bold uppercase tracking-widest text-[10px]">Total Deductions</span>
                <span class="text-red-500 font-black">₹<?= number_format($ps['total_deductions']) ?></span>
            </div>
        </div>

            <div class="pt-6 border-t border-gray-100 flex items-center justify-between">
            <p class="text-2xl font-black text-indigo-600 tracking-tight">₹<?= number_format($ps['net_salary']) ?></p>
            <div class="flex gap-3">
                <button onclick="openPayslipDetailsModal(<?= htmlspecialchars(json_encode($ps)) ?>)" class="text-xs font-bold text-gray-400 hover:text-indigo-600 transition-colors uppercase tracking-widest">Details</button>
                <button onclick="downloadPayslip(<?= intval($ps['id']) ?>)" class="text-xs font-bold text-white bg-indigo-600 px-3 py-2 rounded-xl hover:bg-indigo-700">Download</button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($payslips)): ?>
        <div class="col-span-full bg-gray-50 border-2 border-dashed border-gray-200 rounded-3xl p-16 text-center">
            <p class="text-gray-400 font-bold">No payslips generated yet. ??</p>
        </div>
    <?php endif; ?>
</div>

<!-- Payslip Details Modal -->
<div id="payslip-detail-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl max-h-screen overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-100 p-6 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Payslip Details</h2>
            <button onclick="closeModal('payslip-detail-modal')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <div class="p-6 space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Month</h3>
                    <p class="text-sm font-bold text-gray-900" id="slip-month"></p>
                </div>
                <div>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Status</h3>
                    <p class="text-sm font-bold text-gray-900" id="slip-status"></p>
                </div>
            </div>
            
            <div class="border-t border-gray-100 pt-6">
                <h3 class="text-lg font-black text-gray-900 mb-4">Earnings</h3>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Base Salary</span>
                        <span class="text-gray-900 font-bold">₹<span id="slip-base"></span></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">HRA</span>
                        <span class="text-gray-900 font-bold">₹<span id="slip-hra"></span></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">DA</span>
                        <span class="text-gray-900 font-bold">₹<span id="slip-da"></span></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Special Allowance</span>
                        <span class="text-gray-900 font-bold">₹<span id="slip-allowance"></span></span>
                    </div>
                    <div class="flex justify-between text-sm font-bold border-t pt-2">
                        <span class="text-gray-900">Gross Salary</span>
                        <span class="text-indigo-600">₹<span id="slip-gross"></span></span>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-100 pt-6">
                <h3 class="text-lg font-black text-gray-900 mb-4">Deductions</h3>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">PF Deduction</span>
                        <span class="text-gray-900 font-bold">₹<span id="slip-pf"></span></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Tax Deduction</span>
                        <span class="text-gray-900 font-bold">₹<span id="slip-tax"></span></span>
                    </div>
                    <div class="flex justify-between text-sm font-bold border-t pt-2">
                        <span class="text-gray-900">Total Deductions</span>
                        <span class="text-red-500">₹<span id="slip-deductions"></span></span>
                    </div>
                </div>
            </div>
            
            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4">
                <div class="flex justify-between">
                    <span class="text-sm font-black text-gray-900">NET SALARY</span>
                    <span class="text-xl font-black text-indigo-600">₹<span id="slip-net"></span></span>
                </div>
            </div>
            
            <div class="pt-4 flex gap-3">
                <input type="hidden" id="slip-id" value="">
                <button type="button" onclick="closeModal('payslip-detail-modal')" class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-200 transition-all">Close</button>
                <button type="button" id="download-slip-btn" onclick="downloadPayslip(document.getElementById('slip-id').value)" class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all">Download</button>
            </div>
        </div>
    </div>
</div>

<script>
function openPayslipDetailsModal(payslip) {
    document.getElementById('slip-month').textContent = new Date(payslip.year, payslip.month - 1).toLocaleDateString('en-IN', { month: 'long', year: 'numeric' });
    document.getElementById('slip-status').textContent = payslip.status.toUpperCase();
    document.getElementById('slip-base').textContent = parseInt(payslip.base_salary).toLocaleString('en-IN');
    document.getElementById('slip-hra').textContent = parseInt(payslip.hra).toLocaleString('en-IN');
    document.getElementById('slip-da').textContent = parseInt(payslip.da).toLocaleString('en-IN');
    document.getElementById('slip-allowance').textContent = parseInt(payslip.special_allowance).toLocaleString('en-IN');
    document.getElementById('slip-gross').textContent = parseInt(payslip.gross_salary).toLocaleString('en-IN');
    document.getElementById('slip-pf').textContent = parseInt(payslip.pf_deduction).toLocaleString('en-IN');
    document.getElementById('slip-tax').textContent = parseInt(payslip.tax_deduction || 0).toLocaleString('en-IN');
    document.getElementById('slip-deductions').textContent = parseInt(payslip.total_deductions).toLocaleString('en-IN');
    document.getElementById('slip-net').textContent = parseInt(payslip.net_salary).toLocaleString('en-IN');
    document.getElementById('slip-id').value = payslip.id || '';
    openModal('payslip-detail-modal');
}

async function downloadPayslip(payrollId) {
    if (!payrollId) { showToast('Payslip ID missing', 'error'); return; }
    try {
        const res = await fetch('/ifms/api/payroll.php?action=download&id=' + payrollId, { method: 'GET', credentials: 'same-origin' });
        if (!res.ok) {
            const txt = await res.text();
            showToast('Error downloading payslip: ' + txt, 'error');
            return;
        }
        const blob = await res.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        // Try to get filename from headers
        const cd = res.headers.get('Content-Disposition');
        let filename = 'payslip.html';
        if (cd) {
            const m = cd.match(/filename="?([^";]+)"?/);
            if (m) filename = m[1];
        }
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);
    } catch (err) {
        showToast('Error downloading payslip: ' + err.message, 'error');
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>