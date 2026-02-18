<?php
/**
 * IFMS - Developer Projects (single, consolidated page)
 */
session_start();
require_once __DIR__ . '/../../config/auth.php';
requireLogin();
// allow developers and senior developers
if (!isDeveloper() && !isSeniorDeveloper()) {
    header('Location: /ifms/employee/index.php'); exit;
}

$db = getDB();
$user = getCurrentUser();

// Get current user's employee record
$userEmpStmt = $db->prepare("SELECT id FROM employees WHERE user_id = ? LIMIT 1");
$userEmpStmt->execute([$user['id']]);
$userEmp = $userEmpStmt->fetch(PDO::FETCH_ASSOC);
$empId = $userEmp['id'] ?? 0;

// Get assigned projects through project_team table (safely)
$projects = [];
$myTasks = [];
if ($empId) {
    $projects = $db->query(
        "SELECT p.*, o.name as client_name,
            COUNT(DISTINCT t.id) as task_count,
            COUNT(DISTINCT CASE WHEN t.status = 'completed' THEN t.id END) as completed_tasks,
            COUNT(DISTINCT ta.id) as assigned_count
         FROM projects p
         LEFT JOIN organizations o ON p.organization_id = o.id
         LEFT JOIN project_team pt ON p.id = pt.project_id AND pt.employee_id = " . intval($empId) . "
         LEFT JOIN tasks t ON p.id = t.project_id
         LEFT JOIN task_assignments ta ON t.id = ta.task_id AND ta.employee_id = " . intval($empId) . "
         WHERE pt.project_id IS NOT NULL OR p.status = 'active'
         GROUP BY p.id
         ORDER BY p.priority DESC, p.start_date DESC"
    )->fetchAll(PDO::FETCH_ASSOC);

    $myTasks = $db->query(
        "SELECT t.*, p.title as project_name, ta.status as task_status
         FROM tasks t
         JOIN projects p ON t.project_id = p.id
         LEFT JOIN task_assignments ta ON t.id = ta.task_id
         WHERE ta.employee_id = " . intval($empId) . "
         ORDER BY t.priority DESC, t.due_date ASC
         LIMIT 10"
    )->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects - IFMS</title>
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
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">My Projects</h1>
                    <p class="text-gray-600 mt-2">View and manage your assigned projects</p>
                </div>

                <!-- Statistics -->
                <div class="grid grid-cols-3 gap-4 mb-8">
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                        <p class="text-sm font-bold text-gray-500 mb-2">Total Projects</p>
                        <p class="text-3xl font-bold text-indigo-600"><?= count($projects) ?></p>
                    </div>
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                        <p class="text-sm font-bold text-gray-500 mb-2">Active Tasks</p>
                        <p class="text-3xl font-bold text-blue-600"><?= count(array_filter($myTasks, fn($t) => $t['task_status'] !== 'completed')) ?></p>
                    </div>
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                        <p class="text-sm font-bold text-gray-500 mb-2">Completed Tasks</p>
                        <p class="text-3xl font-bold text-green-600"><?= count(array_filter($myTasks, fn($t) => $t['task_status'] === 'completed')) ?></p>
                    </div>
                </div>

                <!-- Projects Section -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Projects</h2>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <?php foreach ($projects as $project): ?>
                        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($project['title']) ?></h3>
                                    <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($project['client_name'] ?? 'Internal Project') ?></p>
                                </div>
                                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-bold">
                                    <?= ucfirst($project['status']) ?>
                                </span>
                            </div>

                            <?php if ($project['description']): ?>
                            <p class="text-sm text-gray-600 mb-4"><?= htmlspecialchars(substr($project['description'], 0, 100)) ?>...</p>
                            <?php endif; ?>

                            <!-- Progress Bar -->
                            <?php 
                            $progress = $project['task_count'] > 0 ? ($project['completed_tasks'] / $project['task_count'] * 100) : 0;
                            ?>
                            <div class="mb-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs font-bold text-gray-600">Progress</span>
                                    <span class="text-xs font-bold text-gray-900"><?= round($progress) ?>%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-indigo-600 h-2 rounded-full" style="width: <?= $progress ?>%"></div>
                                </div>
                            </div>

                            <!-- Stats -->
                            <div class="grid grid-cols-2 gap-4 mb-4 pb-4 border-b border-gray-100">
                                <div>
                                    <p class="text-xs text-gray-500 font-bold">Tasks Assigned</p>
                                    <p class="text-lg font-bold text-gray-900"><?= $project['assigned_count'] ?></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-bold">Total Tasks</p>
                                    <p class="text-lg font-bold text-gray-900"><?= $project['task_count'] ?></p>
                                </div>
                            </div>

                            <!-- Dates -->
                            <div class="flex items-center justify-between text-xs text-gray-600 mb-4">
                                <span><strong>Start:</strong> <?= $project['start_date'] ? date('M d, Y', strtotime($project['start_date'])) : 'N/A' ?></span>
                                <span><strong>End:</strong> <?= $project['end_date'] ? date('M d, Y', strtotime($project['end_date'])) : 'N/A' ?></span>
                            </div>

                            <a href="#" onclick="viewProject(<?= $project['id'] ?>)" class="btn-primary w-full px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all inline-block text-center">
                                View Details
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (empty($projects)): ?>
                    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 text-center">
                        <p class="text-gray-600">No projects assigned yet.</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- My Tasks Section -->
                <div class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">My Tasks</h2>
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-100">
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Task</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Project</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Due Date</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Status</th>
                                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach ($myTasks as $task): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 font-bold text-gray-900"><?= htmlspecialchars(substr($task['title'], 0, 50)) ?></td>
                                        <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($task['project_name']) ?></td>
                                        <td class="px-6 py-4 text-gray-600">
                                            <?= $task['due_date'] ? date('M d, Y', strtotime($task['due_date'])) : 'No date' ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 bg-<?= $task['task_status'] === 'completed' ? 'green' : 'blue' ?>-100 text-<?= $task['task_status'] === 'completed' ? 'green' : 'blue' ?>-700 rounded-full text-xs font-bold">
                                                <?= ucfirst($task['task_status'] ?? 'pending') ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="#" onclick="updateTaskStatus(<?= $task['id'] ?>)" class="text-indigo-600 hover:text-indigo-700 font-bold">Update</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Task Status Modal -->
    <div id="task-status-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="modal-content bg-white rounded-3xl max-w-md w-full shadow-2xl">
            <div class="sticky top-0 bg-white border-b border-gray-100 p-6 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900">Update Task Status</h2>
                <button onclick="closeModal('task-status-modal')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <form id="task-status-form" class="p-6 space-y-4">
                <input type="hidden" name="task_id" id="task_id">
                
                <div>
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">New Status</label>
                    <select name="status" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                        <option value="">Select Status</option>
                        <option value="pending">Pending</option>
                        <option value="in-progress">In Progress</option>
                        <option value="review">Under Review</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>

                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="closeModal('task-status-modal')" class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-200 transition-all">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function updateTaskStatus(taskId) {
        document.getElementById('task_id').value = taskId;
        openModal('task-status-modal');
    }

    document.getElementById('task-status-form').onsubmit = async (e) => {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(e.target));
        try {
            const res = await fetch('/ifms/api/tasks.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'update_status', ...data })
            });
            const json = await res.json();
            if (json.success) {
                showToast('Task updated successfully!');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(json.error || 'Error updating task', 'error');
            }
        } catch (err) {
            showToast('Error updating task', 'error');
        }
    };

    function viewProject(projectId) {
        showToast('Opening project details...');
        // In a real implementation, navigate to project detail page
    }
    </script>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>
