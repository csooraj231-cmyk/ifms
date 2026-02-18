<?php
/**
 * IFMS API - Client/Organization Management
 */
require_once __DIR__ . '/../config/auth.php';
requireAPI();

$data = getPostData();
$action = $data['action'] ?? $_GET['action'] ?? '';
$db = getDB();

switch ($action) {
    case 'create':
        // Only admins and HR can create clients
        if (getUserRole() !== 'admin' && !isHREmployee()) {
            jsonResponse(['error' => 'Unauthorized - Admin or HR access required'], 403);
        }

        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $contactName = $data['contact_name'] ?? '';
        $contactEmail = $data['contact_email'] ?? '';
        $contactPassword = $data['contact_password'] ?? '';

        if (!$name || !$email || !$contactName || !$contactEmail || !$contactPassword) {
            jsonResponse(['error' => 'Missing required fields'], 400);
        }

        try {
            $db->beginTransaction();

            // Generate slug from organization name
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
            
            // Create organization
            $stmt = $db->prepare("INSERT INTO organizations (name, slug, email, phone, website, gst_number, address, city, state, industry) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $email, $data['phone'] ?? '', $data['website'] ?? '', $data['gst_number'] ?? '', $data['address'] ?? '', $data['city'] ?? '', $data['state'] ?? '', $data['industry'] ?? '']);
            $orgId = $db->lastInsertId();

            // Create user for the primary contact
            $hash = password_hash($contactPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (email, password, role, full_name) VALUES (?, ?, 'client', ?)");
            $stmt->execute([$contactEmail, $hash, $contactName]);
            $userId = $db->lastInsertId();

            // Create client_user link
            $stmt = $db->prepare("INSERT INTO client_users (user_id, organization_id, designation, is_primary) VALUES (?, ?, ?, 1)");
            $stmt->execute([$userId, $orgId, $data['contact_designation'] ?? '']);

            $db->commit();
            jsonResponse(['success' => true, 'message' => 'Organization created']);
        }
        catch (Exception $e) {
            $db->rollBack();
            jsonResponse(['error' => 'Failed: ' . $e->getMessage()], 500);
        }
        break;

    case 'list':
        $clients = $db->query("SELECT o.*, COUNT(p.id) as project_count FROM organizations o LEFT JOIN projects p ON o.id = p.organization_id GROUP BY o.id ORDER BY o.name")->fetchAll();
        jsonResponse(['success' => true, 'data' => $clients]);
        break;

    case 'detail':
        $orgId = $data['id'] ?? $_GET['id'] ?? 0;
        if (!$orgId) {
            jsonResponse(['error' => 'Organization ID is required'], 400);
        }

        $org = $db->query("SELECT * FROM organizations WHERE id = {$orgId}")->fetch();
        
        if (!$org) {
            jsonResponse(['error' => 'Organization not found'], 404);
        }

        jsonResponse(['success' => true, 'data' => $org]);
        break;

    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}
