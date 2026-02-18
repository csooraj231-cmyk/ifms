<?php
/**
 * IFMS API - Employee Management
 */
require_once __DIR__ . '/../config/auth.php';
requireAPI();

$data = getPostData();
$action = $data['action'] ?? $_GET['action'] ?? '';

$db = getDB();

switch ($action) {
    case 'create':
        // Only HR employees and admins can create employees
        if (getUserRole() !== 'admin' && !isHREmployee()) {
            jsonResponse(['error' => 'Unauthorized - HR access required'], 403);
        }

        $fullName = $data['full_name'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $phone = $data['phone'] ?? '';
        $empCode = $data['employee_code'] ?? 'EMP-' . date('YmdHis');
        $deptId = $data['department_id'] ?? '';
        $designation = $data['designation'] ?? '';
        $doj = $data['date_of_joining'] ?? date('Y-m-d');
        $baseSalary = $data['base_salary'] ?? 0;
        $hra = $data['hra'] ?? 0;
        $seniorDevId = $data['senior_developer_id'] ?? null;

        if (!$fullName || !$email || !$password || !$deptId || !$designation) {
            jsonResponse(['error' => 'Missing required fields'], 400);
        }

        try {
            $db->beginTransaction();

            // Create user account
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (email, password, role, full_name, phone) VALUES (?, ?, 'employee', ?, ?)");
            $stmt->execute([$email, $hash, $fullName, $phone]);
            $userId = $db->lastInsertId();

            // Create employee record with senior_developer_id and salary_type
            $stmt = $db->prepare("INSERT INTO employees (user_id, employee_code, department_id, designation, date_of_joining, base_salary, hra, senior_developer_id, salary_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $empCode, $deptId, $designation, $doj, $baseSalary, $hra, $seniorDevId ? intval($seniorDevId) : null, $data['salary_type'] ?? 'monthly']);

            $db->commit();
            jsonResponse(['success' => true, 'message' => 'Employee created successfully']);
        }
        catch (Exception $e) {
            $db->rollBack();
            jsonResponse(['error' => 'Failed to create employee: ' . $e->getMessage()], 500);
        }
        break;

    case 'list':
        $employees = $db->query("SELECT e.*, u.email, u.full_name, u.phone, d.name AS dept FROM employees e JOIN users u ON e.user_id = u.id JOIN departments d ON e.department_id = d.id ORDER BY u.full_name")->fetchAll();
        jsonResponse(['success' => true, 'data' => $employees]);
        break;

    case 'get':
        if (getUserRole() !== 'admin' && !isHREmployee()) jsonResponse(['error' => 'Unauthorized'], 403);
        $eid = intval($data['employee_id'] ?? $_GET['employee_id'] ?? 0);
        if (!$eid) jsonResponse(['error' => 'Employee ID required'], 400);
        $stmt = $db->prepare("SELECT e.*, u.email, u.full_name, u.phone, u.role as user_role FROM employees e JOIN users u ON e.user_id = u.id WHERE e.id = ? LIMIT 1");
        $stmt->execute([$eid]);
        $row = $stmt->fetch();
        if (!$row) jsonResponse(['error' => 'Employee not found'], 404);
        // HR cannot fetch administrators
        if (isHREmployee() && $row['user_role'] === 'admin') jsonResponse(['error' => 'Unauthorized to manage administrator'], 403);
        jsonResponse(['success' => true, 'data' => $row]);
        break;

    case 'update':
        if (getUserRole() !== 'admin' && !isHREmployee()) jsonResponse(['error' => 'Unauthorized'], 403);
        $eid = intval($data['employee_id'] ?? 0);
        if (!$eid) jsonResponse(['error' => 'Employee ID required'], 400);
        // Load existing
        $stmt = $db->prepare("SELECT e.*, u.id as user_id, u.role as user_role FROM employees e JOIN users u ON e.user_id = u.id WHERE e.id = ? LIMIT 1");
        $stmt->execute([$eid]);
        $row = $stmt->fetch();
        if (!$row) jsonResponse(['error' => 'Employee not found'], 404);
        if (isHREmployee() && $row['user_role'] === 'admin') jsonResponse(['error' => 'Unauthorized to manage administrator'], 403);

        // Allowed fields
        $fullName = $data['full_name'] ?? $row['full_name'];
        $email = $data['email'] ?? $row['email'];
        $phone = $data['phone'] ?? $row['phone'];
        $department_id = $data['department_id'] ?? $row['department_id'];
        $designation = $data['designation'] ?? $row['designation'];
        $base_salary = $data['base_salary'] ?? $row['base_salary'];
        $salary_type = $data['salary_type'] ?? $row['salary_type'];
        $is_active = isset($data['is_active']) ? intval($data['is_active']) : $row['is_active'];

        // Check email uniqueness if changed
        if ($email !== $row['email']) {
            $check = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $check->execute([$email]);
            if ($check->fetch()) jsonResponse(['error' => 'Email already in use'], 400);
        }

        try {
            $db->beginTransaction();
            $uStmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
            $uStmt->execute([$fullName, $email, $phone, $row['user_id']]);

            $eStmt = $db->prepare("UPDATE employees SET department_id = ?, designation = ?, base_salary = ?, salary_type = ?, is_active = ? WHERE id = ?");
            $eStmt->execute([$department_id, $designation, $base_salary, $salary_type, $is_active, $eid]);

            $db->commit();
            jsonResponse(['success' => true, 'message' => 'Employee updated']);
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(['error' => 'Failed to update employee: ' . $e->getMessage()], 500);
        }
        break;

    case 'deactivate':
        if (getUserRole() !== 'admin' && !isHREmployee()) jsonResponse(['error' => 'Unauthorized'], 403);
        $eid = intval($data['employee_id'] ?? 0);
        if (!$eid) jsonResponse(['error' => 'Employee ID required'], 400);
        $stmt = $db->prepare("SELECT e.*, u.id as user_id, u.role as user_role FROM employees e JOIN users u ON e.user_id = u.id WHERE e.id = ? LIMIT 1");
        $stmt->execute([$eid]);
        $row = $stmt->fetch();
        if (!$row) jsonResponse(['error' => 'Employee not found'], 404);
        if (isHREmployee() && $row['user_role'] === 'admin') jsonResponse(['error' => 'Unauthorized to manage administrator'], 403);
        try {
            $db->beginTransaction();
            $db->prepare("UPDATE employees SET is_active = 0 WHERE id = ?")->execute([$eid]);
            $db->prepare("UPDATE users SET is_active = 0 WHERE id = ?")->execute([$row['user_id']]);
            $db->commit();
            jsonResponse(['success' => true, 'message' => 'Employee deactivated']);
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(['error' => 'Failed to deactivate: ' . $e->getMessage()], 500);
        }
        break;

    case 'delete':
        // Only admin can permanently delete (hard delete)
        if (getUserRole() !== 'admin') jsonResponse(['error' => 'Unauthorized - Admin access required'], 403);
        $eid = intval($data['employee_id'] ?? 0);
        if (!$eid) jsonResponse(['error' => 'Employee ID required'], 400);
        $stmt = $db->prepare("SELECT e.*, u.id as user_id, u.role as user_role FROM employees e JOIN users u ON e.user_id = u.id WHERE e.id = ? LIMIT 1");
        $stmt->execute([$eid]);
        $row = $stmt->fetch();
        if (!$row) jsonResponse(['error' => 'Employee not found'], 404);
        // Cannot delete other admins
        if ($row['user_role'] === 'admin') jsonResponse(['error' => 'Cannot delete administrator accounts'], 403);
        try {
            $db->beginTransaction();
            // Delete all related records in reverse dependency order
            $db->prepare("DELETE FROM task_assignments WHERE employee_id = ?")->execute([$eid]);
            $db->prepare("DELETE FROM daily_updates WHERE employee_id = ?")->execute([$eid]);
            $db->prepare("DELETE FROM attendance WHERE employee_id = ?")->execute([$eid]);
            $db->prepare("DELETE FROM payroll WHERE employee_id = ?")->execute([$eid]);
            $db->prepare("DELETE FROM project_team WHERE employee_id = ?")->execute([$eid]);
            $db->prepare("DELETE FROM employees WHERE id = ?")->execute([$eid]);
            $db->prepare("DELETE FROM users WHERE id = ?")->execute([$row['user_id']]);
            $db->commit();
            jsonResponse(['success' => true, 'message' => 'Employee permanently deleted']);
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(['error' => 'Failed to delete: ' . $e->getMessage()], 500);
        }
        break;

    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}

