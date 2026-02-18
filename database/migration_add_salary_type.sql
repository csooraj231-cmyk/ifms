-- Add salary_type column to employees table
ALTER TABLE employees ADD COLUMN salary_type ENUM('monthly', 'annual', 'hourly') DEFAULT 'monthly' AFTER base_salary;

-- Note: Run this migration if the column doesn't exist
-- This allows storing how the salary is defined for proper payroll calculation
