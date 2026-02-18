<?php
/**
 * IFMS - Holidays API
 * Manage holidays for the system
 */
session_start();
require_once __DIR__ . '/../config/auth.php';
requireAPI();

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Enforce admin-only operations
if (in_array($action, ['create', 'update', 'delete'])) {
    requireRole('admin');
}

switch ($action) {
    case 'get':
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID required']);
            exit;
        }
        $holiday = $db->prepare("SELECT * FROM holidays WHERE id = ?")->execute([$id])->fetch();
        echo json_encode([
            'success' => !!$holiday,
            'holiday' => $holiday ?: null
        ]);
        break;

    case 'list':
        $holidays = $db->query("SELECT * FROM holidays ORDER BY date DESC")->fetchAll();
        echo json_encode(['success' => true, 'holidays' => $holidays]);
        break;

    case 'create':
        $title = $_POST['title'] ?? '';
        $date = $_POST['date'] ?? '';
        $type = $_POST['type'] ?? 'national';
        
        if (!$title || !$date) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Title and date required']);
            exit;
        }

        $stmt = $db->prepare("INSERT INTO holidays (title, date, type) VALUES (?, ?, ?)");
        if ($stmt->execute([$title, $date, $type])) {
            echo json_encode(['success' => true, 'message' => 'Holiday created']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Failed to create holiday']);
        }
        break;

    case 'update':
        $id = (int)($_POST['id'] ?? 0);
        $title = $_POST['title'] ?? '';
        $date = $_POST['date'] ?? '';
        $type = $_POST['type'] ?? 'national';

        if ($id <= 0 || !$title || !$date) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
            exit;
        }

        $stmt = $db->prepare("UPDATE holidays SET title = ?, date = ?, type = ? WHERE id = ?");
        if ($stmt->execute([$title, $date, $type, $id])) {
            echo json_encode(['success' => true, 'message' => 'Holiday updated']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Failed to update holiday']);
        }
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID required']);
            exit;
        }

        $stmt = $db->prepare("DELETE FROM holidays WHERE id = ?");
        if ($stmt->execute([$id])) {
            echo json_encode(['success' => true, 'message' => 'Holiday deleted']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Failed to delete holiday']);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Unknown action']);
        break;
}
