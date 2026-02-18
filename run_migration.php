<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once __DIR__ . '/config/database.php';

echo "Running Database Migration - Add RBAC Support...\n\n";

$db = getDB();
$migration_queries = [
    "ALTER TABLE employees ADD COLUMN senior_developer_id INT AFTER designation",
    "ALTER TABLE employees ADD CONSTRAINT fk_employees_senior_dev FOREIGN KEY (senior_developer_id) REFERENCES employees(id) ON DELETE SET NULL",
    "CREATE INDEX idx_employees_senior_dev ON employees(senior_developer_id)",
    "CREATE INDEX idx_employees_designation ON employees(designation)"
];

try {
    foreach ($migration_queries as $i => $query) {
        echo "Executing query " . ($i + 1) . "...\n";
        echo "  Query: " . substr($query, 0, 80) . (strlen($query) > 80 ? '...' : '') . "\n";
        $db->exec($query);
        echo "  ✓ Success\n\n";
    }
    
    echo "✓ Migration completed successfully!\n";
    
    // Verify the column was added
    $result = $db->query("SHOW COLUMNS FROM employees WHERE Field = 'senior_developer_id'")->fetch();
    if ($result) {
        echo "✓ Column senior_developer_id verified in employees table\n";
    }
    
} catch (Throwable $e) {
    // Some queries might fail if they already exist
    if (strpos($e->getMessage(), 'already exists') !== false || 
        strpos($e->getMessage(), 'Duplicate column') !== false ||
        strpos($e->getMessage(), 'already exists') !== false) {
        echo "Note: Some migration steps were skipped (already applied): " . $e->getMessage() . "\n";
        echo "\n✓ Migration likely already applied or column exists\n";
    } else {
        echo "✗ Migration Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}
?>
