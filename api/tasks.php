<?php
/**
 * IFMS API - Tasks & Daily Updates
 */
require_once __DIR__ . '/../config/auth.php';
requireAPI();

$data = getPostData();
$action = $data['action'] ?? $_GET['action'] ?? '';
$db = getDB();
$user = getCurrentUser();

switch ($action) {
    case 'update_status':
        // Developers and Senior Developers can update task status
        if (!isDeveloper() && !isSeniorDeveloper() && getUserRole() !== 'admin') {
            jsonResponse(['error' => 'Unauthorized - Developer access required'], 403);
        }
        $taskId = $data['task_id'] ?? 0;
        $status = $data['status'] ?? '';

        if (!$taskId || !$status)
            jsonResponse(['error' => 'Missing fields'], 400);

        $db->prepare("UPDATE tasks SET status = ?, updated_at = NOW() WHERE id = ?")->execute([$status, $taskId]);

        // Also update assignment status
        $empId = $user['employee_id'];
        if ($empId) {
            $db->prepare("UPDATE task_assignments SET status = ? WHERE task_id = ? AND employee_id = ?")->execute([$status, $taskId, $empId]);
        }

        jsonResponse(['success' => true, 'message' => 'Task status updated']);
        break;

    case 'daily_update':
        $empId = $user['employee_id'];
        if (!$empId)
            jsonResponse(['error' => 'Not an employee'], 403);

        $projectId = $data['project_id'] ?? 0;
        $workDone = $data['work_done'] ?? '';
        if (!$projectId || !$workDone)
            jsonResponse(['error' => 'Missing fields'], 400);

        $stmt = $db->prepare("INSERT INTO daily_updates (employee_id, project_id, update_date, work_done, hours_worked, blockers, plan_for_tomorrow) VALUES (?, ?, CURDATE(), ?, ?, ?, ?)");
        $stmt->execute([
            $empId, $projectId, $workDone,
            $data['hours_worked'] ?? 8,
            $data['blockers'] ?? null,
            $data['plan_for_tomorrow'] ?? null
        ]);
        jsonResponse(['success' => true, 'message' => 'Daily update submitted']);
        break;

    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}
