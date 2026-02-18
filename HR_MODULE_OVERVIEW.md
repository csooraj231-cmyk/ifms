# HR Section - Complete Feature Overview

**Last Updated**: February 18, 2026

---

## Module Location
- **Route**: `employee/hr/`
- **Access**: HR department employees + Admins
- **Prerequisites**: User must have `isHREmployee()` role or be admin

---

## Available Pages & Features

### 1. **Employee Management** (`employees.php`)
**Full CRUD capability** for managing workforce

#### View & List
- ✅ View all employees with detailed info
- ✅ Search by name or employee code (real-time)
- ✅ Filter by department
- ✅ Filter by active/inactive status
- ✅ Display: Name, Code, Dept, Designation, Salary, Status

#### Add New Employee
- ✅ Create user account + employee record (atomic transaction)
- ✅ Auto-generate employee code
- ✅ Dynamic designation loading based on department
- ✅ Set temporary password (auto-prompt to change on first login)
- ✅ Configure: Name, Email, Phone, Dept, Designation, Salary

#### Edit/Update
- ✅ Modify any employee detail
- ✅ Email uniqueness validation
- ✅ Department changes auto-reload designation options
- ✅ Toggle active/inactive status
- ✅ Atomic database updates

#### Delete/Deactivate
- ✅ Soft delete (deactivate) - preserves all data
- ✅ Admin-only hard delete (permanent removal)
- ✅ Confirmation modal prevents accidental deletion
- ✅ Cascade delete of related records (for hard delete)

**Permissions**:
- HR: Can add, edit, deactivate employees (except admins)
- Admin: Can add, edit, deactivate, hard delete all employees
- Others: No access

**API**: `POST/GET /api/employees.php`
- Actions: `create`, `get`, `list`, `update`, `deactivate`, `delete`

---

### 2. **Attendance Management** (`attendance.php`)
**Track and manage employee attendance**

#### Features
- ✅ View employee attendance records
- ✅ View by date ranges
- ✅ See check-in/check-out times
- ✅ View attendance status (Present/Absent/Half-day/Leave/Holiday)
- ✅ Add/Edit attendance records
- ✅ Track work hours

#### Data Points
- Employee name & code
- Date
- Check-in time
- Check-out time
- Work hours calculated
- Status flag
- Notes/remarks

**Permissions**: HR can view and manage all employee attendance

**Database**: `attendance` table
- Fixed SQL query bugs (was escaping multi-line string incorrectly)

---

### 3. **Payroll Management** (`payroll.php`) 
**Finance operations within HR scope**

#### Features
- ✅ View payroll records
- ✅ See salary breakdowns (gross, deductions, net)
- ✅ View payment status (draft/approved/paid)
- ✅ Download payslips as PDF
- ✅ Manage payroll entries

#### Data Points
- Employee name
- Month/Year
- Base salary + allowances
- Deductions (PF, Tax, etc.)
- Gross salary
- Net salary
- Payment date

**Permissions**: HR + Finance employees can access

**Database**: `payroll` table (links to employees)

---

## Access & Navigation

### Sidebar Links
From the main sidebar (when logged in as HR):
```
HR Module
├── Employee Management
├── Attendance
└── Payroll
```

### Direct URLs
- Employee Management: `/employee/hr/employees.php`
- Attendance: `/employee/hr/attendance.php`
- Payroll: `/employee/hr/payroll.php`

---

## Technical Stack

### Frontend
- Tailwind CSS (responsive styling)
- Vanilla JavaScript (modal, filters, form handling)
- Fetch API (async AJAX calls)
- Toast notifications (success/error feedback)

### Backend
- PHP (procedural, PDO for DB)
- MySQL (InnoDB tables with constraints)
- Atomic transactions (BEGIN/COMMIT/ROLLBACK)
- Prepared statements (SQL injection prevention)

### Authentication
- Session-based auth (`config/auth.php`)
- Role helpers: `isHREmployee()`, `isAdmin()`
- Route guards: `requireHRAccess()`, `requireRole('admin')`

---

## Database Tables

### Primary Tables
```
employees (id, user_id, employee_code, department_id, designation, ...)
users (id, email, password, role, full_name, phone, ...)
departments (id, name, slug, ...)
attendance (id, employee_id, date, check_in, check_out, ...)
payroll (id, employee_id, month, year, base_salary, ...)
```

### Relationships
- employees.user_id → users.id (1:1)
- employees.department_id → departments.id (N:1)
- attendance.employee_id → employees.id (N:1)
- payroll.employee_id → employees.id (N:1)

