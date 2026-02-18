<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

echo "\n";
echo "═══════════════════════════════════════════════════════\n";
echo "   IFMS - FEATURE FIX VERIFICATION TEST\n";
echo "═══════════════════════════════════════════════════════\n\n";

// Test setup
require_once __DIR__ . '/config/database.php';

$db = getDB();
$checks = [];

// 1. Test Employee API - Create Action
echo "1. EMPLOYEE & CLIENT MANAGEMENT\n";
echo "───────────────────────────────────────────────────────\n";
try {
    $stmt = $db->prepare("SELECT id FROM departments LIMIT 1");
    $stmt->execute();
    $dept = $stmt->fetch();
    
    if ($dept) {
        echo "  ✓ Department found\n";
        $checks['dept'] = true;
    } else {
        echo "  ⚠ No departments found\n";
    }
    
    // Check if API endpoints exist
    $endpoints = [
        'api/employees.php' => 'Employee API',
        'api/clients.php' => 'Client API',
        'api/projects.php' => 'Project API',
        'api/attendance.php' => 'Attendance API'
    ];
    
    foreach ($endpoints as $file => $name) {
        $path = __DIR__ . '/' . $file;
        if (file_exists($path)) {
            $content = file_get_contents($path);
            if (strpos($content, 'action') !== false) {
                echo "  ✓ $name exists and has action handlers\n";
                $checks[$file] = true;
            }
        }
    }
    
} catch (Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// 2. Test Client Form Fields
echo "2. CLIENT FORM FIELDS\n";
echo "───────────────────────────────────────────────────────\n";
$required_client_fields = [
    'contact_name',
    'contact_email',
    'contact_password'
];

try {
    $clientsPage = file_get_contents(__DIR__ . '/admin/clients.php');
    
    foreach ($required_client_fields as $field) {
        if (strpos($clientsPage, "name=\"$field\"") !== false) {
            echo "  ✓ Client form has '$field' field\n";
            $checks["client_$field"] = true;
        } else {
            echo "  ✗ Client form missing '$field' field\n";
            $checks["client_$field"] = false;
        }
    }
} catch (Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. Test Search & Filter Implementation
echo "3. EMPLOYEE SEARCH & FILTER\n";
echo "───────────────────────────────────────────────────────\n";
try {
    $employeePage = file_get_contents(__DIR__ . '/admin/employees.php');
    
    $features = [
        'filterTable' => 'Filter function',
        'searchInput' => 'Search input field',
        'deptFilter' => 'Department filter',
        'statusFilter' => 'Status filter',
        'employee-row' => 'Data attributes for rows'
    ];
    
    foreach ($features as $pattern => $desc) {
        if (strpos($employeePage, $pattern) !== false) {
            echo "  ✓ $desc implemented\n";
            $checks[$pattern] = true;
        } else {
            echo "  ✗ $desc missing\n";
            $checks[$pattern] = false;
        }
    }
} catch (Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// 4. Test Dashboard Card Links
echo "4. DASHBOARD CARD LINKS\n";
echo "───────────────────────────────────────────────────────\n";
try {
    $dashboardPage = file_get_contents(__DIR__ . '/admin/index.php');
    
    $links = [
        '/ifms/admin/employees.php' => 'Employees card',
        '/ifms/admin/projects.php' => 'Projects card',
        '/ifms/admin/invoices.php' => 'Invoices card',
        '/ifms/admin/tickets.php' => 'Tickets card'
    ];
    
    foreach ($links as $url => $desc) {
        if (strpos($dashboardPage, $url) !== false) {
            echo "  ✓ $desc has link\n";
            $checks[$url] = true;
        } else {
            echo "  ✗ $desc missing link\n";
            $checks[$url] = false;
        }
    }
} catch (Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// 5. Test Attendance Features
echo "5. ATTENDANCE FEATURES\n";
echo "───────────────────────────────────────────────────────\n";
try {
    $attendancePage = file_get_contents(__DIR__ . '/admin/attendance.php');
    
    $features = [
        'Previous Day' => 'Previous day button',
        'Next Day' => 'Next day button',
        'input type="time"' => 'Editable time inputs',
        'updateTime' => 'Update time function',
        'update_time' => 'Update time action'
    ];
    
    foreach ($features as $pattern => $desc) {
        if (strpos($attendancePage, $pattern) !== false) {
            echo "  ✓ $desc implemented\n";
            $checks[$pattern] = true;
        } else {
            echo "  ✗ $desc missing\n";
            $checks[$pattern] = false;
        }
    }
} catch (Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// 6. Test Project Assignment
echo "6. PROJECT ASSIGNMENT\n";
echo "───────────────────────────────────────────────────────\n";
try {
    $projectsPage = file_get_contents(__DIR__ . '/admin/projects.php');
    $projectsAPI = file_get_contents(__DIR__ . '/api/projects.php');
    
    $features = [
        'openAssignmentModal' => 'Assignment modal function',
        'assign-team-modal' => 'Assignment team modal',
        'assign-employee-id' => 'Employee selection',
        'assign-role' => 'Role selection'
    ];
    
    foreach ($features as $pattern => $desc) {
        if (strpos($projectsPage, $pattern) !== false) {
            echo "  ✓ $desc in UI\n";
            $checks[$pattern] = true;
        } else {
            echo "  ✗ $desc missing in UI\n";
            $checks[$pattern] = false;
        }
    }
    
    if (strpos($projectsAPI, "case 'assign':") !== false && 
        strpos($projectsAPI, "case 'unassign':") !== false) {
        echo "  ✓ API assignment endpoints implemented\n";
        $checks['api_assign'] = true;
    } else {
        echo "  ✗ API assignment endpoints missing\n";
        $checks['api_assign'] = false;
    }
    
} catch (Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Summary
echo "═══════════════════════════════════════════════════════\n";
echo "SUMMARY\n";
echo "───────────────────────────────────────────────────────\n";

$pass = count(array_filter($checks, fn($v) => $v === true));
$fail = count(array_filter($checks, fn($v) => $v === false));
$total = count($checks);

echo "Total Checks: $total | Pass: $pass | Fail: $fail\n";
echo "Success Rate: " . round(($pass / $total) * 100, 1) . "%\n";

if ($fail === 0) {
    echo "\n✓ ALL FEATURES SUCCESSFULLY IMPLEMENTED!\n";
} else {
    echo "\n⚠ Some features need attention\n";
    echo "\nFailed checks:\n";
    foreach ($checks as $check => $result) {
        if ($result === false) {
            echo "  - $check\n";
        }
    }
}

echo "═══════════════════════════════════════════════════════\n\n";
?>
