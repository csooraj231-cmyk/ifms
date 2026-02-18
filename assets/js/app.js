/**
 * IFMS - Common JavaScript Utilities
 */

// ─── API Helper ──────────────────────────────────────────
async function api(url, method = 'GET', data = null) {
    const options = {
        method,
        headers: { 'Content-Type': 'application/json' },
    };
    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }
    try {
        const res = await fetch(url, options);
        const ct = res.headers.get('Content-Type') || res.headers.get('content-type') || '';
        let json;
        if (ct.includes('application/json')) {
            json = await res.json();
        } else {
            const raw = await res.text();
            throw new Error('Server returned invalid response: ' + raw);
        }
        if (!res.ok) throw new Error(json.error || json.message || 'Request failed');
        return json;
    } catch (err) {
        showToast(err.message, 'error');
        throw err;
    }
}

// ─── Toast Notifications ─────────────────────────────────
function showToast(message, type = 'success', duration = 4000) {
    const container = document.getElementById('toast-container') || createToastContainer();
    const toast = document.createElement('div');
    const icons = {
        success: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>`,
        error: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>`,
        warning: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3l9 16H3L12 3z"/></svg>`,
        info: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/></svg>`
    };
    const colors = {
        success: 'bg-emerald-500',
        error: 'bg-red-500',
        warning: 'bg-amber-500',
        info: 'bg-blue-500'
    };
    toast.className = `flex items-center gap-3 px-5 py-3 rounded-xl text-white shadow-2xl ${colors[type]} transform translate-x-full transition-all duration-500 ease-out`;
    toast.innerHTML = `${icons[type]}<span class="text-sm font-medium">${message}</span>`;
    container.appendChild(toast);
    requestAnimationFrame(() => toast.classList.remove('translate-x-full'));
    setTimeout(() => {
        toast.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => toast.remove(), 500);
    }, duration);
}

function createToastContainer() {
    const c = document.createElement('div');
    c.id = 'toast-container';
    c.className = 'fixed top-5 right-5 z-[9999] flex flex-col gap-3';
    document.body.appendChild(c);
    return c;
}

// ─── Modal System ────────────────────────────────────────
function openModal(id) {
    const m = document.getElementById(id);
    if (m) {
        m.classList.remove('hidden');
        m.classList.add('flex');
        requestAnimationFrame(() => {
            m.querySelector('.modal-content')?.classList.remove('scale-95', 'opacity-0');
            m.querySelector('.modal-content')?.classList.add('scale-100', 'opacity-100');
        });
    }
}

function closeModal(id) {
    const m = document.getElementById(id);
    if (m) {
        m.querySelector('.modal-content')?.classList.remove('scale-100', 'opacity-100');
        m.querySelector('.modal-content')?.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            m.classList.remove('flex');
            m.classList.add('hidden');
        }, 200);
    }
}

// ─── Sidebar Toggle ──────────────────────────────────────
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}

// ─── Profile Dropdown ────────────────────────────────────
function toggleProfileDropdown() {
    const dd = document.getElementById('profile-dropdown');
    dd.classList.toggle('hidden');
}

// Close dropdown on outside click
document.addEventListener('click', (e) => {
    const dd = document.getElementById('profile-dropdown');
    const btn = document.getElementById('profile-btn');
    if (dd && btn && !btn.contains(e.target) && !dd.contains(e.target)) {
        dd.classList.add('hidden');
    }
});

// ─── Format Currency (INR) ──────────────────────────────
function formatINR(amount) {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(amount);
}

// ─── Format Date ─────────────────────────────────────────
function formatDate(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
}

// ─── Status Badge Colors ─────────────────────────────────
function getStatusColor(status) {
    const colors = {
        'pending': 'bg-amber-100 text-amber-700',
        'approved': 'bg-blue-100 text-blue-700',
        'in_progress': 'bg-indigo-100 text-indigo-700',
        'completed': 'bg-emerald-100 text-emerald-700',
        'on_hold': 'bg-gray-100 text-gray-600',
        'cancelled': 'bg-red-100 text-red-700',
        'open': 'bg-amber-100 text-amber-700',
        'resolved': 'bg-emerald-100 text-emerald-700',
        'closed': 'bg-gray-100 text-gray-600',
        'waiting': 'bg-purple-100 text-purple-700',
        'draft': 'bg-gray-100 text-gray-600',
        'sent': 'bg-blue-100 text-blue-700',
        'paid': 'bg-emerald-100 text-emerald-700',
        'overdue': 'bg-red-100 text-red-700',
        'present': 'bg-emerald-100 text-emerald-700',
        'absent': 'bg-red-100 text-red-700',
        'half_day': 'bg-amber-100 text-amber-700',
        'late': 'bg-orange-100 text-orange-700',
        'on_leave': 'bg-purple-100 text-purple-700',
        'holiday': 'bg-cyan-100 text-cyan-700',
        'todo': 'bg-gray-100 text-gray-600',
        'review': 'bg-purple-100 text-purple-700',
        'blocked': 'bg-red-100 text-red-700',
        'generated': 'bg-blue-100 text-blue-700',
    };
    return colors[status] || 'bg-gray-100 text-gray-600';
}

function statusBadge(status) {
    const label = status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ${getStatusColor(status)}">${label}</span>`;
}

// ─── Priority Badge ──────────────────────────────────────
function priorityBadge(priority) {
    const colors = {
        'low': 'bg-gray-100 text-gray-600',
        'medium': 'bg-blue-100 text-blue-700',
        'high': 'bg-orange-100 text-orange-700',
        'critical': 'bg-red-100 text-red-700',
    };
    const label = priority.charAt(0).toUpperCase() + priority.slice(1);
    return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ${colors[priority] || colors.medium}">${label}</span>`;
}

// ─── Confirm Dialog ──────────────────────────────────────
function confirmAction(message) {
    return new Promise(resolve => {
        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm';
        overlay.innerHTML = `
            <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4 transform transition-all">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3l9 16H3L12 3z"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirm Action</h3>
                </div>
                <p class="text-gray-600 mb-6">${message}</p>
                <div class="flex gap-3 justify-end">
                    <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50 font-medium text-sm cancel-btn">Cancel</button>
                    <button class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 font-medium text-sm confirm-btn">Confirm</button>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);
        overlay.querySelector('.cancel-btn').addEventListener('click', () => {
            overlay.remove();
            resolve(false);
        });
        overlay.querySelector('.confirm-btn').addEventListener('click', () => {
            overlay.remove();
            resolve(true);
        });
    });
}

// ─── Search & Filter Helper ──────────────────────────────
function debounce(func, wait = 300) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

// ─── Charts Helper (Simple SVG) ──────────────────────────
function createDonutChart(container, data, size = 120) {
    const total = data.reduce((s, d) => s + d.value, 0);
    let cumulative = 0;
    const radius = size / 2 - 10;
    const cx = size / 2, cy = size / 2;

    let paths = '';
    data.forEach(d => {
        const start = (cumulative / total) * 2 * Math.PI - Math.PI / 2;
        cumulative += d.value;
        const end = (cumulative / total) * 2 * Math.PI - Math.PI / 2;
        const largeArc = d.value / total > 0.5 ? 1 : 0;
        const x1 = cx + radius * Math.cos(start);
        const y1 = cy + radius * Math.sin(start);
        const x2 = cx + radius * Math.cos(end);
        const y2 = cy + radius * Math.sin(end);
        paths += `<path d="M${cx},${cy} L${x1},${y1} A${radius},${radius},0,${largeArc},1,${x2},${y2} Z" fill="${d.color}" opacity="0.9"/>`;
    });

    container.innerHTML = `
        <svg width="${size}" height="${size}" viewBox="0 0 ${size} ${size}">
            ${paths}
            <circle cx="${cx}" cy="${cy}" r="${radius * 0.6}" fill="white"/>
        </svg>
    `;
}
