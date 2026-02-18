<?php
/**
 * IFMS - Developer Daily Updates
 */
session_start();
require_once __DIR__ . '/../../config/auth.php';
requireLogin();
requireDeveloperAccess();

$db = getDB();
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Updates - IFMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/ifms/assets/js/app.js"></script>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
        <div class="flex-1 overflow-auto">
            <?php include __DIR__ . '/../../includes/header.php'; ?>
            <div class="p-6 max-w-7xl mx-auto">
                <h1 class="text-3xl font-bold text-gray-900">Daily Updates</h1>
                <p class="text-gray-600 mt-2">Submit and manage your daily work updates</p>
                <div class="mt-8 bg-white rounded-3xl p-8 shadow-sm border border-gray-100 text-center">
                    <p class="text-gray-600 text-lg">Track your daily progress and updates here.</p>
                </div>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>
