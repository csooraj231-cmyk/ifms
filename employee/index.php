<?php
/**
 * IFMS - Employee Dashboard
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('employee');

$db = getDB();
$user = getCurrentUser();
$empId = $user['employee_id'];
$pageTitle = 'Employee Dashboard';

// Attendance for today
$today = date('Y-m-d');
$attendance = $db->query("SELECT * FROM attendance WHERE employee_id = {$empId} AND date = '{$today}'")->fetch();

// Tasks stats
$taskStats = $db->query("SELECT status, COUNT(*) as cnt FROM task_assignments WHERE employee_id = {$empId} GROUP BY status")->fetchAll();
$tasksCount = array_column($taskStats, 'cnt', 'status');

// Active tasks
$activeTasks = $db->query("
    SELECT t.*, p.title AS project_title, ta.status AS assignment_status
    FROM tasks t
    JOIN task_assignments ta ON t.id = ta.task_id
    LEFT JOIN projects p ON t.project_id = p.id
    WHERE ta.employee_id = {$empId} AND ta.status IN ('assigned', 'accepted', 'in_progress')
    ORDER BY t.priority = 'critical' DESC, t.due_date ASC
    LIMIT 5
")->fetchAll();

// Overdue tasks
$overdueCount = $db->query("
    SELECT COUNT(*) FROM tasks t 
    JOIN task_assignments ta ON t.id = ta.task_id 
    WHERE ta.employee_id = {$empId} AND ta.status != 'completed' AND t.due_date < CURDATE()
")->fetchColumn();

// Project progress
$projects = $db->query("
    SELECT p.*, pt.role FROM projects p
    JOIN project_team pt ON p.id = pt.project_id
    WHERE pt.employee_id = {$empId}
    ORDER BY p.progress_percentage DESC
")->fetchAll();

// Notices & Holidays
$notices = $db->query("SELECT * FROM notices WHERE is_active = 1 ORDER BY created_at DESC LIMIT 3")->fetchAll();
$holidays = $db->query("SELECT * FROM holidays WHERE date >= CURDATE() ORDER BY date LIMIT 3")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<!-- Welcome Banner -->
<div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-violet-700 rounded-3xl p-8 mb-8 text-white relative overflow-hidden shadow-xl shadow-indigo-500/20">
    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/3"></div>
    <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/4"></div>
    
    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-3xl font-black tracking-tight">Hello, <?= htmlspecialchars(explode(' ', $user['full_name'])[0]) ?>!</h2>
            <p class="text-indigo-100 mt-2 font-medium opacity-90"><?= $user['designation'] ?> · <?= $user['department'] ?></p>
        </div>
        <div class="flex items-center gap-4 bg-white/10 backdrop-blur-md px-6 py-4 rounded-2xl border border-white/20">
            <div class="text-right">
                <p class="text-[10px] font-bold uppercase tracking-widest opacity-70">Attendance Today</p>
                <p class="text-lg font-bold">
                    <?php if ($attendance): ?>
                        <span class="text-emerald-300"><?= ucfirst($attendance['status']) ?></span>
                    <?php else: ?>
                        <span class="text-amber-300">Not Marked</span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        <p class="text-sm font-bold text-gray-400 uppercase tracking-widest leading-none mb-3">Tasks Todo</p>
        <p class="text-3xl font-black text-gray-900"><?= intval($tasksCount['assigned'] ?? 0) + intval($tasksCount['accepted'] ?? 0) ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        <p class="text-sm font-bold text-gray-400 uppercase tracking-widest leading-none mb-3">In Progress</p>
        <p class="text-3xl font-black text-indigo-600"><?= intval($tasksCount['in_progress'] ?? 0) ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        <p class="text-sm font-bold text-gray-400 uppercase tracking-widest leading-none mb-3">Completed</p>
        <p class="text-3xl font-black text-emerald-600"><?= intval($tasksCount['completed'] ?? 0) ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        <p class="text-sm font-bold text-gray-400 uppercase tracking-widest leading-none mb-3">Tasks Overdue</p>
        <p class="text-3xl font-black <?= $overdueCount > 0 ? 'text-red-500' : 'text-gray-900' ?>"><?= $overdueCount ?></p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Active Tasks -->
    <div class="lg:col-span-2 space-y-6">
        <div class="flex items-center justify-between">
            <h3 class="text-xl font-black text-gray-900 tracking-tight">?? My Active Tasks</h3>
            <a href="/ifms/employee/tasks.php" class="text-sm font-bold text-indigo-600 hover:underline">View Kanban Box ?</a>
        </div>
        
        <div class="space-y-4">
            <?php foreach ($activeTasks as $task): ?>
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all group">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-[10px] font-black uppercase tracking-widest text-indigo-500"><?= htmlspecialchars($task['project_title']) ?></span>
                            <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase"><?= $task['priority'] ?> Priority</span>
                        </div>
                        <h4 class="text-base font-bold text-gray-900"><?= htmlspecialchars($task['title']) ?></h4>
                    </div>
                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter <?php echo match($task['assignment_status']) { 'in_progress' => 'bg-indigo-50 text-indigo-600', 'accepted' => 'bg-emerald-50 text-emerald-600', default => 'bg-amber-50 text-amber-600' }; ?>">
                        <?= str_replace('_', ' ', $task['assignment_status']) ?>
                    </span>
                </div>
                <div class="flex items-center justify-between pt-4 border-t border-gray-50 mt-4">
                    <div class="flex items-center gap-2 text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span class="text-xs font-bold"><?= date('d M Y', strtotime($task['due_date'])) ?></span>
                    </div>
                    <button class="text-xs font-bold text-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity">Update Status</button>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($activeTasks)): ?>
                <div class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl p-12 text-center">
                    <p class="text-gray-400 font-bold">No active tasks assigned! Chill time? ?</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="space-y-8">
        <!-- Project Progress -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <h3 class="text-lg font-black text-gray-900 mb-6 tracking-tight">?? My Projects</h3>
            <div class="space-y-6">
                <?php foreach ($projects as $proj): ?>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-bold text-gray-800"><?= htmlspecialchars($proj['title']) ?></p>
                        <span class="text-[10px] font-black text-indigo-600"><?= $proj['progress_percentage'] ?>%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-1.5 rounded-full" style="width: <?= $proj['progress_percentage'] ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Notices -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <h3 class="text-lg font-black text-gray-900 mb-6 tracking-tight">?? Notices</h3>
            <div class="space-y-4">
                <?php foreach ($notices as $note): ?>
                <div class="flex gap-4">
                    <div class="w-1.5 h-10 rounded-full <?php echo match($note['type']) { 'urgent' => 'bg-red-500', 'important' => 'bg-amber-500', default => 'bg-indigo-500' }; ?>"></div>
                    <div>
                        <p class="text-xs font-bold text-gray-800"><?= htmlspecialchars($note['title']) ?></p>
                        <p class="text-[10px] text-gray-400 font-medium mt-1"><?= date('d M', strtotime($note['created_at'])) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Holidays -->
        <div class="bg-indigo-600 p-6 rounded-2xl shadow-lg shadow-indigo-500/20 text-white">
            <h3 class="text-lg font-black mb-6 tracking-tight">?? Next Holidays</h3>
            <div class="space-y-4">
                <?php foreach ($holidays as $h): ?>
                <div class="flex items-center justify-between bg-white/10 p-3 rounded-xl">
                    <span class="text-xs font-bold"><?= htmlspecialchars($h['title']) ?></span>
                    <span class="text-[10px] font-black opacity-80"><?= date('d M', strtotime($h['date'])) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>