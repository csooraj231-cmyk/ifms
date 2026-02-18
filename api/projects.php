<?php
/**
 * IFMS API - Project Management
 */
require_once __DIR__ . '/../config/auth.php';
requireAPI();

$data = getPostData();
$action = $data['action'] ?? $_GET['action'] ?? '';
$db = getDB();

switch ($action) {
    case 'create':
        // Admins, Senior Devs, and Senior Dev managers can create projects
        if (getUserRole() !== 'admin' && !isSeniorDeveloper()) {
            jsonResponse(['error' => 'Unauthorized - Senior Developer access required'], 403);
        }

        $title = $data['title'] ?? '';
        if (!$title)
            jsonResponse(['error' => 'Title is required'], 400);

        // Generate slug from title
        $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $title));
        $slug = trim($slug, '-');

        // Set defaults for optional fields
        $description = $data['description'] ?? '';
        $org_id = isset($data['organization_id']) && $data['organization_id'] !== '' ? $data['organization_id'] : null;
        $priority = $data['priority'] ?? 'medium';
        $start_date = isset($data['start_date']) && $data['start_date'] !== '' ? $data['start_date'] : null;
        $end_date = isset($data['end_date']) && $data['end_date'] !== '' ? $data['end_date'] : null;
        $budget = isset($data['estimated_budget']) && $data['estimated_budget'] !== '' ? $data['estimated_budget'] : null;
        $status = $data['status'] ?? 'pending';
        $created_by = getCurrentUser()['id'];

        $stmt = $db->prepare("INSERT INTO projects (title, slug, description, organization_id, priority, start_date, end_date, estimated_budget, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $title,
            $slug,
            $description,
            $org_id,
            $priority,
            $start_date,
            $end_date,
            $budget,
            $status,
            $created_by
        ]);
        jsonResponse(['success' => true, 'message' => 'Project created', 'id' => $db->lastInsertId()]);
        break;

    case 'assign':
        // Admins and Senior Devs can assign team members
        if (getUserRole() !== 'admin' && !isSeniorDeveloper()) {
            jsonResponse(['error' => 'Unauthorized - Admin or Senior Developer access required'], 403);
        }

        $projectId = $data['project_id'] ?? 0;
        $employeeId = $data['employee_id'] ?? 0; // can be single id, CSV or array
        $role = $data['role'] ?? null;

        if (!$projectId || !$employeeId) {
            jsonResponse(['error' => 'Project and Employee IDs are required'], 400);
        }

        // Normalize employee ids to array
        $employeeIds = [];
        if (is_array($employeeId)) {
            $employeeIds = $employeeId;
        } else {
            if (is_string($employeeId) && strpos($employeeId, ',') !== false) {
                $employeeIds = array_map('trim', explode(',', $employeeId));
            } else {
                $employeeIds = [$employeeId];
            }
        }

        $assignedCount = 0;

        // Get employee's department and designation for smart role assignment
        if (!$role) {
                $firstEmpId = intval($employeeIds[0] ?? 0);
                $emp = $firstEmpId ? $db->query("SELECT e.designation, d.slug as dept_slug FROM employees e JOIN departments d ON e.department_id = d.id WHERE e.id = {$firstEmpId}")->fetch() : null;

            if ($emp) {
                // Smart role assignment based on department and designation
                if (strpos($emp['dept_slug'], 'development') !== false || strpos($emp['dept_slug'], 'it') !== false) {
                    if (stripos($emp['designation'], 'senior') !== false || stripos($emp['designation'], 'lead') !== false) {
                        $role = 'project_lead';
                    } else if (stripos($emp['designation'], 'junior') !== false) {
                        $role = 'junior_developer';
                    } else {
                        $role = 'developer';
                    }
                } else if (strpos($emp['dept_slug'], 'hr') !== false) {
                    $role = 'hr_manager';
                } else if (strpos($emp['dept_slug'], 'finance') !== false) {
                    $role = 'finance_manager';
                } else if (strpos($emp['dept_slug'], 'design') !== false) {
                    $role = 'designer';
                } else if (strpos($emp['dept_slug'], 'support') !== false || strpos($emp['dept_slug'], 'qa') !== false) {
                    $role = 'qa_tester';
                } else {
                    $role = 'member';
                }
            } else {
                $role = 'member';
            }
        }

        foreach ($employeeIds as $empId) {
            $empId = intval($empId);
            if (!$empId) continue;

            // Check if already assigned
            $existing = $db->query("SELECT id FROM project_team WHERE project_id = {$projectId} AND employee_id = {$empId}")->fetch();

            if ($existing) {
                $db->prepare("UPDATE project_team SET role = ? WHERE project_id = ? AND employee_id = ?")->execute([$role, $projectId, $empId]);
            }
            else {
                $db->prepare("INSERT INTO project_team (project_id, employee_id, role) VALUES (?, ?, ?)")->execute([$projectId, $empId, $role]);
            }
            $assignedCount++;
        }

        jsonResponse(['success' => true, 'message' => 'Team member(s) assigned', 'count' => $assignedCount, 'role' => $role]);
        break;

    case 'unassign':
        // Admins and Senior Devs can remove team members
        if (getUserRole() !== 'admin' && !isSeniorDeveloper()) {
            jsonResponse(['error' => 'Unauthorized - Admin or Senior Developer access required'], 403);
        }

        $projectId = $data['project_id'] ?? 0;
        $employeeId = $data['employee_id'] ?? 0;

        if (!$projectId || !$employeeId) {
            jsonResponse(['error' => 'Project and Employee IDs are required'], 400);
        }

        $db->prepare("DELETE FROM project_team WHERE project_id = ? AND employee_id = ?")->execute([$projectId, $employeeId]);
        jsonResponse(['success' => true, 'message' => 'Team member removed']);
        break;

    case 'detail':
        $projectId = $data['id'] ?? $_GET['id'] ?? 0;
        if (!$projectId) {
            jsonResponse(['error' => 'Project ID is required'], 400);
        }

        $project = $db->query("
            SELECT p.*, o.name AS org_name 
            FROM projects p 
            LEFT JOIN organizations o ON p.organization_id = o.id 
            WHERE p.id = {$projectId}
        ")->fetch();

        if (!$project) {
            jsonResponse(['error' => 'Project not found'], 404);
        }

        // Get team members
        $team = $db->query("
            SELECT pt.*, e.id, u.full_name, u.email, e.designation, d.name AS department 
            FROM project_team pt 
            JOIN employees e ON pt.employee_id = e.id 
            JOIN users u ON e.user_id = u.id 
            JOIN departments d ON e.department_id = d.id 
            WHERE pt.project_id = {$projectId}
        ")->fetchAll();

        $project['team'] = $team;
        jsonResponse(['success' => true, 'data' => $project]);
        break;

    case 'edit':
        // Admins and Senior Devs can edit projects
        if (getUserRole() !== 'admin' && !isSeniorDeveloper()) {
            jsonResponse(['error' => 'Unauthorized - Admin or Senior Developer access required'], 403);
        }

        $projectId = $data['id'] ?? 0;
        $title = $data['title'] ?? '';

        if (!$projectId || !$title) {
            jsonResponse(['error' => 'Project ID and Title are required'], 400);
        }

        $stmt = $db->prepare("
            UPDATE projects 
            SET title = ?, description = ?, priority = ?, start_date = ?, end_date = ?, estimated_budget = ?, status = ? 
            WHERE id = ?
        ");
        $stmt->execute([
            $title,
            $data['description'] ?? '',
            $data['priority'] ?? 'medium',
            $data['start_date'] ?: null,
            $data['end_date'] ?: null,
            $data['estimated_budget'] ?: null,
            $data['status'] ?? 'pending',
            $projectId
        ]);

        jsonResponse(['success' => true, 'message' => 'Project updated']);
        break;

    case 'task_add':
        // Add task to project
        if (getUserRole() !== 'admin' && !isSeniorDeveloper()) {
            jsonResponse(['error' => 'Unauthorized - Senior Developer access required'], 403);
        }

        $projectId = $data['project_id'] ?? 0;
        $title = $data['title'] ?? '';

        if (!$projectId || !$title) {
            jsonResponse(['error' => 'Project ID and Title are required'], 400);
        }

        // Support file upload (multipart/form-data). Accept both JSON and form-data
        $description = $data['description'] ?? '';
        if (!empty($_FILES['attachment']['name'])) {
            $upDir = __DIR__ . '/../assets/uploads/tasks';
            if (!is_dir($upDir)) mkdir($upDir, 0755, true);
            $file = $_FILES['attachment'];
            $safe = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($file['name']));
            $target = $upDir . '/' . time() . '_' . $safe;
            if (move_uploaded_file($file['tmp_name'], $target)) {
                $publicPath = '/ifms/assets/uploads/tasks/' . basename($target);
                $description .= "\n\nReference File: " . $publicPath;
            }
        }

        $stmt = $db->prepare("INSERT INTO tasks (project_id, title, description, assigned_to, status, priority, due_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $projectId,
            $title,
            $description,
            $data['assigned_to'] ?: null,
            $data['status'] ?? 'open',
            $data['priority'] ?? 'medium',
            $data['due_date'] ?: null
        ]);

        jsonResponse(['success' => true, 'message' => 'Task created', 'id' => $db->lastInsertId()]);
        break;

    case 'task_list':
        $projectId = $data['project_id'] ?? $_GET['project_id'] ?? 0;
        if (!$projectId) {
            jsonResponse(['error' => 'Project ID is required'], 400);
        }

        $tasks = $db->query("
            SELECT t.*, u.full_name AS assigned_name 
            FROM tasks t 
            LEFT JOIN users u ON t.assigned_to = u.id 
            WHERE t.project_id = {$projectId}
            ORDER BY t.due_date ASC
        ")->fetchAll();

        jsonResponse(['success' => true, 'data' => $tasks]);
        break;

    case 'milestone_add':
        // Add milestone to project
        if (getUserRole() !== 'admin' && !isSeniorDeveloper()) {
            jsonResponse(['error' => 'Unauthorized - Senior Developer access required'], 403);
        }

        $projectId = $data['project_id'] ?? 0;
        $title = $data['title'] ?? '';

        if (!$projectId || !$title) {
            jsonResponse(['error' => 'Project ID and Title are required'], 400);
        }

        // Support file upload attachment
        $description = $data['description'] ?? '';
        if (!empty($_FILES['attachment']['name'])) {
            $upDir = __DIR__ . '/../assets/uploads/milestones';
            if (!is_dir($upDir)) mkdir($upDir, 0755, true);
            $file = $_FILES['attachment'];
            $safe = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($file['name']));
            $target = $upDir . '/' . time() . '_' . $safe;
            if (move_uploaded_file($file['tmp_name'], $target)) {
                $publicPath = '/ifms/assets/uploads/milestones/' . basename($target);
                $description .= "\n\nReference File: " . $publicPath;
            }
        }

        $stmt = $db->prepare("INSERT INTO milestones (project_id, title, description, target_date, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $projectId,
            $title,
            $description,
            $data['target_date'] ?: null,
            $data['status'] ?? 'pending'
        ]);

        jsonResponse(['success' => true, 'message' => 'Milestone created', 'id' => $db->lastInsertId()]);
        break;

    case 'milestone_list':
        $projectId = $data['project_id'] ?? $_GET['project_id'] ?? 0;
        if (!$projectId) {
            jsonResponse(['error' => 'Project ID is required'], 400);
        }

        $milestones = $db->query("
            SELECT * FROM milestones 
            WHERE project_id = {$projectId}
            ORDER BY target_date ASC
        ")->fetchAll();

        jsonResponse(['success' => true, 'data' => $milestones]);
        break;

    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}
