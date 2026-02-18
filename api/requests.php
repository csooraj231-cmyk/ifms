<?php
/**
 * IFMS API - Employee Requests (leave/support/other)
 */
require_once __DIR__ . '/../config/auth.php';
requireAPI();

$data = getPostData();
$action = $data['action'] ?? $_GET['action'] ?? '';
$db = getDB();

switch ($action) {
    case 'create':
        $user = getCurrentUser();
        $employeeId = $user['employee_id'] ?? null;
        $type = $_POST['type'] ?? $data['type'] ?? 'general';
        $title = trim($_POST['title'] ?? $data['title'] ?? '');
        $message = trim($_POST['message'] ?? $data['message'] ?? '');
        $leaveDate = $_POST['leave_date'] ?? $data['leave_date'] ?? null;
        $leaveDays = $_POST['leave_days'] ?? $data['leave_days'] ?? 1;

        if (!$employeeId || !$title || !$message) jsonResponse(['error' => 'Missing fields'], 400);

        try {
            $status = 'pending';
            $stmt = $db->prepare("
                INSERT INTO requests (employee_id, type, title, message, status, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$employeeId, $type, $title, $message, $status]);
            
            // If leave request, store additional details in message or separate logic
            if ($type === 'leave' && $leaveDate) {
                $leaveInfo = "Leave Date: $leaveDate | Days: $leaveDays";
                $fullMessage = $message . "\n\n" . $leaveInfo;
                $stmt = $db->prepare("UPDATE requests SET message = ? WHERE employee_id = ? AND created_at = NOW() LIMIT 1");
                $stmt->execute([$fullMessage, $employeeId]);
            }
            
            jsonResponse(['success' => true, 'message' => 'Request submitted']);
        } catch (Exception $e) {
            jsonResponse(['error' => 'Failed to submit request: ' . $e->getMessage()], 500);
        }
        break;

    case 'list':
        // Employees get their own requests; HR/admin get all
        $user = getCurrentUser();
        if (getUserRole() === 'admin' || isHREmployee()) {
            $reqs = $db->query("SELECT r.*, u.full_name FROM requests r JOIN employees e ON r.employee_id = e.id JOIN users u ON e.user_id = u.id ORDER BY r.created_at DESC")->fetchAll();
        } else {
            $empId = $user['employee_id'] ?? 0;
            $stmt = $db->prepare("SELECT r.*, u.full_name FROM requests r JOIN employees e ON r.employee_id = e.id JOIN users u ON e.user_id = u.id WHERE r.employee_id = ? ORDER BY r.created_at DESC");
            $stmt->execute([$empId]);
            $reqs = $stmt->fetchAll();
        }
        jsonResponse(['success' => true, 'data' => $reqs]);
        break;

    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}
