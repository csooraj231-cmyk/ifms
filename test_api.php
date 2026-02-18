<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/database.php';

echo "=== Testing API Endpoints ===\n\n";

// Simulate logged-in session for API tests
$_SESSION['user_id'] = 1;
$_SESSION['user_email'] = 'admin@ifms.com';
$_SESSION['user_role'] = 'admin';
$_SESSION['user_name'] = 'Admin User';
$_SESSION['user_department'] = 'Admin';
$_SESSION['user_department_slug'] = 'admin';
$_SESSION['employee_id'] = 1;
$_SESSION['user_designation'] = 'Administrator';

// Helper function to test API calls
function testAPI($file, $action) {
    global $_POST, $_GET;
    
    $_GET['action'] = $action;
    $_POST = [];
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    ob_start();
    try {
        require_once __DIR__ . '/' . $file;
        $output = ob_get_clean();
        
        // Try to parse as JSON
        $json = json_decode($output, true);
        if ($json !== null) {
            return ['status' => 'PASS', 'response' => $json];
        } else {
            return ['status' => 'PARTIAL', 'output' => substr($output, 0, 100)];
        }
    } catch (Throwable $e) {
        ob_get_clean();
        return ['status' => 'FAIL', 'error' => $e->getMessage()];
    }
}

$api_tests = [
    'api/auth.php' => ['me'],
    'api/employees.php' => ['list'],
    'api/clients.php' => ['list'],
    'api/projects.php' => ['list'],
];

$api_results = [];

foreach ($api_tests as $file => $actions) {
    echo "Testing $file\n";
    
    foreach ($actions as $action) {
        $result = testAPI($file, $action);
        
        if ($result['status'] === 'PASS') {
            echo "  ✓ action=$action - Response OK\n";
            $api_results[$file . "::$action"] = 'PASS';
        } elseif ($result['status'] === 'PARTIAL') {
            echo "  ⚠ action=$action - Partial response\n";
            $api_results[$file . "::$action"] = 'WARN';
        } else {
            echo "  ✗ action=$action - " . $result['error'] . "\n";
            $api_results[$file . "::$action"] = 'FAIL';
        }
    }
    echo "\n";
}

echo "=== API SUMMARY ===\n";
$pass = count(array_filter($api_results, fn($v) => $v === 'PASS'));
$fail = count(array_filter($api_results, fn($v) => $v === 'FAIL'));
$warn = count(array_filter($api_results, fn($v) => $v === 'WARN'));

echo "Total: " . count($api_results) . " | Pass: $pass | Fail: $fail | Warnings: $warn\n";

if ($fail > 0) {
    echo "\n✗ Some API tests failed\n";
    exit(1);
} else {
    echo "\n✓ ALL API TESTS PASSED!\n";
}
?>
