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
                <p class="text-sm text-gray-500 font-medium uppercase tracking-widest mt-1"><?= $user['role'] ?> � <?= $user['department'] ?? $user['organization_name'] ?? 'General' ?></p>
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
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Designation</label>
                    <input type="text" value="<?= htmlspecialchars($user['designation'] ?? 'N/A') ?>" readonly class="w-full bg-gray-100 border-none rounded-xl px-4 py-3 text-sm font-bold text-gray-500 cursor-not-allowed">
                </div>
                <div class="md:col-span-2 pt-4">
                    <button type="submit" class="w-full md:w-auto px-8 py-3 bg-indigo-600 text-white rounded-xl font-black text-sm uppercase tracking-widest shadow-lg shadow-indigo-500/20 hover:bg-indigo-700 transition-all">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('profile-form').onsubmit = async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target));
    const res = await api('auth.php', 'POST', { action: 'update_profile', ...data });
    if (res.success) {
        showToast('Profile updated successfully!');
        setTimeout(() => location.reload(), 1000);
    } else {
        showToast(res.message || 'Error updating profile', 'error');
    }
};
</script>