# IFMS Architecture Diagrams & Workflows
## Visual System Design & Data Flow

---

## 📊 System Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────┐
│                     CLIENT LAYER (User Browser)                     │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │  HTML5 + Tailwind CSS + Vanilla JavaScript                   │  │
│  │  • Responsive UI (Desktop, Tablet, Mobile)                   │  │
│  │  • Form validation                                           │  │
│  │  • Toast notifications                                       │  │
│  └──────────────────────────────────────────────────────────────┘  │
└──────────────────────────────┬──────────────────────────────────────┘
                               │ HTTP/HTTPS
                               ▼
┌─────────────────────────────────────────────────────────────────────┐
│              WEB SERVER LAYER (Apache 2.4)                          │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │  • Route requests to appropriate PHP files                   │  │
│  │  • Handle SSL/TLS encryption                                 │  │
│  │  • Session management                                        │  │
│  │  • Static asset serving                                      │  │
│  └──────────────────────────────────────────────────────────────┘  │
└──────────────────────────────┬──────────────────────────────────────┘
                               │
            ┌──────────────────┼──────────────────┐
            │                  │                  │
            ▼                  ▼                  ▼
    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
    │ index.php   │    │ admin/*     │    │ employee/*  │
    │ (Login)     │    │             │    │             │
    └─────────────┘    └─────────────┘    └─────────────┘
            │                  │                  │
            └──────────────────┼──────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────────────┐
│         APPLICATION LAYER (PHP Business Logic)                      │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │  config/auth.php - Authentication & RBAC                    │  │
│  │  • Login/logout functions                                    │  │
│  │  • Session management                                        │  │
│  │  • Permission checking (role + department)                   │  │
│  │  • Module access control                                     │  │
│  ├──────────────────────────────────────────────────────────────┤  │
│  │  api/* - REST Endpoints                                      │  │
│  │  • auth.php (login, logout, profile update)                 │  │
│  │  • projects.php (CRUD operations)                            │  │
│  │  • tasks.php (assignment, status update)                    │  │
│  │  • employees.php (employee management)                       │  │
│  │  • payroll.php (salary calculation)                         │  │
│  │  • attendance.php (check-in/out)                            │  │
│  │  • tickets.php (support tickets)                            │  │
│  │  • clients.php (client management)                          │  │
│  └──────────────────────────────────────────────────────────────┘  │
└──────────────────────────────┬──────────────────────────────────────┘
                               │ PDO (Prepared Statements)
                               ▼
┌─────────────────────────────────────────────────────────────────────┐
│              DATABASE LAYER (MySQL 5.7+)                            │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │  ┌──────────────────────────────────────────────────────┐   │  │
│  │  │ Users & Access Control                               │   │  │
│  │  │ • users (authentication)                             │   │  │
│  │  │ • employees (employee details)                       │   │  │
│  │  │ • client_users (client mapping)                      │   │  │
│  │  │ • departments (department structure)                 │   │  │
│  │  └──────────────────────────────────────────────────────┘   │  │
│  │ ┌──────────────────────────────────────────────────────┐   │  │
│  │ │ Project Management                                   │   │  │
│  │ │ • projects (project definitions)                     │   │  │
│  │ │ • project_team (team assignments)                    │   │  │
│  │ │ • milestones (project milestones)                    │   │  │
│  │ │ • tasks (individual tasks)                           │   │  │
│  │ │ • task_assignments (team assignments)                │   │  │
│  │ □ • daily_updates (work progress logs)                │   │  │
│  │ └──────────────────────────────────────────────────────┘   │  │
│  │ ┌──────────────────────────────────────────────────────┐   │  │
│  │ │ Financial Management                                 │   │  │
│  │ │ • invoices (billing)                                 │   │  │
│  │ │ • invoice_items (line items)                         │   │  │
│  │ │ • payroll (salary records)                           │   │  │
│  │ │ • organizations (client companies)                   │   │  │
│  │ └──────────────────────────────────────────────────────┘   │  │
│  │ ┌──────────────────────────────────────────────────────┐   │  │
│  │ │ Operations & Support                                 │   │  │
│  │ │ • attendance (daily attendance)                      │   │  │
│  │ │ • support_tickets (issue tracking)                   │   │  │
│  │ │ • ticket_replies (communications)                    │   │  │
│  │ │ • notices (company announcements)                    │   │  │
│  │ │ • holidays (holiday calendar)                        │   │  │
│  │ └──────────────────────────────────────────────────────┘   │  │
│  └──────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 🔐 Authentication & Authorization Flow

```
User Visits http://localhost/ifms/
       │
       ▼
   ┌────────────────┐
   │  index.php     │   (Login Page)
   └────────────────┘
       │
       │ User enters credentials
       │
       ▼
   ┌────────────────────────────────────┐
   │ POST /api/auth.php                │
   │ {action: 'login',                 │
   │  email: '...', password: '...'}   │
   └────────────────────────────────────┘
       │
       ▼
   ┌────────────────────────────────────┐
   │ config/auth.php:loginUser()        │
   │                                    │
   │ 1. Find user by email             │
   │ 2. Verify password (bcrypt)       │
   │ 3. If invalid → return error      │
   │ 4. Get employee/client info       │
   │ 5. Set SESSION variables          │
   │    - user_id, user_email          │
   │    - user_role, user_name         │
   │    - user_department (if employee)│
   │    - organization_id (if client)  │
   │ 6. Update last_login timestamp    │
   │ 7. Return redirect URL            │
   └────────────────────────────────────┘
       │
       ├─ Success (role=admin)
       │       │
       │       ▼
       │   /ifms/admin/
       │
       ├─ Success (role=employee)
       │       │
       │       ▼
       │   /ifms/employee/
       │
       └─ Success (role=client)
               │
               ▼
           /ifms/client/

┌─────────────────────────────────────────────┐
│         PERMISSION MATRIX CHECKING          │
│                                             │
│  Each page loaded by user:                 │
│                                             │
│  1. Check: isLoggedIn()    (session set?)  │
│  2. Check: requireRole()   (correct role?) │
│  3. Check: requireDept()   (correct dept?) │
│  4. Check: canAccessModule() (module ok?)  │
│                                             │
│  If ANY check fails → 403 Forbidden        │
│  Otherwise → Page loads with user data     │
└─────────────────────────────────────────────┘
```

---

## 📋 Page Request Lifecycle

```
┌─────────────────────────────────────────────────────────────┐
│              User Requests: /ifms/admin/projects.php        │
└──────────────────┬──────────────────────────────────────────┘
                   │
                   ▼
        ┌──────────────────────┐
        │ Session Started      │  (PHP session_start())
        │ $_SESSION initialized│
        └──────────┬───────────┘
                   │
                   ▼
        ┌──────────────────────────────────────┐
        │ requireLogin() called                │
        │ Checks if user_id in $_SESSION      │
        │ If not → Redirect to /ifms/         │
        └──────────┬───────────────────────────┘
                   │
                   ▼
        ┌──────────────────────────────────────┐
        │ requireRole('admin') called          │
        │ Checks $_SESSION['user_role']        │
        │ If not admin → Include 403.php       │
        └──────────┬───────────────────────────┘
                   │
                   ▼
        ┌──────────────────────────────────────┐
        │ Database connected                   │
        │ getDB() returns PDO connection       │
        └──────────┬───────────────────────────┘
                   │
                   ▼
        ┌──────────────────────────────────────┐
        │ getData from database                │
        │ SELECT * FROM projects               │
        │ $projects = $db->query(...)->        │
        │             fetchAll()               │
        └──────────┬───────────────────────────┘
                   │
                   ▼
        ┌──────────────────────────────────────┐
        │ Include Header                       │
        │ includes/header.php                  │
        │ • Navigation                         │
        │ • Title                              │
        │ • User info                          │
        └──────────┬───────────────────────────┘
                   │
                   ▼
        ┌──────────────────────────────────────┐
        │ Output Page Content                  │
        │ HTML with data from $projects       │
        │ Forms for interactions               │
        │ JavaScript for dynamic behavior      │
        └──────────┬───────────────────────────┘
                   │
                   ▼
        ┌──────────────────────────────────────┐
        │ Include Footer                       │
        │ includes/footer.php                  │
        │ • Footer content                     │
        │ • Close HTML tags                    │
        └──────────┬───────────────────────────┘
                   │
                   ▼
        ┌──────────────────────────────────────┐
        │ Browser Renders HTML                 │
        │ Loads Tailwind CSS from CDN          │
        │ Loads app.js for interactions        │
        │ Ready for user interaction           │
        └──────────────────────────────────────┘
```

---

## 🔄 Data Operations Flow

### **CREATE Operation** (User Creates Project)
```
┌─────────────────────────────┐
│  Admin clicks "New Project" │
└──────────────┬──────────────┘
               │
               ▼
    ┌─────────────────────────┐
    │ Modal Form Appears      │
    │ User enters data:       │
    │ • title                 │
    │ • description           │
    │ • start_date            │
    │ • budget                │
    └──────────┬──────────────┘
               │
               ▼
    ┌──────────────────────────────┐
    │ JavaScript validation        │
    │ Check required fields        │
    │ If invalid → Show error      │
    └──────────┬───────────────────┘
               │ (Valid)
               ▼
    ┌──────────────────────────────────┐
    │ fetch('/ifms/api/projects.php')  │
    │ method: POST                      │
    │ body: {                           │
    │   action: 'create',               │
    │   title: '...',                   │
    │   ...                             │
    │ }                                 │
    └──────────┬──────────────────────┘
               │
               ▼
    ┌──────────────────────────────┐
    │ /api/projects.php            │
    │                              │
    │ 1. requireAPI() check        │
    │    Is user logged in?        │
    │                              │
    │ 2. $data = getPostData()     │
    │    Parse JSON request        │
    │                              │
    │ 3. Check action == 'create'  │
    │                              │
    │ 4. Validate input            │
    │    All required fields?      │
    │    Valid data types?         │
    │                              │
    │ 5. Database insert           │
    │    INSERT INTO projects      │
    │    VALUES (...)              │
    │                              │
    │ 6. Get last insert ID        │
    │    $newID = db->lastID()     │
    │                              │
    │ 7. Return JSON response      │
    │    {success: true, id: ...}  │
    └──────────┬───────────────────┘
               │
               ▼
    ┌──────────────────────────┐
    │ JavaScript receives      │
    │ JSON response            │
    │                          │
    │ if (result.success) {    │
    │   showToast('Created!'); │
    │   location.reload();     │
    │ } else {                 │
    │   showToast(error,       │
    │             'error');    │
    │ }                        │
    └──────────┬───────────────┘
               │
               ▼
    ┌──────────────────────────┐
    │ Page refreshes           │
    │ Shows new project        │
    └──────────────────────────┘
```

---

## 👥 Multi-Role Access Control

```
                    DEPARTMENT MATRIX
    
    USER LOGIN
         │
         ▼
    ROLE CHECK
    ├─→ admin         → Full Access to Everything
    │
    ├─→ employee     → Department-based access
    │   │
    │   ├─→ HR dept
    │   │   ├─ Can access: employees, attendance, leaves, notices
    │   │   └─ Cannot access: projects, payroll, finance
    │   │
    │   ├─→ Finance dept
    │   │   ├─ Can access: payroll, invoices, quotations, reports
    │   │   └─ Cannot access: employees, projects, tasks
    │   │
    │   ├─→ Development dept
    │   │   ├─ Can access: projects (assigned), tasks, daily-updates
    │   │   └─ Cannot access: payroll, employees, finance
    │   │
    │   ├─→ Support dept
    │   │   ├─ Can access: tickets, clients
    │   │   └─ Cannot access: payroll, projects
    │   │
    │   └─→ Data & Research dept
    │       ├─ Can access: reports, analytics, data-exports
    │       └─ Cannot access: payroll, projects
    │
    └─→ client         → Limited Access
        ├─ Can access: projects (own), invoices (own), tickets, profile
        └─ Cannot access: employees, payroll, other companies

PERMISSION MATRIX DEFINED IN:
    config/auth.php :: getPermissionMatrix()
    
ENFORCED BY:
    1. requireRole($roles)           - Check user role
    2. requireDepartment($depts)     - Check department
    3. requireModuleAccess($modules) - Check module permission
    4. canAccessModule($module)      - Utility function
```

---

## 💻 Component Interaction

```
┌───────────────────────────────────────────────────────────────┐
│                    USER INTERFACE LAYER                       │
│                                                               │
│  ┌──────────────┐  ┌──────────────┐  ┌─────────────┐         │
│  │   Header     │  │  Sidebar     │  │   Content   │         │
│  │  (includes/  │  │  (includes/  │  │   Area      │         │
│  │   header.php)│  │   sidebar.php)  │  (template) │         │
│  └──────────────┘  └──────────────┘  └─────────────┘         │
│         │                 │                 │                 │
│         └─────────────────┼─────────────────┘                 │
│                           │                                   │
│                    (Tailwind CSS)                            │
│                                                               │
└─────────────────────────────┬─────────────────────────────────┘
                              │
                    ┌─────────▼────────┐
                    │ User Interaction │
                    │                  │
                    │ • Form submission│
                    │ • Button click   │
                    │ • Link click     │
                    │ • Search input   │
                    └─────────┬────────┘
                              │
         ┌────────────────────┴────────────────────┐
         │                                         │
    FORM SUBMIT                            API CALL
    (GET/POST)                             (fetch)
         │                                    │
         ▼                                    ▼
    New Page                         /api/endpoint.php
         │                                    │
         ├─→ Process in PHP                   ├─→ Validate
         ├─→ Get from DB                      ├─→ Process
         ├─→ Render template                  ├─→ Update DB
         └─→ Return HTML                      └─→ Return JSON
                                                   │
                                          ┌────────▼────────┐
                                          │ Response handler│
                                          │                 │
                                          │ if success {    │
                                          │   showToast()   │
                                          │   reload/nav    │
                                          │ } else {        │
                                          │   showError()   │
                                          │ }               │
                                          └─────────────────┘
```

---

## 📊 Database Relationships

```
USERS (Core)
├── ├─ Many → ONE employees (via user_id)
├── ├─ Many → ONE client_users (via user_id)
├── └─ Many → ONE password_resets (via email)
│
EMPLOYEES (Employee Data)
├── ├─ ONE → ONE departments (via department_id)
├── ├─ Many → ONE project_team (via employee_id)
├── ├─ Many → ONE task_assignments (via employee_id)
├── ├─ Many → ONE daily_updates (via employee_id)
├── ├─ Many → ONE attendance (via employee_id)
├── ├─ Many → ONE payroll (via employee_id)
└── └─ Many → ONE support_tickets (assigned_to)
│
ORGANIZATIONS (Client Companies)
├── ├─ Many → ONE client_users (via organization_id)
├── ├─ Many → ONE projects (via organization_id)
└── └─ Many → ONE invoices (via organization_id)
│
PROJECTS (Project Management)
├── ├─ Many → ONE project_team (via project_id)
├── ├─ Many → ONE milestones (via project_id)
├── ├─ Many → ONE tasks (via project_id)
├── ├─ Many → ONE daily_updates (via project_id)
├── ├─ Many → ONE support_tickets (via project_id)
├── ├─ Many → ONE invoices (via project_id)
└── └─ Many → ONE project_notes (via project_id)
│
TASKS (Task Management)
├── ├─ Many → ONE task_assignments (via task_id)
├── ├─ Many → ONE milestones (via milestone_id)
└── └─ Many → ONE tickets (via related in ticket replies)
│
INVOICES (Financial)
├── ├─ Many → ONE invoice_items (via invoice_id)
└── └─ ONE  ← ONE projects (via project_id)
│
SUPPORT_TICKETS (Support)
├── ├─ Many → ONE ticket_replies (via ticket_id)
└── └─ ONE  ← ONE projects (via project_id)

DEPARTMENTS (Organizational)
├── ├─ Many → ONE employees (via department_id)
└── └─ Many → ONE notices (via department_id)

NOTICES & HOLIDAYS (Admin)
├── Standalone lookup tables
└── Referenced by various modules

ATTENDANCE & PAYROLL (Operations)
├── Many → ONE employees
└── Operational data linked to employee lifecycle
```

---

## 🔄 Sample Workflow: Project Creation to Invoice

```
1. ADMIN CREATES PROJECT
   └─→ admin/projects.php → /api/projects.php?action=create
       └─→ INSERT INTO projects
           └─→ Project ID: 15

2. ADMIN ASSIGNS TEAM
   └─→ /api/projects.php?action=assign_team
       └─→ INSERT INTO project_team (project_id=15, employees=[4,5])

3. ADMIN CREATES MILESTONE
   └─→ /api/projects.php?action=create_milestone
       └─→ INSERT INTO milestones (project_id=15, title='Design', due_date='...')

4. PROJECT MANAGER CREATES TASKS
   └─→ /api/tasks.php?action=create
       └─→ INSERT INTO tasks (project_id=15, milestone_id=3, title='Homepage', ...)

5. SENIOR DEV ASSIGNS TASKS
   └─→ /api/tasks.php?action=assign
       └─→ INSERT INTO task_assignments (task_id=[7,8,9], employee_id=4)

6. DEVELOPERS LOG DAILY UPDATES
   └─→ employee/daily-updates.php → /api/daily_updates.php
       └─→ INSERT INTO daily_updates (project_id=15, employee_id=4, ...)

7. TASKS COMPLETED & MILESTONES CLOSED
   └─→ Status updated to 'completed'

8. FINANCE GENERATES INVOICE
   └─→ admin/invoices.php → /api/invoices.php?action=generate_from_project
       └─→ INSERT INTO invoices (project_id=15, organization_id=1, ...)
       └─→ INSERT INTO invoice_items (invoice_id=8, description='Development', ...)

9. CLIENT VIEWS INVOICE
   └─→ client/invoices.php → Shows invoices for their organization
       └─→ SELECT FROM invoices WHERE organization_id=1

10. INVOICE MARKED PAID
    └─→ Updates invoice status to 'paid'
```

---

## 🎯 Department Access Implementation

```
User Logs In
     │
     ▼
SESSION SET:
 • user_id=4
 • user_role='employee'
 • user_department='Development'
 • user_department_slug='development'
     │
     ▼
USER VISITS: /ifms/admin/employees.php
     │
     ├─→ requireRole('admin')
     │   Check: $_SESSION['user_role'] == 'admin'? NO
     │   └─→ Return 403 Forbidden ✗
     │
     └─→ User is redirected to dashboard

USER VISITS: /ifms/employee/projects.php
     │
     ├─→ requireRole('employee') ✓ (Pass)
     │
     ├─→ canAccessModule('projects') ✓ (Pass)
     │   Development dept CAN access projects
     │
     └─→ Page loads with DEV'S ASSIGNED PROJECTS ONLY
         SELECT FROM projects p
         JOIN project_team pt ON p.id = pt.project_id
         WHERE pt.employee_id = 4


ADMIN TRIES SAME:
     │
     ├─→ requireRole('admin') ✓ (Pass)
     │
     ├─→ canAccessModule('projects') ✓ (Pass)
     │   Admin can access everything
     │
     └─→ Page loads with ALL PROJECTS IN SYSTEM
         SELECT FROM projects
         (No employee_id filter for admin)
```

---

*Architecture diagrams and workflows defined. System is ready for implementation of Phase 2 features.*
