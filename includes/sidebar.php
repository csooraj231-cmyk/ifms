<?php
/**
 * IFMS - Role-based Sidebar
 */
$currentUrl = $_SERVER['REQUEST_URI'];
$role = getUserRole();
$dept = getDepartmentSlug();

function isActive($path) {
    global $currentUrl;
    return strpos($currentUrl, $path) !== false ? 'sidebar-item-active' : 'text-gray-500 hover:bg-indigo-50 hover:text-indigo-600';
}
?>
<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 border-r border-gray-100 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out shadow-sm" style="background-color: #AEE7F0;">
    <div class="flex flex-col h-full">
        <!-- Logo -->
        <div class="p-8 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <span class="text-xl font-black text-gray-900 tracking-tight">IFMS</span>
                    <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-[0.2em] leading-none mt-1">Core Tech</p>
                </div>
            </div>
        </div>

        <!-- Menu -->
        <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto custom-scrollbar">
            <?php if ($role === 'admin'): ?>
                <!-- Admin Menu -->
                <p class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 mt-4">Management</p>
                <a href="/ifms/admin/index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-sm <?= isActive('admin/index.php') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg> Dashboard
                </a>
                <a href="/ifms/admin/employees.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-sm <?= isActive('admin/employees.php') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Employees
                </a>
                <a href="/ifms/admin/clients.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-sm <?= isActive('admin/clients.php') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg> Clients
                </a>
                <a href="/ifms/admin/projects.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-sm <?= isActive('admin/projects.php') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg> Projects
                </a>
                <a href="/ifms/admin/attendance.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-sm <?= isActive('admin/attendance.php') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Attendance
                </a>
                <a href="/ifms/admin/payroll.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-sm <?= isActive('admin/payroll.php') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg> Payroll
                </a>
                <a href="/ifms/admin/holidays.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-sm <?= isActive('admin/holidays.php') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg> Holidays
                </a>

            <?php elseif ($role === 'employee'): ?>
                <!-- Employee Menu -->
                <a href="/ifms/employee/index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-sm <?= isActive('employee/index.php') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg> Dashboard
                </a>
                
                <!-- Requests & General -->
                <p class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 mt-4">General</p>
                <a href="/ifms/holidays.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-sm <?= isActive('holidays.php') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg> Holidays
                </a>
                <a href="/ifms/employee/requests.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-sm <?= isActive('employee/requests.php') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M7 8h10M7 12h10m-10 4h10M3 6a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V6z"/></svg> Requests
                </a>
                <p class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 mt-4">HR Operations</p>
                <a href="/ifms/employee/hr/employees.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-sm <?= isActive('employee/hr/employees.php') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Employees
                </a>
                <a href="/ifms/employee/hr/attendance.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-sm <?= isActive('employee/hr/attendance.php') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg> Attendance
                </a>

                <!-- Finance Menu -->
                <?php if (isFinanceEmployee()): ?>
                <p class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 mt-4">Finance Operations</p>
                <a href="/ifms/employee/finance/payroll.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-sm <?= isActive('employee/finance/payroll.php') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg> Payroll
                </a>
                <a href="/ifms/employee/finance/invoices.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-sm <?= isActive('employee/finance/invoices.php') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Invoices
                </a>
                <?php endif; ?>

                <!-- Developer Menu -->
                <?php if (isDeveloper() || isSeniorDeveloper()): ?>
                <p class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 mt-4">Development</p>
                <a href="/ifms/employee/developer/projects.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-sm <?= isActive('employee/developer/projects.php') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg> Projects
                </a>
                <a href="/ifms/employee/developer/tasks.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-sm <?= isActive('employee/developer/tasks.php') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Tasks
                </a>
                <a href="/ifms/employee/developer/daily-updates.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-sm <?= isActive('employee/developer/daily-updates.php') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg> Daily Updates
                </a>
                <?php endif; ?>

                <!-- Senior Developer Menu -->
                <?php if (isSeniorDeveloper()): ?>
                <p class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 mt-4">Team Management</p>
                <a href="/ifms/employee/developer/team.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-sm <?= isActive('employee/developer/team.php') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Team
                </a>
                <a href="/ifms/employee/developer/milestones.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-sm <?= isActive('employee/developer/milestones.php') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg> Milestones
                </a>
                <?php endif; ?>

                <!-- Standard Employee Menu -->
                <p class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 mt-4">Personal</p>
                <a href="/ifms/employee/profile.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-sm <?= isActive('employee/profile.php') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg> Profile
                </a>
                <a href="/ifms/employee/requests.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-sm <?= isActive('employee/requests.php') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Requests
                </a>
                <a href="/ifms/employee/payroll.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-sm <?= isActive('employee/payroll.php') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg> My Payslips
                </a>

            <?php elseif ($role === 'client'): ?>
                <!-- Client Menu -->
                <a href="/ifms/client/index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-sm <?= isActive('client/index.php') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg> Dashboard
                </a>
                <a href="/ifms/client/projects.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-sm <?= isActive('client/projects.php') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg> My Projects
                </a>
                <a href="/ifms/client/tickets.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-sm <?= isActive('client/tickets.php') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg> Support Tickets
                </a>
                <a href="/ifms/client/billing.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium text-sm <?= isActive('client/billing.php') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Billing & Invoices
                </a>
            <?php endif; ?>
        </nav>

        <!-- Footer -->
        <div class="p-6 border-t border-gray-100">
            <div class="p-4 bg-slate-50 rounded-2xl">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Status</p>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                    <span class="text-xs font-bold text-gray-700">All Systems Online</span>
                </div>
            </div>
        </div>
    </div>
</aside>