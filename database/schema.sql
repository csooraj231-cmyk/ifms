-- ============================================================
-- IFMS - Infrastructure Management Software
-- Complete Database Schema + Seed Data
-- ============================================================
-- Login Credentials (after seeding):
--   Admin:       admin@ifms.com       / admin123
--   HR:          hr@ifms.com          / emp123
--   Finance:     finance@ifms.com     / emp123
--   Developer:   dev@ifms.com         / emp123
--   Sr. Dev/PM:  pm@ifms.com          / emp123
--   Support:     support@ifms.com     / emp123
--   Data:        data@ifms.com        / emp123
--   Client:      client@techcorp.com  / client123
-- ============================================================

CREATE DATABASE IF NOT EXISTS ifms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ifms_db;

-- ============================================================
-- 1. USERS TABLE (Central Auth)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','employee','client') NOT NULL DEFAULT 'employee',
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    avatar VARCHAR(500),
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 2. DEPARTMENTS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    head_employee_id INT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 3. EMPLOYEES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    employee_code VARCHAR(20) NOT NULL UNIQUE,
    department_id INT NOT NULL,
    designation VARCHAR(100),
    senior_developer_id INT,
    employment_type ENUM('full-time','part-time','contract','intern') DEFAULT 'full-time',
    date_of_joining DATE NOT NULL,
    date_of_birth DATE,
    gender ENUM('male','female','other'),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    pincode VARCHAR(10),
    emergency_contact VARCHAR(20),
    bank_name VARCHAR(100),
    bank_account_no VARCHAR(50),
    ifsc_code VARCHAR(20),
    pan_number VARCHAR(20),
    aadhar_number VARCHAR(20),
    base_salary DECIMAL(12,2) DEFAULT 0,
    hra DECIMAL(12,2) DEFAULT 0,
    da DECIMAL(12,2) DEFAULT 0,
    special_allowance DECIMAL(12,2) DEFAULT 0,
    pf_deduction DECIMAL(12,2) DEFAULT 0,
    tax_deduction DECIMAL(12,2) DEFAULT 0,
    other_deductions DECIMAL(12,2) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (senior_developer_id) REFERENCES employees(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 4. ORGANIZATIONS TABLE (Client Companies)
-- ============================================================
CREATE TABLE IF NOT EXISTS organizations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    industry VARCHAR(100),
    website VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    country VARCHAR(100) DEFAULT 'India',
    pincode VARCHAR(10),
    gst_number VARCHAR(30),
    logo VARCHAR(500),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 5. CLIENT_USERS TABLE (Users under Organizations)
-- ============================================================
CREATE TABLE IF NOT EXISTS client_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    organization_id INT NOT NULL,
    designation VARCHAR(100),
    is_primary TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 6. PROJECTS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    organization_id INT,
    status ENUM('pending','approved','in_progress','on_hold','completed','cancelled') DEFAULT 'pending',
    priority ENUM('low','medium','high','critical') DEFAULT 'medium',
    start_date DATE,
    end_date DATE,
    estimated_budget DECIMAL(14,2),
    actual_cost DECIMAL(14,2) DEFAULT 0,
    progress_percentage TINYINT DEFAULT 0,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organization_id) REFERENCES organizations(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- ============================================================
-- 7. PROJECT_TEAM TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS project_team (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    employee_id INT NOT NULL,
    role ENUM('lead','developer','designer','tester','analyst','support') DEFAULT 'developer',
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (project_id, employee_id)
) ENGINE=InnoDB;

-- ============================================================
-- 8. MILESTONES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS milestones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    due_date DATE,
    status ENUM('pending','in_progress','completed','overdue') DEFAULT 'pending',
    created_by INT,
    completed_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- ============================================================
-- 9. TASKS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    milestone_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('todo','in_progress','review','completed','blocked') DEFAULT 'todo',
    priority ENUM('low','medium','high','critical') DEFAULT 'medium',
    assignment_type ENUM('individual','group','department','global') DEFAULT 'individual',
    department_id INT,
    due_date DATE,
    estimated_hours DECIMAL(6,2),
    actual_hours DECIMAL(6,2),
    created_by INT,
    completed_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    FOREIGN KEY (milestone_id) REFERENCES milestones(id) ON DELETE SET NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- ============================================================
-- 10. TASK_ASSIGNMENTS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS task_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    employee_id INT NOT NULL,
    assigned_by INT,
    status ENUM('assigned','accepted','in_progress','completed') DEFAULT 'assigned',
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- ============================================================
-- 11. DAILY_UPDATES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS daily_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    employee_id INT NOT NULL,
    update_date DATE NOT NULL,
    work_done TEXT NOT NULL,
    hours_worked DECIMAL(4,2) DEFAULT 0,
    blockers TEXT,
    plan_for_tomorrow TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 12. ATTENDANCE TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    date DATE NOT NULL,
    check_in TIME,
    check_out TIME,
    status ENUM('present','absent','half_day','late','on_leave','holiday','weekend') DEFAULT 'present',
    work_hours DECIMAL(4,2),
    notes VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (employee_id, date)
) ENGINE=InnoDB;

