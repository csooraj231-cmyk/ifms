<?php
require_once __DIR__ . '/../../config/auth.php';
requireLogin();
if (!isSupportStaff()) { header('Location: /ifms/employee/index.php'); exit; }
$db = getDB();
$tickets = $db->query("SELECT t.*, u.full_name as created_by_name, p.title as project_title FROM support_tickets t LEFT JOIN users u ON u.id = t.created_by LEFT JOIN projects p ON p.id = t.project_id ORDER BY t.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Support Tickets</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
<?php include __DIR__ . '/../../includes/sidebar.php'; ?>
<div class="p-8 ml-64">
  <h1 class="text-2xl font-bold mb-4">Support Tickets</h1>
  <div class="space-y-4">
    <?php foreach ($tickets as $t): ?>
      <div class="bg-white p-4 rounded-xl shadow">
        <div class="flex justify-between">
          <div>
                  <h3 class="font-bold"><?= htmlspecialchars($t['subject']) ?></h3>
                  <p class="text-sm text-gray-600"><?= htmlspecialchars($t['description'] ?? '') ?></p>
                  <div class="text-xs text-gray-400">Created by: <?= htmlspecialchars($t['created_by_name'] ?? 'N/A') ?><?= !empty($t['project_title']) ? ' — Project: ' . htmlspecialchars($t['project_title']) : '' ?></div>
          </div>
          <div class="text-sm">Status: <?= htmlspecialchars($t['status'] ?? 'open') ?></div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (empty($tickets)): ?>
      <div class="bg-white p-8 rounded-xl text-center">No tickets found.</div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
