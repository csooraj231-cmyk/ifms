<?php
/**
 * IFMS - Client Dashboard
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('client');

$db = getDB();
$user = getCurrentUser();
$userId = $user['id'];
$pageTitle = 'Client Dashboard';

// Get client user and org
$clientUser = $db->prepare("SELECT * FROM client_users WHERE user_id = ?");
$clientUser->execute([$userId]);
$clientInfo = $clientUser->fetch();
$orgId = $clientInfo['organization_id'] ?? 0;

$org = $db->query("SELECT * FROM organizations WHERE id = {$orgId}")->fetch();

// Client's projects
$projects = $db->query("
    SELECT p.*, 
        (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id) AS task_count,
        (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id AND t.status = 'completed') AS done_tasks,
        (SELECT COUNT(*) FROM milestones m WHERE m.project_id = p.id) AS milestone_count
    FROM projects p WHERE p.organization_id = {$orgId}
    ORDER BY p.updated_at DESC
")->fetchAll();

// Active tickets
$tickets = $db->query("
    SELECT st.*, p.title AS project_title 
    FROM support_tickets st 
    LEFT JOIN projects p ON st.project_id = p.id 
    WHERE st.created_by = {$userId}
    ORDER BY st.created_at DESC LIMIT 5
")->fetchAll();

// Recent invoices
$invoices = $db->query("
    SELECT * FROM invoices WHERE organization_id = {$orgId} ORDER BY created_at DESC LIMIT 5
")->fetchAll();

$totalInvoiced = $db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM invoices WHERE organization_id = {$orgId} AND type = 'invoice'")->fetch()['total'];
$totalPaid = $db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM invoices WHERE organization_id = {$orgId} AND type = 'invoice' AND status = 'paid'")->fetch()['total'];

include __DIR__ . '/../includes/header.php';
?>

<!-- Welcome -->
<div class="bg-gradient-to-r from-violet-600 via-purple-600 to-indigo-700 rounded-2xl p-6 mb-6 text-white relative overflow-hidden">
    <div class="absolute bottom-0 right-0 w-72 h-72 bg-white/5 rounded-full translate-y-1/2 translate-x-1/3"></div>
    <h2 class="text-2xl font-bold text-white relative z-10">Welcome, <?= htmlspecialchars(explode(' ', $user['full_name'])[0]) ?>!</h2>
    <p class="text-violet-200 mt-1 relative z-10"><?= htmlspecialchars($org['name'] ?? 'Organization') ?> � Client Portal</p>
</div>

<!-- KPIs -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm text-center">
        <p class="text-2xl font-bold text-indigo-600"><?= count($projects) ?></p><p class="text-xs text-gray-500 font-medium uppercase tracking-tighter">Active Projects</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm text-center">
        <p class="text-2xl font-bold text-amber-600"><?= count(array_filter($tickets, fn($t) => $t['status'] !== 'closed')) ?></p><p class="text-xs text-gray-500 font-medium uppercase tracking-tighter">Open Tickets</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm text-center">
        <p class="text-2xl font-bold text-emerald-600">₹<?= number_format($totalPaid) ?></p><p class="text-xs text-gray-500 font-medium uppercase tracking-tighter">Paid Amount</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm text-center">
        <p class="text-2xl font-bold text-gray-900">₹<?= number_format($totalInvoiced) ?></p><p class="text-xs text-gray-500 font-medium uppercase tracking-tighter">Total Invoiced</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Projects (2 cols) -->
    <div class="lg:col-span-2 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-900">?? Your Projects</h3>
            <a href="/ifms/client/projects.php" class="text-xs font-semibold text-indigo-600 hover:underline transition-all">View All ?</a>
        </div>
        <?php foreach ($projects as $proj): ?>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-all">
            <div class="flex items-start justify-between mb-3">
                <div><h4 class="text-sm font-bold text-gray-900"><?= htmlspecialchars($proj['title']) ?></h4>
                <p class="text-xs text-gray-500 mt-0.5"><?= ucfirst($proj['priority']) ?> Priority � <?= ucfirst(str_replace('_',' ',$proj['status'])) ?></p></div>
                <span class="text-sm font-bold text-indigo-600"><?= $proj['progress_percentage'] ?>%</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2 mb-3"><div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-2 rounded-full" style="width: <?= $proj['progress_percentage'] ?>%"></div></div>
            <div class="flex gap-4 text-[10px] text-gray-400 font-bold uppercase tracking-wider">
                <span><?= $proj['task_count'] ?> tasks</span>
                <span><?= $proj['done_tasks'] ?> completed</span>
                <span><?= $proj['milestone_count'] ?> milestones</span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Right Column -->
    <div class="space-y-6">
        <!-- Recent Tickets -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-50 flex justify-between bg-gray-50/50">
                <h3 class="text-sm font-bold text-gray-900">?? Support Tickets</h3>
                <a href="/ifms/client/tickets.php" class="text-xs font-semibold text-indigo-600">All ?</a>
            </div>
            <div class="divide-y divide-gray-50">
                <?php foreach ($tickets as $t): ?>
                <div class="px-5 py-4 hover:bg-gray-50 transition-all">
                    <p class="text-sm font-bold text-gray-900"><?= htmlspecialchars($t['subject']) ?></p>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="text-[10px] font-black px-2 py-0.5 rounded-full <?php echo match($t['status']) { 'open' => 'bg-amber-100 text-amber-700', 'in_progress' => 'bg-indigo-100 text-indigo-700', 'resolved' => 'bg-emerald-100 text-emerald-700', default => 'bg-gray-100 text-gray-600' }; ?>"><?= strtoupper(str_replace('_',' ',$t['status'])) ?></span>
                        <span class="text-[10px] font-bold text-gray-400"><?= date('d M', strtotime($t['created_at'])) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($tickets)): ?><div class="px-5 py-8 text-center text-gray-400 text-sm font-medium">No tickets found</div><?php endif; ?>
            </div>
        </div>

        <!-- Recent Invoices -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-50 bg-gray-50/50"><h3 class="text-sm font-bold text-gray-900">?? Recent Invoices</h3></div>
            <div class="divide-y divide-gray-50">
                <?php foreach ($invoices as $inv): ?>
                <div class="px-5 py-4 flex justify-between hover:bg-gray-50 transition-all">
                    <div><p class="text-xs font-mono font-bold text-indigo-600"><?= $inv['invoice_number'] ?></p><p class="text-[10px] text-gray-400 font-bold mt-1"><?= date('d M Y', strtotime($inv['issue_date'])) ?></p></div>
                    <div class="text-right">
                        <p class="text-sm font-black text-gray-900">₹<?= number_format($inv['total_amount']) ?></p>
                        <span class="text-[10px] font-black uppercase mt-1 inline-block <?php echo match($inv['status']) { 'paid' => 'text-emerald-500', default => 'text-amber-500' }; ?>"><?= $inv['status'] ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>