-- ============================================================
-- 13. PAYROLL TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS payroll (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    month INT NOT NULL,
    year INT NOT NULL,
    total_working_days INT DEFAULT 0,
    days_present INT DEFAULT 0,
    days_absent INT DEFAULT 0,
    days_half TINYINT DEFAULT 0,
    days_leave INT DEFAULT 0,
    base_salary DECIMAL(12,2) DEFAULT 0,
    hra DECIMAL(12,2) DEFAULT 0,
    da DECIMAL(12,2) DEFAULT 0,
    special_allowance DECIMAL(12,2) DEFAULT 0,
    gross_salary DECIMAL(12,2) DEFAULT 0,
    pf_deduction DECIMAL(12,2) DEFAULT 0,
    tax_deduction DECIMAL(12,2) DEFAULT 0,
    absent_deduction DECIMAL(12,2) DEFAULT 0,
    other_deductions DECIMAL(12,2) DEFAULT 0,
    total_deductions DECIMAL(12,2) DEFAULT 0,
    net_salary DECIMAL(12,2) DEFAULT 0,
    status ENUM('draft','generated','approved','paid') DEFAULT 'draft',
    generated_at DATETIME,
    approved_by INT,
    paid_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    UNIQUE KEY unique_payroll (employee_id, month, year)
) ENGINE=InnoDB;

-- ============================================================
-- 14. INVOICES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    project_id INT,
    organization_id INT NOT NULL,
    type ENUM('invoice','quotation') DEFAULT 'invoice',
    status ENUM('draft','sent','approved','paid','overdue','cancelled') DEFAULT 'draft',
    issue_date DATE NOT NULL,
    due_date DATE,
    subtotal DECIMAL(14,2) DEFAULT 0,
    tax_rate DECIMAL(5,2) DEFAULT 18.00,
    tax_amount DECIMAL(14,2) DEFAULT 0,
    discount DECIMAL(14,2) DEFAULT 0,
    total_amount DECIMAL(14,2) DEFAULT 0,
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (organization_id) REFERENCES organizations(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- ============================================================
-- 15. INVOICE_ITEMS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    description VARCHAR(500) NOT NULL,
    quantity DECIMAL(10,2) DEFAULT 1,
    unit_price DECIMAL(12,2) DEFAULT 0,
    amount DECIMAL(14,2) DEFAULT 0,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 16. SUPPORT_TICKETS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_number VARCHAR(20) NOT NULL UNIQUE,
    project_id INT,
    created_by INT NOT NULL,
    assigned_to INT,
    subject VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('low','medium','high','critical') DEFAULT 'medium',
    status ENUM('open','in_progress','waiting','resolved','closed') DEFAULT 'open',
    category VARCHAR(100),
    resolution TEXT,
    resolved_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (assigned_to) REFERENCES employees(id)
) ENGINE=InnoDB;

-- ============================================================
-- 17. TICKET_REPLIES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS ticket_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- ============================================================
-- 18. NOTICES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS notices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    type ENUM('general','important','urgent') DEFAULT 'general',
    target ENUM('all','department','individual') DEFAULT 'all',
    department_id INT,
    start_date DATE,
    end_date DATE,
    is_active TINYINT(1) DEFAULT 1,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- ============================================================
