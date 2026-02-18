<?php
/**
 * IFMS - CLIENT: Account Settings
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('client');

$pageTitle = 'Account Settings';
include __DIR__ . '/../includes/header.php';

$user = getCurrentUser();
?>

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 mb-8">
        <h3 class="text-xl font-black text-gray-900 mb-6">Security Settings</h3>
        
        <form id="password-form" class="space-y-6">
            <div>
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Current Password</label>
                <input type="password" name="current_password" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            <div>
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">New Password</label>
                <input type="password" name="new_password" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            <div>
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Confirm New Password</label>
                <input type="password" name="confirm_password" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            <div class="pt-4">
                <button type="submit" class="w-full px-8 py-3 bg-indigo-600 text-white rounded-xl font-black text-sm uppercase tracking-widest shadow-lg shadow-indigo-500/20 hover:bg-indigo-700 transition-all">Update Password</button>
            </div>
        </form>
    </div>
</div>

<script src="/ifms/assets/js/app.js"></script>
<script>
document.getElementById('password-form').onsubmit = async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target));
    if (data.new_password !== data.confirm_password) {
        showToast('New passwords do not match', 'error');
        return;
    }
    try {
        const res = await fetch('/ifms/api/auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'update_password', ...data })
        });
        const json = await res.json();
        if (json.success) {
            showToast('Password updated successfully!');
            e.target.reset();
        } else {
            showToast(json.message || 'Error updating password', 'error');
        }
    } catch (err) {
        showToast('Error updating password', 'error');
    }
};
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>