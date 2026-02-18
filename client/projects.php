<?php
/**
 * IFMS - Client: My Projects
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('client');

$db = getDB();
$user = getCurrentUser();
$userId = $user['id'];
$pageTitle = 'My Projects';

$clientUser = $db->prepare("SELECT organization_id FROM client_users WHERE user_id = ?");
$clientUser->execute([$userId]);
$orgId = $clientUser->fetchColumn();

$projects = $db->query("
    SELECT p.*, 
        (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id) AS task_count,
        (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id AND t.status = 'completed') AS done_tasks
    FROM projects p WHERE p.organization_id = {$orgId}
    ORDER BY p.updated_at DESC
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="mb-8">
    <h2 class="text-2xl font-black text-gray-900 tracking-tight">Project Portfolio</h2>
    <p class="text-sm text-gray-500 font-medium">Detailed overview of all ongoing and past projects.</p>
</div>

<div class="space-y-8">
    <?php foreach ($projects as $proj): ?>
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 hover:shadow-xl transition-all group">
        <div class="flex flex-col lg:flex-row lg:items-center gap-8">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-4">
                    <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-full text-[10px] font-black uppercase tracking-widest"><?= $proj['status'] ?></span>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Due <?= date('M d, Y', strtotime($proj['end_date'])) ?></span>
                </div>
                <h3 class="text-2xl font-black text-gray-900 group-hover:text-indigo-600 transition-colors mb-4"><?= htmlspecialchars($proj['title']) ?></h3>
                <p class="text-gray-500 font-medium leading-relaxed mb-6"><?= htmlspecialchars($proj['description']) ?></p>
                
                <div class="flex flex-wrap gap-4">
                    <div class="px-4 py-2 bg-slate-50 rounded-xl">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Budget</p>
                        <p class="text-sm font-black text-gray-900">₹<?= number_format($proj['estimated_budget']) ?></p>
                    </div>
                    <div class="px-4 py-2 bg-slate-50 rounded-xl">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Tasks</p>
                        <p class="text-sm font-black text-gray-900"><?= $proj['done_tasks'] ?>/<?= $proj['task_count'] ?></p>
                    </div>
                </div>
            </div>
            
            <div class="w-full lg:w-48 flex flex-col items-center justify-center p-8 bg-indigo-600 rounded-3xl text-white shadow-lg shadow-indigo-500/20">
                <span class="text-[10px] font-black uppercase tracking-[0.2em] opacity-80 mb-2">Progress</span>
                <span class="text-4xl font-black"><?= $proj['progress_percentage'] ?>%</span>
                <div class="mt-4 w-full bg-white/20 h-1.5 rounded-full overflow-hidden">
                    <div class="h-full bg-white rounded-full" style="width: <?= $proj['progress_percentage'] ?>%"></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>