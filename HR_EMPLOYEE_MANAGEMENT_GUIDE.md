# HR Employee Management - Full Feature Guide

## Overview
HR employees now have full management capability over system employees with complete CRUD operations (Create, Read, Update, Delete).

## Features Implemented

### 1. **View/List All Employees**
- View comprehensive employee database
- See: Full Name, Employee Code, Department, Designation, Base Salary, Active Status
- Real-time search by name or employee code
- Filter by Department 
- Filter by Status (Active/Inactive)

**Location**: `employee/hr/employees.php`
**Database query**: Joins employees, users, departments tables for complete info

---

### 2. **Add New Employee**
Complete form with validation:
- **Full Name** (required)
- **Email Address** (required, must be unique)
- **Temporary Password** (required, min 6 chars)
- **Phone Number** (optional)
- **Department** (required dropdown)
- **Designation** (dynamically loaded based on department)
- **Base Salary** (required, numeric)

**Process**:
1. Click "Add Employee" button
2. Fill all required fields
3. Department dropdown triggers dynamic designation loading via API
4. Submit creates both user and employee records in single transaction
5. Auto-generates Employee Code (EMP-TIMESTAMP format)
6. New employee gets role 'employee' automatically

**API Endpoint**: `POST /api/employees.php`
- Action: `create`
- Creates: User account + Employee record (atomic transaction)

---

### 3. **Edit/Update Employee**
Modify existing employee details:
- Full Name
- Email Address (uniqueness checked if changed)
- Phone Number
- Department (changes trigger re-load of available designations)
- Designation
- Base Salary
- Active Status (toggle)

**Process**:
1. Hover over employee row → Edit button (pencil icon)
2. Form loads with current employee data
3. Department changes dynamically load new designation options
4. Submit updates both users and employees tables
5. Confirmation toast shown on success

**API Endpoint**: `POST /api/employees.php`
- Action: `update`
- Updates: User + Employee records (atomic transaction)
- Validation: HR cannot edit administrators

---

### 4. **Deactivate Employee (Soft Delete)**
Safely disable employee without losing data:
- Sets `is_active = 0` on both user and employee records
- Employee becomes inactive but all data preserved
- Inactive employees shown in list with red "Inactive" badge
- Can be reactivated via edit (change is_active back to 1)

**Process**:
1. Hover over employee row → Delete button (trash icon)
2. Confirmation modal appears with employee name
3. Click "Delete Employee" to confirm
4. Employee deactivated, page refreshes
5. Inactive employees still visible in list (filter by status to see)

**API Endpoint**: `POST /api/employees.php`
- Action: `deactivate`
- Updates: Sets `is_active = 0` on both tables

---

### 5. **Hard Delete (Admin Only)**
Permanent removal from database:
- Only available to Admin users (not HR)
- Requires special confirmation
- Cascades delete all related records:
  - Task assignments
  - Daily updates
  - Attendance records
  - Payroll records
  - Project team memberships
  - Employee record
  - User account
- Cannot delete administrator accounts

**API Endpoint**: `POST /api/employees.php`
- Action: `delete`
- Role: Admin only
- Transactional with proper cascade deletion

---

## Access Control

### HR Employees Can:
✅ View all employees
✅ Add new employees
✅ Edit employee details (name, email, phone, dept, designation, salary)
✅ Deactivate employees (soft delete)
✅ Filter and search employees
✅ Cannot edit or delete administrator accounts

### Admin Can:
✅ All HR permissions PLUS
✅ Hard delete employees (permanent removal)
✅ Manage administrator accounts
✅ Full system control

### Other Roles:
❌ Cannot access employee management (redirected to dashboard)

---

## Database Schema

### employees table
```sql
id (PK)
user_id (FK → users.id)
employee_code (unique)
department_id (FK → departments.id)
designation
senior_developer_id (FK → employees.id, nullable)
date_of_joining
base_salary
hra
da
special_allowance
pf_deduction
tax_deduction
other_deductions
is_active (soft delete flag)
```

### users table (linked)
```sql
id (PK)
email (unique)
password (hashed)
role ('employee', 'admin', 'client')
full_name
phone
is_active (soft delete flag)
```

---

## API Endpoints

### 1. Create Employee
```http
POST /api/employees.php
Content-Type: application/json

{
  "action": "create",
  "full_name": "John Doe",
  "email": "john@company.com",
  "password": "temppass123",
  "phone": "9876543210",
  "department_id": 1,
  "designation": "Senior Developer",
  "base_salary": 50000,
  "hra": 5000,
  "salary_type": "monthly"
}
```

