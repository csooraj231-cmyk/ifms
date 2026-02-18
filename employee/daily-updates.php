<?php
/**
 * IFMS - Employee: Daily Updates
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('employee');

$db = getDB();
$user = getCurrentUser();
$empId = $user['employee_id'];
$pageTitle = 'Daily Updates';

$updates = $db->query("
    SELECT du.*, p.title AS project_title
    FROM daily_updates du
    JOIN projects p ON du.project_id = p.id
    WHERE du.employee_id = {$empId}
    ORDER BY du.update_date DESC, du.created_at DESC
")->fetchAll();

$projects = $db->query("
    SELECT p.id, p.title
    FROM projects p
    JOIN project_team pt ON p.id = pt.project_id
    WHERE pt.employee_id = {$empId}
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h2 class="text-2xl font-black text-gray-900 tracking-tight">Daily Updates</h2>
        <p class="text-sm text-gray-500 font-medium">Log your work, blockers, and plans.</p>
    </div>
    <button onclick="openModal('add-update-modal')" class="btn-primary inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-2xl font-bold text-sm hover:bg-indigo-700 shadow-xl shadow-indigo-500/20 active:scale-95 transition-all">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        New Update
    </button>
</div>

<div class="max-w-4xl space-y-8">
    <?php 
    $currentDate = '';
    foreach ($updates as $up): 
        if ($currentDate !== $up['update_date']):
            $currentDate = $up['update_date'];
    ?>
        <div class="relative pl-8 pb-4">
            <div class="absolute left-0 top-0 bottom-0 w-px bg-indigo-100"></div>
            <div class="absolute left-[-4px] top-0 w-2 h-2 rounded-full bg-indigo-500 ring-4 ring-indigo-50"></div>
            <h3 class="text-sm font-black text-indigo-600 uppercase tracking-widest mb-6"><?= date('D, d M Y', strtotime($currentDate)) ?></h3>
    <?php endif; ?>
    
        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm mb-4 hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[10px] font-black text-indigo-500 uppercase tracking-widest"><?= htmlspecialchars($up['project_title']) ?></span>
                <span class="text-[10px] font-bold text-gray-400"><?= $up['hours_worked'] ?> Hours</span>
            </div>
            
            <div class="space-y-4">
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Work Done</p>
                    <p class="text-sm text-gray-700 leading-relaxed"><?= nl2br(htmlspecialchars($up['work_done'])) ?></p>
                </div>
                
                <?php if ($up['blockers']): ?>
                <div class="p-3 bg-red-50 rounded-xl border border-red-100">
                    <p class="text-[10px] font-black text-red-500 uppercase tracking-widest mb-1">Blockers</p>
                    <p class="text-xs text-red-700 font-medium"><?= htmlspecialchars($up['blockers']) ?></p>
                </div>
                <?php endif; ?>
                
                <div>
                    <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-1">Plan for Tomorrow</p>
                    <p class="text-xs text-gray-600 font-medium italic">"<?= htmlspecialchars($up['plan_for_tomorrow']) ?>"</p>
                </div>
            </div>
        </div>
    <?php if (next($updates) === false || $updates[key($updates)]['update_date'] !== $currentDate): ?>
        </div>
    <?php endif; endforeach; ?>
</div>

<!-- Modal -->
<div id="add-update-modal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeModal('add-update-modal')"></div>
    <div class="modal-content absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg bg-white rounded-3xl shadow-2xl p-8">
        <h3 class="text-xl font-black text-gray-900 mb-6">Log Daily Update</h3>
        <form id="daily-update-form" class="space-y-4">
            <input type="hidden" name="action" value="create_update">
            <div>
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block px-1">Project</label>
                <select name="project_id" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 font-bold">
                    <?php foreach ($projects as $p): ?><option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['title']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block px-1">Date</label>
                    <input type="date" name="update_date" value="<?= date('Y-m-d') ?>" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 font-bold">
                </div>
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block px-1">Hours</label>
                    <input type="number" step="0.5" name="hours_worked" value="8" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 font-bold">
                </div>
            </div>
            <div>
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block px-1">Work Done</label>
                <textarea name="work_done" rows="3" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 font-medium"></textarea>
            </div>
            <div>
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block px-1">Blockers (Optional)</label>
                <input type="text" name="blockers" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 font-medium">
            </div>
            <div>
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block px-1">Plan for Tomorrow</label>
                <input type="text" name="plan_for_tomorrow" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 font-medium">
            </div>
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeModal('add-update-modal')" class="flex-1 px-6 py-3 bg-gray-100 text-gray-500 rounded-xl font-bold text-sm">Cancel</button>
                <button type="submit" class="btn-primary flex-1 px-6 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-indigo-500/20">Submit Update</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('daily-update-form').onsubmit = async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target));
    const res = await api('/ifms/api/tasks.php', 'POST', data);
    if (res.success) { showToast('Update logged!'); setTimeout(() => location.reload(), 800); }
};
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>