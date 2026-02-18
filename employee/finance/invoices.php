<?php
/**
 * IFMS - Finance Invoices Management
 * Finance employees can manage invoices
 */
session_start();
require_once __DIR__ . '/../../config/auth.php';
requireLogin();
requireFinanceAccess();

$db = getDB();
$user = getCurrentUser();

// Get all invoices
$invoices = $db->query("
    SELECT 
        i.*,
        c.name as client_name,
        c.email as contact_email,
        COUNT(il.id) as item_count
    FROM invoices i
    LEFT JOIN organizations c ON i.organization_id = c.id
    LEFT JOIN invoice_items il ON i.id = il.invoice_id
    GROUP BY i.id
    ORDER BY i.created_at DESC
")->fetchAll() ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices - IFMS</title>
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
                        <h1 class="text-3xl font-bold text-gray-900">Invoices</h1>
                        <p class="text-gray-600 mt-2">Manage and track all invoices</p>
                    </div>
                    <button onclick="openModal('create-invoice-modal')" class="btn-primary px-6 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-all">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Create Invoice
                    </button>
                </div>

                <!-- Statistics -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                        <p class="text-sm font-bold text-gray-500 mb-2">Total Invoices</p>
                        <p class="text-3xl font-bold text-indigo-600"><?= count($invoices) ?></p>
                    </div>
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                        <p class="text-sm font-bold text-gray-500 mb-2">Pending</p>
                        <p class="text-3xl font-bold text-orange-600">
                            <?= count(array_filter($invoices, fn($i) => $i['status'] === 'draft' || $i['status'] === 'sent')) ?>
                        </p>
                    </div>
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                        <p class="text-sm font-bold text-gray-500 mb-2">Paid</p>
                        <p class="text-3xl font-bold text-green-600">
                            <?= count(array_filter($invoices, fn($i) => $i['status'] === 'paid')) ?>
                        </p>
                    </div>
                </div>

                <!-- Invoices Table -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100">
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Invoice #</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Client</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Amount</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Status</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($invoices as $invoice): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 font-bold text-gray-900"><?= htmlspecialchars($invoice['invoice_number'] ?? 'INV-' . $invoice['id']) ?></td>
                                    <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($invoice['client_name'] ?? 'N/A') ?></td>
                                    <td class="px-6 py-4 text-right font-bold text-gray-900">
                                        ₹<?= number_format($invoice['total_amount'] ?? 0, 0) ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">
                                        <?= $invoice['created_at'] ? date('M d, Y', strtotime($invoice['created_at'])) : 'N/A' ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 bg-<?= $invoice['status'] === 'paid' ? 'green' : 'orange' ?>-100 text-<?= $invoice['status'] === 'paid' ? 'green' : 'orange' ?>-700 rounded-full text-xs font-bold">
                                            <?= ucfirst($invoice['status'] ?? 'draft') ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="#" onclick="viewInvoice(<?= $invoice['id'] ?>)" class="text-indigo-600 hover:text-indigo-700 font-bold mr-4">View</a>
                                        <a href="/ifms/api/pdf_export.php?action=invoice&id=<?= $invoice['id'] ?>" target="_blank" class="text-green-600 hover:text-green-700 font-bold">Download</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (empty($invoices)): ?>
                    <div class="p-8 text-center">
                        <p class="text-gray-600">No invoices found.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    function viewInvoice(invoiceId) {
        showToast(`Opening invoice #${invoiceId}...`);
    }
    </script>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>
