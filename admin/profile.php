<?php
/**
 * IFMS - ADMIN: My Profile
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('admin');

$pageTitle = 'My Profile';
include __DIR__ . '/../includes/header.php';

$user = getCurrentUser();
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden mb-8">
        <div class="h-32 bg-gradient-to-r from-indigo-600 to-purple-600"></div>
        <div class="px-8 pb-8">
            <div class="relative flex justify-between items-end -mt-12 mb-6">
                <div class="w-24 h-24 rounded-2xl bg-white p-1 shadow-xl">
                    <div class="w-full h-full rounded-xl bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center text-white text-3xl font-black">
                        <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button onclick="location.href='settings.php'" class="px-5 py-2.5 bg-gray-50 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-100 transition-all">Account Settings</button>
                </div>
            </div>

            <div class="mb-8">
                <h3 class="text-2xl font-black text-gray-900"><?= htmlspecialchars($user['full_name']) ?></h3>
                <p class="text-sm text-gray-500 font-medium uppercase tracking-widest mt-1">Administrator</p>
            </div>

            <form id="profile-form" class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-gray-50">
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Full Name</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                </div>
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Email Address</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                </div>
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Phone Number</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
                </div>
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Role</label>
                    <input type="text" value="<?= htmlspecialchars($user['role'] ?? 'Administrator') ?>" readonly class="w-full bg-gray-100 border-none rounded-xl px-4 py-3 text-sm font-bold text-gray-500 cursor-not-allowed">
                </div>
                <div class="md:col-span-2 pt-4">
                    <button type="submit" class="w-full md:w-auto px-8 py-3 bg-indigo-600 text-white rounded-xl font-black text-sm uppercase tracking-widest shadow-lg shadow-indigo-500/20 hover:bg-indigo-700 transition-all">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Access Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <a href="employees.php" class="bg-white rounded-2xl border border-gray-100 p-6 hover:shadow-lg transition-all hover:border-indigo-300 group cursor-pointer">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center mb-4 group-hover:bg-indigo-100 transition-colors">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <h3 class="text-sm font-bold text-gray-900 group-hover:text-indigo-600">Manage Employees</h3>
            <p class="text-xs text-gray-500 mt-1">View and manage employee records</p>
        </a>

        <a href="clients.php" class="bg-white rounded-2xl border border-gray-100 p-6 hover:shadow-lg transition-all hover:border-emerald-300 group cursor-pointer">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center mb-4 group-hover:bg-emerald-100 transition-colors">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/></svg>
            </div>
            <h3 class="text-sm font-bold text-gray-900 group-hover:text-emerald-600">Manage Clients</h3>
            <p class="text-xs text-gray-500 mt-1">Manage organizations and accounts</p>
        </a>

        <a href="projects.php" class="bg-white rounded-2xl border border-gray-100 p-6 hover:shadow-lg transition-all hover:border-purple-300 group cursor-pointer">
            <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center mb-4 group-hover:bg-purple-100 transition-colors">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <h3 class="text-sm font-bold text-gray-900 group-hover:text-purple-600">Manage Projects</h3>
            <p class="text-xs text-gray-500 mt-1">Track projects and milestones</p>
        </a>

        <a href="invoices.php" class="bg-white rounded-2xl border border-gray-100 p-6 hover:shadow-lg transition-all hover:border-amber-300 group cursor-pointer">
            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center mb-4 group-hover:bg-amber-100 transition-colors">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-sm font-bold text-gray-900 group-hover:text-amber-600">View Invoices</h3>
            <p class="text-xs text-gray-500 mt-1">Manage billing and payments</p>
        </a>
    </div>
</div>

<script src="/ifms/assets/js/app.js"></script>
<script>
document.getElementById('profile-form').onsubmit = async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target));
    try {
        const res = await fetch('/ifms/api/auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'update_profile', ...data })
        });
        const json = await res.json();
        if (json.success) {
            showToast('Profile updated successfully!');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(json.message || 'Error updating profile', 'error');
        }
    } catch (err) {
        showToast('Error updating profile', 'error');
    }
};
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>