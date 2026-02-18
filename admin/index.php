<?php
/**
 * IFMS - Admin Dashboard
 * Central hub showing all key performance indicators
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('admin');

$db = getDB();
$pageTitle = 'Admin Dashboard';

// --- Fetch KPI Data --------------------------------------
$totalEmployees = $db->query("SELECT COUNT(*) FROM employees WHERE is_active = 1")->fetchColumn();
$totalProjects = $db->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$activeProjects = $db->query("SELECT COUNT(*) FROM projects WHERE status = 'in_progress'")->fetchColumn();
$totalOrgs = $db->query("SELECT COUNT(*) FROM organizations WHERE is_active = 1")->fetchColumn();
$totalRevenue = $db->query("SELECT COALESCE(SUM(total_amount), 0) FROM invoices WHERE type = 'invoice' AND status = 'paid'")->fetchColumn();
$openTickets = $db->query("SELECT COUNT(*) FROM support_tickets WHERE status IN ('open', 'in_progress')")->fetchColumn();

// Project status
$projectStats = $db->query("SELECT status, COUNT(*) as cnt FROM projects GROUP BY status")->fetchAll();

// Recent projects
$recentProjects = $db->query("
    SELECT p.*, o.name AS org_name 
    FROM projects p LEFT JOIN organizations o ON p.organization_id = o.id 
    ORDER BY p.updated_at DESC LIMIT 5
")->fetchAll();

// Recent tickets
$recentTickets = $db->query("
    SELECT st.*, u.full_name AS created_by_name, p.title AS project_title
    FROM support_tickets st
    LEFT JOIN users u ON st.created_by = u.id
    LEFT JOIN projects p ON st.project_id = p.id
    ORDER BY st.created_at DESC LIMIT 5
")->fetchAll();

// Upcoming holidays
$holidays = $db->query("SELECT * FROM holidays WHERE date >= CURDATE() ORDER BY date LIMIT 5")->fetchAll();

// Recent notices
$notices = $db->query("SELECT * FROM notices WHERE is_active = 1 ORDER BY created_at DESC LIMIT 3")->fetchAll();

// Recent employees
$recentEmployees = $db->query("
    SELECT e.*, u.full_name, u.email, d.name AS department_name
    FROM employees e
    LEFT JOIN users u ON e.user_id = u.id
    LEFT JOIN departments d ON e.department_id = d.id
    WHERE e.is_active = 1
    ORDER BY e.created_at DESC LIMIT 5
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-8">
    <h2 class="text-2xl font-bold text-gray-900">Dashboard</h2>
    <p class="text-sm text-gray-500 mt-1">Welcome back, <?= htmlspecialchars(explode(' ', getCurrentUser()['full_name'])[0]) ?>. Here's your overview.</p>
</div>

<!-- KPI Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-8">
    <!-- Total Employees -->
    <a href="/ifms/admin/employees.php" class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-all group cursor-pointer">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/25">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">View All</span>
        </div>
        <p class="text-3xl font-bold text-gray-900"><?= $totalEmployees ?></p>
        <p class="text-sm text-gray-500 mt-1">Total Employees</p>
    </a>

    <!-- Active Projects -->
    <a href="/ifms/admin/projects.php" class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-all group cursor-pointer">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/25">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-gray-900"><?= $activeProjects ?><span class="text-lg text-gray-400 font-medium">/<?= $totalProjects ?></span></p>
        <p class="text-sm text-gray-500 mt-1">Active Projects</p>
    </a>

    <!-- Revenue -->
    <a href="/ifms/admin/invoices.php" class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-all group cursor-pointer">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-amber-500/25">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-gray-900">₹<?= number_format($totalRevenue) ?></p>
        <p class="text-sm text-gray-500 mt-1">Total Revenue</p>
    </a>

    <!-- Open Tickets -->
    <a href="/ifms/admin/tickets.php" class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-all group cursor-pointer">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-500 to-pink-600 flex items-center justify-center shadow-lg shadow-red-500/25">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-gray-900"><?= $openTickets ?></p>
        <p class="text-sm text-gray-500 mt-1">Open Tickets</p>
    </a>
</div>

<!-- Main Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Recent Projects (2 cols) -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between p-5 border-b border-gray-50">
            <h3 class="text-base font-bold text-gray-900">Recent Projects</h3>
            <a href="/ifms/admin/projects.php" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">View All ?</a>
        </div>
        <div class="divide-y divide-gray-50">
            <?php foreach ($recentProjects as $proj): ?>
            <div class="px-5 py-3.5 hover:bg-gray-50/50 transition-colors">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <p class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($proj['title']) ?></p>
                        <p class="text-xs text-gray-500"><?= htmlspecialchars($proj['org_name'] ?? 'Internal') ?></p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?php
                        echo match($proj['status']) {
                            'in_progress' => 'bg-indigo-100 text-indigo-700',
                            'completed' => 'bg-emerald-100 text-emerald-700',
                            'pending' => 'bg-amber-100 text-amber-700',
                            default => 'bg-gray-100 text-gray-600'
                        };
                    ?>"><?= ucfirst(str_replace('_', ' ', $proj['status'])) ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-1.5 rounded-full" style="width: <?= $proj['progress_percentage'] ?>%"></div>
                    </div>
                    <span class="text-xs font-semibold text-gray-600"><?= $proj['progress_percentage'] ?>%</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Recent Employees -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm mb-8">
    <div class="flex items-center justify-between p-5 border-b border-gray-50">
        <h3 class="text-base font-bold text-gray-900">Recent Employees</h3>
        <a href="/ifms/admin/employees.php" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">View All</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead><tr class="text-left text-xs font-semibold text-gray-500 uppercase bg-gray-50/80">
                <th class="px-5 py-3">Name</th><th class="px-5 py-3">Email</th><th class="px-5 py-3">Department</th><th class="px-5 py-3">Status</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($recentEmployees as $emp): ?>
                <tr class="hover:bg-gray-50/50">
                    <td class="px-5 py-3 text-sm font-medium text-gray-900"><?= htmlspecialchars($emp['full_name'] ?? 'N/A') ?></td>
                    <td class="px-5 py-3 text-sm text-gray-600"><?= htmlspecialchars($emp['email'] ?? 'N/A') ?></td>
                    <td class="px-5 py-3 text-sm text-gray-600"><?= htmlspecialchars($emp['department_name'] ?? 'N/A') ?></td>
                    <td class="px-5 py-3"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $emp['is_active'] ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' ?>"><?= $emp['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Main Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Recent Projects (2 cols) -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between p-5 border-b border-gray-50">
            <h3 class="text-base font-bold text-gray-900">Recent Projects</h3>
            <a href="/ifms/admin/projects.php" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">View All ?</a>
        </div>
        <div class="divide-y divide-gray-50">
            <?php foreach ($recentProjects as $proj): ?>
            <div class="px-5 py-3.5 hover:bg-gray-50/50 transition-colors">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <p class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($proj['title']) ?></p>
                        <p class="text-xs text-gray-500"><?= htmlspecialchars($proj['org_name'] ?? 'Internal') ?></p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?php
                        echo match($proj['status']) {
                            'in_progress' => 'bg-indigo-100 text-indigo-700',
                            'completed' => 'bg-emerald-100 text-emerald-700',
                            'pending' => 'bg-amber-100 text-amber-700',
                            default => 'bg-gray-100 text-gray-600'
                        };
                    ?>"><?= ucfirst(str_replace('_', ' ', $proj['status'])) ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-1.5 rounded-full" style="width: <?= $proj['progress_percentage'] ?>%"></div>
                    </div>
                    <span class="text-xs font-semibold text-gray-600"><?= $proj['progress_percentage'] ?>%</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Right Column -->
    <div class="space-y-6">
        <!-- Upcoming Holidays -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="p-5 border-b border-gray-50">
                <h3 class="text-base font-bold text-gray-900">🗓️ Upcoming Holidays</h3>
            </div>
            <div class="divide-y divide-gray-50">
                <?php foreach ($holidays as $h): ?>
                <div class="px-5 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($h['title']) ?></p>
                        <p class="text-xs text-gray-500"><?= date('d M Y', strtotime($h['date'])) ?></p>
                    </div>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full <?= $h['type'] === 'national' ? 'bg-red-50 text-red-600' : 'bg-indigo-50 text-indigo-600' ?>"><?= ucfirst($h['type']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Notices -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="p-5 border-b border-gray-50">
                <h3 class="text-base font-bold text-gray-900">?? Notices</h3>
            </div>
            <div class="divide-y divide-gray-50">
                <?php foreach ($notices as $n): ?>
                <div class="px-5 py-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full <?= $n['type'] === 'urgent' ? 'bg-red-500' : ($n['type'] === 'important' ? 'bg-amber-500' : 'bg-blue-500') ?>"></span>
                        <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($n['title']) ?></p>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-4 line-clamp-2"><?= htmlspecialchars(substr($n['content'], 0, 80)) ?>...</p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Tickets -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm mb-8">
    <div class="flex items-center justify-between p-5 border-b border-gray-50">
        <h3 class="text-base font-bold text-gray-900">?? Recent Support Tickets</h3>
        <a href="/ifms/admin/tickets.php" class="text-xs font-semibold text-indigo-600">View All ?</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead><tr class="text-left text-xs font-semibold text-gray-500 uppercase bg-gray-50/80">
                <th class="px-5 py-3">Ticket</th><th class="px-5 py-3">Subject</th><th class="px-5 py-3">Created By</th><th class="px-5 py-3">Priority</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Date</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($recentTickets as $t): ?>
                <tr class="hover:bg-gray-50/50">
                    <td class="px-5 py-3 text-sm font-mono font-bold text-indigo-600"><?= $t['ticket_number'] ?></td>
                    <td class="px-5 py-3 text-sm font-medium text-gray-900"><?= htmlspecialchars($t['subject']) ?></td>
                    <td class="px-5 py-3 text-sm text-gray-600"><?= htmlspecialchars($t['created_by_name']) ?></td>
                    <td class="px-5 py-3"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold <?php echo match($t['priority']) { 'critical' => 'bg-red-100 text-red-700', 'high' => 'bg-orange-100 text-orange-700', default => 'bg-blue-100 text-blue-700' }; ?>"><?= ucfirst($t['priority']) ?></span></td>
                    <td class="px-5 py-3"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold <?php echo match($t['status']) { 'open' => 'bg-amber-100 text-amber-700', 'in_progress' => 'bg-indigo-100 text-indigo-700', 'resolved' => 'bg-emerald-100 text-emerald-700', default => 'bg-gray-100 text-gray-600' }; ?>"><?= ucfirst(str_replace('_',' ',$t['status'])) ?></span></td>
                    <td class="px-5 py-3 text-xs text-gray-500"><?= date('d M Y', strtotime($t['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/header.php'; ?>