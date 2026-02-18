<?php
/**
 * IFMS - Admin: Project Detail Management
 * View and manage a single project with full CRUD for team, milestones, tasks, and tickets
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('admin');

$db = getDB();
$projectId = intval($_GET['id'] ?? 0);
if (!$projectId) {
    header('Location: /ifms/admin/projects.php');
    exit;
}

// Get project details
$project = $db->prepare("
    SELECT p.*, o.name AS org_name, o.id AS org_id, u.full_name AS created_by_name
    FROM projects p 
    LEFT JOIN organizations o ON p.organization_id = o.id
    LEFT JOIN users u ON p.created_by = u.id
    WHERE p.id = ?
")->fetchAll(PDO::FETCH_ASSOC);
$project = $project[0] ?? null;

if (!$project) {
    header('Location: /ifms/admin/projects.php');
    exit;
}

// Get team members
$stmt = $db->prepare("
    SELECT pt.*, e.id, e.user_id, u.full_name, u.email, e.designation, d.name AS department
    FROM project_team pt 
    JOIN employees e ON pt.employee_id = e.id 
    JOIN users u ON e.user_id = u.id 
    JOIN departments d ON e.department_id = d.id 
    WHERE pt.project_id = ?
    ORDER BY pt.role DESC, u.full_name ASC
");
$stmt->execute([$projectId]);
$team = $stmt->fetchAll();

// Get milestones
$milestones = $db->prepare("
    SELECT *, 
        (SELECT COUNT(*) FROM tasks WHERE milestone_id = milestones.id) AS task_count,
        (SELECT COUNT(*) FROM tasks WHERE milestone_id = milestones.id AND status = 'completed') AS completed_tasks
    FROM milestones
    WHERE project_id = ?
    ORDER BY due_date ASC, created_at DESC
");
$milestones->execute([$projectId]);
$milestoneList = $milestones->fetchAll();

// Get tasks
$tasks = $db->prepare("
    SELECT t.*, m.title AS milestone_title, u.full_name AS assigned_name
    FROM tasks t
    LEFT JOIN milestones m ON t.milestone_id = m.id
    LEFT JOIN users u ON t.created_by = u.id
    WHERE t.project_id = ?
    ORDER BY t.status ASC, t.due_date ASC
");
$tasks->execute([$projectId]);
$taskList = $tasks->fetchAll();

// Get support tickets
$tickets = $db->prepare("
    SELECT t.*, u.full_name AS creator_name, e.user_id AS assigned_user_id
    FROM support_tickets t
    LEFT JOIN users u ON t.created_by = u.id
    LEFT JOIN employees e ON t.assigned_to = e.id
    WHERE t.project_id = ?
    ORDER BY t.status DESC, t.created_at DESC
");
$tickets->execute([$projectId]);
$ticketList = $tickets->fetchAll();

$pageTitle = htmlspecialchars($project['title']) . ' - Project Details';
include __DIR__ . '/../includes/header.php';
?>

<!-- Back Button & Title -->
<div class="mb-8 flex items-center gap-4">
    <a href="/ifms/admin/projects.php" class="p-2 hover:bg-gray-100 rounded-lg transition-all">
        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <div>
        <h1 class="text-3xl font-black text-gray-900"><?= htmlspecialchars($project['title']) ?></h1>
        <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($project['org_name'] ?? 'Internal Project') ?> • <?= ucfirst($project['status']) ?></p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Project Details Card -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">Project Details</h2>
                <button onclick="openModal('edit-project-modal')" class="text-xs font-bold text-indigo-600 hover:text-indigo-700">
                    <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>Edit
                </button>
            </div>
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Status</p>
                        <p class="text-sm font-bold text-gray-900 mt-1"><?= ucfirst($project['status']) ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Priority</p>
                        <p class="text-sm font-bold text-gray-900 mt-1"><?= ucfirst($project['priority']) ?></p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Start Date</p>
                        <p class="text-sm font-bold text-gray-900 mt-1"><?= $project['start_date'] ? date('M d, Y', strtotime($project['start_date'])) : 'Not set' ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">End Date</p>
                        <p class="text-sm font-bold text-gray-900 mt-1"><?= $project['end_date'] ? date('M d, Y', strtotime($project['end_date'])) : 'Not set' ?></p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Budget</p>
                        <p class="text-sm font-bold text-gray-900 mt-1">$<?= number_format($project['estimated_budget'] ?? 0, 2) ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Spent</p>
                        <p class="text-sm font-bold text-gray-900 mt-1">$<?= number_format($project['actual_cost'] ?? 0, 2) ?></p>
                    </div>
                </div>
                
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase">Progress</p>
                    <div class="mt-2 flex items-center gap-3">
                        <div class="flex-1 h-3 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-500" style="width: <?= $project['progress_percentage'] ?>%"></div>
                        </div>
                        <span class="text-sm font-bold text-indigo-600"><?= $project['progress_percentage'] ?>%</span>
                    </div>
                </div>
                
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase">Description</p>
                    <p class="text-sm text-gray-600 mt-2 leading-relaxed"><?= nl2br(htmlspecialchars($project['description'] ?? 'No description')) ?></p>
                </div>
            </div>
        </div>

        <!-- Milestones Section -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">Milestones (<?= count($milestoneList) ?>)</h2>
                <button onclick="openModal('add-milestone-modal')" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>Add Milestone
                </button>
            </div>
            
            <?php if (empty($milestoneList)): ?>
                <div class="text-center py-8 text-gray-500">No milestones added yet.</div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($milestoneList as $m): ?>
                        <div class="border border-gray-100 rounded-xl p-4 hover:bg-gray-50 transition-all">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="font-bold text-gray-900"><?= htmlspecialchars($m['title']) ?></h3>
                                    <p class="text-xs text-gray-500 mt-1">Due: <?= date('M d, Y', strtotime($m['due_date'])) ?> • Status: <?= ucfirst($m['status']) ?></p>
                                    <p class="text-xs text-gray-500 mt-2"><?= $m['completed_tasks'] ?>/<?= $m['task_count'] ?> tasks completed</p>
                                </div>
                                <button onclick="editMilestone(<?= $m['id'] ?>)" class="text-xs font-bold text-indigo-600 hover:underline">Edit</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tasks Section -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">Tasks (<?= count($taskList) ?>)</h2>
                <button onclick="openModal('add-task-modal')" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>Add Task
                </button>
            </div>
            
            <?php if (empty($taskList)): ?>
                <div class="text-center py-8 text-gray-500">No tasks added yet.</div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($taskList as $t): ?>
                        <div class="border border-gray-100 rounded-xl p-4 hover:bg-gray-50 transition-all">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-1 text-[10px] font-bold <?php
                                            echo match($t['priority']) {
                                                'critical' => 'bg-red-50 text-red-600',
                                                'high' => 'bg-orange-50 text-orange-600',
                                                'medium' => 'bg-blue-50 text-blue-600',
                                                default => 'bg-gray-50 text-gray-600'
                                            };
                                        ?> rounded">
                                            <?= ucfirst($t['priority']) ?>
                                        </span>
                                        <h3 class="font-bold text-gray-900"><?= htmlspecialchars($t['title']) ?></h3>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Status: <?= ucfirst($t['status']) ?> • Due: <?= $t['due_date'] ? date('M d, Y', strtotime($t['due_date'])) : 'Not set' ?></p>
                                    <?php if ($t['milestone_title']): ?>
                                        <p class="text-xs text-indigo-600 mt-1">Milestone: <?= htmlspecialchars($t['milestone_title']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <button onclick="editTask(<?= $t['id'] ?>)" class="text-xs font-bold text-indigo-600 hover:underline">Edit</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Support Tickets Section -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">Support Tickets (<?= count($ticketList) ?>)</h2>
            </div>
            
            <?php if (empty($ticketList)): ?>
                <div class="text-center py-8 text-gray-500">No support tickets for this project.</div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($ticketList as $t): ?>
                        <div class="border border-gray-100 rounded-xl p-4 hover:bg-gray-50 transition-all">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-1 text-[10px] font-bold <?php
                                            echo match($t['status']) {
                                                'open' => 'bg-red-50 text-red-600',
                                                'in_progress' => 'bg-blue-50 text-blue-600',
                                                'resolved' => 'bg-emerald-50 text-emerald-600',
                                                'closed' => 'bg-gray-50 text-gray-600',
                                                default => 'bg-gray-50 text-gray-600'
                                            };
                                        ?> rounded">
                                            <?= ucfirst(str_replace('_', ' ', $t['status'])) ?>
                                        </span>
                                        <h3 class="font-bold text-gray-900"><?= htmlspecialchars($t['subject']) ?></h3>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">#<?= htmlspecialchars($t['ticket_number']) ?> • Priority: <?= ucfirst($t['priority']) ?> • Created: <?= date('M d, Y', strtotime($t['created_at'])) ?></p>
                                </div>
                                <a href="/ifms/admin/tickets.php?id=<?= $t['id'] ?>" class="text-xs font-bold text-indigo-600 hover:underline">View</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Column: Team Members -->
    <div class="space-y-6">
        <!-- Team Members Card -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">Team (<?= count($team) ?>)</h2>
                <button onclick="openModal('add-team-modal')" class="text-xs font-bold text-indigo-600 hover:text-indigo-700">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>Add
                </button>
            </div>
            
            <div class="space-y-3 max-h-96 overflow-y-auto">
                <?php if (empty($team)): ?>
                    <div class="text-center py-8">
                        <p class="text-gray-500">No team members yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($team as $member): ?>
                        <div class="border border-gray-100 rounded-lg p-3 hover:bg-gray-50 transition-all group">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs">
                                        <?= strtoupper(substr($member['full_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-900"><?= htmlspecialchars($member['full_name']) ?></p>
                                        <p class="text-[10px] text-gray-500"><?= htmlspecialchars($member['role']) ?></p>
                                    </div>
                                </div>
                                <button onclick="removeTeamMember(<?= $member['id'] ?>, <?= $member['project_id'] ?>, <?= $member['employee_id'] ?>)" class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-600 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-xs text-gray-500"><?= htmlspecialchars($member['email']) ?></p>
                            <p class="text-xs text-gray-500"><?= htmlspecialchars($member['designation'] ?? 'N/A') ?> • <?= htmlspecialchars($member['department']) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Project Stats -->
        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-3xl border border-indigo-100 p-6">
            <h3 class="font-bold text-gray-900 mb-4">Project Stats</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Team Members</span>
                    <span class="font-bold text-indigo-600"><?= count($team) ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Milestones</span>
                    <span class="font-bold text-indigo-600"><?= count($milestoneList) ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Tasks</span>
                    <span class="font-bold text-indigo-600"><?= count($taskList) ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Support Tickets</span>
                    <span class="font-bold text-indigo-600"><?= count($ticketList) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Project Modal -->
<div id="edit-project-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl max-h-screen overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-100 p-6 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Edit Project</h2>
            <button onclick="closeModal('edit-project-modal')" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form id="edit-project-form" class="p-6 space-y-4">
            <input type="hidden" name="project_id" value="<?= $projectId ?>">
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Title</label>
                <input type="text" name="title" value="<?= htmlspecialchars($project['title']) ?>" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Description</label>
                <textarea name="description" rows="4" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Status</label>
                    <select name="status" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                        <option value="pending" <?= $project['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="approved" <?= $project['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="in_progress" <?= $project['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="on_hold" <?= $project['status'] === 'on_hold' ? 'selected' : '' ?>>On Hold</option>
                        <option value="completed" <?= $project['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $project['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Priority</label>
                    <select name="priority" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                        <option value="low" <?= $project['priority'] === 'low' ? 'selected' : '' ?>>Low</option>
                        <option value="medium" <?= $project['priority'] === 'medium' ? 'selected' : '' ?>>Medium</option>
                        <option value="high" <?= $project['priority'] === 'high' ? 'selected' : '' ?>>High</option>
                        <option value="critical" <?= $project['priority'] === 'critical' ? 'selected' : '' ?>>Critical</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Start Date</label>
                    <input type="date" name="start_date" value="<?= $project['start_date'] ?>" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                </div>
                <div>
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">End Date</label>
                    <input type="date" name="end_date" value="<?= $project['end_date'] ?>" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                </div>
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Estimated Budget</label>
                <input type="number" name="estimated_budget" step="0.01" min="0" value="<?= $project['estimated_budget'] ?>" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeModal('edit-project-modal')" class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-200 transition-all">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Team Member Modal -->
<div id="add-team-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl max-h-screen overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-100 p-6 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Add Team Member</h2>
            <button onclick="closeModal('add-team-modal')" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form id="add-team-form" class="p-6 space-y-4">
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Select Employee</label>
                <select name="employee_id" id="employee-select" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">Choose an employee...</option>
                </select>
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Role</label>
                <select name="role" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                    <option value="lead">Lead</option>
                    <option value="developer">Developer</option>
                    <option value="designer">Designer</option>
                    <option value="tester">Tester</option>
                    <option value="analyst">Analyst</option>
                    <option value="support">Support</option>
                </select>
            </div>
            
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeModal('add-team-modal')" class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-200 transition-all">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all">Add Member</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Milestone Modal -->
<div id="add-milestone-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl max-h-screen overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-100 p-6 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Add Milestone</h2>
            <button onclick="closeModal('add-milestone-modal')" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form id="add-milestone-form" class="p-6 space-y-4">
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Title</label>
                <input type="text" name="title" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Description</label>
                <textarea name="description" rows="3" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20"></textarea>
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Due Date</label>
                <input type="date" name="due_date" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Status</label>
                <select name="status" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="overdue">Overdue</option>
                </select>
            </div>
            
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeModal('add-milestone-modal')" class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-200 transition-all">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all">Add Milestone</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Task Modal -->
<div id="add-task-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl max-h-screen overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-100 p-6 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Add Task</h2>
            <button onclick="closeModal('add-task-modal')" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form id="add-task-form" class="p-6 space-y-4">
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Title</label>
                <input type="text" name="title" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Description</label>
                <textarea name="description" rows="3" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20"></textarea>
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Milestone (Optional)</label>
                <select name="milestone_id" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">-- No Milestone --</option>
                    <?php foreach ($milestoneList as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Priority</label>
                <select name="priority" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Due Date</label>
                <input type="date" name="due_date" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeModal('add-task-modal')" class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-200 transition-all">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all">Add Task</button>
            </div>
        </form>
    </div>
</div>

<script src="/ifms/assets/js/app.js"></script>
<script>
const projectId = <?= $projectId ?>;

// Load employees for team member selection
fetch('/ifms/api/employees.php?action=list_for_project&project_id=' + projectId)
    .then(r => r.json())
    .then(json => {
        if (json.success && json.data) {
            const select = document.getElementById('employee-select');
            json.data.forEach(emp => {
                const option = document.createElement('option');
                option.value = emp.id;
                option.textContent = emp.full_name + ' (' + emp.designation + ')';
                select.appendChild(option);
            });
        }
    })
    .catch(err => console.error('Error loading employees:', err));

// Edit project
document.getElementById('edit-project-form').onsubmit = async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target));
    try {
        const res = await fetch('/ifms/api/projects.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'edit', id: projectId, ...data })
        });
        const json = await res.json();
        if (json.success) {
            showToast('Project updated successfully!');
            closeModal('edit-project-modal');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(json.error || 'Update failed', 'error');
        }
    } catch (err) {
        showToast(err?.message || 'Error updating project', 'error');
    }
};

// Add team member
document.getElementById('add-team-form').onsubmit = async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target));
    try {
        const res = await fetch('/ifms/api/projects.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'assign', project_id: projectId, employee_id: data.employee_id, role: data.role })
        });
        const json = await res.json();
        if (json.success) {
            showToast(json.message || 'Team member added!');
            closeModal('add-team-modal');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(json.error || 'Failed to add team member', 'error');
        }
    } catch (err) {
        showToast(err?.message || 'Error adding team member', 'error');
    }
};

// Remove team member
function removeTeamMember(teamId, projId, empId) {
    if (!confirm('Remove this team member?')) return;
    fetch('/ifms/api/projects.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'unassign', project_id: projId, employee_id: empId })
    })
    .then(r => r.json())
    .then(json => {
        if (json.success) {
            showToast('Team member removed!');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(json.error || 'Failed to remove', 'error');
        }
    });
}

// Add milestone
document.getElementById('add-milestone-form').onsubmit = async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target));
    try {
        const res = await fetch('/ifms/api/projects.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'milestone_add', project_id: projectId, ...data })
        });
        const json = await res.json();
        if (json.success) {
            showToast('Milestone added successfully!');
            closeModal('add-milestone-modal');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(json.error || 'Failed to add milestone', 'error');
        }
    } catch (err) {
        showToast(err?.message || 'Error adding milestone', 'error');
    }
};

// Add task
document.getElementById('add-task-form').onsubmit = async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target));
    try {
        const res = await fetch('/ifms/api/projects.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'task_add', project_id: projectId, ...data })
        });
        const json = await res.json();
        if (json.success) {
            showToast('Task added successfully!');
            closeModal('add-task-modal');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(json.error || 'Failed to add task', 'error');
        }
    } catch (err) {
        showToast(err?.message || 'Error adding task', 'error');
    }
};

// Placeholder functions for edit milestone/task (can be expanded)
function editMilestone(id) {
    alert('Edit milestone functionality - can be implemented with a modal fetch');
}

function editTask(id) {
    alert('Edit task functionality - can be implemented with a modal fetch');
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
