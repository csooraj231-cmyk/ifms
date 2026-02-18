<?php
/**
 * IFMS - Designations Mapping API
 * API for designation-department mappings
 */

// Department-Designation mapping
// Based on: Administration/Admin, Data & Research/Data Analyst, Development/Sr. Developer|Developer|Junior Developer, Finance/Accountant|Finance Manager, HR/HR Manager|HR Executive, Support/Support Staff
const DEPT_DESIGNATION_MAP = [
    1 => ['Admin'],  // Administration
    2 => ['HR Manager', 'HR Executive'],  // Human Resources
    3 => ['Accountant', 'Finance Manager'],  // Finance
    4 => ['Sr. Developer', 'Developer', 'Junior Developer'],  // Development
    5 => ['Support Staff'],  // Support
    6 => ['Data Analyst'],  // Data & Research
];

// Return designations for a department
if (isset($_GET['dept_id'])) {
    $dept_id = (int)$_GET['dept_id'];
    $designations = DEPT_DESIGNATION_MAP[$dept_id] ?? [];
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'designations' => $designations]);
    exit;
}

// Return full mapping
if (isset($_GET['action']) && $_GET['action'] === 'mapping') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'mapping' => DEPT_DESIGNATION_MAP]);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['success' => false, 'error' => 'No action specified']);
