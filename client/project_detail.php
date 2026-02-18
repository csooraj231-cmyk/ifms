<?php
require_once __DIR__ . '/../config/auth.php';
requireLogin();
$db = getDB();
$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: /ifms/client/projects.php');
    exit;
}

$stmt = $db->prepare("SELECT p.*, o.name as client_name FROM projects p LEFT JOIN organizations o ON p.organization_id = o.id WHERE p.id = ? LIMIT 1");
$stmt->execute([$id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$project) {
    echo 'Project not found';
    exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Project - <?= htmlspecialchars($project['name']) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
<?php include __DIR__ . '/../includes/sidebar.php'; ?>
<div class="p-8 ml-64">
  <h1 class="text-2xl font-bold mb-2">Project: <?= htmlspecialchars($project['name']) ?></h1>
  <p class="text-sm text-gray-600 mb-4">Status: <?= htmlspecialchars($project['status'] ?? 'N/A') ?></p>

  <div class="bg-white p-6 rounded-2xl shadow">
    <h2 class="font-bold mb-2">Overview</h2>
    <p><?= nl2br(htmlspecialchars($project['description'] ?? 'No description')) ?></p>
  </div>
</div>
</body>
</html>
