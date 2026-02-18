<?php
/**
 * IFMS - Senior Developer Team Management
 * Senior developers can manage their junior developers and team tasks
 */
session_start();
require_once __DIR__ . '/../../config/auth.php';
requireLogin();
requireSeniorDeveloperAccess();

$db = getDB();
$user = getCurrentUser();

// Get current user's employee record
$userEmp = $db->prepare("SELECT id, employee_code FROM employees WHERE user_id = ?")->execute([$user['id']])->fetch();
$empId = $userEmp['id'] ?? 0;

// Get junior developers assigned to this senior developer
$juniors = $db->query("
    SELECT 
        e.id,
        e.employee_code,
        u.id as user_id,
        u.full_name,
        u.email,
        u.phone,
        COUNT(DISTINCT t.id) as assigned_tasks,
        COUNT(DISTINCT CASE WHEN t.status = 'completed' THEN t.id END) as completed_tasks
    FROM employees e
    JOIN users u ON e.user_id = u.id
    LEFT JOIN task_assignments ta ON e.id = ta.employee_id
    LEFT JOIN tasks t ON ta.task_id = t.id
    WHERE e.senior_developer_id = $empId AND e.is_active = 1
    GROUP BY e.id
    ORDER BY u.full_name
")->fetchAll();

// Get team tasks
$teamTasks = $db->query("
    SELECT 
        t.*,
        p.title as project_name,
        e.id as emp_id,
        u.full_name as assigned_to,
        ta.status as task_status
    FROM tasks t
    JOIN projects p ON t.project_id = p.id
    LEFT JOIN task_assignments ta ON t.id = ta.task_id
    LEFT JOIN employees e ON ta.employee_id = e.id
    LEFT JOIN users u ON e.user_id = u.id
    WHERE e.senior_developer_id = $empId
    ORDER BY t.priority DESC, t.due_date ASC
")->fetchAll();

// Get projects managed by this senior developer (where tasks have junior developers assigned)
$projects = $db->query("
    SELECT DISTINCT
        p.*,
        COUNT(DISTINCT t.id) as task_count,
        COUNT(DISTINCT CASE WHEN t.status = 'completed' THEN t.id END) as completed_tasks
    FROM projects p
    JOIN tasks t ON p.id = t.project_id
    JOIN task_assignments ta ON t.id = ta.task_id
    JOIN employees e ON ta.employee_id = e.id
    WHERE e.senior_developer_id = $empId
    GROUP BY p.id
    ORDER BY p.priority DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Management - IFMS</title>
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
                    <h1 class="text-3xl font-bold text-gray-900">Team Management</h1>
                    <p class="text-gray-600 mt-2">Manage your junior developers and team tasks</p>
                </div>

                <!-- Team Statistics -->
                <div class="grid grid-cols-3 gap-4 mb-8">
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                        <p class="text-sm font-bold text-gray-500 mb-2">Team Size</p>
                        <p class="text-3xl font-bold text-indigo-600"><?= count($juniors) ?></p>
                    </div>
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                        <p class="text-sm font-bold text-gray-500 mb-2">Team Tasks</p>
                        <p class="text-3xl font-bold text-blue-600"><?= count($teamTasks) ?></p>
                    </div>
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                        <p class="text-sm font-bold text-gray-500 mb-2">Team Completion</p>
                        <p class="text-3xl font-bold text-green-600">
                            <?= count($teamTasks) > 0 ? round(count(array_filter($teamTasks, fn($t) => $t['task_status'] === 'completed')) / count($teamTasks) * 100) : 0 ?>%
                        </p>
                    </div>
                </div>

                <!-- Junior Developers Section -->
                <div class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Junior Developers</h2>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <?php foreach ($juniors as $junior): ?>
                        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($junior['full_name']) ?></h3>
                                    <p class="text-sm text-gray-500"><?= htmlspecialchars($junior['employee_code']) ?></p>
                                </div>
                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">Active</span>
                            </div>

                            <div class="space-y-3 mb-4 pb-4 border-b border-gray-100">
                                <p class="text-sm">
                                    <span class="font-bold text-gray-700">Email:</span>
                                    <span class="text-gray-600"><?= htmlspecialchars($junior['email']) ?></span>
                                </p>
                                <p class="text-sm">
                                    <span class="font-bold text-gray-700">Phone:</span>
                                    <span class="text-gray-600"><?= htmlspecialchars($junior['phone'] ?? 'N/A') ?></span>
                                </p>
                            </div>

                            <!-- Task Stats -->
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div class="bg-blue-50 rounded-lg p-3">
                                    <p class="text-xs text-blue-600 font-bold">Assigned Tasks</p>
                                    <p class="text-lg font-bold text-blue-900"><?= $junior['assigned_tasks'] ?></p>
                                </div>
                                <div class="bg-green-50 rounded-lg p-3">
                                    <p class="text-xs text-green-600 font-bold">Completed</p>
                                    <p class="text-lg font-bold text-green-900"><?= $junior['completed_tasks'] ?></p>
                                </div>
                            </div>

                            <a href="#" onclick="viewJuniorTasks(<?= $junior['id'] ?>, '<?= htmlspecialchars($junior['full_name']) ?>')" class="btn-primary w-full px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all inline-block text-center">
                                View Tasks
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (empty($juniors)): ?>
                    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 text-center">
                        <p class="text-gray-600">No junior developers assigned to your team yet.</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Team Tasks Section -->
                <div class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Team Tasks</h2>
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-100">
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Task</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Project</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Assigned To</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Status</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Due Date</th>
                                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach ($teamTasks as $task): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 font-bold text-gray-900"><?= htmlspecialchars(substr($task['title'], 0, 40)) ?></td>
                                        <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($task['project_name']) ?></td>
                                        <td class="px-6 py-4">
                                            <span class="text-gray-900 font-bold"><?= htmlspecialchars($task['assigned_to'] ?? 'Unassigned') ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 bg-<?= $task['task_status'] === 'completed' ? 'green' : 'blue' ?>-100 text-<?= $task['task_status'] === 'completed' ? 'green' : 'blue' ?>-700 rounded-full text-xs font-bold">
                                                <?= ucfirst($task['task_status'] ?? 'pending') ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600">
                                            <?= $task['due_date'] ? date('M d, Y', strtotime($task['due_date'])) : 'No date' ?>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="#" onclick="reviewTask(<?= $task['id'] ?>)" class="text-indigo-600 hover:text-indigo-700 font-bold">Review</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (empty($teamTasks)): ?>
                        <div class="p-8 text-center">
                            <p class="text-gray-600">No team tasks found.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Managed Projects -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Managed Projects</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php foreach ($projects as $project): ?>
                        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                            <div class="flex items-start justify-between mb-4">
                                <h3 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($project['title']) ?></h3>
                                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-bold">
                                    <?= ucfirst($project['status']) ?>
                                </span>
                            </div>

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

                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600"><strong><?= $project['task_count'] ?></strong> tasks</p>
                                </div>
                                <p class="text-sm font-bold text-green-600"><?= $project['completed_tasks'] ?> completed</p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (empty($projects)): ?>
                    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 text-center">
                        <p class="text-gray-600">No team projects found.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    function viewJuniorTasks(juniorId, juniorName) {
        showToast(`Opening tasks for ${juniorName}...`);
        // In a real implementation, navigate to junior's tasks page
    }

    function reviewTask(taskId) {
        showToast(`Opening task review for task #${taskId}...`);
        // In a real implementation, open task review modal
    }
    </script>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>
