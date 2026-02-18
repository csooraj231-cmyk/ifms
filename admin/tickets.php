<?php
/**
 * IFMS - Admin: Ticket Management
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('admin');

$db = getDB();
$pageTitle = 'Support Tickets';

$tickets = $db->query("
    SELECT st.*, u.full_name AS created_by_name, p.title AS project_title, e.user_id AS assigned_user_id
    FROM support_tickets st
    LEFT JOIN users u ON st.created_by = u.id
    LEFT JOIN projects p ON st.project_id = p.id
    LEFT JOIN employees e ON st.assigned_to = e.id
    ORDER BY st.priority = 'critical' DESC, st.created_at DESC
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="mb-8">
    <h2 class="text-2xl font-black text-gray-900 tracking-tight">Support Tickets</h2>
    <p class="text-sm text-gray-500 font-medium">Monitor and resolve client issues.</p>
</div>

<div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/50">
                    <th class="px-6 py-4">Ticket</th>
                    <th class="px-6 py-4">Subject</th>
                    <th class="px-6 py-4">Project</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Priority</th>
                    <th class="px-6 py-4">Created</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($tickets as $t): ?>
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-6 py-4 text-sm font-mono font-bold text-indigo-600"><?= $t['ticket_number'] ?></td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-bold text-gray-900"><?= htmlspecialchars($t['subject']) ?></p>
                        <p class="text-[10px] text-gray-400 font-bold uppercase mt-0.5">By <?= htmlspecialchars($t['created_by_name']) ?></p>
                    </td>
                    <td class="px-6 py-4 text-xs font-bold text-gray-500"><?= htmlspecialchars($t['project_title'] ?? 'General Support') ?></td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter <?php
                            echo match($t['status']) {
                                'open' => 'bg-amber-100 text-amber-700',
                                'in_progress' => 'bg-indigo-100 text-indigo-700',
                                'resolved' => 'bg-emerald-100 text-emerald-700',
                                default => 'bg-gray-100 text-gray-600'
                            };
                        ?>"><?= str_replace('_', ' ', $t['status']) ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-[10px] font-black uppercase tracking-widest <?php
                            echo match($t['priority']) {
                                'critical' => 'text-red-500',
                                'high' => 'text-orange-500',
                                default => 'text-blue-500'
                            };
                        ?>"><?= $t['priority'] ?></span>
                    </td>
                    <td class="px-6 py-4 text-xs text-gray-400 font-medium"><?= date('d M Y', strtotime($t['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>