<?php
/**
 * IFMS - Employee: My Projects
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('employee');

$db = getDB();
$user = getCurrentUser();
$empId = $user['employee_id'];
$pageTitle = 'My Projects';

$projects = $db->query("
    SELECT p.*, pt.role,
        (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id) AS total_tasks,
        (SELECT COUNT(*) FROM milestones m WHERE m.project_id = p.id) AS milestone_cnt
    FROM projects p
    JOIN project_team pt ON p.id = pt.project_id
    WHERE pt.employee_id = {$empId}
    ORDER BY p.progress_percentage DESC
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="mb-8">
    <h2 class="text-2xl font-black text-gray-900 tracking-tight">Assigned Projects</h2>
    <p class="text-sm text-gray-500 font-medium">Browse and track projects you are a part of.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <?php foreach ($projects as $proj): ?>
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 hover:shadow-xl transition-all group">
        <div class="flex items-start justify-between mb-6">
            <div>
                <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-full text-[10px] font-black uppercase tracking-widest"><?= $proj['role'] ?></span>
                <h3 class="text-xl font-black text-gray-900 mt-3 group-hover:text-indigo-600 transition-colors"><?= htmlspecialchars($proj['title']) ?></h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-xs font-black text-gray-400">
                <?= $proj['progress_percentage'] ?>%
            </div>
        </div>

        <p class="text-sm text-gray-500 font-medium leading-relaxed mb-6 line-clamp-2"><?= htmlspecialchars($proj['description']) ?></p>

        <div class="w-full bg-slate-50 rounded-full h-2 mb-8 overflow-hidden">
            <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full" style="width: <?= $proj['progress_percentage'] ?>%"></div>
        </div>

        <div class="grid grid-cols-2 gap-4 pt-6 border-t border-gray-50">
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Tasks</p>
                <p class="text-sm font-black text-gray-900"><?= $proj['total_tasks'] ?></p>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Milestones</p>
                <p class="text-sm font-black text-gray-900"><?= $proj['milestone_cnt'] ?></p>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>