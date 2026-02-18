<?php
/**
 * IFMS - Employee Tasks (Kanban Board)
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('employee');

$db = getDB();
$user = getCurrentUser();
$empId = $user['employee_id'];
$pageTitle = 'Task Kanban';

$tasks = $db->query("
    SELECT t.*, p.title AS project_title, ta.status AS assignment_status, ta.id AS assignment_id
    FROM tasks t
    JOIN task_assignments ta ON t.id = ta.task_id
    LEFT JOIN projects p ON t.project_id = p.id
    WHERE ta.employee_id = {$empId}
    ORDER BY t.priority = 'critical' DESC, t.due_date ASC
")->fetchAll();

$columns = [
    'assigned' => ['label' => 'Backlog', 'color' => 'bg-slate-400'],
    'accepted' => ['label' => 'Todo', 'color' => 'bg-amber-400'],
    'in_progress' => ['label' => 'In Progress', 'color' => 'bg-indigo-500'],
    'completed' => ['label' => 'Done', 'color' => 'bg-emerald-500']
];

include __DIR__ . '/../includes/header.php';
?>

<div class="mb-8 overflow-x-auto pb-4">
    <div class="flex gap-6 min-w-[1000px] h-[calc(100vh-250px)]">
        <?php foreach ($columns as $status => $meta): 
            $colTasks = array_filter($tasks, fn($t) => $t['assignment_status'] === $status);
        ?>
        <div class="flex-1 bg-gray-100/50 rounded-3xl p-4 flex flex-col">
            <div class="flex items-center justify-between mb-4 px-2">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full <?= $meta['color'] ?>"></span>
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest"><?= $meta['label'] ?></h3>
                </div>
                <span class="text-xs font-black text-gray-400 bg-white px-2 py-0.5 rounded-lg border border-gray-100"><?= count($colTasks) ?></span>
            </div>
            
            <div class="flex-1 space-y-4 overflow-y-auto custom-scrollbar px-1">
                <?php foreach ($colTasks as $task): ?>
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl transition-all group relative cursor-grab active:cursor-grabbing border-l-4 <?php
                    echo match($task['priority']) {
                        'critical' => 'border-red-500',
                        'high' => 'border-orange-500',
                        'medium' => 'border-blue-500',
                        default => 'border-gray-200'
                    };
                ?>">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-[10px] font-black text-indigo-500 uppercase tracking-tight"><?= htmlspecialchars($task['project_title']) ?></span>
                    </div>
                    <h4 class="text-sm font-bold text-gray-900 leading-snug group-hover:text-indigo-600 transition-colors"><?= htmlspecialchars($task['title']) ?></h4>
                    <p class="text-[11px] text-gray-400 font-medium mt-2 line-clamp-2"><?= htmlspecialchars($task['description']) ?></p>
                    
                    <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-50">
                        <div class="flex items-center gap-2 text-gray-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span class="text-[10px] font-bold"><?= date('d M', strtotime($task['due_date'])) ?></span>
                        </div>
                        <div class="flex gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                            <?php if ($status !== 'completed'): ?>
                                <button onclick="updateTaskStatus(<?= $task['id'] ?>, 'completed')" class="p-1.5 hover:bg-emerald-50 text-emerald-500 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></button>
                            <?php endif; ?>
                            <button class="p-1.5 hover:bg-indigo-50 text-indigo-500 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
async function updateTaskStatus(taskId, status) {
    const res = await api('/ifms/api/tasks.php', 'POST', { action: 'update_status', task_id: taskId, status: status });
    if (res.success) { showToast('Task updated!'); setTimeout(() => location.reload(), 800); }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>