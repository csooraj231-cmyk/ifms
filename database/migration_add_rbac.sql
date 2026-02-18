-- ============================================================
-- IFMS Database Migration - Add Senior Developer Column
-- Run this if upgrading an existing database
-- ============================================================

-- Add senior_developer_id column to employees table
ALTER TABLE employees ADD COLUMN senior_developer_id INT AFTER designation;

-- Add foreign key constraint
ALTER TABLE employees ADD CONSTRAINT fk_employees_senior_dev 
FOREIGN KEY (senior_developer_id) REFERENCES employees(id) ON DELETE SET NULL;

-- Add indexes for common queries
CREATE INDEX idx_employees_senior_dev ON employees(senior_developer_id);
CREATE INDEX idx_employees_designation ON employees(designation);

-- ============================================================
-- Sample Data - Update Existing Employees with Designations
-- Only run if you want to populate with example data
-- ============================================================

-- Update sample employee designations (uncomment to use)
-- UPDATE employees SET designation = 'HR Manager' WHERE department_id = (SELECT id FROM departments WHERE slug = 'hr') LIMIT 1;
-- UPDATE employees SET designation = 'Finance Manager' WHERE department_id = (SELECT id FROM departments WHERE slug = 'finance') LIMIT 1;
-- UPDATE employees SET designation = 'Senior Developer' WHERE department_id = (SELECT id FROM departments WHERE slug = 'development') LIMIT 1;
-- UPDATE employees SET designation = 'Junior Developer' WHERE department_id = (SELECT id FROM departments WHERE slug = 'development') LIMIT 2;

-- Link junior developers to senior developers (example)
-- Assuming employee ID 4 is Senior Developer and 5,6 are juniors
-- UPDATE employees SET senior_developer_id = 4 WHERE id IN (5, 6);

-- ============================================================
-- Verification Queries
-- ============================================================

-- Check current designations
-- SELECT id, user_id, designation, department_id, senior_developer_id FROM employees;

-- Check senior-junior relationships
-- SELECT e1.id as junior_id, u1.full_name as junior_name, e2.id as senior_id, u2.full_name as senior_name 
-- FROM employees e1 
-- LEFT JOIN employees e2 ON e1.senior_developer_id = e2.id 
-- LEFT JOIN users u1 ON e1.user_id = u1.id 
-- LEFT JOIN users u2 ON e2.user_id = u2.id 
-- WHERE e1.senior_developer_id IS NOT NULL;
