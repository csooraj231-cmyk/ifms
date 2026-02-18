<?php
/**
 * IFMS - Admin: Notices & Holidays
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('admin');

$db = getDB();
$pageTitle = 'Notices & Holidays';

$notices = $db->query("SELECT * FROM notices ORDER BY created_at DESC")->fetchAll();
$holidays = $db->query("SELECT * FROM holidays ORDER BY date ASC")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Notices -->
    <div class="space-y-6">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-xl font-bold text-gray-900">Company Notices</h3>
            <button onclick="openModal('add-notice-modal')" class="text-xs font-bold text-indigo-600 hover:bg-indigo-50 px-3 py-1.5 rounded-lg transition-all">+ Add Notice</button>
        </div>
        <div class="space-y-4">
            <?php foreach ($notices as $note): ?>
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 <?php echo match($note['type']) { 'urgent' => 'bg-red-500', 'important' => 'bg-amber-500', default => 'bg-indigo-500' }; ?>"></div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-black uppercase tracking-widest text-gray-400"><?= date('d M Y', strtotime($note['created_at'])) ?></span>
                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full <?php echo match($note['type']) { 'urgent' => 'bg-red-50 text-red-600', 'important' => 'bg-amber-50 text-amber-600', default => 'bg-indigo-50 text-indigo-600' }; ?>"><?= $note['type'] ?></span>
                </div>
                <h4 class="text-base font-bold text-gray-900 mb-2"><?= htmlspecialchars($note['title']) ?></h4>
                <p class="text-sm text-gray-500 leading-relaxed"><?= nl2br(htmlspecialchars($note['content'])) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Holidays -->
    <div class="space-y-6">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-xl font-bold text-gray-900">Holiday Calendar 2026</h3>
            <button class="text-xs font-bold text-gray-400">View All</button>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="divide-y divide-gray-50 font-medium">
                <?php foreach ($holidays as $h): ?>
                <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-all">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 flex flex-col items-center justify-center border border-gray-100">
                            <span class="text-[10px] font-black text-indigo-600 leading-none"><?= strtoupper(date('M', strtotime($h['date']))) ?></span>
                            <span class="text-sm font-black text-gray-900"><?= date('d', strtotime($h['date'])) ?></span>
                        </div>
                        <span class="text-sm text-gray-800"><?= htmlspecialchars($h['title']) ?></span>
                    </div>
                    <span class="text-[10px] font-black uppercase text-gray-400"><?= $h['type'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>