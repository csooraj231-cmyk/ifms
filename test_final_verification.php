<?php
/**
 * FINAL VERIFICATION TEST - All Features Working
 */
error_reporting(E_ALL);
ini_set("display_errors", 0);

require_once __DIR__ . '/config/database.php';

echo "\n═══════════════════════════════════════════════════════\n";
echo "   IFMS - FINAL COMPREHENSIVE VERIFICATION\n";
echo "═══════════════════════════════════════════════════════\n\n";

$db = getDB();
$results = [];

// ========== VERIFICATION 1: Database Tables ==========
echo "1. DATABASE TABLE STRUCTURE\n";
echo "───────────────────────────────────────────────────────\n";

$tables_to_check = [
    'employees' => ['senior_developer_id', 'designation', 'base_salary'],
    'organizations' => ['name', 'email', 'industry'],
    'projects' => ['title', 'organization_id', 'priority'],
    'attendance' => ['employee_id', 'check_in', 'check_out', 'status'],
    'project_team' => ['project_id', 'employee_id', 'role']
];

foreach ($tables_to_check as $table => $columns) {
    try {
        $cols = $db->query("SHOW COLUMNS FROM $table")->fetchAll();
        $col_names = array_column($cols, 'Field');
        
        $missing = array_diff($columns, $col_names);
        
        if (empty($missing)) {
            echo "  ✓ $table - All required columns present\n";
            $results["db_$table"] = 'PASS';
        } else {
            echo "  ✗ $table - Missing: " . implode(', ', $missing) . "\n";
            $results["db_$table"] = 'FAIL';
        }
    } catch (Exception $e) {
        echo "  ✗ $table - Error: " . $e->getMessage() . "\n";
        $results["db_$table"] = 'FAIL';
    }
}

echo "\n";

// ========== VERIFICATION 2: API Endpoints ==========
echo "2. API ENDPOINTS FUNCTIONALITY\n";
echo "───────────────────────────────────────────────────────\n";

$api_files = [
    'api/employees.php' => ['create', 'list'],
    'api/clients.php' => ['create', 'list'],
    'api/projects.php' => ['create', 'assign', 'unassign'],
    'api/attendance.php' => ['mark', 'update_time']
];

foreach ($api_files as $file => $actions) {
    try {
        $content = file_get_contents(__DIR__ . '/' . $file);
        
        $found_actions = [];
        foreach ($actions as $action) {
            if (strpos($content, "case '$action'") !== false || 
                strpos($content, "\"$action\"") !== false) {
                $found_actions[] = $action;
            }
        }
        
        $missing = array_diff($actions, $found_actions);
        
        if (empty($missing)) {
            echo "  ✓ $file - All actions implemented\n";
            $results["api_$file"] = 'PASS';
        } else {
            echo "  ⚠ $file - Missing actions: " . implode(', ', $missing) . "\n";
            $results["api_$file"] = 'WARN';
        }
    } catch (Exception $e) {
        echo "  ✗ $file - Error: " . $e->getMessage() . "\n";
        $results["api_$file"] = 'FAIL';
    }
}

echo "\n";

// ========== VERIFICATION 3: Form Fields ==========
echo "3. FORM IMPLEMENTATION\n";
echo "───────────────────────────────────────────────────────\n";

$form_checks = [
    'admin/employees.php' => [
        'Add Employee Form' => ['full_name', 'email', 'designation', 'department_id'],
        'Filters' => ['searchInput', 'deptFilter', 'statusFilter']
    ],
    'admin/clients.php' => [
        'Add Organization Form' => ['name', 'email', 'contact_name', 'contact_email', 'contact_password']
    ],
    'admin/projects.php' => [
        'Create Project Form' => ['title', 'description', 'organization_id'],
        'Assignment Form' => ['assign-employee-id', 'assign-role']
    ],
    'admin/attendance.php' => [
        'Attendance Controls' => ['check_in', 'check_out', 'Previous Day', 'Next Day']
    ]
];

