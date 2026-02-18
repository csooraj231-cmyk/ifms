<?php
/**
 * IFMS API - Support Tickets
 */
require_once __DIR__ . '/../config/auth.php';
requireAPI();

$data = getPostData();
$action = $data['action'] ?? $_GET['action'] ?? '';
$db = getDB();
$user = getCurrentUser();

switch ($action) {
    case 'create':
        // Employees, developers, clients can create tickets
        $userRole = getUserRole();
        if (!in_array($userRole, ['employee', 'client', 'admin'])) {
            jsonResponse(['error' => 'Unauthorized - Employee, Developer, or Client access required'], 403);
        }
        $subject = $data['subject'] ?? '';
        $description = $data['description'] ?? '';
        $priority = $data['priority'] ?? 'medium';
        $projectId = $data['project_id'] ?: null;

        if (!$subject || !$description)
            jsonResponse(['error' => 'Subject and description required'], 400);

        // Generate ticket number
        $lastTicket = $db->query("SELECT MAX(id) as max_id FROM support_tickets")->fetch();
        $ticketNum = 'TKT-' . str_pad(($lastTicket['max_id'] ?? 0) + 1, 4, '0', STR_PAD_LEFT);

        $stmt = $db->prepare("INSERT INTO support_tickets (ticket_number, subject, description, priority, project_id, created_by, status, category) VALUES (?, ?, ?, ?, ?, ?, 'open', 'general')");
        $stmt->execute([$ticketNum, $subject, $description, $priority, $projectId, $user['id']]);

        jsonResponse(['success' => true, 'message' => 'Ticket created', 'ticket_number' => $ticketNum]);
        break;

    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}
