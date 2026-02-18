<?php
/**
 * IFMS - Admin: Reports & Analytics
 */
require_once __DIR__ . '/../config/auth.php';
requireRole('admin');

$db = getDB();
$pageTitle = 'Reports & Analytics';

// Department distribution
$deptStats = $db->query("
    SELECT d.name, COUNT(e.id) as emp_count 
    FROM departments d LEFT JOIN employees e ON d.id = e.department_id 
    GROUP BY d.id
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="mb-8">
    <h2 class="text-2xl font-black text-gray-900 tracking-tight">Analytics Overview</h2>
    <p class="text-sm text-gray-500 font-medium">Visual breakdown of resources and performance.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Dept Chart -->
    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
        <h3 class="text-lg font-bold text-gray-900 mb-6">Staffing by Department</h3>
        <canvas id="deptChart" height="250"></canvas>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-indigo-600 p-8 rounded-3xl text-white shadow-xl shadow-indigo-500/20">
            <p class="text-sm font-bold opacity-80 uppercase tracking-widest">Efficiency</p>
            <p class="text-4xl font-black mt-4">94%</p>
        </div>
        <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
            <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">Active Projects</p>
            <p class="text-4xl font-black text-gray-900 mt-4">12</p>
        </div>
        <div class="bg-emerald-500 p-8 rounded-3xl text-white shadow-xl shadow-emerald-500/20">
            <p class="text-sm font-bold opacity-80 uppercase tracking-widest">Hiring Index</p>
            <p class="text-4xl font-black mt-4">8.2</p>
        </div>
        <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
            <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">Budget Utilization</p>
            <p class="text-4xl font-black text-gray-900 mt-4">76%</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('deptChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($deptStats, 'name')) ?>,
            datasets: [{
                label: 'Employees',
                data: <?= json_encode(array_column($deptStats, 'emp_count')) ?>,
                backgroundColor: '#4f46e5',
                borderRadius: 12
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, grid: { display: false } }, x: { grid: { display: false } } }
        }
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>