---

## API Endpoints

### Employee Management
| Method | Endpoint | Action | Auth |
|--------|----------|--------|------|
| POST | `/api/employees.php` | `create` | HR, Admin |
| GET | `/api/employees.php` | `get` | HR, Admin |
| GET | `/api/employees.php` | `list` | HR, Admin |
| POST | `/api/employees.php` | `update` | HR, Admin |
| POST | `/api/employees.php` | `deactivate` | HR, Admin |
| POST | `/api/employees.php` | `delete` | Admin only |

### Attendance
| Method | Endpoint | Action | Auth |
|--------|----------|--------|------|
| GET | `/api/attendance.php` | `list` | HR, Finance |
| POST | `/api/attendance.php` | `add` | HR |
| POST | `/api/attendance.php` | `update` | HR |

### Payroll
| Method | Endpoint | Action | Auth |
|--------|----------|--------|------|
| GET | `/api/payroll.php` | `list` | HR, Finance |
| POST | `/api/payroll.php` | `generate` | Finance, Admin |
| POST | `/api/payroll.php` | `download` | HR, Finance |

---

## Common Workflows

### Workflow 1: Onboard New Employee
1. Login as HR employee
2. Go to Employee Management
3. Click "Add Employee"
4. Fill form (name, email, password, dept, designation, salary)
5. Submit
6. System creates user account + employee record
7. New employee receives login credentials
8. Employee can now login and update profile

### Workflow 2: Update Employee Details
1. Open Employee Management
2. Search for employee
3. Hover row → Click Edit
4. Modify fields (name, email, salary, etc.)
5. Submit
6. Changes immediately reflected in system

### Workflow 3: Deactivate Employee Leaving
1. Find employee in list
2. Hover → Click Delete
3. Confirm in modal
4. Employee marked inactive
5. Cannot login, but data preserved
6. Can be reactivated if needed

### Workflow 4: Track Attendance
1. Go to Attendance page
2. View all attendance records
3. Filter by date/employee
4. Add/Edit records as needed
5. System calculates work hours automatically

---

## Features Checklist

### ✅ Completed Features
- [x] Employee CRUD (Create, Read, Update, Delete)
- [x] Soft delete (deactivate) with data preservation
- [x] Hard delete with cascade (admin only)
- [x] Dynamic designation loading by department
- [x] Email uniqueness validation
- [x] Real-time search and filtering
- [x] Responsive UI design
- [x] Transaction safety (atomic operations)
- [x] Proper access control (role-based)
- [x] Attendance tracking
- [x] Payroll management
- [x] PDF downloads (payslips)

### 🔜 Potential Future Enhancements
- [ ] Bulk import employees (CSV)
- [ ] Export employee list (Excel/PDF)
- [ ] Employee document uploads
- [ ] Salary history timeline
- [ ] Performance reviews
- [ ] Leave request integration
- [ ] Email notifications
- [ ] Two-factor authentication
- [ ] Audit logs for changes
- [ ] Department hierarchy management
- [ ] Employee skills/certifications
- [ ] Training records

---

## Error Handling

### Common Errors & Solutions

| Error | Cause | Solution |
|-------|-------|----------|
| "Email already in use" | Duplicate email | Use unique email address |
| "Employee not found" | Invalid ID | Refresh page, try again |
| "Database connection failed" | DB down | Check server status |
| "Unauthorized access" | Wrong role | Login as HR or Admin |
| "Transaction failed" | FK constraint | Ensure related records exist |

---

## Security Measures

- ✅ Prepared statements (prevent SQL injection)
- ✅ Password hashing (bcrypt)
- ✅ Role-based access control
- ✅ Session validation
- ✅ Atomic transactions (data consistency)
- ✅ Email uniqueness constraints
- ✅ Admin action audit trail potential
- ✅ Cannot delete admin accounts (protection)

---

## Performance Tips

- Search/filter happens on client-side (no DB query)
- Use department filter to reduce visible rows
- Sort by status to find inactive employees quickly
- Bulk operations planned for future

---

## Related Modules

- **Admin Employees**: `admin/employees.php` (similar functionality, full access)
- **API Layer**: `api/employees.php` (backend logic)
- **Auth System**: `config/auth.php` (role validation)
- **Designations**: `api/designations.php` (dynamic loading)

---

**Status**: ✅ Fully Implemented & Tested  
**Version**: 1.0  
**Database**: MySQL InnoDB  
**PHP Version**: 7.4+
