<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

ob_start();
session_start();

$_SERVER['REQUEST_URI'] = '/ifms/admin/';
$_SESSION['user_id'] = 1;
$_SESSION['user_email'] = 'test@test.com';
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

echo "Testing admin/index.php...\n";
try {
    require_once __DIR__ . '/admin/index.php';
    echo "✓ Admin index.php loaded successfully\n";
} catch (Throwable $e) {
    echo "✗ Error loading admin/index.php: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

$output = ob_get_clean();
echo "\n--- Page Output ---\n";
echo substr($output, 0, 500);
?>
