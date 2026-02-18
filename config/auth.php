<?php
/**
 * IFMS - Authentication & Session Management
 * Provides login, logout, role checking, and RBAC middleware
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/database.php';

// ─── Session Helpers ─────────────────────────────────────
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUser()
{
    if (!isLoggedIn())
        return null;
    return [
        'id' => $_SESSION['user_id'],
        'email' => $_SESSION['user_email'],
        'phone' => $_SESSION['user_phone'] ?? null,
        'role' => $_SESSION['user_role'],
        'full_name' => $_SESSION['user_name'],
        'avatar' => $_SESSION['user_avatar'] ?? null,
        'department' => $_SESSION['user_department'] ?? null,
        'department_slug' => $_SESSION['user_department_slug'] ?? null,
        'employee_id' => $_SESSION['employee_id'] ?? null,
        'designation' => $_SESSION['user_designation'] ?? null,
        'organization_id' => $_SESSION['organization_id'] ?? null,
        'organization_name' => $_SESSION['organization_name'] ?? null,
        'senior_developer_id' => $_SESSION['senior_developer_id'] ?? null,
    ];
}

function getUserRole()
{
    return $_SESSION['user_role'] ?? null;
}

function getDepartmentSlug()
{
    return $_SESSION['user_department_slug'] ?? null;
}

// ─── Login ───────────────────────────────────────────────
function loginUser($email, $password)
{
    $db = getDB();

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid email or password'];
    }

    // Set base session data
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_phone'] = $user['phone'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_avatar'] = $user['avatar'];

    // Get role-specific data
    if ($user['role'] === 'employee' || $user['role'] === 'admin') {
        $stmt = $db->prepare("
            SELECT e.*, d.name AS department_name, d.slug AS department_slug 
            FROM employees e 
            JOIN departments d ON e.department_id = d.id 
            WHERE e.user_id = ?
        ");
        $stmt->execute([$user['id']]);
        $emp = $stmt->fetch();

        if ($emp) {
            $_SESSION['employee_id'] = $emp['id'];
            $_SESSION['user_department'] = $emp['department_name'];
            $_SESSION['user_department_slug'] = $emp['department_slug'];
            $_SESSION['user_designation'] = $emp['designation'];
            $_SESSION['senior_developer_id'] = $emp['senior_developer_id'] ?? null;
        }
    }
    elseif ($user['role'] === 'client') {
        $stmt = $db->prepare("
            SELECT cu.*, o.name AS org_name, o.id AS org_id 
            FROM client_users cu 
            JOIN organizations o ON cu.organization_id = o.id 
            WHERE cu.user_id = ?
        ");
        $stmt->execute([$user['id']]);
        $client = $stmt->fetch();

        if ($client) {
            $_SESSION['organization_id'] = $client['org_id'];
            $_SESSION['organization_name'] = $client['org_name'];
            $_SESSION['user_designation'] = $client['designation'];
        }
    }

    // Update last login
    $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);

    // Determine redirect URL
    $redirect = '/ifms/';
    switch ($user['role']) {
        case 'admin':
            $redirect = '/ifms/admin/';
            break;
        case 'employee':
            $redirect = '/ifms/employee/';
            break;
        case 'client':
            $redirect = '/ifms/client/';
            break;
    }

    return ['success' => true, 'redirect' => $redirect, 'role' => $user['role']];
}

// ─── Logout ──────────────────────────────────────────────
function logoutUser()
{
    session_unset();
    session_destroy();
    header('Location: /ifms/');
    exit;
}

// ─── Access Control Middleware ───────────────────────────
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: /ifms/');
        exit;
    }
}

function requireRole($roles)
{
    requireLogin();
    if (!is_array($roles))
        $roles = [$roles];
    if (!in_array(getUserRole(), $roles)) {
        http_response_code(403);
        include __DIR__ . '/../includes/403.php';
        exit;
    }
}

function requireDepartment($slugs)
{
    requireLogin();
    if (getUserRole() === 'admin')
        return; // Admin can access everything
    if (!is_array($slugs))
        $slugs = [$slugs];
    $deptSlug = getDepartmentSlug();
    if (!in_array($deptSlug, $slugs)) {
        http_response_code(403);
        include __DIR__ . '/../includes/403.php';
        exit;
    }
}

// ─── Designation-Based Access Control ────────────────────
/**
 * Check if user is HR Department
 */