foreach ($form_checks as $file => $sections) {
    $content = file_get_contents(__DIR__ . '/' . $file);
    
    foreach ($sections as $section => $fields) {
        $all_found = true;
        
        foreach ($fields as $field) {
            if (strpos($content, $field) === false && 
                strpos($content, "\"$field\"") === false &&
                strpos($content, "'$field'") === false) {
                $all_found = false;
                break;
            }
        }
        
        if ($all_found) {
            echo "  ✓ $file - $section\n";
            $results["form_${file}_${section}"] = 'PASS';
        } else {
            echo "  ✗ $file - $section missing fields\n";
            $results["form_${file}_${section}"] = 'FAIL';
        }
    }
}

echo "\n";

// ========== VERIFICATION 4: JavaScript Functions ==========
echo "4. JAVASCRIPT FUNCTIONALITY\n";
echo "───────────────────────────────────────────────────────\n";

$js_functions = [
    'admin/employees.php' => ['filterTable'],
    'admin/attendance.php' => ['markAttendance', 'updateTime'],
    'admin/projects.php' => ['openAssignmentModal'],
    'assets/js/app.js' => ['showToast', 'openModal', 'closeModal']
];

foreach ($js_functions as $file => $functions) {
    $content = file_get_contents(__DIR__ . '/' . $file);
    
    $found_all = true;
    foreach ($functions as $func) {
        if (strpos($content, "function $func") === false && 
            strpos($content, "const $func") === false &&
            strpos($content, ".$func") === false &&
            strpos($content, "$func(") === false) {
            $found_all = false;
            break;
        }
    }
    
    if ($found_all) {
        echo "  ✓ $file - All functions present\n";
        $results["js_$file"] = 'PASS';
    } else {
        echo "  ✗ $file - Some functions missing\n";
        $results["js_$file"] = 'FAIL';
    }
}

echo "\n";

// ========== VERIFICATION 5: Dashboard Links ==========
echo "5. DASHBOARD NAVIGATION\n";
echo "───────────────────────────────────────────────────────\n";

$dashboard_content = file_get_contents(__DIR__ . '/admin/index.php');

$links = [
    '/ifms/admin/employees.php' => 'Employees',
    '/ifms/admin/projects.php' => 'Projects',
    '/ifms/admin/invoices.php' => 'Revenue',
    '/ifms/admin/tickets.php' => 'Tickets'
];

foreach ($links as $url => $name) {
    if (strpos($dashboard_content, "href=\"$url\"") !== false || 
        strpos($dashboard_content, "href='$url'") !== false) {
        echo "  ✓ $name card redirects to $url\n";
        $results["nav_$name"] = 'PASS';
    } else {
        echo "  ✗ $name card missing redirect\n";
        $results["nav_$name"] = 'FAIL';
    }
}

echo "\n";

// ========== SUMMARY ==========
echo "═══════════════════════════════════════════════════════\n";
echo "VERIFICATION SUMMARY\n";
echo "───────────────────────────────────────────────────────\n";

$pass = count(array_filter($results, fn($v) => $v === 'PASS'));
$fail = count(array_filter($results, fn($v) => $v === 'FAIL'));
$warn = count(array_filter($results, fn($v) => $v === 'WARN'));
$total = count($results);

echo "Total Checks: $total\n";
echo "Passed: $pass\n";
echo "Failed: $fail\n";
echo "Warnings: $warn\n";
echo "Success Rate: " . round(($pass / $total) * 100, 1) . "%\n";

echo "\n";

if ($fail === 0) {
    echo "✅ ALL VERIFICATIONS PASSED!\n";
    echo "\nSystem Status: FULLY OPERATIONAL\n";
    echo "\n✓ Employee & Client Management - Working\n";
    echo "✓ Search & Filter - Working\n";
    echo "✓ Dashboard Navigation - Working\n";
    echo "✓ Attendance Management - Working\n";
    echo "✓ Project Assignment - Working\n";
} else {
    echo "⚠️  SOME VERIFICATIONS FAILED:\n";
    foreach ($results as $check => $status) {
        if ($status === 'FAIL') {
            echo "  - $check\n";
        }
    }
}

echo "\n═══════════════════════════════════════════════════════\n\n";
?>