-- 19. HOLIDAYS TABLE (Indian public holidays)
-- ============================================================
CREATE TABLE IF NOT EXISTS holidays (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    type ENUM('national','public','restricted','company') DEFAULT 'public',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_holiday (date)
) ENGINE=InnoDB;

-- ============================================================
-- 20. PROJECT_NOTES TABLE (Client notes on projects)
-- ============================================================
CREATE TABLE IF NOT EXISTS project_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    user_id INT NOT NULL,
    note TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;


-- ============================================================
-- SEED DATA
-- ============================================================

-- Departments
INSERT INTO departments (name, slug, description) VALUES
('Administration', 'admin', 'Top-level management and system administration'),
('Human Resources', 'hr', 'Employee management, onboarding, and attendance'),
('Finance', 'finance', 'Financial operations, payroll, invoicing'),
('Development', 'development', 'Software development and engineering'),
('Support', 'support', 'Customer support and ticket management'),
('Data & Research', 'data-research', 'Data analytics, research, and reporting');

-- Users (passwords are hashed version of the passwords listed at top)
-- We use PHP password_hash compatible hashes
INSERT INTO users (email, password, role, full_name, phone) VALUES
('admin@ifms.com', '$2y$10$j8BYcplCKb16gOFdecHnC.RrPkDdoy7PVWT2F9xmryK/W7gJoqj/6', 'admin', 'Rajesh Kumar', '9876543210'),
('hr@ifms.com', '$2y$10$RWGNWiFTQBoNqv1.CeJE1.zZIGQrLjGjGlGgo314Av0v.1UFbNBku', 'employee', 'Priya Sharma', '9876543211'),
('finance@ifms.com', '$2y$10$RWGNWiFTQBoNqv1.CeJE1.zZIGQrLjGjGlGgo314Av0v.1UFbNBku', 'employee', 'Amit Patel', '9876543212'),
('dev@ifms.com', '$2y$10$RWGNWiFTQBoNqv1.CeJE1.zZIGQrLjGjGlGgo314Av0v.1UFbNBku', 'employee', 'Sneha Reddy', '9876543213'),
('pm@ifms.com', '$2y$10$RWGNWiFTQBoNqv1.CeJE1.zZIGQrLjGjGlGgo314Av0v.1UFbNBku', 'employee', 'Vikram Singh', '9876543214'),
('support@ifms.com', '$2y$10$RWGNWiFTQBoNqv1.CeJE1.zZIGQrLjGjGlGgo314Av0v.1UFbNBku', 'employee', 'Anita Desai', '9876543215'),
('data@ifms.com', '$2y$10$RWGNWiFTQBoNqv1.CeJE1.zZIGQrLjGjGlGgo314Av0v.1UFbNBku', 'employee', 'Rahul Verma', '9876543216'),
('client@techcorp.com', '$2y$10$jmVTvXSgiWqRrLOleRdYEufocCBXqN2JzRqGBA.0AAeOGxAFt7ulK', 'client', 'Deepak Mehta', '9876543217');

-- Employees
INSERT INTO employees (user_id, employee_code, department_id, designation, date_of_joining, base_salary, hra, da, special_allowance, pf_deduction, tax_deduction) VALUES
(1, 'EMP001', 1, 'System Administrator', '2020-01-15', 120000, 24000, 12000, 10000, 2160, 5000),
(2, 'EMP002', 2, 'HR Manager', '2021-03-01', 80000, 16000, 8000, 6000, 1440, 3000),
(3, 'EMP003', 3, 'Finance Manager', '2021-06-15', 85000, 17000, 8500, 7000, 1530, 3200),
(4, 'EMP004', 4, 'Software Developer', '2022-01-10', 70000, 14000, 7000, 5000, 1260, 2500),
(5, 'EMP005', 4, 'Senior Developer / Project Manager', '2020-08-20', 110000, 22000, 11000, 9000, 1980, 4500),
(6, 'EMP006', 5, 'Support Executive', '2022-04-01', 45000, 9000, 4500, 3000, 810, 1500),
(7, 'EMP007', 6, 'Data Analyst', '2022-09-15', 65000, 13000, 6500, 5000, 1170, 2200);

