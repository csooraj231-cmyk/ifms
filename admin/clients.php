<?php
/**
 * IFMS - Admin: Client Management
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('admin');

$db = getDB();
$pageTitle = 'Organization Management';

$orgs = $db->query("
    SELECT o.*, 
        (SELECT COUNT(*) FROM projects p WHERE p.organization_id = o.id) AS project_count,
        (SELECT COUNT(*) FROM client_users cu WHERE cu.organization_id = o.id) AS user_count
    FROM organizations o ORDER BY o.name ASC
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h2 class="text-2xl font-black text-gray-900 tracking-tight">Organizations</h2>
        <p class="text-sm text-gray-500 font-medium">Manage client accounts and their portal access.</p>
    </div>
    <button onclick="openModal('add-org-modal')" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-2xl font-bold text-sm hover:bg-indigo-700 shadow-xl shadow-indigo-500/20 active:scale-95 transition-all">
        Add Organization
    </button>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($orgs as $org): ?>
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 hover:shadow-xl transition-all group">
        <h3 class="text-lg font-black text-gray-900 group-hover:text-indigo-600 transition-colors"><?= htmlspecialchars($org['name']) ?></h3>
        <p class="text-xs text-indigo-500 font-bold uppercase tracking-widest mt-1"><?= htmlspecialchars($org['industry']) ?></p>
        
        <div class="mt-6 flex gap-4">
            <div class="px-3 py-2 bg-slate-50 rounded-xl">
                <p class="text-[10px] font-black text-gray-400 uppercase mb-0.5">Projects</p>
                <p class="text-sm font-black text-gray-900"><?= $org['project_count'] ?></p>
            </div>
            <div class="px-3 py-2 bg-slate-50 rounded-xl">
                <p class="text-[10px] font-black text-gray-400 uppercase mb-0.5">Users</p>
                <p class="text-sm font-black text-gray-900"><?= $org['user_count'] ?></p>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t border-gray-50 flex items-center justify-between">
            <span class="text-[10px] font-bold text-gray-400 italic"><?= htmlspecialchars($org['city']) ?>, <?= htmlspecialchars($org['state']) ?></span>
            <button onclick="openOrgDetailsModal(<?= $org['id'] ?>, '<?= htmlspecialchars($org['name']) ?>')" class="text-xs font-bold text-indigo-600 group-hover:underline">View Details</button>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Add Organization Modal -->
<div id="add-org-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl max-h-screen overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-100 p-6 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Add New Organization</h2>
            <button onclick="closeModal('add-org-modal')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <form id="add-org-form" class="p-6 space-y-4">
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Organization Name</label>
                <input type="text" name="name" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Organization Email</label>
                <input type="email" name="email" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Contact Person Name</label>
                <input type="text" name="contact_name" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Contact Email</label>
                <input type="email" name="contact_email" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Contact Password (Initial)</label>
                <input type="password" name="contact_password" required class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Phone Number</label>
                <input type="tel" name="phone" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">Industry</label>
                <input type="text" name="industry" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">City</label>
                <input type="text" name="city" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-2">State</label>
                <input type="text" name="state" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeModal('add-org-modal')" class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-200 transition-all">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all">Add Organization</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('add-org-form').onsubmit = async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target));
    try {
        const res = await fetch('/ifms/api/clients.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'create', ...data })
        });
        const json = await res.json();
        if (json.success) {
            showToast('Organization added successfully!');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(json.message || 'Error adding organization', 'error');
        }
    } catch (err) {
        showToast('Error adding organization', 'error');
    }
};

// Organization Details Modal
async function openOrgDetailsModal(orgId, orgName) {
    try {
        const res = await fetch('/ifms/api/clients.php?action=detail&id=' + orgId);
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
            showToast('Error loading organization details: ' + (json.error || 'Unknown error'), 'error');
            return;
        }
        
        const org = json.data;
        document.getElementById('org-detail-title').textContent = org.name;
        document.getElementById('org-detail-industry').textContent = org.industry || 'N/A';
        document.getElementById('org-detail-website').innerHTML = org.website ? `<a href="${org.website}" target="_blank" class="text-indigo-600 hover:underline">${org.website}</a>` : 'N/A';
        document.getElementById('org-detail-email').textContent = org.email || 'N/A';
        document.getElementById('org-detail-phone').textContent = org.phone || 'N/A';
        document.getElementById('org-detail-address').textContent = org.address || 'N/A';
        document.getElementById('org-detail-city').textContent = org.city || 'N/A';
        document.getElementById('org-detail-state').textContent = org.state || 'N/A';
        document.getElementById('org-detail-gst').textContent = org.gst_number || 'N/A';
        
        openModal('org-detail-modal');
    } catch (err) {
        showToast('Error loading organization details: ' + err.message, 'error');
    }
}
</script>

<!-- Organization Details Modal -->
<div id="org-detail-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl max-h-screen overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-100 p-6 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900" id="org-detail-title"></h2>
            <button onclick="closeModal('org-detail-modal')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <div class="p-6 space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Industry</h3>
                    <p class="text-sm font-bold text-gray-900" id="org-detail-industry"></p>
                </div>
                <div>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Website</h3>
                    <div class="text-sm font-bold text-gray-900" id="org-detail-website"></div>
                </div>
                <div>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Email</h3>
                    <p class="text-sm font-bold text-gray-900" id="org-detail-email"></p>
                </div>
                <div>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Phone</h3>
                    <p class="text-sm font-bold text-gray-900" id="org-detail-phone"></p>
                </div>
            </div>
            
            <div>
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Address</h3>
                <p class="text-sm font-bold text-gray-900" id="org-detail-address"></p>
            </div>
            
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">City</h3>
                    <p class="text-sm font-bold text-gray-900" id="org-detail-city"></p>
                </div>
                <div>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">State</h3>
                    <p class="text-sm font-bold text-gray-900" id="org-detail-state"></p>
                </div>
                <div>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">GST Number</h3>
                    <p class="text-sm font-bold text-gray-900" id="org-detail-gst"></p>
                </div>
            </div>
            
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeModal('org-detail-modal')" class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-200 transition-all">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>