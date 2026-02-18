<?php
require_once __DIR__ . '/../../config/auth.php';
requireLogin();
if (!isDeveloper() && !isSeniorDeveloper()) { header('Location: /ifms/employee/index.php'); exit; }
$db = getDB();
$projectId = intval($_GET['project_id'] ?? 0);
$stmt = $db->prepare("SELECT t.* FROM tasks t WHERE t.project_id = ? ORDER BY t.priority DESC, t.id DESC");
$stmt->execute([$projectId]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Tasks</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
<?php include __DIR__ . '/../../includes/sidebar.php'; ?>
<div class="p-8 ml-64">
  <h1 class="text-2xl font-bold mb-4">Project Tasks</h1>
  <div class="space-y-4">
    <?php foreach ($tasks as $t): ?>
      <div class="bg-white p-4 rounded-xl shadow">
        <div class="flex justify-between">
          <div>
            <h3 class="font-bold"><?= htmlspecialchars($t['title']) ?></h3>
            <p class="text-sm text-gray-600"><?= htmlspecialchars($t['description'] ?? '') ?></p>
          </div>
          <div class="text-sm text-gray-500">Status: <?= htmlspecialchars($t['status'] ?? 'open') ?></div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (empty($tasks)): ?>
      <div class="bg-white p-8 rounded-xl text-center">No tasks found for this project.</div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
<?php
/**
 * IFMS - Developer Tasks
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
    <title>Tasks - IFMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/ifms/assets/js/app.js"></script>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
        <div class="flex-1 overflow-auto">
            <?php include __DIR__ . '/../../includes/header.php'; ?>
            <div class="p-6 max-w-7xl mx-auto">
                <h1 class="text-3xl font-bold text-gray-900">My Tasks</h1>
                <p class="text-gray-600 mt-2">Manage your development tasks</p>
                <div class="mt-8 bg-white rounded-3xl p-8 shadow-sm border border-gray-100 text-center">
                    <p class="text-gray-600 text-lg">This page will help you manage your assigned tasks efficiently.</p>
                </div>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>
