<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Simulate a logged-in admin session
session_start();
$_SERVER['REQUEST_URI'] = '/ifms/admin/';
$_SESSION['user_id'] = 1;
$_SESSION['user_email'] = 'admin@ifms.com';
$_SESSION['user_role'] = 'admin';
$_SESSION['user_name'] = 'Admin User';
$_SESSION['user_avatar'] = null;
$_SESSION['user_department'] = 'Admin';
$_SESSION['user_department_slug'] = 'admin';
$_SESSION['employee_id'] = 1;
$_SESSION['user_designation'] = 'Administrator';
$_SESSION['organization_id'] = 1;
$_SESSION['organization_name'] = 'Main Org';
$_SESSION['senior_developer_id'] = null;

$pages_to_test = [
    'Admin Pages' => [
        'admin/index.php' => '/ifms/admin/',
        'admin/employees.php' => '/ifms/admin/employees.php',
        'admin/clients.php' => '/ifms/admin/clients.php',
    ],
    'Employee Pages' => [
        'employee/index.php' => '/ifms/employee/',
        'employee/profile.php' => '/ifms/employee/profile.php',
    ],
    'Client Pages' => [
        'client/index.php' => '/ifms/client/',
    ]
];

$results = [];

foreach ($pages_to_test as $category => $pages) {
    echo "\n=== Testing $category ===\n";
    
    foreach ($pages as $file => $uri) {
        $_SERVER['REQUEST_URI'] = $uri;
        ob_start();
        
        try {
            require_once __DIR__ . '/' . $file;
            $output = ob_get_clean();
            
            if (strpos($output, 'DOCTYPE') !== false || strlen($output) > 100) {
                echo "✓ $file - PASS\n";
                $results[$file] = 'PASS';
            } else {
                echo "⚠ $file - Loaded but minimal output\n";
                $results[$file] = 'WARN';
            }
        } catch (Throwable $e) {
            ob_get_clean();
            echo "✗ $file - ERROR: " . $e->getMessage() . "\n";
            echo "  File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
            $results[$file] = 'FAIL';
        }
    }
}

echo "\n=== SUMMARY ===\n";
$pass = count(array_filter($results, fn($v) => $v === 'PASS'));
$fail = count(array_filter($results, fn($v) => $v === 'FAIL'));
$warn = count(array_filter($results, fn($v) => $v === 'WARN'));

echo "Total: " . count($results) . " | Pass: $pass | Fail: $fail | Warnings: $warn\n";

if ($fail > 0) {
    echo "\nFailed tests:\n";
    foreach ($results as $page => $status) {
        if ($status === 'FAIL') {
            echo "  - $page\n";
        }
    }
}

exit($fail > 0 ? 1 : 0);
?>
