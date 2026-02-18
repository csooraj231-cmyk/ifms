# IFMS - Infrastructure Management System
## Professional CRM+ERP for IT Companies (500+ Employees)

![Version](https://img.shields.io/badge/Version-1.0--Beta-blue)
![License](https://img.shields.io/badge/License-MIT-green)
![Status](https://img.shields.io/badge/Status-In%20Development-orange)

---

## 📋 Table of Contents
1. [Project Overview](#project-overview)
2. [Key Features](#key-features)
3. [Tech Stack](#tech-stack)
4. [System Requirements](#system-requirements)
5. [Installation Guide](#installation-guide)
6. [Architecture](#architecture)
7. [Role-Based Features](#role-based-features)
8. [API Documentation](#api-documentation)
9. [Database Schema](#database-schema)
10. [Development Roadmap](#development-roadmap)

---

## 🎯 Project Overview

**IFMS** is a comprehensive **Infrastructure & Business Management Suite** designed specifically for IT companies. It combines **CRM**, **ERP**, and **HRIS** capabilities into a single, role-based platform that manages:

- **Employee Lifecycle**: Onboarding, profiles, skills, departments
- **Project Management**: Creation, team assignment, milestones, task tracking
- **Financial Management**: Payroll, invoices, quotations, expense tracking
- **Human Resources**: Attendance, leave management, employee notices
- **Client Relations**: Project visibility, ticket management, invoicing
- **Analytics & Reporting**: Business KPIs, financial reports, team utilization

**Target Audience**: IT services companies, consultancies, software development firms with 100-5000+ employees

---

## ✨ Key Features

### 🔐 Authentication & Security
- ✅ Session-based authentication with bcrypt password hashing
- ✅ Password reset via email verification
- ✅ Role-based access control (Admin, Employee, Client)
- ✅ Department-level permissions (HR, Finance, Development, Support, Data)
- ✅ Activity logging & session management

### 👨‍💼 Admin Dashboard & Control
- ✅ **Central Dashboard**: KPI overview (employees, projects, revenue, tickets)
- ✅ **Employee Management**: Full CRUD operations, department assignment
- ✅ **Client Management**: Organization profiles, user management
- ✅ **Project Management**: Create projects, assign teams, track progress
- ✅ **Payroll Management**: Generate payroll, approval workflow
- ✅ **Invoice Management**: Create invoices, track payments
- ✅ **Attendance Management**: Track attendance, view reports
- ✅ **Leave Management**: Configure leave types, approve requests
- ✅ **Notices & Holidays**: Post company notices, manage holidays
- ✅ **Reports & Analytics**: Business intelligence dashboard

### 💼 Employee Features
- ✅ **Employee Dashboard**: Task overview, project assignments
- ✅ **Task Management**: View assigned tasks, update status
- ✅ **Daily Updates**: Log work progress, hourly tracking
- ✅ **Project View**: See assigned projects with progress
- ✅ **Attendance**: Mark attendance, view history
- ✅ **Payroll Access**: View salary slips, payment history
- ✅ **Leave Requests**: Apply for leave, check balance
- ✅ **Profile Management**: Update personal details, change password
- ✅ **Team View**: Collaborate with team members

### 🤝 Client Features
- ✅ **Client Dashboard**: Project overview, progress tracking
- ✅ **Project Management**: View project status, milestones, daily updates
- ✅ **Invoice Tracking**: View invoices, payment history
- ✅ **Support Tickets**: Create tickets, track issues, communicate with team
- ✅ **Organization Profile**: View company details
- ✅ **Communication**: Reply to ticket comments

### 💰 Financial Management
- ✅ Payroll generation with automatic calculations
- ✅ Salary configuration (base, HRA, DA, allowances, deductions)
- ✅ Attendance-based deduction calculations
- ✅ Invoice generation from projects
- ✅ Quotation management
- ✅ Payment tracking
- ✅ Financial reports & trends

### 🎯 Project Management
- ✅ Project creation with status tracking
- ✅ Team member assignment
- ✅ Milestone creation and tracking
- ✅ Task creation with assignment types (individual, group, department)
- ✅ Task status workflow (⏰ → In Progress → 📋 Review → ✅ Completed)
- ✅ Project notes from clients
- ✅ Daily progress updates

### 🎫 Support & Ticketing
- ✅ Support ticket creation by clients
- ✅ Auto-assignment or manual assignment to developers
- ✅ Ticket status workflow
- ✅ Communication thread with replies
- ✅ Priority classification (low, medium, high, critical)
- ✅ Resolution tracking

### 📊 Analytics & Reporting
- ✅ Revenue dashboard (paid invoices, pending)
- ✅ Project metrics (completion %, timeline)
- ✅ Employee metrics (utilization, tasks completed)
- ✅ Attendance trends
- ✅ Payroll summaries
- ✅ Financial reports

---

## 🛠️ Tech Stack

### **Frontend**
- **HTML5** - Semantic markup
- **Tailwind CSS** - Modern utility-first styling
- **Vanilla JavaScript** - No framework dependency
- **Chart.js** - Data visualization (optional, for analytics)

### **Backend**
- **PHP 7.4+** - Server-side logic
- **PDO** - Database abstraction
- **Session Management** - Built-in PHP sessions
- **Composer** - Dependency management (for future packages)

### **Database**
- **MySQL 5.7+** - Relational database
- **InnoDB** - Transaction support
- **21 Tables** - Comprehensive schema

### **Server & Deployment**
- **Apache 2.4** - Web server (via XAMPP)
- **XAMPP** - Local development stack
- **Future**: Docker, Kubernetes, AWS/Azure

---

## 📦 System Requirements

### **Minimum Requirements**
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache 2.4 or higher
- 2GB RAM minimum
- 1GB disk space

### **Recommended Requirements**
- PHP 8.0+
- MySQL 8.0+
- Apache 2.4.5+
- 8GB+ RAM
- 10GB+ disk space
- Ubuntu 20.04 LTS or Windows Server 2019

### **Browser Support**
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

---

## 💾 Installation Guide

### **1. Prerequisites**
```bash
# Install XAMPP (includes Apache, PHP, MySQL)
# Download from: https://www.apachefriends.org/
```

### **2. Clone/Place Project**
```bash
# Copy project to XAMPP htdocs
cp -r ifms C:\xampp\htdocs\
# OR on Linux/Mac:
cp -r ifms /opt/lampp/htdocs/
```

### **3. Create Database**
```bash
# Open PHPMyAdmin: http://localhost/phpmyadmin
# Or via MySQL CLI:
mysql -u root -p < database/schema.sql
```

### **4. Configure Settings**
```php
// config/database.php - Already configured for XAMPP defaults
$dsn = 'mysql:host=localhost;dbname=ifms_db;charset=utf8mb4';
$user = 'root';
$password = '';
```

### **5. Start Services**
```bash
# Start XAMPP (Apache + MySQL)
# Windows: Click Start in XAMPP Control Panel
# Linux/Mac: sudo /opt/lampp/lampp start
```

### **6. Access Application**
```
URL: http://localhost/ifms/

Test Credentials:
- Admin:       admin@ifms.com          / admin123
- HR:          hr@ifms.com             / emp123
- Finance:     finance@ifms.com        / emp123
- Developer:   dev@ifms.com            / emp123
- PM:          pm@ifms.com             / emp123
- Support:     support@ifms.com        / emp123
- Client:      client@techcorp.com     / client123
```

---

## 🏗️ System Architecture

### **High-Level Architecture**
```
┌─────────────┐
│  Client App │ (Browser - HTML/CSS/JS)
└──────┬──────┘
       │ HTTP Requests
       ▼
┌─────────────────────────────────────┐
│      Apache Web Server              │
│   (Handles routing, SSL/TLS)        │
└──────┬──────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────┐
│      PHP Application Layer          │
│  ├─ index.php (Login)               │
│  ├─ admin/* (Admin pages)           │
│  ├─ employee/* (Employee pages)     │
│  ├─ client/* (Client pages)         │
│  └─ api/* (REST API endpoints)      │
└──────┬──────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│   Authentication & Authorization     │
│  ├─ config/auth.php (RBAC)          │
│  ├─ Session Management              │
│  └─ Permission Checks               │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│      MySQL Database                  │
│  ├─ Users & Employees               │
│  ├─ Projects & Tasks                │
│  ├─ Financial Data                  │
│  ├─ Attendance & Payroll            │
│  └─ Ticketing & Support             │
└──────────────────────────────────────┘
```

### **Data Flow**
```
User Login
  └─> Session Created (auth.php)
      └─> Permissions Loaded
          └─> Dashboard/Module Loaded
              └─> API calls for data
                  └─> Database Query
                      └─> Response to UI
                          └─> Render Page
```

---

## 🎯 Role-Based Features Matrix

| Feature | Admin | HR | Finance | Development | Senior Dev | Client |
|---------|-------|----|---------|--------------|-----------|----|
| **View All Employees** | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| **Manage Payroll** | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| **Create Invoices** | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| **View Projects** | ✅ | ❌ | ❌ | ✅ | ✅ | ✅ |
| **Assign Tasks** | ✅ | ❌ | ❌ | ❌ | ✅ | ❌ |
| **Log Daily Updates** | ❌ | ❌ | ❌ | ✅ | ✅ | ❌ |
| **Approve Leave** | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **View Support Tickets** | ✅ | ❌ | ❌ | ❌ | ✅ | ✅ |
| **Create Support Tickets** | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| **View Invoices** | ✅ | ❌ | ✅ | ❌ | ❌ | ✅ |
| **View Analytics** | ✅ | ❌ | ✅ | ❌ | ✅ | ❌ |

---

## 📡 API Documentation

### **Authentication Endpoints**

#### Login
```bash
POST /api/auth.php
Content-Type: application/json

{
  "action": "login",
  "email": "dev@ifms.com",
  "password": "emp123"
}

Response:
{
  "success": true,
  "redirect": "/ifms/employee/",
  "role": "employee"
}
```

#### Get Current User
```bash
POST /api/auth.php
Content-Type: application/json

{
  "action": "me"
}

Response:
{
  "id": 4,
  "email": "dev@ifms.com",
  "role": "employee",
  "full_name": "Sneha Reddy",
  "department": "Development",
  "designation": "Software Developer"
}
```

### **Project Endpoints**
```bash
GET /api/projects.php
GET /api/projects.php?id=1
POST /api/projects.php (action: create, update, assign_team)
```

### **Task Endpoints**
```bash
GET /api/tasks.php
POST /api/tasks.php (action: create, assign, update_status)
```

### **Payroll Endpoints** (In Development)
```bash
GET /api/payroll.php
POST /api/payroll.php (action: generate, approve, view_slip)
```

---

## 🗄️ Database Schema

### **Core Tables** (21 Total)

#### Users & Access Control
- `users` - Central authentication table
- `employees` - Employee detail extensions
- `client_users` - Client user mapping
- `departments` - Department definitions

#### Projects & Tasks
- `projects` - Project definitions
- `project_team` - Team assignments
- `milestones` - Project milestones
- `tasks` - Task definitions
- `task_assignments` - Task assignments to employees
- `daily_updates` - Daily work progress logs

#### Financial
- `payroll` - Monthly payroll records
- `invoices` - Invoice management
- `invoice_items` - Line items in invoices

#### Operations
- `attendance` - Daily attendance tracking
- `organizations` - Client organizations

#### Support
- `support_tickets` - Support ticket management
- `ticket_replies` - Ticket communication

#### Admin
- `notices` - Company notices
- `holidays` - Holiday definitions
- `password_resets` - Password reset tokens
- `project_notes` - Client project notes

**Total Entities**: 21 tables with 300+ fields

---

## 🚀 Development Roadmap

### **Current Status**: MVP Complete (65%)

### **Phase 1: Foundation (✅ Mostly Complete)**
- ✅ User authentication & RBAC
- ✅ Admin dashboard with KPIs
- ✅ Employee & client management
- ✅ Basic project management
- ✅ Task assignment & tracking
- ✅ Attendance marking
- ✅ Payroll generation (basic)
- ✅ Invoice management
- ✅ Support tickets

### **Phase 2: Enhancement (🔧 In Progress)**
- 🔧 Advanced payroll calculations
- 🔧 Email notifications
- 🔧 Invoice generation from projects
- 🔧 Advanced analytics dashboard
- 🔧 Leave management system
- ⏳ PDF salary slip generation
- ⏳ Budget tracking

### **Phase 3: Scale & Polish (⏳ Planned)**
- ⏳ Performance optimization
- ⏳ Caching layer (Redis)
- ⏳ API rate limiting
- ⏳ Audit logging
- ⏳ Two-factor authentication
- ⏳ Document management

### **Phase 4: Enterprise (🚀 Future)**
- 🚀 Microservices architecture
- 🚀 Mobile app (iOS/Android)
- 🚀 AI-powered reporting
- 🚀 Integration APIs (SAP, Slack)
- 🚀 Multi-tenancy support
- 🚀 Custom workflow builder

---

## 📚 File Structure

```
ifms/
├── index.php                    # Login page
├── admin/
│   ├── index.php               # Admin dashboard
│   ├── employees.php           # Employee management
│   ├── clients.php             # Client management
│   ├── projects.php            # Project management
│   ├── tasks.php               # Task management
│   ├── payroll.php             # Payroll management
│   ├── invoices.php            # Invoice management
│   ├── attendance.php          # Attendance management
│   ├── profile.php             # Admin profile
│   ├── settings.php            # Admin settings
│   ├── notices.php             # Notices & holidays
│   ├── tickets.php             # Support tickets
│   ├── reports.php             # Analytics & reports
│   └── ...
├── employee/
│   ├── index.php               # Employee dashboard
│   ├── projects.php            # My projects
│   ├── tasks.php               # My tasks
│   ├── daily-updates.php       # Daily updates
│   ├── attendance.php          # Attendance
│   ├── payroll.php             # My payroll
│   ├── profile.php             # My profile
│   ├── settings.php            # Settings
│   └── ...
├── client/
│   ├── index.php               # Client dashboard
│   ├── projects.php            # My projects
│   ├── invoices.php            # My invoices
│   ├── tickets.php             # My tickets
│   ├── profile.php             # Profile
│   ├── settings.php            # Settings
│   └── ...
├── api/
│   ├── auth.php                # Authentication
│   ├── projects.php            # Project API
│   ├── tasks.php               # Task API
│   ├── employees.php           # Employee API
│   ├── clients.php             # Client API
│   ├── payroll.php             # Payroll API
│   ├── attendance.php          # Attendance API
│   ├── tickets.php             # Ticket API
│   ├── password-reset.php      # Password reset API
│   └── ...
├── config/
│   ├── database.php            # Database connection
│   └── auth.php                # Authentication functions
├── includes/
│   ├── header.php              # Page header
│   ├── footer.php              # Page footer
│   ├── sidebar.php             # Navigation sidebar
│   ├── profile-content.php     # Profile template
│   ├── settings-content.php    # Settings template
│   ├── 403.php                 # Access denied page
│   └── email-templates/        # Email templates
├── assets/
│   └── js/
│       └── app.js              # Global JavaScript
├── database/
│   └── schema.sql              # Database schema
├── plan.md                      # Project plan
├── PROJECT_ANALYSIS.md          # Detailed analysis
├── IMPLEMENTATION_ROADMAP.md    # Development roadmap
└── README.md                    # This file
```

---

## 🔐 Security Features

- ✅ Bcrypt password hashing (PHP password_hash)
- ✅ Session-based authentication with PHP sessions
- ✅ SQL injection prevention (prepared statements)
- ✅ CSRF token considerations
- ✅ Role-based access control
- ✅ Password reset with token expiration
- ✅ Active/inactive user status
- ✅ Last login tracking

---

## 🤝 Contributing

This is an in-development project. For contributions:
1. Create feature branches
2. Follow the naming convention: `feature/module-name`
3. Test thoroughly before pull requests
4. Update documentation

---

## 📞 Support & Documentation

- **Project Analysis**: See `PROJECT_ANALYSIS.md`
- **Implementation Plan**: See `IMPLEMENTATION_ROADMAP.md`
- **Database Schema**: See `database/schema.sql`
- **API Endpoints**: Documentation in each api/*.php file

---

## 📝 License

MIT License - Feel free to use and modify

---

## 🎓 Version History

- **v1.0-Beta** (Feb 13, 2026) - Initial MVP with core features
  - Authentication & RBAC
  - Admin, Employee, Client dashboards
  - Project & task management
  - Payroll & attendance
  - Invoice & ticket management
  - Professional UI design

---

**Last Updated**: February 13, 2026  
**Project Status**: In Active Development  
**Next Phase**: Phase 2 - Enhancement Features
