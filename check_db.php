<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once __DIR__ . '/config/database.php';

echo "Testing Database Connection...\n";

try {
    $db = getDB();
    echo "✓ Database connected successfully\n";
    
    // Check if employees table exists
    $result = $db->query("SHOW COLUMNS FROM employees")->fetchAll();
    echo "✓ Employees table found\n";
    
    // Check for senior_developer_id column
    $columns = array_column($result, 'Field');
    
    if (in_array('senior_developer_id', $columns)) {
        echo "✓ senior_developer_id column EXISTS\n";
    } else {
        echo "✗ senior_developer_id column DOES NOT EXIST\n";
        echo "  Available columns: " . implode(', ', $columns) . "\n";
    }
    
    // Show all columns
    echo "\nEmployee table columns:\n";
    foreach ($result as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
    
} catch (Throwable $e) {
    echo "✗ Database Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
