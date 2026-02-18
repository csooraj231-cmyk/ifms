<?php
/**
 * IFMS - Admin: Client Detail Management
 * View and manage a single client organization
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('admin');

$db = getDB();
$orgId = intval($_GET['id'] ?? 0);
if (!$orgId) {
    header('Location: /ifms/admin/clients.php');
    exit;
}

// Get organization
$org = $db->prepare("SELECT * FROM organizations WHERE id = ? LIMIT 1");
$org->execute([$orgId]);
$orgData = $org->fetch();
if (!$orgData) {
    header('Location: /ifms/admin/clients.php');
    exit;
}

// Get client users
$users = $db->prepare("
    SELECT cu.*, u.email, u.full_name, u.phone 
    FROM client_users cu 
    JOIN users u ON cu.user_id = u.id 
    WHERE cu.organization_id = ? 
    ORDER BY cu.is_primary DESC, u.full_name ASC
");
$users->execute([$orgId]);
$clientUsers = $users->fetchAll();

// Get projects
$projects = $db->prepare("
    SELECT p.*, 
        (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id) AS task_count,
        (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id AND t.status = 'completed') AS done_tasks
    FROM projects p 
    WHERE p.organization_id = ? 
    ORDER BY p.updated_at DESC
");
$projects->execute([$orgId]);
$projectList = $projects->fetchAll();

$pageTitle = htmlspecialchars($orgData['name']) . ' - Client Details';
include __DIR__ . '/../includes/header.php';
?>

<!-- Back Button & Title -->
<div class="mb-8 flex items-center gap-4">
    <a href="/ifms/admin/clients.php" class="p-2 hover:bg-gray-100 rounded-lg transition-all">
        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <div>
        <h1 class="text-3xl font-black text-gray-900"><?= htmlspecialchars($orgData['name']) ?></h1>
        <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($orgData['industry'] ?? 'Organization') ?></p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Organization Details Card -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">Organization Details</h2>
                <button onclick="openModal('edit-org-modal')" class="text-xs font-bold text-indigo-600 hover:text-indigo-700">
                    <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </button>
            </div>
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Company Name</p>
                        <p class="text-sm font-bold text-gray-900 mt-1"><?= htmlspecialchars($orgData['name']) ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Industry</p>
                        <p class="text-sm font-bold text-gray-900 mt-1"><?= htmlspecialchars($orgData['industry'] ?? 'N/A') ?></p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Email</p>
                        <p class="text-sm font-bold text-gray-900 mt-1"><?= htmlspecialchars($orgData['email']) ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Phone</p>
                        <p class="text-sm font-bold text-gray-900 mt-1"><?= htmlspecialchars($orgData['phone'] ?? 'N/A') ?></p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Website</p>
                        <p class="text-sm font-bold text-gray-900 mt-1"><?= htmlspecialchars($orgData['website'] ?? 'N/A') ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">GST Number</p>
                        <p class="text-sm font-bold text-gray-900 mt-1"><?= htmlspecialchars($orgData['gst_number'] ?? 'N/A') ?></p>
                    </div>
                </div>
                
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase">Address</p>
                    <p class="text-sm font-bold text-gray-900 mt-1"><?= htmlspecialchars($orgData['address'] ?? 'N/A') ?></p>
                </div>
                
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">City</p>
                        <p class="text-sm font-bold text-gray-900 mt-1"><?= htmlspecialchars($orgData['city'] ?? 'N/A') ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">State</p>
                        <p class="text-sm font-bold text-gray-900 mt-1"><?= htmlspecialchars($orgData['state'] ?? 'N/A') ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Pincode</p>
                        <p class="text-sm font-bold text-gray-900 mt-1"><?= htmlspecialchars($orgData['pincode'] ?? 'N/A') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Projects Section -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">Projects (<?= count($projectList) ?>)</h2>
                <button onclick="openModal('create-project-modal')" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <?= count($projectList) > 0 ? 'New Project' : 'Start a Project' ?>
                </button>
            </div>
            
            <?php if (empty($projectList)): ?>
                <div class="text-center py-8">
                    <p class="text-gray-500 mb-4">No projects yet for this client.</p>
                    <button onclick="openModal('create-project-modal')" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-bold text-sm hover:bg-indigo-700">
                        Start a Project
                    </button>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($projectList as $proj): ?>
                        <div class="border border-gray-100 rounded-xl p-4 hover:bg-gray-50 transition-all">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <h3 class="font-bold text-gray-900"><?= htmlspecialchars($proj['title']) ?></h3>
                                    <p class="text-xs text-gray-500 mt-1">Status: <?= ucfirst($proj['status']) ?> • Priority: <?= ucfirst($proj['priority']) ?></p>
                                </div>
                                <a href="/ifms/admin/project_detail.php?id=<?= $proj['id'] ?>" class="text-xs font-bold text-indigo-600 hover:underline">View</a>
                            </div>
                            <div class="flex gap-3 text-xs text-gray-500">
                                <span><?= $proj['task_count'] ?> tasks</span>
                                <span><?= $proj['done_tasks'] ?> completed</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Column: Users -->
    <div class="space-y-6">
        <!-- Client Users Card -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">Users (<?= count($clientUsers) ?>)</h2>
                <button onclick="openModal('add-user-modal')" class="text-xs font-bold text-indigo-600 hover:text-indigo-700">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add User
                </button>
            </div>
            
            <div class="space-y-3 max-h-96 overflow-y-auto">
                <?php if (empty($clientUsers)): ?>
                    <div class="text-center py-8">
                        <p class="text-gray-500">No users added yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($clientUsers as $user): ?>
                        <div class="border border-gray-100 rounded-lg p-3 hover:bg-gray-50 transition-all group">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs">
                                        <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-900"><?= htmlspecialchars($user['full_name']) ?></p>
                                        <?php if ($user['is_primary']): ?>
                                            <p class="text-[10px] text-indigo-600 font-bold">Primary Contact</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <button onclick="openEditUserModal(<?= $user['id'] ?>, <?= $orgId ?>)" class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-indigo-600 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-xs text-gray-500"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Edit Organization Modal -->
<div id="edit-org-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl max-h-screen overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-100 p-6 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Edit Organization Details</h2>
            <button onclick="closeModal('edit-org-modal')" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form id="edit-org-form" class="p-6 space-y-4">
            <input type="hidden" name="org_id" value="<?= $orgId ?>">
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Organization Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($orgData['name']) ?>" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($orgData['email']) ?>" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Phone</label>
                <input type="tel" name="phone" value="<?= htmlspecialchars($orgData['phone'] ?? '') ?>" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Website</label>
                <input type="url" name="website" value="<?= htmlspecialchars($orgData['website'] ?? '') ?>" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Industry</label>
                <input type="text" name="industry" value="<?= htmlspecialchars($orgData['industry'] ?? '') ?>" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">GST Number</label>
                <input type="text" name="gst_number" value="<?= htmlspecialchars($orgData['gst_number'] ?? '') ?>" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Address</label>
                <textarea name="address" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20"><?= htmlspecialchars($orgData['address'] ?? '') ?></textarea>
            </div>
            
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">City</label>
                    <input type="text" name="city" value="<?= htmlspecialchars($orgData['city'] ?? '') ?>" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                </div>
                <div>
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">State</label>
                    <input type="text" name="state" value="<?= htmlspecialchars($orgData['state'] ?? '') ?>" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                </div>
                <div>
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Pincode</label>
                    <input type="text" name="pincode" value="<?= htmlspecialchars($orgData['pincode'] ?? '') ?>" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                </div>
            </div>
            
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeModal('edit-org-modal')" class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-200 transition-all">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Create Project Modal -->
<div id="create-project-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl max-h-screen overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-100 p-6 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Create New Project</h2>
            <button onclick="closeModal('create-project-modal')" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form id="create-project-form" class="p-6 space-y-4">
            <input type="hidden" name="org_id" value="<?= $orgId ?>">
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Project Title</label>
                <input type="text" name="title" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Description</label>
                <textarea name="description" rows="3" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20"></textarea>
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
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Start Date</label>
                    <input type="date" name="start_date" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                </div>
                <div>
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">End Date</label>
                    <input type="date" name="end_date" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                </div>
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Estimated Budget</label>
                <input type="number" name="estimated_budget" step="0.01" min="0" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeModal('create-project-modal')" class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-200 transition-all">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all">Create Project</button>
            </div>
        </form>
    </div>
</div>

<!-- Add User Modal -->
<div id="add-user-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl max-h-screen overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-100 p-6 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Add User to Organization</h2>
            <button onclick="closeModal('add-user-modal')" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form id="add-user-form" class="p-6 space-y-4">
            <input type="hidden" name="org_id" value="<?= $orgId ?>">
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Full Name</label>
                <input type="text" name="full_name" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Email Address</label>
                <input type="email" name="email" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Password</label>
                <input type="password" name="password" required minlength="6" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Phone Number</label>
                <input type="tel" name="phone" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Designation</label>
                <input type="text" name="designation" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20" placeholder="e.g. Project Manager">
            </div>
            
            <div class="flex items-center gap-2">
                <input type="checkbox" id="is_primary" name="is_primary" class="w-4 h-4 rounded">
                <label for="is_primary" class="text-sm text-gray-600 font-bold">Set as primary contact</label>
            </div>
            
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeModal('add-user-modal')" class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-200 transition-all">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all">Add User</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="edit-user-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl max-h-screen overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-100 p-6 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Edit User Profile</h2>
            <button onclick="closeModal('edit-user-modal')" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form id="edit-user-form" class="p-6 space-y-4">
            <input type="hidden" name="user_id" id="edit-user-id">
            <input type="hidden" name="org_id" id="edit-org-id">
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Full Name</label>
                <input type="text" name="full_name" id="edit-full-name" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Email Address</label>
                <input type="email" name="email" id="edit-email" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Phone Number</label>
                <input type="tel" name="phone" id="edit-phone" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Designation</label>
                <input type="text" name="designation" id="edit-designation" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div class="flex items-center gap-2">
                <input type="checkbox" id="edit-is-primary" name="is_primary" class="w-4 h-4 rounded">
                <label for="edit-is-primary" class="text-sm text-gray-600 font-bold">Set as primary contact</label>
            </div>
            
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeModal('edit-user-modal')" class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-200 transition-all">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
// Edit Organization
document.getElementById('edit-org-form').onsubmit = async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target));
    try {
        const res = await fetch('/ifms/api/clients.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'update_org', ...data })
        });
        const json = await res.json();
        if (json.success) {
            showToast('Organization updated successfully!');
            closeModal('edit-org-modal');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(json.error || 'Update failed', 'error');
        }
    } catch (err) {
        showToast(err?.message || 'Error updating organization', 'error');
    }
};

// Create Project
document.getElementById('create-project-form').onsubmit = async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target));
    try {
        const res = await fetch('/ifms/api/clients.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'create_project', ...data })
        });
        const json = await res.json();
        if (json.success) {
            showToast('Project created successfully!');
            closeModal('create-project-modal');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(json.error || 'Project creation failed', 'error');
        }
    } catch (err) {
        showToast(err?.message || 'Error creating project', 'error');
    }
};

// Add User
document.getElementById('add-user-form').onsubmit = async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target));
    data.is_primary = data.is_primary ? 1 : 0;
    try {
        const res = await fetch('/ifms/api/clients.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'add_user', ...data })
        });
        const json = await res.json();
        if (json.success) {
            showToast('User added successfully!');
            closeModal('add-user-modal');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(json.error || 'User addition failed', 'error');
        }
    } catch (err) {
        showToast(err?.message || 'Error adding user', 'error');
    }
};

// Edit User
async function openEditUserModal(clientUserId, orgId) {
    try {
        const res = await fetch(`/ifms/api/clients.php?action=get_user&id=${clientUserId}`);
        const json = await res.json();
        if (json.success && json.data) {
            const u = json.data;
            document.getElementById('edit-user-id').value = clientUserId;
            document.getElementById('edit-org-id').value = orgId;
            document.getElementById('edit-full-name').value = u.full_name || '';
            document.getElementById('edit-email').value = u.email || '';
            document.getElementById('edit-phone').value = u.phone || '';
            document.getElementById('edit-designation').value = u.designation || '';
            document.getElementById('edit-is-primary').checked = u.is_primary ? true : false;
            openModal('edit-user-modal');
        }
    } catch (err) {
        showToast('Error loading user data', 'error');
    }
}

document.getElementById('edit-user-form').onsubmit = async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target));
    data.is_primary = data.is_primary ? 1 : 0;
    try {
        const res = await fetch('/ifms/api/clients.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'update_user', ...data })
        });
        const json = await res.json();
        if (json.success) {
            showToast('User updated successfully!');
            closeModal('edit-user-modal');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(json.error || 'Update failed', 'error');
        }
    } catch (err) {
        showToast(err?.message || 'Error updating user', 'error');
    }
};
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