-- Set department heads
UPDATE departments SET head_employee_id = 1 WHERE slug = 'admin';
UPDATE departments SET head_employee_id = 2 WHERE slug = 'hr';
UPDATE departments SET head_employee_id = 3 WHERE slug = 'finance';
UPDATE departments SET head_employee_id = 5 WHERE slug = 'development';
UPDATE departments SET head_employee_id = 6 WHERE slug = 'support';
UPDATE departments SET head_employee_id = 7 WHERE slug = 'data-research';

-- Organizations (Client Companies)
INSERT INTO organizations (name, slug, industry, website, email, phone, address, city, state, gst_number) VALUES
('TechCorp Solutions', 'techcorp-solutions', 'IT Services', 'https://techcorp.com', 'contact@techcorp.com', '0112345678', '123 Business Park, Sector 62', 'Noida', 'Uttar Pradesh', '09ABCDE1234F1Z5'),
('StartupHub India', 'startuphub-india', 'Technology', 'https://startuphub.in', 'hello@startuphub.in', '0229876543', '456 Innovation Tower', 'Mumbai', 'Maharashtra', '27XYZAB5678G2H6'),
('Digital Wave Agency', 'digital-wave', 'Digital Marketing', 'https://digitalwave.in', 'info@digitalwave.in', '0801234567', '789 Creative Plaza', 'Bangalore', 'Karnataka', '29MNOPQ9012I3J7');

-- Client Users
INSERT INTO client_users (user_id, organization_id, designation, is_primary) VALUES
(8, 1, 'CTO', 1);

-- Projects
INSERT INTO projects (title, slug, description, organization_id, status, priority, start_date, end_date, estimated_budget, progress_percentage, created_by) VALUES
('E-Commerce Platform Redesign', 'ecommerce-redesign', 'Complete redesign of the TechCorp e-commerce platform with modern UI/UX and improved performance.', 1, 'in_progress', 'high', '2026-01-15', '2026-06-30', 1500000, 35, 1),
('Mobile App Development', 'mobile-app-dev', 'Native mobile application for TechCorp customers on iOS and Android.', 1, 'approved', 'medium', '2026-03-01', '2026-09-30', 2000000, 10, 1),
('CRM Integration', 'crm-integration', 'Integration of Salesforce CRM with existing StartupHub systems.', 2, 'in_progress', 'high', '2026-02-01', '2026-05-31', 800000, 50, 1),
('SEO Optimization Suite', 'seo-suite', 'Advanced SEO analytics and optimization tool for Digital Wave clients.', 3, 'pending', 'low', '2026-04-01', '2026-08-31', 500000, 0, 1);

-- Project Team assignments
INSERT INTO project_team (project_id, employee_id, role) VALUES
(1, 5, 'lead'),
(1, 4, 'developer'),
(2, 5, 'lead'),
(3, 4, 'developer'),
(3, 5, 'lead');

-- Milestones
INSERT INTO milestones (project_id, title, description, due_date, status, created_by) VALUES
(1, 'UI/UX Design Completion', 'Complete all wireframes and design mockups', '2026-02-28', 'completed', 1),
(1, 'Frontend Development', 'Implement all frontend components and pages', '2026-04-15', 'in_progress', 1),
(1, 'Backend API Development', 'Build all REST APIs and database logic', '2026-05-15', 'pending', 1),
(1, 'Testing & QA', 'Complete integration testing and QA', '2026-06-15', 'pending', 1),
(2, 'Requirement Analysis', 'Gather requirements and create specifications', '2026-03-15', 'in_progress', 1),
(3, 'API Integration', 'Complete Salesforce API integration', '2026-03-31', 'in_progress', 1);

