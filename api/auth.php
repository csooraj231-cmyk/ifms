<?php
/**
 * IFMS - Auth API Endpoint
 */
require_once __DIR__ . '/../config/auth.php';

header('Content-Type: application/json');

$data = getPostData();
$action = $data['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            jsonResponse(['success' => false, 'message' => 'Email and password are required'], 400);
        }

        $result = loginUser($email, $password);
        jsonResponse($result, $result['success'] ? 200 : 401);
        break;

    case 'logout':
        session_unset();
        session_destroy();
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            header('Location: /ifms/');
            exit;
        }
        jsonResponse(['success' => true, 'message' => 'Logged out']);
        break;

    case 'me':
        requireAPI();
        $user = getCurrentUser();
        jsonResponse(['success' => true, 'user' => $user]);
        break;

    case 'update_profile':
        requireAPI();
        $user = getCurrentUser();
        $fullName = trim($data['full_name'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $email = trim($data['email'] ?? '');
        
        if (empty($fullName)) jsonResponse(['success' => false, 'message' => 'Full name is required'], 400);

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['success' => false, 'message' => 'Invalid email address'], 400);
        }

        $db = getDB();

        // If email provided and changed, ensure uniqueness
        if (!empty($email) && $email !== $user['email']) {
            $check = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
            $check->execute([$email, $user['id']]);
            if ($check->fetch()) {
                jsonResponse(['success' => false, 'message' => 'Email is already in use'], 409);
            }
        }

        $stmt = $db->prepare("UPDATE users SET full_name = ?, phone = ?, email = ? WHERE id = ?");
        $stmt->execute([$fullName, $phone, $email ?: $user['email'], $user['id']]);

        // Update session
        $_SESSION['user_name'] = $fullName;
        $_SESSION['user_phone'] = $phone;
        if (!empty($email)) {
            $_SESSION['user_email'] = $email;
        }

        jsonResponse(['success' => true, 'message' => 'Profile updated']);
        break;

    case 'update_password':
        requireAPI();
        $user = getCurrentUser();
        $currentPass = $data['current_password'] ?? '';
        $newPass = $data['new_password'] ?? '';
        
        if (empty($currentPass) || empty($newPass)) jsonResponse(['success' => false, 'message' => 'All fields are required'], 400);
        
        $db = getDB();
        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $u = $stmt->fetch();
        
        if (!password_verify($currentPass, $u['password'])) {
            jsonResponse(['success' => false, 'message' => 'Current password is incorrect'], 401);
        }
        
        $hashed = password_hash($newPass, PASSWORD_DEFAULT);
        $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $user['id']]);
        
        jsonResponse(['success' => true, 'message' => 'Password updated']);
        break;

    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}