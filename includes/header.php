<?php
/**
 * IFMS - Shared Header
 */
require_once __DIR__ . '/../config/auth.php';
requireLogin();
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'IFMS' ?> - Infrastructure Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); }
        .sidebar-item-active { background: linear-gradient(to right, #4f46e5, #7c3aed); color: white; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3); }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">
    <div class="flex">
        <!-- Sidebar -->
        <?php include_once __DIR__ . '/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 min-h-screen transition-all duration-300 ml-0 lg:ml-64 p-4 lg:p-8">
            <!-- Top Nav -->
            <header class="flex items-center justify-between mb-8 bg-white/80 backdrop-blur-md sticky top-4 z-40 px-6 py-3 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex items-center gap-4">
                    <button id="toggle-sidebar" class="lg:hidden p-2 hover:bg-gray-100 rounded-xl">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <h1 class="text-lg font-bold text-gray-800 hidden sm:block"><?= $pageTitle ?? 'Dashboard' ?></h1>
                </div>

                <div class="flex items-center gap-3 lg:gap-6">
                    <!-- Search -->
                    <div class="hidden md:flex items-center relative">
                        <svg class="w-4 h-4 absolute left-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" placeholder="Search..." class="pl-10 pr-4 py-2 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 w-64">
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2">
                        <button class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-colors relative">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
                        </button>
                        
                        <div class="h-8 w-px bg-gray-100 mx-1"></div>

                        <!-- Profile -->
                        <div class="relative group">
                            <button class="flex items-center gap-3 p-1 hover:bg-gray-50 rounded-xl transition-all">
                                <div class="w-9 h-9 rounded-lg bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold text-sm shadow-md">
                                    <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                                </div>
                                <div class="hidden sm:block text-left">
                                    <p class="text-sm font-bold text-gray-800 leading-none"><?= htmlspecialchars($user['full_name']) ?></p>
                                    <p class="text-[10px] font-medium text-gray-400 mt-1 uppercase tracking-wider"><?= $user['role'] ?></p>
                                </div>
                            </button>
                            <!-- Dropdown mock -->
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-2xl border border-gray-100 shadow-xl py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                                <a href="/ifms/<?= $user['role'] ?>/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">My Profile</a>
                                <a href="/ifms/<?= $user['role'] ?>/settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Settings</a>
                                <div class="h-px bg-gray-100 my-1"></div>
                                <a href="/ifms/api/auth.php?action=logout" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Logout</a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>