<?php
/**
 * IFMS - Admin: Invoices & Quotations
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('admin');

$db = getDB();
$pageTitle = 'Billing & Invoices';

$type = $_GET['type'] ?? 'invoice';
$invoices = $db->query("
    SELECT i.*, o.name AS org_name, p.title AS project_title
    FROM invoices i
    JOIN organizations o ON i.organization_id = o.id
    LEFT JOIN projects p ON i.project_id = p.id
    WHERE i.type = '{$type}'
    ORDER BY i.issue_date DESC
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h2 class="text-2xl font-black text-gray-900 tracking-tight"><?= ucfirst($type) ?>s</h2>
        <p class="text-sm text-gray-500 font-medium">Manage financial documents and billing.</p>
    </div>
    <div class="flex items-center gap-3">
        <div class="flex p-1 bg-gray-100 rounded-xl">
            <a href="?type=invoice" class="px-4 py-2 rounded-lg text-xs font-bold <?= $type === 'invoice' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500' ?>">Invoices</a>
            <a href="?type=quotation" class="px-4 py-2 rounded-lg text-xs font-bold <?= $type === 'quotation' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500' ?>">Quotations</a>
        </div>
    </div>
</div>

<div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="text-left text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/50">
                <th class="px-6 py-4">Number</th>
                <th class="px-6 py-4">Client / Project</th>
                <th class="px-6 py-4 text-right">Total Amount</th>
                        <th class="px-6 py-4">Action</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4">Date</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            <?php foreach ($invoices as $inv): ?>
            <tr class="hover:bg-gray-50/50 transition-colors group">
                <td class="px-6 py-4 text-sm font-mono font-bold text-indigo-600"><?= $inv['invoice_number'] ?></td>
                <td class="px-6 py-4">
                    <p class="text-sm font-bold text-gray-900"><?= htmlspecialchars($inv['org_name']) ?></p>
                    <p class="text-[10px] text-gray-400 font-bold uppercase"><?= htmlspecialchars($inv['project_title'] ?? 'General') ?></p>
                </td>
                <td class="px-6 py-4 text-right">
                    <p class="text-sm font-black text-gray-900">₹<?= number_format($inv['total_amount']) ?></p>
                </td>
                <td class="px-6 py-4">
                    <a href="/ifms/api/invoices.php?action=download&id=<?= intval($inv['id']) ?>" class="text-indigo-600 hover:text-indigo-700 font-bold">Download</a>
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter <?php
                        echo match($inv['status']) {
                            'paid' => 'bg-emerald-50 text-emerald-600',
                            'sent' => 'bg-indigo-50 text-indigo-600',
                            'overdue' => 'bg-red-50 text-red-600',
                            default => 'bg-gray-100 text-gray-400'
                        };
                    ?>"><?= $inv['status'] ?></span>
                </td>
                <td class="px-6 py-4 text-xs text-gray-400 font-medium"><?= date('d M Y', strtotime($inv['issue_date'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>