<?php
/**
 * IFMS - Client: Support Tickets
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('client');

$db = getDB();
$user = getCurrentUser();
$userId = $user['id'];
$pageTitle = 'Support Tickets';

$tickets = $db->query("
    SELECT st.*, p.title AS project_title 
    FROM support_tickets st 
    LEFT JOIN projects p ON st.project_id = p.id 
    WHERE st.created_by = {$userId}
    ORDER BY st.created_at DESC
")->fetchAll();

$clientUser = $db->prepare("SELECT organization_id FROM client_users WHERE user_id = ?");
$clientUser->execute([$userId]);
$orgId = $clientUser->fetchColumn();
$projects = $db->query("SELECT id, title FROM projects WHERE organization_id = {$orgId}")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h2 class="text-2xl font-black text-gray-900 tracking-tight">Support Tickets</h2>
        <p class="text-sm text-gray-500 font-medium">Get help with your projects and services.</p>
    </div>
    <button onclick="openModal('create-ticket-modal')" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-2xl font-bold text-sm hover:bg-indigo-700 shadow-xl shadow-indigo-500/20 active:scale-95 transition-all">
        Raise New Ticket
    </button>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <?php foreach ($tickets as $t): ?>
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition-all">
        <div class="flex items-center justify-between mb-4">
            <span class="text-xs font-mono font-bold text-indigo-600"><?= $t['ticket_number'] ?></span>
            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter <?php echo match($t['status']) { 'open' => 'bg-amber-100 text-amber-700', 'in_progress' => 'bg-indigo-100 text-indigo-700', 'resolved' => 'bg-emerald-100 text-emerald-700', default => 'bg-gray-100 text-gray-600' }; ?>">
                <?= str_replace('_',' ',$t['status']) ?>
            </span>
        </div>
        <h4 class="text-base font-bold text-gray-900 mb-2"><?= htmlspecialchars($t['subject']) ?></h4>
        <p class="text-xs text-gray-500 line-clamp-2 h-8"><?= htmlspecialchars($t['description']) ?></p>
        
        <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-50">
            <span class="text-[10px] font-black text-gray-400 uppercase"><?= htmlspecialchars($t['project_title'] ?? 'General') ?></span>
            <span class="text-[10px] font-bold text-gray-500"><?= date('d M Y', strtotime($t['created_at'])) ?></span>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div id="create-ticket-modal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeModal('create-ticket-modal')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg bg-white rounded-3xl shadow-2xl p-8">
        <h3 class="text-xl font-black text-gray-900 mb-6">Create New Ticket</h3>
        <form id="create-ticket-form" class="space-y-4">
            <div>
                <label class="text-[10px] font-black text-gray-400 uppercase block mb-1.5 px-1">Project</label>
                <select name="project_id" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 font-bold">
                    <option value="">General Support</option>
                    <?php foreach ($projects as $p): ?><option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['title']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="text-[10px] font-black text-gray-400 uppercase block mb-1.5 px-1">Priority</label>
                <select name="priority" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 font-bold">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>
            <div>
                <label class="text-[10px] font-black text-gray-400 uppercase block mb-1.5 px-1">Subject</label>
                <input type="text" name="subject" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 font-bold">
            </div>
            <div>
                <label class="text-[10px] font-black text-gray-400 uppercase block mb-1.5 px-1">Description</label>
                <textarea name="description" rows="4" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 font-medium"></textarea>
            </div>
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeModal('create-ticket-modal')" class="flex-1 px-6 py-3 bg-gray-100 text-gray-500 rounded-xl font-bold text-sm">Cancel</button>
                <button type="submit" class="flex-1 px-6 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm">Create Ticket</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('create-ticket-form').onsubmit = async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target));
    data.action = 'create';
    const res = await api('/ifms/api/tickets.php', 'POST', data);
    if (res.success) { showToast('Ticket created: ' + res.ticket_number); setTimeout(() => location.reload(), 800); }
};
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>