### 2. Get Employee
```http
GET /api/employees.php?action=get&id=5
```

### 3. Update Employee
```http
POST /api/employees.php
Content-Type: application/json

{
  "action": "update",
  "employee_id": 5,
  "full_name": "John Updated",
  "email": "john.updated@company.com",
  "phone": "9876543211",
  "department_id": 2,
  "designation": "Lead Developer",
  "base_salary": 60000,
  "is_active": 1
}
```

### 4. Deactivate Employee
```http
POST /api/employees.php
Content-Type: application/json

{
  "action": "deactivate",
  "employee_id": 5
}
```

### 5. Hard Delete Employee (Admin only)
```http
POST /api/employees.php
Content-Type: application/json

{
  "action": "delete",
  "employee_id": 5
}
```

### 6. List Employees
```http
GET /api/employees.php?action=list
```

---

## Testing Workflow

### Test 1: Add Employee
1. Login as HR employee
2. Navigate to Employee Management (sidebar → HR → Employees)
3. Click "Add Employee"
4. Fill form:
   - Name: Test Employee
   - Email: test@company.com
   - Password: testpass123
   - Phone: 9999999999
   - Department: Development
   - Designation: Junior Developer
   - Salary: 30000
5. Submit
6. ✅ Verify: Employee appears in list

### Test 2: Edit Employee
1. Find newly created employee in list
2. Hover → Click Edit (pencil icon)
3. Change:
   - Name: Test Employee Updated
   - Salary: 35000
4. Submit
5. ✅ Verify: Changes reflected in list

### Test 3: Search/Filter
1. Type employee name in search box
2. ✅ Verify: Table updates in real-time
3. Select Department filter
4. ✅ Verify: Only employees from selected dept shown
5. Select Status filter (Inactive)
6. ✅ Verify: Only inactive employees shown

### Test 4: Deactivate Employee
1. Find an active employee
2. Hover → Click Delete (trash icon)
3. Confirmation modal appears
4. Click "Delete Employee"
5. ✅ Verify: Employee now shows "Inactive" status

### Test 5: Admin Hard Delete
1. Login as Admin
2. Go to Admin → Employees
3. Click deactivate on any employee (not admin)
4. ✅ Verify: Employee deactivated
5. (Optional) Verify in database that all related records removed

### Test 6: Access Control
1. Logout and login as regular employee
2. Try to navigate to `/employee/hr/employees.php`
3. ✅ Verify: Access denied, redirected to dashboard

---

## Error Handling

### Field Validation
- Email must be unique (checked in database)
- Phone number format validated client-side
- Password minimum 6 characters
- Base salary must be numeric
- Required fields must be filled

### Database Errors
- Duplicate email prevented (UNIQUE constraint)
- Transaction rollback on any failure
- Proper error messages returned to frontend

### Authorization Errors
- HR cannot manage admins
- HR can only deactivate (not hard delete)
- Non-admin users blocked from API
- Proper 403 Forbidden responses

---

## UI/UX Features

### Visual Feedback
- Toast notifications for success/error
- Loading states during API calls
- Hover effects on action buttons
- Real-time table filtering
- Modal confirmations for destructive actions

### Responsive Design
- Works on desktop/tablet/mobile
- Collapsible filters on small screens
- Scrollable table on mobile
- Touch-friendly buttons and modals

### Accessibility
- Proper form labels
- Keyboard navigation support
- Clear error messages
- Semantic HTML structure

---

## Related Documentation

- **API Reference**: See `API_RBAC_PATTERNS.php`
- **Database Schema**: See `database/schema.sql`
- **Auth System**: See `config/auth.php` for role checks
- **Sidebar**: See `includes/sidebar.php` (HR links)

---

## Common Issues & Solutions

### Issue: Designation not loading after department selection
**Solution**: Wait 300ms for API call to complete, then dropdown auto-fills

### Issue: Cannot delete admin account
**Solution**: This is intentional for security. Only users with 'employee' role can be deleted/deactivated.

### Issue: Email already exists error
**Solution**: Use unique email - check existing employees or add suffix (test+1@company.com)

### Issue: Transaction failed error
**Solution**: Check database connection and permissions. Ensure all foreign key constraints met.

---

## Future Enhancements

- Bulk import employees (CSV)
- Export employee list (PDF/Excel)
- Employee documents upload
- Salary history tracking
- Performance reviews
- Leave management integration
- Email notifications on account creation
- Two-factor authentication for admin operations

---

**Last Updated**: February 18, 2026  
**Status**: ✅ Complete & Tested  
**Roles**: HR Employee, Admin
