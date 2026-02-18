<?php
/**
 * IFMS - Admin: Project Management
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('admin');

$db = getDB();
$pageTitle = 'Project Management';

$status = $_GET['status'] ?? 'all';
$query = "SELECT p.*, o.name AS org_name FROM projects p LEFT JOIN organizations o ON p.organization_id = o.id";
if ($status !== 'all') { $query .= " WHERE p.status = '{$status}'"; }
$projects = $db->query($query . " ORDER BY p.updated_at DESC")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h2 class="text-2xl font-black text-gray-900 tracking-tight">Projects</h2>
        <p class="text-sm text-gray-500 font-medium">Track progress, teams, and milestones across all clients.</p>
    </div>
    <button onclick="openModal('create-project-modal')" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-2xl font-bold text-sm hover:bg-indigo-700 shadow-xl shadow-indigo-500/20 transition-all active:scale-95">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        New Project
    </button>
</div>

<!-- Tabs -->
<div class="flex items-center gap-1 p-1 bg-gray-100 rounded-2xl w-fit mb-8">
    <a href="?status=all" class="px-6 py-2 rounded-xl text-xs font-bold transition-all <?= $status === 'all' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' ?>">All</a>
    <a href="?status=in_progress" class="px-6 py-2 rounded-xl text-xs font-bold transition-all <?= $status === 'in_progress' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' ?>">Active</a>
    <a href="?status=pending" class="px-6 py-2 rounded-xl text-xs font-bold transition-all <?= $status === 'pending' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' ?>">Pending</a>
    <a href="?status=completed" class="px-6 py-2 rounded-xl text-xs font-bold transition-all <?= $status === 'completed' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' ?>">Completed</a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($projects as $proj): ?>
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 hover:shadow-xl transition-all group border-b-4 <?php
        echo match($proj['status']) {
            'in_progress' => 'border-indigo-500',
            'completed' => 'border-emerald-500',
            'pending' => 'border-amber-500',
            default => 'border-gray-300'
        };
    ?>">
        <div class="flex items-start justify-between mb-4">
            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter bg-gray-50 text-gray-500 group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-colors">
                <?= htmlspecialchars($proj['org_name'] ?? 'Internal') ?>
            </span>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest <?php
                echo match($proj['priority']) {
                    'critical' => 'text-red-500',
                    'high' => 'text-orange-500',
                    'medium' => 'text-blue-500',
                    default => 'text-gray-400'
                };
            ?>">? <?= $proj['priority'] ?></span>
        </div>
        
        <h3 class="text-lg font-black text-gray-900 group-hover:text-indigo-600 transition-colors"><?= htmlspecialchars($proj['title']) ?></h3>
        <p class="text-xs text-gray-500 font-medium mt-2 line-clamp-2 h-8"><?= htmlspecialchars($proj['description'] ?? 'No description provided') ?></p>
        
        <div class="mt-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Progress</span>
                <span class="text-xs font-black text-indigo-600"><?= $proj['progress_percentage'] ?>%</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                <div class="h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-600 rounded-full transition-all duration-500 group-hover:scale-x-105 origin-left" style="width: <?= $proj['progress_percentage'] ?>%"></div>
            </div>
        </div>

        <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-50">
            <div class="flex -space-x-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-100 border-2 border-white flex items-center justify-center text-[10px] font-black text-indigo-600">PM</div>
                <div class="w-8 h-8 rounded-lg bg-purple-100 border-2 border-white flex items-center justify-center text-[10px] font-black text-purple-600">DE</div>
                <div class="w-8 h-8 rounded-lg bg-gray-100 border-2 border-white flex items-center justify-center text-[10px] font-black text-gray-400">+2</div>
            </div>
            <div class="flex gap-2">
                <button onclick="openDetailModal(<?= $proj['id'] ?>)" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 hover:underline transition-colors">View</button>
                <button onclick="openEditModal(<?= $proj['id'] ?>)" class="text-xs font-bold text-amber-600 hover:text-amber-700 hover:underline transition-colors">Edit</button>
                <button onclick="openManageModal(<?= $proj['id'] ?>)" class="text-xs font-bold text-purple-600 hover:text-purple-700 hover:underline transition-colors">Manage</button>
                <button onclick="openAssignmentModal(<?= $proj['id'] ?>, '<?= htmlspecialchars($proj['title']) ?>')" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline transition-colors">Assign</button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Create Project Modal -->
<div id="create-project-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl max-h-screen overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-100 p-6 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Create New Project</h2>
            <button onclick="closeModal('create-project-modal')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <form id="create-project-form" class="p-6 space-y-4">
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Project Title</label>
                <input type="text" name="title" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Description</label>
                <textarea name="description" rows="3" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20 resize-none"></textarea>
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Organization</label>
                <select name="organization_id" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">Select Organization</option>
                    <?php 
                    $orgs = $db->query("SELECT id, name FROM organizations ORDER BY name")->fetchAll();
                    foreach ($orgs as $org): ?>
                    <option value="<?= $org['id'] ?>"><?= htmlspecialchars($org['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Priority</label>
                <select name="priority" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">End Date</label>
                <input type="date" name="end_date" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeModal('create-project-modal')" class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-200 transition-all">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all">Create Project</button>
            </div>
        </form>
    </div>
</div>

<script>
const _createProjectForm = document.getElementById('create-project-form');
if (_createProjectForm) {
    _createProjectForm.onsubmit = async (e) => {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(e.target));
        try {
            const res = await fetch('/ifms/api/projects.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'create', ...data })
            });
            const ct = res.headers.get('Content-Type') || res.headers.get('content-type') || '';
            let json;
            if (ct.includes('application/json')) {
                json = await res.json();
            } else {
                const raw = await res.text();
                showToast('Server error: ' + raw, 'error');
                return;
            }
            if (json.success) {
                showToast('Project created successfully!');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(json.message || 'Error creating project', 'error');
            }
        } catch (err) {
            showToast('Error creating project: ' + err.message, 'error');
        }
    };
}

// Detail Modal Functions
async function openDetailModal(projectId) {
    try {
        const res = await fetch('/ifms/api/projects.php?action=detail&id=' + projectId);
        const ct = res.headers.get('Content-Type') || res.headers.get('content-type') || '';
        let json;
        if (ct.includes('application/json')) {
            json = await res.json();
        } else {
            const raw = await res.text();
            showToast('Server error: ' + raw, 'error');
            return;
        }
        if (!json.success) {
            showToast('Error loading project details: ' + (json.error || 'Unknown error'), 'error');
            return;
        }

        const proj = json.data;
        document.getElementById('detail-project-title').textContent = proj.title;
        document.getElementById('detail-description').textContent = proj.description || 'No description provided';
        document.getElementById('detail-status').innerHTML = `<span class="px-2 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-600">${proj.status}</span>`;
        document.getElementById('detail-priority').textContent = proj.priority || 'N/A';
        document.getElementById('detail-budget').textContent = proj.estimated_budget ? '₹' + proj.estimated_budget : 'N/A';
        document.getElementById('detail-start').textContent = proj.start_date || 'N/A';
        document.getElementById('detail-end').textContent = proj.end_date || 'N/A';
        
        // Populate team
        const teamHtml = proj.team && proj.team.length > 0 
            ? proj.team.map(t => `<div class="flex items-center justify-between py-2 border-b border-gray-50"><div><p class="text-sm font-bold text-gray-900">${t.full_name}</p><p class="text-xs text-gray-500">${t.role}</p></div><p class="text-xs font-bold text-indigo-600">${t.department}</p></div>`).join('')
            : '<p class="text-sm text-gray-500 italic">No team members assigned</p>';
        document.getElementById('detail-team').innerHTML = teamHtml;
        
        openModal('detail-modal');
    } catch (err) {
        showToast('Error loading project details: ' + err.message, 'error');
    }
}

// Edit Modal Functions
async function openEditModal(projectId) {
    try {
        const res = await fetch('/ifms/api/projects.php?action=detail&id=' + projectId);
        const ct = res.headers.get('Content-Type') || res.headers.get('content-type') || '';
        let json;
        if (ct.includes('application/json')) {
            json = await res.json();
        } else {
            const raw = await res.text();
            showToast('Server error: ' + raw, 'error');
            return;
        }
        if (!json.success) {
            showToast('Error loading project details: ' + (json.error || 'Unknown error'), 'error');
            return;
        }

        const proj = json.data;
        document.getElementById('edit-project-id').value = projectId;
        document.getElementById('edit-title').value = proj.title;
        document.getElementById('edit-description').value = proj.description || '';
        document.getElementById('edit-priority').value = proj.priority || 'medium';
        document.getElementById('edit-status').value = proj.status || 'pending';
        document.getElementById('edit-start-date').value = proj.start_date || '';
        document.getElementById('edit-end-date').value = proj.end_date || '';
        document.getElementById('edit-budget').value = proj.estimated_budget || '';
        
        openModal('edit-modal');
    } catch (err) {
        showToast('Error loading project details: ' + err.message, 'error');
    }
}

const _editProjectForm = document.getElementById('edit-project-form');
if (_editProjectForm) {
    _editProjectForm.onsubmit = async (e) => {
    e.preventDefault();
    const projectId = document.getElementById('edit-project-id').value;
    const data = Object.fromEntries(new FormData(e.target));
    delete data._project_id;
    
    try {
        const res = await fetch('/ifms/api/projects.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'edit', id: projectId, ...data })
        });
        const ct2 = res.headers.get('Content-Type') || res.headers.get('content-type') || '';
        let json;
        if (ct2.includes('application/json')) {
            json = await res.json();
        } else {
            const raw = await res.text();
            showToast('Server error: ' + raw, 'error');
            return;
        }
        if (json.success) {
            showToast('Project updated successfully!');
            closeModal('edit-modal');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(json.message || 'Error updating project', 'error');
        }
    } catch (err) {
        showToast('Error updating project', 'error');
    }
    };
}

// Manage Modal Functions
async function openManageModal(projectId) {
    document.getElementById('manage-project-id').value = projectId;
    
    try {
        // Load project name
        const res = await fetch('/ifms/api/projects.php?action=detail&id=' + projectId);
        const ct = res.headers.get('Content-Type') || res.headers.get('content-type') || '';
        let json;
        if (ct.includes('application/json')) {
            json = await res.json();
        } else {
            const raw = await res.text();
            showToast('Server error: ' + raw, 'error');
            return;
        }
        if (json.success) {
            document.getElementById('manage-project-title').textContent = json.data.title;
        }
        
        // Load tasks
        await loadTasks(projectId);
        // Load milestones
        await loadMilestones(projectId);
        
        openModal('manage-modal');
    } catch (err) {
        showToast('Error loading project data: ' + err.message, 'error');
    }
}

async function loadTasks(projectId) {
    try {
        const res = await fetch('/ifms/api/projects.php?action=task_list&project_id=' + projectId);
        const json = await res.json();
        const tasks = json.data || [];
        
        const tasksHtml = tasks.length > 0
            ? tasks.map(t => `<div class="flex items-center justify-between py-2 border-b border-gray-50">
                <div><p class="text-sm font-bold text-gray-900">${t.title}</p><p class="text-xs text-gray-500">${t.status}</p></div>
                <button type="button" onclick="deleteTask(${t.id})" class="text-xs font-bold text-red-500 hover:underline">Remove</button>
            </div>`).join('')
            : '<p class="text-sm text-gray-500 italic">No tasks yet</p>';
        
        document.getElementById('tasks-list').innerHTML = tasksHtml;
    } catch (err) {
        console.error('Error loading tasks:', err);
    }
}

async function loadMilestones(projectId) {
    try {
        const res = await fetch('/ifms/api/projects.php?action=milestone_list&project_id=' + projectId);
        const json = await res.json();
        const milestones = json.data || [];
        
        const milestonesHtml = milestones.length > 0
            ? milestones.map(m => `<div class="flex items-center justify-between py-2 border-b border-gray-50">
                <div><p class="text-sm font-bold text-gray-900">${m.title}</p><p class="text-xs text-gray-500">${m.target_date}</p></div>
                <button type="button" onclick="deleteMilestone(${m.id})" class="text-xs font-bold text-red-500 hover:underline">Remove</button>
            </div>`).join('')
            : '<p class="text-sm text-gray-500 italic">No milestones yet</p>';
        
        document.getElementById('milestones-list').innerHTML = milestonesHtml;
    } catch (err) {
        console.error('Error loading milestones:', err);
    }
}

const _addTaskForm = document.getElementById('add-task-form');
if (_addTaskForm) {
    _addTaskForm.onsubmit = async (e) => {
    e.preventDefault();
    const projectId = document.getElementById('manage-project-id').value;
    const form = new FormData(e.target);
    form.append('action', 'task_add');
    form.append('project_id', projectId);

    try {
        const res = await fetch('/ifms/api/projects.php', {
            method: 'POST',
            body: form
        });
        const json = await res.json();
        if (json.success) {
            showToast('Task added successfully!');
            e.target.reset();
            await loadTasks(projectId);
        } else {
            showToast(json.message || 'Error adding task', 'error');
        }
    } catch (err) {
        showToast('Error adding task', 'error');
    }
    };
}

const _addMilestoneForm = document.getElementById('add-milestone-form');
if (_addMilestoneForm) {
    _addMilestoneForm.onsubmit = async (e) => {
    e.preventDefault();
    const projectId = document.getElementById('manage-project-id').value;
    const form = new FormData(e.target);
    form.append('action', 'milestone_add');
    form.append('project_id', projectId);

    try {
        const res = await fetch('/ifms/api/projects.php', {
            method: 'POST',
            body: form
        });
        const json = await res.json();
        if (json.success) {
            showToast('Milestone added successfully!');
            e.target.reset();
            await loadMilestones(projectId);
        } else {
            showToast(json.message || 'Error adding milestone', 'error');
        }
    } catch (err) {
        showToast('Error adding milestone', 'error');
    }
    };
}

async function deleteTask(taskId) {
    if (!confirm('Delete this task?')) return;
    // TODO: Implement task deletion via API
    showToast('Task removal not yet implemented', 'warning');
}

async function deleteMilestone(milestoneId) {
    if (!confirm('Delete this milestone?')) return;
    // TODO: Implement milestone deletion via API
    showToast('Milestone removal not yet implemented', 'warning');
}

// Assignment Modal Functions
function openAssignmentModal(projectId, projectTitle) {
    document.getElementById('assign-project-id').value = projectId;
    document.getElementById('assign-project-title').textContent = projectTitle;
    openModal('assign-team-modal');
}

    const _assignTeamForm = document.getElementById('assign-team-form');
    if (_assignTeamForm) {
        _assignTeamForm.onsubmit = async (e) => {
        e.preventDefault();
        const projectId = document.getElementById('assign-project-id').value;
        const employeeSelect = document.getElementById('assign-employee-id');
        const employeeId = Array.from(employeeSelect.selectedOptions).map(o => o.value);
        const role = document.getElementById('assign-role').value;
        
        try {
            const res = await fetch('/ifms/api/projects.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'assign', project_id: projectId, employee_id: employeeId, role: role })
            });
            let json;
            const raw = await res.text();
            try { json = JSON.parse(raw); } catch (parseErr) { showToast('Server error: ' + raw, 'error'); return; }
            if (json.success) {
                const countMsg = json.count ? ' (' + json.count + ' assigned)' : '';
                showToast('Team member(s) assigned successfully' + countMsg + '! (Role: ' + (json.role || 'auto-selected') + ')');
                // Clear selection
                Array.from(employeeSelect.options).forEach(o => o.selected = false);
                closeModal('assign-team-modal');
            } else {
            showToast(json.message || json.error || 'Error assigning team member', 'error');
        }
    } catch (err) {
        showToast('Error assigning team member: ' + err.message, 'error');
    }
    };
}
</script>

<!-- Assign Team Modal -->
<div id="assign-team-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl max-h-screen overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-100 p-6 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Assign Team for <span id="assign-project-title"></span></h2>
            <button onclick="closeModal('assign-team-modal')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <form id="assign-team-form" class="p-6 space-y-4">
            <input type="hidden" id="assign-project-id">
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Employee</label>
                <select id="assign-employee-id" required multiple size="6" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                    <?php 
                    $employees = $db->query("SELECT e.id, u.full_name FROM employees e JOIN users u ON e.user_id = u.id WHERE e.is_active = 1 ORDER BY u.full_name")->fetchAll();
                    foreach ($employees as $emp): ?>
                    <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-gray-400 mt-2">Hold Ctrl/Cmd to select multiple employees.</p>
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Role (Leave blank for auto-assignment)</label>
                <select id="assign-role" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">Auto-assign based on department</option>
                    <option value="developer">Developer</option>
                    <option value="project_lead">Project Lead</option>
                    <option value="qa_tester">QA Tester</option>
                    <option value="designer">Designer</option>
                    <option value="hr_manager">HR Manager</option>
                    <option value="finance_manager">Finance Manager</option>
                </select>
            </div>
            
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeModal('assign-team-modal')" class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-200 transition-all">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all">Assign</button>
            </div>
        </form>
    </div>
</div>

<!-- Detail Modal -->
<div id="detail-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl max-h-screen overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-100 p-6 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900" id="detail-project-title"></h2>
            <button onclick="closeModal('detail-modal')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <div class="p-6 space-y-6">
            <div>
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Description</h3>
                <p class="text-sm text-gray-700" id="detail-description"></p>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Status</h3>
                    <div id="detail-status"></div>
                </div>
                <div>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Priority</h3>
                    <p class="text-sm font-bold text-gray-900" id="detail-priority"></p>
                </div>
                <div>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Start Date</h3>
                    <p class="text-sm font-bold text-gray-900" id="detail-start"></p>
                </div>
                <div>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">End Date</h3>
                    <p class="text-sm font-bold text-gray-900" id="detail-end"></p>
                </div>
                <div>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Budget</h3>
                    <p class="text-sm font-bold text-gray-900" id="detail-budget"></p>
                </div>
            </div>
            
            <div>
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Team Members</h3>
                <div id="detail-team" class="space-y-2"></div>
            </div>
            
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeModal('detail-modal')" class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-200 transition-all">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="edit-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl max-h-screen overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-100 p-6 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Edit Project</h2>
            <button onclick="closeModal('edit-modal')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <form id="edit-project-form" class="p-6 space-y-4">
            <input type="hidden" id="edit-project-id" name="_project_id">
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Title</label>
                <input type="text" id="edit-title" name="title" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Description</label>
                <textarea id="edit-description" name="description" rows="3" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20 resize-none"></textarea>
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Status</label>
                <select id="edit-status" name="status" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="on_hold">On Hold</option>
                </select>
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Priority</label>
                <select id="edit-priority" name="priority" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Start Date</label>
                <input type="date" id="edit-start-date" name="start_date" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">End Date</label>
                <input type="date" id="edit-end-date" name="end_date" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Estimated Budget</label>
                <input type="number" id="edit-budget" name="estimated_budget" min="0" step="1000" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeModal('edit-modal')" class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-200 transition-all">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all">Update Project</button>
            </div>
        </form>
    </div>
</div>

<!-- Manage Modal -->
<div id="manage-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl max-h-screen overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-100 p-6 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Manage <span id="manage-project-title"></span></h2>
            <button onclick="closeModal('manage-modal')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <div class="p-6 space-y-8">
            <input type="hidden" id="manage-project-id">
            
            <!-- Tasks Section -->
            <div>
                <h3 class="text-lg font-black text-gray-900 mb-4">Tasks</h3>
                <div id="tasks-list" class="mb-4 space-y-2 bg-gray-50 rounded-xl p-4"></div>
                
                <form id="add-task-form" class="space-y-3 bg-indigo-50 rounded-xl p-4 border border-indigo-100">
                    <div>
                        <input type="text" name="title" placeholder="New task title..." required class="w-full bg-white border-none rounded-lg px-3 py-2 text-xs font-bold focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                    <div>
                        <textarea name="description" placeholder="Task description..." rows="2" class="w-full bg-white border-none rounded-lg px-3 py-2 text-xs font-bold focus:ring-2 focus:ring-indigo-500/20 resize-none"></textarea>
                    </div>
                    <div>
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Reference File (optional)</label>
                        <input type="file" name="attachment" class="w-full bg-white border-none rounded-lg px-3 py-2 text-xs font-bold">
                    </div>
                    <div class="flex gap-2">
                        <input type="date" name="due_date" class="flex-1 bg-white border-none rounded-lg px-3 py-2 text-xs font-bold focus:ring-2 focus:ring-indigo-500/20">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-bold text-xs hover:bg-indigo-700 transition-all">Add Task</button>
                    </div>
                </form>
            </div>
            
            <!-- Milestones Section -->
            <div>
                <h3 class="text-lg font-black text-gray-900 mb-4">Milestones</h3>
                <div id="milestones-list" class="mb-4 space-y-2 bg-gray-50 rounded-xl p-4"></div>
                
                <form id="add-milestone-form" class="space-y-3 bg-purple-50 rounded-xl p-4 border border-purple-100">
                    <div>
                        <input type="text" name="title" placeholder="New milestone title..." required class="w-full bg-white border-none rounded-lg px-3 py-2 text-xs font-bold focus:ring-2 focus:ring-purple-500/20">
                    </div>
                    <div>
                        <textarea name="description" placeholder="Milestone description..." rows="2" class="w-full bg-white border-none rounded-lg px-3 py-2 text-xs font-bold focus:ring-2 focus:ring-purple-500/20 resize-none"></textarea>
                    </div>
                    <div>
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Reference File (optional)</label>
                        <input type="file" name="attachment" class="w-full bg-white border-none rounded-lg px-3 py-2 text-xs font-bold">
                    </div>
                    <div class="flex gap-2">
                        <input type="date" name="target_date" class="flex-1 bg-white border-none rounded-lg px-3 py-2 text-xs font-bold focus:ring-2 focus:ring-purple-500/20">
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg font-bold text-xs hover:bg-purple-700 transition-all">Add Milestone</button>
                    </div>
                </form>
            </div>
            
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeModal('manage-modal')" class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-200 transition-all">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>