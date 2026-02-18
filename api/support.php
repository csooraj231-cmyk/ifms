<?php
/**
 * Simple Support API: list, update status
 */
require_once __DIR__ . '/../config/auth.php';
requireAPI();
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$db = getDB();

switch ($action) {
    case 'list':
        $tickets = $db->query("SELECT t.*, u.full_name as created_by_name, p.title as project_title FROM support_tickets t LEFT JOIN users u ON u.id = t.created_by LEFT JOIN projects p ON p.id = t.project_id ORDER BY t.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $tickets]);
        exit;
    case 'update_status':
        if (!isSupportStaff() && getUserRole() !== 'admin') jsonResponse(['error' => 'Unauthorized'], 403);
        $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
        $status = $_POST['status'] ?? $_GET['status'] ?? '';
        if (!$id || !$status) jsonResponse(['error' => 'Missing parameters'], 400);
        $stmt = $db->prepare("UPDATE support_tickets SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        jsonResponse(['success' => true, 'message' => 'Status updated']);
        break;
    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}

function jsonResponse($resp, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($resp);
    exit;
}