function isHREmployee()
{
    $dept = getDepartmentSlug();
    return $dept === 'hr';
}

/**
 * Check if user is Finance Department
 */
function isFinanceEmployee()
{
    $dept = getDepartmentSlug();
    return $dept === 'finance';
}

/**
 * Check if user is a Developer
 */
function isDeveloper()
{
    $designation = $_SESSION['user_designation'] ?? '';
    return stripos($designation, 'developer') !== false && stripos($designation, 'senior') === false;
}

/**
 * Check if user is a Senior Developer
 */
function isSeniorDeveloper()
{
    $designation = $_SESSION['user_designation'] ?? '';
    return stripos($designation, 'senior') !== false && stripos($designation, 'developer') !== false;
}

/**
 * Get list of junior developers under a senior developer
 */
function getJuniorDevelopers()
{
    if (!isSeniorDeveloper()) {
        return [];
    }
    
    $db = getDB();
    $stmt = $db->prepare("
        SELECT e.id, u.full_name, e.designation
        FROM employees e
        JOIN users u ON e.user_id = u.id
        WHERE e.senior_developer_id = ? AND e.is_active = 1
        ORDER BY u.full_name
    ");
    $stmt->execute([$_SESSION['employee_id']]);
    return $stmt->fetchAll();
}

/**
 * Check if senior developer can manage a specific junior developer
 */
function canManageJunior($juniorEmployeeId)
{
    if (!isSeniorDeveloper()) {
        return false;
    }
    
    $db = getDB();
    $stmt = $db->prepare("
        SELECT id FROM employees 
        WHERE id = ? AND senior_developer_id = ? LIMIT 1
    ");
    $stmt->execute([$juniorEmployeeId, $_SESSION['employee_id']]);
    return $stmt->fetch() ? true : false;
}

// ─── Permission Checks with Designation-Level Access ────
/**
 * Define module access matrix for each role + designation
 */
function getPermissionMatrix()
{
    return [
        'admin' => [
            '*' => ['*'] // Admin has access to everything
        ],
        'employee' => [
            'hr' => [
                // HR can manage employees, attendance, leaves
                'employee-add', 'employee-edit', 'employee-remove', 
                'employee-view', 'attendance-manage', 'attendance-view',
                'department-manage', 'leaves', 'notices', 'holidays',
                'payroll-view', 'profile', 'settings'
            ],
            'finance' => [
                // Finance can manage payroll, invoices, quotations
                'attendance-view', 'payroll-manage', 'payroll-generate',
                'invoices-manage', 'invoices-view', 'quotations-manage',
                'quotations-view', 'expenses-manage', 'financial-reports',
                'profile', 'settings'
            ],
            'development' => [
                // Developer (base permissions)
                'projects-view', 'milestones-view', 'tasks-view',
                'tasks-manage', 'daily-updates-manage', 'progress-track',
                'tickets-view', 'tickets-provide-updates', 'team-view',
                'profile', 'settings'
            ],
            'support' => [
                'tickets', 'profile', 'clients-view', 'settings'
            ],
            'data-research' => [
                'reports', 'analytics', 'data-exports', 'profile', 'settings'
            ],
        ],
        'client' => [
            '*' => ['projects', 'tickets', 'invoices', 'quotations', 'profile']
        ],
    ];
}

/**
 * Check if user can access a specific module (base permission check)
 */
function canAccessModule($module)
{
    $role = getUserRole();
    $dept = getDepartmentSlug() ?? '*';
    
    // Admin can access everything
    if ($role === 'admin') return true;
    
    // Designation-based overrides
    if ($role === 'employee') {
        // HR specific access
        if (isHREmployee()) {
            $hrModules = [
                'employee-add', 'employee-edit', 'employee-remove',
                'employee-view', 'attendance-manage', 'attendance-view'
            ];
            if (in_array($module, $hrModules)) return true;
        }
        
        // Finance specific access
        if (isFinanceEmployee()) {
            $financeModules = [
                'attendance-view', 'payroll-manage', 'payroll-generate',
                'invoices-manage', 'invoices-view', 'quotations-manage',
                'quotations-view', 'expenses-manage', 'financial-reports'
            ];
            if (in_array($module, $financeModules)) return true;
        }
        
        // Developer access
        if (isDeveloper()) {
            $devModules = [
                'projects-view', 'milestones-view', 'tasks-view',
                'tasks-manage', 'daily-updates-manage', 'progress-track',
                'tickets-view', 'tickets-provide-updates', 'team-view'
            ];
            if (in_array($module, $devModules)) return true;
        }
        
        // Senior Developer access (all developer + management)
        if (isSeniorDeveloper()) {
            $seniorModules = [
                'projects-view', 'milestones-view', 'milestones-manage',
                'tasks-view', 'tasks-manage', 'daily-updates-manage',
                'progress-track', 'team-manage', 'tickets-view',
                'tickets-provide-updates', 'junior-manage'
            ];
            if (in_array($module, $seniorModules)) return true;
        }
    }
    
    $permissions = getPermissionMatrix();
    
    if (isset($permissions[$role])) {
        // Check department-specific permissions
        if (isset($permissions[$role][$dept])) {
            $modules = $permissions[$role][$dept];
            if (in_array('*', $modules)) return true;
            return in_array($module, $modules);
        }
        // Fall back to wildcard permissions
        if (isset($permissions[$role]['*'])) {
            $modules = $permissions[$role]['*'];
            if (in_array('*', $modules)) return true;
            return in_array($module, $modules);
        }
    }
    
    return false;
}

/**
 * Require specific module access (middleware)
 */
function requireModuleAccess($modules)
{
    requireLogin();
    
    if (!is_array($modules))
        $modules = [$modules];
    
    foreach ($modules as $module) {
        if (!canAccessModule($module)) {
            http_response_code(403);
            include __DIR__ . '/../includes/403.php';
            exit;
        }
    }
}

/**
 * Require HR employee access
 */
function requireHRAccess()
{
    requireLogin();
    requireRole('employee');
    
    if (!isHREmployee()) {
        http_response_code(403);
        include __DIR__ . '/../includes/403.php';
        exit;
    }
}

/**
 * Require Finance employee access
 */
function requireFinanceAccess()
{
    requireLogin();
    requireRole('employee');
    
    if (!isFinanceEmployee()) {
        http_response_code(403);
        include __DIR__ . '/../includes/403.php';
        exit;
    }
}

/**
 * Require Developer access (includes Senior Developers)
 */
function requireDeveloperAccess()
{
    requireLogin();
    requireRole('employee');
    
    if (!isDeveloper() && !isSeniorDeveloper()) {
        http_response_code(403);
        include __DIR__ . '/../includes/403.php';
        exit;
    }
}

/**
 * Require Senior Developer access
 */
function requireSeniorDeveloperAccess()
{
    requireLogin();
    requireRole('employee');
    
    if (!isSeniorDeveloper()) {
        http_response_code(403);
        include __DIR__ . '/../includes/403.php';
        exit;
    }
}

/**
 * Get all modules accessible by current user
 */
function getUserAccessibleModules()
{
    $role = getUserRole();
    $dept = getDepartmentSlug() ?? '*';
    
    // Admin gets everything
    if ($role === 'admin') {
        return array_keys(getPermissionMatrix()['employee']);
    }
    
    $permissions = getPermissionMatrix();
    if (isset($permissions[$role][$dept])) {
        return $permissions[$role][$dept];
    }
    if (isset($permissions[$role]['*'])) {
        return $permissions[$role]['*'];
    }
    
    return ['profile'];
}

function isProjectManager()
{
    // Senior developers and project leads can manage projects
    return isSeniorDeveloper() ||
        stripos($_SESSION['user_designation'] ?? '', 'project manager') !== false ||
        stripos($_SESSION['user_designation'] ?? '', 'lead') !== false;
}

// ─── API Helpers ─────────────────────────────────────────
function jsonResponse($data, $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function requireAPI()
{
    if (!isLoggedIn()) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }
}

function getPostData()
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
    return $_POST;
}
