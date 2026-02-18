<?php
/**
 * CLI helper: create attendance_audit table
 * Usage: php create_attendance_audit.php
 */
require_once __DIR__ . '/../config/auth.php';
$db = getDB();
try {
    $sql = file_get_contents(__DIR__ . '/../database/migration_add_attendance_audit.sql');
    if ($sql === false) {
        echo "Migration file not found: database/migration_add_attendance_audit.sql\n";
        exit(1);
    }
    $db->exec($sql);
    echo "attendance_audit table ensured.\n";
} catch (PDOException $e) {
    echo "Error creating attendance_audit: " . $e->getMessage() . "\n";
    exit(1);
}
