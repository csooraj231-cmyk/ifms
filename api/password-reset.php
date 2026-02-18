<?php
/**
 * IFMS - Password Reset API
 */
require_once __DIR__ . '/../config/auth.php';
header('Content-Type: application/json');

$db = getDB();
$data = getPostData();
$action = $data['action'] ?? '';

switch ($action) {
    case 'send_token':
        $email = $data['email'] ?? '';
        if (empty($email)) jsonResponse(['success' => false, 'message' => 'Email is required'], 400);

        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        if (!$stmt->fetch()) {
            jsonResponse(['success' => true, 'message' => 'If this email is registered, a reset link has been sent.']);
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expires]);

        jsonResponse(['success' => true, 'message' => 'Reset link sent!', 'debug_token' => $token]);
        break;

    case 'verify_token':
        $token = $data['token'] ?? '';
        if (empty($token)) jsonResponse(['success' => false, 'message' => 'Token is required'], 400);

        $stmt = $db->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

        if (!$reset) jsonResponse(['success' => false, 'message' => 'Invalid or expired token'], 400);
        jsonResponse(['success' => true, 'email' => $reset['email']]);
        break;

    case 'reset_password':
        $token = $data['token'] ?? '';
        $password = $data['password'] ?? '';
        
        if (empty($token) || empty($password)) jsonResponse(['success' => false, 'message' => 'Required fields missing'], 400);

        $stmt = $db->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

        if (!$reset) jsonResponse(['success' => false, 'message' => 'Invalid or expired token'], 400);

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $db->prepare("UPDATE users SET password = ? WHERE email = ?")->execute([$hashed, $reset['email']]);
        $db->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$reset['email']]);

        jsonResponse(['success' => true, 'message' => 'Password reset successfully!']);
        break;

    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}