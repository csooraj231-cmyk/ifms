<?php
/**
 * IFMS - Admin: Holiday Management
 * Admin can manage company holidays
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('admin');

$db = getDB();
$pageTitle = 'Holiday Management';

// Fetch all holidays
$holidays = $db->query("
    SELECT * FROM holidays 
    ORDER BY date DESC
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h2 class="text-2xl font-black text-gray-900 tracking-tight">Holidays</h2>
        <p class="text-sm text-gray-500 font-medium">Manage company holidays and special days.</p>
    </div>
    <button onclick="openModal('add-holiday-modal')" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-2xl font-bold text-sm hover:bg-indigo-700 shadow-xl shadow-indigo-500/20 transition-all active:scale-95">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Add Holiday
    </button>
</div>

<!-- Holidays Table -->
<div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full" id="holidayTable">
            <thead>
                <tr class="text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.15em] bg-gray-50/50">
                    <th class="px-6 py-4">Holiday Name</th>
                    <th class="px-6 py-4">Date</th>
                    <th class="px-6 py-4">Type</th>
                    <th class="px-6 py-4"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php if (count($holidays) > 0): ?>
                    <?php foreach ($holidays as $holiday): ?>
                    <tr class="hover:bg-gray-50/50 transition-colors group">
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-gray-900"><?= htmlspecialchars($holiday['title']) ?></p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium text-gray-600"><?= date('M d, Y', strtotime($holiday['date'])) ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter <?= $holiday['type'] === 'national' ? 'bg-indigo-50 text-indigo-600' : 'bg-emerald-50 text-emerald-600' ?>">
                                <?= ucfirst($holiday['type']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button onclick="editHoliday(<?= $holiday['id'] ?>)" title="Edit" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all opacity-0 group-hover:opacity-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536M9 11l6-6 3 3-6 6H9v-3z"/></svg>
                            </button>
                            <button onclick="deleteHoliday(<?= $holiday['id'] ?>)" title="Delete" class="ml-2 p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all opacity-0 group-hover:opacity-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-7 7-7-7M5 12v7a1 1 0 001 1h12a1 1 0 001-1v-7"/></svg>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <p class="text-gray-500 text-sm">No holidays yet. <button onclick="openModal('add-holiday-modal')" class="text-indigo-600 font-bold hover:underline">Add one</button></p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Holiday Modal -->
<div id="add-holiday-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl">
        <div class="sticky top-0 bg-white border-b border-gray-100 p-6 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Add Holiday</h2>
            <button onclick="closeModal('add-holiday-modal')" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form id="add-holiday-form" class="p-6 space-y-4">
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Holiday Name</label>
                <input type="text" name="title" required placeholder="e.g., Christmas, Diwali" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Date</label>
                <input type="date" name="date" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Type</label>
                <select name="type" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                    <option value="national">National Holiday</option>
                    <option value="company">Company Holiday</option>
                </select>
            </div>
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeModal('add-holiday-modal')" class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-200 transition-all">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all">Add Holiday</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Holiday Modal -->
<div id="edit-holiday-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl">
        <div class="sticky top-0 bg-white border-b border-gray-100 p-6 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Edit Holiday</h2>
            <button onclick="closeModal('edit-holiday-modal')" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form id="edit-holiday-form" class="p-6 space-y-4">
            <input type="hidden" name="id" id="edit-holiday-id">
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Holiday Name</label>
                <input type="text" name="title" id="edit-holiday-title" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Date</label>
                <input type="date" name="date" id="edit-holiday-date" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Type</label>
                <select name="type" id="edit-holiday-type" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                    <option value="national">National Holiday</option>
                    <option value="company">Company Holiday</option>
                </select>
            </div>
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeModal('edit-holiday-modal')" class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-200 transition-all">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('add-holiday-form').onsubmit = async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target));
    try {
        const res = await fetch('/ifms/api/holidays.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'create', ...data })
        });
        const json = await res.json();
        if (json.success) {
            showToast('Holiday added successfully!');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(json?.error || json?.message || 'Error adding holiday', 'error');
        }
    } catch (err) {
        showToast(err?.message || 'Error adding holiday', 'error');
    }
};

async function editHoliday(id) {
    try {
        const res = await fetch('/ifms/api/holidays.php?action=get&id=' + id);
        const json = await res.json();
        if (!json || !json.success) {
            showToast(json?.error || 'Failed to load holiday', 'error');
            return;
        }
        const h = json.holiday;
        document.getElementById('edit-holiday-id').value = h.id;
        document.getElementById('edit-holiday-title').value = h.title;
        document.getElementById('edit-holiday-date').value = h.date;
        document.getElementById('edit-holiday-type').value = h.type;
        openModal('edit-holiday-modal');
    } catch (err) {
        showToast(err?.message || 'Error fetching holiday', 'error');
    }
}

document.getElementById('edit-holiday-form').onsubmit = async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target));
    try {
        const res = await fetch('/ifms/api/holidays.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'update', ...data })
        });
        const json = await res.json();
        if (json.success) {
            showToast('Holiday updated successfully!');
            closeModal('edit-holiday-modal');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(json?.error || json?.message || 'Update failed', 'error');
        }
    } catch (err) {
        showToast(err?.message || 'Error updating holiday', 'error');
    }
};

async function deleteHoliday(id) {
    if (!confirm('Delete this holiday?')) return;
    try {
        const res = await fetch('/ifms/api/holidays.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete', id })
        });
        const json = await res.json();
        if (json.success) {
            showToast('Holiday deleted!');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(json?.error || 'Failed to delete', 'error');
        }
    } catch (err) {
        showToast(err?.message || 'Error deleting holiday', 'error');
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