-- Tasks
INSERT INTO tasks (project_id, milestone_id, title, description, status, priority, assignment_type, due_date, estimated_hours, created_by) VALUES
(1, 2, 'Build Homepage Component', 'Create responsive homepage with hero section, features, and testimonials', 'in_progress', 'high', 'individual', '2026-03-01', 40, 1),
(1, 2, 'Product Listing Page', 'Implement product grid with filters and search', 'todo', 'high', 'individual', '2026-03-15', 32, 1),
(1, 2, 'Shopping Cart & Checkout', 'Build cart functionality with payment integration', 'todo', 'critical', 'individual', '2026-04-01', 60, 1),
(1, 3, 'User Authentication API', 'Build JWT-based auth with OAuth support', 'todo', 'high', 'individual', '2026-04-15', 24, 1),
(3, 6, 'Salesforce API Connection', 'Set up OAuth and API connection to Salesforce', 'in_progress', 'high', 'individual', '2026-03-15', 20, 1);

-- Task Assignments
INSERT INTO task_assignments (task_id, employee_id, assigned_by, status) VALUES
(1, 4, 1, 'in_progress'),
(2, 4, 1, 'assigned'),
(3, 4, 5, 'assigned'),
(4, 4, 5, 'assigned'),
(5, 4, 1, 'in_progress');

-- Daily Updates
INSERT INTO daily_updates (project_id, employee_id, update_date, work_done, hours_worked, blockers, plan_for_tomorrow) VALUES
(1, 4, '2026-02-12', 'Completed homepage hero section responsive layout. Integrated slider component.', 7.5, 'Waiting for final banner images from design team.', 'Start features section and testimonials carousel.'),
(1, 4, '2026-02-11', 'Set up project structure, configured build tools. Started header/nav component.', 8, NULL, 'Work on homepage hero section.'),
(3, 4, '2026-02-12', 'Tested Salesforce OAuth flow. Debugging token refresh issue.', 6, 'Token refresh returning 401 intermittently.', 'Fix token refresh and start contact sync.');

-- Attendance (sample for Feb 2026)
INSERT INTO attendance (employee_id, date, check_in, check_out, status, work_hours) VALUES
(2, '2026-02-10', '09:00:00', '18:00:00', 'present', 8.00),
(2, '2026-02-11', '09:15:00', '18:30:00', 'present', 8.25),
(2, '2026-02-12', '09:05:00', '18:10:00', 'present', 8.08),
(3, '2026-02-10', '09:30:00', '18:00:00', 'present', 7.50),
(3, '2026-02-11', '09:00:00', '18:00:00', 'present', 8.00),
(3, '2026-02-12', NULL, NULL, 'absent', 0),
(4, '2026-02-10', '10:00:00', '19:00:00', 'present', 8.00),
(4, '2026-02-11', '09:30:00', '19:30:00', 'present', 9.00),
(4, '2026-02-12', '09:00:00', '18:00:00', 'present', 8.00),
(5, '2026-02-10', '09:00:00', '19:00:00', 'present', 9.00),
(5, '2026-02-11', '09:00:00', '18:30:00', 'present', 8.50),
(5, '2026-02-12', '09:00:00', '18:00:00', 'present', 8.00),
(6, '2026-02-10', '09:00:00', '18:00:00', 'present', 8.00),
(6, '2026-02-11', NULL, NULL, 'on_leave', 0),
(6, '2026-02-12', '09:00:00', '18:00:00', 'present', 8.00),
(7, '2026-02-10', '10:00:00', '18:00:00', 'late', 7.00),
(7, '2026-02-11', '09:00:00', '18:00:00', 'present', 8.00),
(7, '2026-02-12', '09:00:00', '18:00:00', 'present', 8.00);

-- Support Tickets
INSERT INTO support_tickets (ticket_number, project_id, created_by, assigned_to, subject, description, priority, status, category) VALUES
('TKT-20260001', 1, 8, 5, 'Homepage loading slow', 'The homepage takes over 5 seconds to load on mobile devices. Need performance optimization.', 'high', 'open', 'Performance'),
('TKT-20260002', 1, 8, 5, 'Payment gateway error', 'Getting 500 error when trying to process payments via UPI. Works fine with credit cards.', 'critical', 'in_progress', 'Bug'),
('TKT-20260003', 3, 8, NULL, 'Data sync mismatch', 'Some contacts from Salesforce are not syncing properly. Missing phone numbers.', 'medium', 'open', 'Data');

