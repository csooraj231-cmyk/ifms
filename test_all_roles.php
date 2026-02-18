<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Test setup for multiple roles
$test_sessions = [
    'Admin' => [
        'user_id' => 1,
        'user_role' => 'admin',
        'user_name' => 'Admin User',
        'user_email' => 'admin@ifms.com',
        'user_department' => 'Admin',
        'user_department_slug' => 'admin',
        'user_designation' => 'Administrator'
    ],
    'Employee' => [
        'user_id' => 2,
        'user_role' => 'employee',
        'user_name' => 'Employee User',
        'user_email' => 'emp@ifms.com',
        'user_department' => 'Development',
        'user_department_slug' => 'development',
        'user_designation' => 'Junior Developer'
    ],
    'Client' => [
        'user_id' => 10,
        'user_role' => 'client',
        'user_name' => 'Client User',
        'user_email' => 'client@techcorp.com',
        'user_department' => null,
        'user_department_slug' => null,
        'user_designation' => null
    ]
];

$pages_to_test = [
    'Admin' => [
        'admin/index.php' => '/ifms/admin/',
        'admin/employees.php' => '/ifms/admin/employees.php',
        'admin/clients.php' => '/ifms/admin/clients.php',
    ],
    'Employee' => [
        'employee/index.php' => '/ifms/employee/',
        'employee/profile.php' => '/ifms/employee/profile.php',
    ],
    'Client' => [
        'client/index.php' => '/ifms/client/',
    ]
];

$total_results = [];

foreach ($test_sessions as $role => $session_data) {
    echo "\n=== Testing as $role ===\n";
    
    $pages = $pages_to_test[$role] ?? [];
    
    foreach ($pages as $file => $uri) {
        // Fresh session for each test
        session_destroy();
        session_start();
        
        $_SERVER['REQUEST_URI'] = $uri;
        $_SESSION['user_id'] = $session_data['user_id'];
        $_SESSION['user_email'] = $session_data['user_email'];
        $_SESSION['user_role'] = $session_data['user_role'];
        $_SESSION['user_name'] = $session_data['user_name'];
        $_SESSION['user_avatar'] = null;
        $_SESSION['user_department'] = $session_data['user_department'];
        $_SESSION['user_department_slug'] = $session_data['user_department_slug'];
        $_SESSION['employee_id'] = $role === 'Employee' ? 2 : null;
        $_SESSION['user_designation'] = $session_data['user_designation'];
        $_SESSION['organization_id'] = $role === 'Client' ? 1 : null;
        $_SESSION['organization_name'] = $role === 'Client' ? 'TechCorp' : null;
        $_SESSION['senior_developer_id'] = null;
        
        ob_start();
        
        try {
            require_once __DIR__ . '/' . $file;
            $output = ob_get_clean();
            
            if (strpos($output, 'DOCTYPE') !== false || strlen($output) > 100) {
                echo "✓ $file - PASS\n";
                $total_results[$file] = 'PASS';
            } else {
                echo "⚠ $file - Loaded but minimal output\n";
                $total_results[$file] = 'WARN';
            }
        } catch (Throwable $e) {
            ob_get_clean();
            echo "✗ $file - ERROR: " . $e->getMessage() . "\n";
            $total_results[$file] = 'FAIL';
        }
    }
}

echo "\n=== SUMMARY ===\n";
$pass = count(array_filter($total_results, fn($v) => $v === 'PASS'));
$fail = count(array_filter($total_results, fn($v) => $v === 'FAIL'));
$warn = count(array_filter($total_results, fn($v) => $v === 'WARN'));

echo "Total: " . count($total_results) . " | Pass: $pass | Fail: $fail | Warnings: $warn\n";

if ($fail > 0) {
    echo "\nFailed tests:\n";
    foreach ($total_results as $page => $status) {
        if ($status === 'FAIL') {
            echo "  - $page\n";
        }
    }
    exit(1);
}

echo "\n✓ ALL TESTS PASSED!\n";
?>
