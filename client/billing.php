<?php
/**
 * IFMS - Client: Billing & Invoices
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('client');

$db = getDB();
$user = getCurrentUser();
$userId = $user['id'];
$pageTitle = 'Billing & Invoices';

$clientUser = $db->prepare("SELECT organization_id FROM client_users WHERE user_id = ?");
$clientUser->execute([$userId]);
$orgId = $clientUser->fetchColumn();

$invoices = $db->query("
    SELECT i.*, p.title AS project_title
    FROM invoices i
    LEFT JOIN projects p ON i.project_id = p.id
    WHERE i.organization_id = {$orgId} AND i.type = 'invoice'
    ORDER BY i.issue_date DESC
")->fetchAll();

$totals = $db->query("
    SELECT 
        SUM(total_amount) as total,
        SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END) as paid
    FROM invoices WHERE organization_id = {$orgId} AND type = 'invoice'
")->fetch();

include __DIR__ . '/../includes/header.php';
?>

<div class="mb-8">
    <h2 class="text-2xl font-black text-gray-900 tracking-tight">Billing & Invoices</h2>
    <p class="text-sm text-gray-500 font-medium">Review your payment history and outstanding invoices.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-indigo-600 p-6 rounded-3xl text-white shadow-lg shadow-indigo-500/20">
        <p class="text-[10px] font-black uppercase tracking-widest opacity-80 mb-2">Total Invoiced</p>
        <p class="text-3xl font-black">₹<?= number_format($totals['total'] ?? 0) ?></p>
    </div>
    <div class="bg-emerald-500 p-6 rounded-3xl text-white shadow-lg shadow-emerald-500/20">
        <p class="text-[10px] font-black uppercase tracking-widest opacity-80 mb-2">Total Paid</p>
        <p class="text-3xl font-black">₹<?= number_format($totals['paid'] ?? 0) ?></p>
    </div>
    <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Outstanding</p>
        <p class="text-3xl font-black text-red-500">₹<?= number_format(($totals['total'] ?? 0) - ($totals['paid'] ?? 0)) ?></p>
    </div>
</div>

<div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
    <table class="w-full text-left">
        <thead>
            <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/50">
                <th class="px-6 py-4">Invoice #</th>
                <th class="px-6 py-4">Project</th>
                <th class="px-6 py-4 text-right">Amount</th>
                <th class="px-6 py-4">Action</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4">Due Date</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            <?php foreach ($invoices as $inv): ?>
            <tr class="hover:bg-gray-50/50 transition-all">
                <td class="px-6 py-4 text-sm font-mono font-bold text-indigo-600"><?= $inv['invoice_number'] ?></td>
                <td class="px-6 py-4 text-xs font-bold text-gray-600"><?= htmlspecialchars($inv['project_title'] ?? 'General Services') ?></td>
                    <td class="px-6 py-4 text-right text-sm font-black text-gray-900">₹<?= number_format($inv['total_amount']) ?></td>
                    <td class="px-6 py-4">
                        <a href="/ifms/api/invoices.php?action=download&id=<?= intval($inv['id']) ?>" class="text-indigo-600 font-bold">Download</a>
                    </td>
                    <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-black uppercase <?php echo match($inv['status']) { 'paid' => 'bg-emerald-50 text-emerald-600', 'overdue' => 'bg-red-50 text-red-600', default => 'bg-amber-50 text-amber-600' }; ?>">
                        <?= $inv['status'] ?>
                    </span>
                </td>
                <td class="px-6 py-4 text-xs font-bold text-gray-400"><?= date('d M Y', strtotime($inv['due_date'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>