-- Invoices
INSERT INTO invoices (invoice_number, project_id, organization_id, type, status, issue_date, due_date, subtotal, tax_rate, tax_amount, discount, total_amount, created_by) VALUES
('INV-2026-001', 1, 1, 'invoice', 'sent', '2026-02-01', '2026-02-28', 500000, 18.00, 90000, 0, 590000, 1),
('INV-2026-002', 3, 2, 'invoice', 'paid', '2026-01-15', '2026-02-15', 400000, 18.00, 72000, 20000, 452000, 1),
('QUO-2026-001', 2, 1, 'quotation', 'sent', '2026-02-10', '2026-03-10', 2000000, 18.00, 360000, 100000, 2260000, 1),
('QUO-2026-002', 4, 3, 'quotation', 'draft', '2026-02-12', '2026-03-12', 500000, 18.00, 90000, 0, 590000, 1);

-- Invoice Items
INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, amount) VALUES
(1, 'UI/UX Design - E-Commerce Platform (Phase 1)', 1, 200000, 200000),
(1, 'Frontend Development Sprint 1', 1, 300000, 300000),
(2, 'Salesforce API Integration - Phase 1', 1, 250000, 250000),
(2, 'Data Migration & Testing', 1, 150000, 150000),
(3, 'Mobile App - iOS Development', 1, 1000000, 1000000),
(3, 'Mobile App - Android Development', 1, 800000, 800000),
(3, 'QA Testing & Deployment', 1, 200000, 200000);

-- Notices
INSERT INTO notices (title, content, type, target, start_date, end_date, created_by) VALUES
('Republic Day Holiday', 'The office will remain closed on 26th January 2026 for Republic Day. Enjoy the holiday!', 'general', 'all', '2026-01-24', '2026-01-26', 1),
('Quarterly Town Hall Meeting', 'All employees are requested to attend the Q1 2026 Town Hall meeting on February 15th at 3 PM in the main conference hall.', 'important', 'all', '2026-02-12', '2026-02-15', 1),
('IT Infrastructure Maintenance', 'Scheduled maintenance on Feb 20th, 10 PM to 2 AM. VPN and internal services may be unavailable.', 'urgent', 'all', '2026-02-18', '2026-02-20', 1);

-- Indian Holidays 2026
INSERT INTO holidays (title, date, type) VALUES
('Republic Day', '2026-01-26', 'national'),
('Maha Shivaratri', '2026-02-17', 'public'),
('Holi', '2026-03-17', 'national'),
('Good Friday', '2026-04-03', 'public'),
('Ram Navami', '2026-04-06', 'public'),
('Dr. Ambedkar Jayanti', '2026-04-14', 'national'),
('Eid ul-Fitr', '2026-04-01', 'public'),
('Buddha Purnima', '2026-05-12', 'public'),
('Eid ul-Adha', '2026-06-07', 'public'),
('Independence Day', '2026-08-15', 'national'),
('Janmashtami', '2026-08-25', 'public'),
('Milad-un-Nabi', '2026-09-05', 'public'),
('Mahatma Gandhi Jayanti', '2026-10-02', 'national'),
('Dussehra', '2026-10-12', 'public'),
('Diwali', '2026-11-01', 'national'),
('Guru Nanak Jayanti', '2026-11-16', 'public'),
('Christmas Day', '2026-12-25', 'national');

-- Project Notes
INSERT INTO project_notes (project_id, user_id, note) VALUES
(1, 8, 'Please prioritize mobile responsiveness for the e-commerce platform. Most of our customers are mobile users.'),
(1, 8, 'Can we add a wishlist feature in phase 2? This is highly requested by our customers.'),
(3, 8, 'Make sure the CRM integration syncs contacts in real-time, not batch.');


-- ============================================================
-- 21. PASSWORD_RESETS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    INDEX (email),
    INDEX (token)
) ENGINE=InnoDB;
