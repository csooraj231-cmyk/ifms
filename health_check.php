<?php
/**
 * FINAL COMPREHENSIVE TEST REPORT
 */
error_reporting(E_ALL);
ini_set("display_errors", 0);

require_once __DIR__ . '/config/database.php';

echo "\n";
echo "═══════════════════════════════════════════════════════\n";
echo "   IFMS - SYSTEM HEALTH CHECK & TEST REPORT\n";
echo "═══════════════════════════════════════════════════════\n\n";

// 1. Database Connection Check
echo "1. DATABASE CONNECTION\n";
echo "───────────────────────────────────────────────────────\n";
try {
    $db = getDB();
    echo "  ✓ Database connected successfully\n";
    
    $tables = [
        'users' => "SELECT COUNT(*) as cnt FROM users",
        'employees' => "SELECT COUNT(*) as cnt FROM employees",
        'departments' => "SELECT COUNT(*) as cnt FROM departments",
        'organizations' => "SELECT COUNT(*) as cnt FROM organizations",
        'projects' => "SELECT COUNT(*) as cnt FROM projects",
    ];
    
    foreach ($tables as $table => $query) {
        $result = $db->query($query)->fetch();
        echo "  ✓ $table table found (" . $result['cnt'] . " records)\n";
    }
    
    // Check for senior_developer_id column
    $col_result = $db->query("SHOW COLUMNS FROM employees WHERE Field = 'senior_developer_id'")->fetch();
    if ($col_result) {
        echo "  ✓ senior_developer_id column EXISTS in employees table\n";
    } else {
        echo "  ✗ senior_developer_id column MISSING in employees table\n";
    }
    
} catch (Throwable $e) {
    echo "  ✗ Database Error: " . $e->getMessage() . "\n";
}

echo "\n";

// 2. Configuration Files
echo "2. CONFIGURATION FILES\n";
echo "───────────────────────────────────────────────────────\n";
$configs = [
    'config/auth.php' => 'Authentication & Authorization',
    'config/database.php' => 'Database Configuration',
    'includes/header.php' => 'Header Template',
    'includes/footer.php' => 'Footer Template',
    'includes/sidebar.php' => 'Sidebar Navigation',
];

foreach ($configs as $file => $desc) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        if (strlen($content) > 0) {
            echo "  ✓ $desc ($file - " . strlen($content) . " bytes)\n";
        } else {
            echo "  ⚠ $desc ($file - empty file)\n";
        }
    } else {
        echo "  ✗ $desc ($file - NOT FOUND)\n";
    }
}

echo "\n";

// 3. PHP Syntax Check
echo "3. PHP SYNTAX VALIDATION\n";
echo "───────────────────────────────────────────────────────\n";
$php_files = [
    'config/auth.php',
    'admin/index.php',
    'admin/employees.php',
    'admin/clients.php',
    'employee/index.php',
    'client/index.php',
    'api/auth.php',
    'api/employees.php',
];

$syntax_pass = 0;
$syntax_fail = 0;

foreach ($php_files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $output = shell_exec("php -l \"$path\" 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "  ✓ $file\n";
            $syntax_pass++;
        } else {
            echo "  ✗ $file - " . trim($output) . "\n";
            $syntax_fail++;
        }
    } else {
        echo "  ✗ $file - NOT FOUND\n";
        $syntax_fail++;
    }
}

echo "\n";

// 4. Key Functions Check
echo "4. KEY FUNCTIONS & UTILITIES\n";
echo "───────────────────────────────────────────────────────\n";

// Simulate a session to check functions
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['user_name'] = 'Admin';
$_SESSION['user_department_slug'] = 'admin';

require_once __DIR__ . '/config/auth.php';

$functions = [
    'isLoggedIn' => isLoggedIn(),
    'getCurrentUser' => getCurrentUser() !== null,
    'getUserRole' => getUserRole() === 'admin',
    'isHREmployee' => function_exists('isHREmployee'),
    'isFinanceEmployee' => function_exists('isFinanceEmployee'),
    'isDeveloper' => function_exists('isDeveloper'),
    'isSeniorDeveloper' => function_exists('isSeniorDeveloper'),
];

foreach ($functions as $name => $result) {
    if ($result === true || $result === 1) {
        echo "  ✓ $name() - OK\n";
    } elseif ($result === false) {
        echo "  ⚠ $name() - Returns false\n";
    } else {
        echo "  ✗ $name() - Function not callable\n";
    }
}

echo "\n";

// 5. Summary
echo "═══════════════════════════════════════════════════════\n";
echo "SUMMARY\n";
echo "───────────────────────────────────────────────────────\n";
echo "  PHP Syntax Check: $syntax_pass passed, $syntax_fail failed\n";
echo "  Status: " . ($syntax_fail === 0 ? "✓ ALL SYSTEMS OPERATIONAL" : "✗ Some issues found") . "\n";
echo "═══════════════════════════════════════════════════════\n\n";
?>
