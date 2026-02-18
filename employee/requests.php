<?php
/**
 * IFMS - Employee Requests
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('employee');

$db = getDB();
$user = getCurrentUser();
$pageTitle = 'My Requests';
include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-4xl mx-auto p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-black">Requests</h2>
        <button onclick="openModal('new-request-modal')" class="px-4 py-2 bg-indigo-600 text-white rounded-xl">New Request</button>
    </div>

    <div id="requests-list" class="space-y-4">
        <!-- populated by JS -->
    </div>
</div>

<!-- New Request Modal -->
<div id="new-request-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-2xl max-w-md w-full p-6">
        <h3 class="text-lg font-bold mb-4">New Request</h3>
        <form id="request-form" class="space-y-3">
            <div>
                <label class="text-xs font-black text-gray-400 uppercase block mb-2">Type</label>
                <select name="type" class="w-full px-3 py-2 bg-gray-50 rounded-lg">
                    <option value="leave">Leave</option>
                    <option value="support">Support</option>
                    <option value="general">General</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase block mb-2">Title</label>
                <input name="title" required class="w-full px-3 py-2 bg-gray-50 rounded-lg">
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase block mb-2">Message</label>
                <textarea name="message" rows="4" required class="w-full px-3 py-2 bg-gray-50 rounded-lg"></textarea>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeModal('new-request-modal')" class="flex-1 px-4 py-2 bg-gray-100 rounded-lg">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg">Submit</button>
            </div>
        </form>
    </div>
</div>

<script>
async function loadRequests() {
    try {
        const res = await fetch('/ifms/api/requests.php?action=list');
        const json = await res.json();
        if (!json.success) { showToast('Error loading requests', 'error'); return; }
        const el = document.getElementById('requests-list');
        if (!json.data.length) { el.innerHTML = '<div class="bg-gray-50 p-6 rounded-xl text-center">No requests yet.</div>'; return; }
        el.innerHTML = json.data.map(r => `
            <div class="bg-white rounded-xl p-4 border">
                <div class="flex justify-between mb-2"><div class="font-bold">${r.title}</div><div class="text-xs text-gray-500">${r.type} • ${r.status}</div></div>
                <div class="text-sm text-gray-700 mb-2">${r.message}</div>
                <div class="text-xs text-gray-400">Submitted by ${r.full_name} on ${r.created_at}</div>
            </div>
        `).join('');
    } catch (err) { showToast('Error loading requests', 'error'); }
}

document.getElementById('request-form').onsubmit = async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target));
    try {
        const res = await fetch('/ifms/api/requests.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ action: 'create', ...data }) });
        const json = await res.json();
        if (json.success) { showToast('Request submitted'); closeModal('new-request-modal'); loadRequests(); }
        else { showToast(json.error || json.message || 'Error submitting request', 'error'); }
    } catch (err) { showToast('Error submitting request', 'error'); }
}

loadRequests();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>