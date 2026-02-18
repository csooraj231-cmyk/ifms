# IFMS - Comprehensive Testing Checklist
## February 17, 2026

---

## 🧪 AUTOMATED TESTING

### Access the Testing Suite
```
URL: http://localhost/ifms/test_modules_comprehensive.php
```

Click through each test category to verify:
- ✅ Holidays Module (API & Page)
- ✅ Employee Management (Admin & HR)
- ✅ Designations API
- ✅ Requests System (API & Page)
- ✅ Authentication & Profile
- ✅ Database Integrity
- ✅ Access Control (Admin/Employee/Client)
- ✅ Sidebar Navigation

---

## 🔐 MANUAL TESTING BY ROLE

### ======== ADMIN ROLE ========

**Login as**: admin / (admin password)

#### 1. Dashboard Access
- [ ] Can access `/admin/index.php`
- [ ] Dashboard loads without errors
- [ ] See "Management" section in sidebar

#### 2. Holidays Management
- [ ] Click "Holidays" in sidebar
- [ ] View all holidays listed
- [ ] Click "Add Holiday" button
- [ ] Fill form: Name, Date, Type (National/Company)
- [ ] Click "Create Holiday"
- [ ] Holiday appears in list
- [ ] Click edit icon on holiday
- [ ] Update holiday details
- [ ] Click "Update Holiday"
- [ ] Changes appear in list
- [ ] Click delete icon
- [ ] Confirmation dialog appears
- [ ] Click confirm delete
- [ ] Holiday removed from list

#### 3. Employee Management
- [ ] Click "Employees" in sidebar
- [ ] View employee list
- [ ] Click "Add Employee" button
- [ ] Fill form with:
  - Employee Code (auto-generated or manual)
  - Full Name
  - Email
  - Phone
  - Department (select one)
  - **Verify: Designation dropdown populates based on department**
  - Select a designation from updated list
  - Salary
  - Salary Type
- [ ] Click "Create Employee"
- [ ] Employee appears in list
- [ ] Click edit icon on employee
- [ ] **Verify: Designation dropdown pre-populated AND updates when department changes**
- [ ] Modify details
- [ ] Click "Update Employee"
- [ ] Changes saved
- [ ] Click deactivate icon
- [ ] Confirmation appears
- [ ] Employee status changes to "Inactive"

#### 4. Clients Management
- [ ] Click "Clients" in sidebar
- [ ] View client list
- [ ] Click "Add Client" button
- [ ] Fill form with organization details
- [ ] Create new client
- [ ] List updates

#### 5. Projects Management
- [ ] Click "Projects" in sidebar
- [ ] View project list
- [ ] (Feature: Click on project → detail page - **NOT YET IMPLEMENTED**)

#### 6. Attendance Management
- [ ] Click "Attendance" in sidebar
- [ ] View attendance records
- [ ] Filters work (date, employee, status)
- [ ] Can mark attendance

#### 7. Payroll Management
- [ ] Click "Payroll" in sidebar
- [ ] View payroll list
- [ ] Filter by employee/month
- [ ] (Feature: Download PDF - **NOT YET IMPLEMENTED**)

#### 8. Sidebar Navigation
- [ ] All admin links visible
- [ ] No employee-specific links shown
- [ ] No client links shown
- [ ] No HR-specific links shown
- [ ] Links properly formatted and clickable

---

### ======== EMPLOYEE ROLE (Regular) ========

**Login as**: employee / (employee password)

#### 1. Dashboard Access
- [ ] Can access `/employee/index.php`
- [ ] Dashboard loads correctly
- [ ] See "General" and "Personal" sections

#### 2. Holidays Viewing
- [ ] Click "Holidays" in sidebar
- [ ] View all holidays
- [ ] Holidays grouped by year
- [ ] Shows date, type, days until/since
- [ ] Card layout is responsive

#### 3. Requests System
- [ ] Click "Requests" in sidebar
- [ ] View submitted requests (if any)
- [ ] Click "Submit Request" button
- [ ] **LEAVE REQUEST:**
  - [ ] Select "Leave" type
  - [ ] Date picker appears
  - [ ] Days field appears
  - [ ] Reason textarea appears
  - [ ] Fill all fields
  - [ ] Click "Submit Request"
  - [ ] Request appears in "Pending" status
- [ ] **SUPPORT REQUEST:**
  - [ ] Select "Support" type
  - [ ] Title field appears
  - [ ] Description appears
  - [ ] Fill all fields
  - [ ] Submit request
  - [ ] Request appears with Support badge (🆘)
- [ ] **GENERAL REQUEST:**
  - [ ] Select "General" type
  - [ ] Title field appears
  - [ ] Description appears
  - [ ] Submit request
  - [ ] Request appears with General badge (📝)
- [ ] Filter requests by type:
  - [ ] Click "All" → shows all requests
  - [ ] Click "Leave" → shows only leave requests
  - [ ] Click "Support" → shows only support requests
  - [ ] Click "General" → shows only general requests
- [ ] Request status colors:
  - [ ] Pending = yellow/orange
  - [ ] Approved = green
  - [ ] Rejected = red

#### 4. Profile
- [ ] Click "Profile" in sidebar
- [ ] View profile information
- [ ] **Email field is editable:**
  - [ ] Click in email field
  - [ ] Change email address
  - [ ] Click "Save Changes"
  - [ ] Verify success message
  - [ ] Refresh and confirm change persisted
- [ ] **Phone field is editable:**
  - [ ] Click in phone field
  - [ ] Change phone number
  - [ ] Click "Save Changes"
  - [ ] Verify success message
  - [ ] Refresh and confirm change persisted
- [ ] Other fields present: full name, department, designation

#### 5. Payroll
- [ ] Click "My Payslips" in sidebar
- [ ] View payslip list
- [ ] (Feature: Download PDF - **NOT YET IMPLEMENTED**)

#### 6. Sidebar Navigation
- [ ] See "General" section with Holidays + Requests
- [ ] See "Personal" section with Profile + Requests + Payslips
- [ ] Do NOT see Admin links
- [ ] Do NOT see Client links
- [ ] Do NOT see HR links (unless HR employee)

---

### ======== HR EMPLOYEE ROLE ========

**Login as**: hr_employee / (hr password)
*(Must be employee in HR department)*

#### 1. HR Dashboard Access
- [ ] Can access employee dashboard
- [ ] See all normal employee sections
- [ ] Additional: See "HR Operations" section in sidebar

#### 2. Employee Management (HR Version)
- [ ] Click "HR Employees" in sidebar (under HR Operations)
- [ ] View employee list (all employees except admins)
- [ ] **Can ADD employees:**
  - [ ] Click "Add Employee"
  - [ ] Fill form with employee details
  - [ ] Select department (triggers designation update)
  - [ ] **Verify designations populate based on dept**
  - [ ] Select designation from list
  - [ ] Submit form
  - [ ] Employee added to list
- [ ] **Can EDIT employees (except admins):**
  - [ ] Click edit icon on regular employee
  - [ ] Form pre-populates
  - [ ] Can change department
  - [ ] Designations update in realtime as dept changes
  - [ ] Can change designation
  - [ ] Click "Update Employee"
  - [ ] Changes saved
- [ ] **Cannot manage admins:**
  - [ ] Admin users do NOT appear in employee list
  - [ ] Or if they do, cannot edit them
  - [ ] Click edit on admin → error or disabled
  - [ ] Verify API blocks HR from modifying admins

#### 3. Attendance Management (HR Version)
- [ ] Click "Attendance" in sidebar (under HR Operations)
- [ ] View attendance for all employees
- [ ] Can mark attendance for multiple employees
- [ ] **Verify no SQL errors** (previously had backslash escaping bug)
- [ ] Page loads quickly

#### 4. Finance Pages (HR + Finance Employee)
**If same employee is in both HR and Finance roles:**
- [ ] See "Finance Operations" section
- [ ] Click "Payroll" → view payroll records
- [ ] Click "Invoices" → view invoices
- [ ] **Verify no SQL errors** (previously had table reference bug)
- [ ] Data loads correctly

#### 5. Sidebar Navigation
- [ ] See "HR Operations" section
  - [ ] Employees link
  - [ ] Attendance link
- [ ] See all normal employee sections
- [ ] See "Finance Operations" if in Finance dept
- [ ] Do NOT see Admin links

---

### ======== FINANCE EMPLOYEE ROLE ========

**Login as**: finance_employee / (finance password)
*(Must be employee in Finance department)*

#### 1. Finance Dashboard
- [ ] Can access employee dashboard
- [ ] See "Finance Operations" section

#### 2. Payroll Management
- [ ] Click "Payroll" under Finance Operations
- [ ] View payroll records
- [ ] Filters work (employee, month, status)
- [ ] (Feature: Download PDF - **NOT YET IMPLEMENTED**)

#### 3. Invoices Management
- [ ] Click "Invoices" under Finance Operations
- [ ] **Verify page loads without SQL error** (previously broken)
- [ ] View invoice list
- [ ] Shows: Invoice #, Organization/Client, Amount, Date
- [ ] Can filter by organization/date
- [ ] (Feature: Download PDF - **NOT YET IMPLEMENTED**)

#### 4. Sidebar Navigation
- [ ] See "Finance Operations" section
- [ ] Payroll + Invoices links visible
- [ ] Do NOT see Admin or HR-specific links

---

### ======== DEVELOPER ROLE ========

**Login as**: developer / (developer password)
*(Must be employee with Developer designation)*

#### 1. Dashboard Access
- [ ] Can access employee dashboard

#### 2. Development Section
- [ ] See "Development" section in sidebar
- [ ] Links visible:
  - [ ] Projects
  - [ ] Tasks
  - [ ] Daily Updates
- [ ] (Feature: These pages not yet fully implemented)

#### 3. Sidebar Navigation
- [ ] Do NOT see HR or Finance sections (unless in those depts)
- [ ] Development section prominent

---

### ======== SENIOR DEVELOPER ROLE ========

**Login as**: sr_developer / (sr_developer password)
*(Must be employee with Senior Developer designation)*

#### 1. Dashboard Access
- [ ] Can access employee dashboard

#### 2. Development Section
- [ ] See "Development" section with Projects, Tasks, Daily Updates

#### 3. Team Management Section
- [ ] See "Team Management" section in sidebar
- [ ] Links visible:
  - [ ] Team
  - [ ] Milestones
- [ ] (Feature: Not yet fully implemented)

#### 4. Sidebar Navigation
- [ ] Additional management options beyond regular developer

---

### ======== CLIENT ROLE ========

**Login as**: client / (client password)

#### 1. Dashboard Access
- [ ] Can access `/client/index.php`
- [ ] Dashboard loads without errors
- [ ] See company name/client organization

#### 2. Projects Viewing
- [ ] Click "My Projects" in sidebar
- [ ] View assigned projects
- [ ] Project list shows: project name, status, start date
- [ ] (Feature: Click project → detail page - **NOT YET IMPLEMENTED**)

#### 3. Support Tickets
- [ ] Click "Support Tickets" in sidebar
- [ ] View submitted tickets
- [ ] Can submit new ticket:
  - [ ] Click "Submit Ticket"
  - [ ] Fill title, description, priority
  - [ ] Submit
  - [ ] Ticket appears in list

#### 4. Billing & Invoices
- [ ] Click "Billing & Invoices" in sidebar
- [ ] View invoices for client
- [ ] Shows invoice #, amount, date, status
- [ ] (Feature: Download PDF - **NOT YET IMPLEMENTED**)

#### 5. Sidebar Navigation
- [ ] Only see Client dashboard, Projects, Tickets, Billing
- [ ] Do NOT see Admin, Employee, or HR sections
- [ ] All links functional

---

## 🔄 CROSS-ROLE VERIFICATION

### User Access Restrictions
- [ ] Admin cannot see employee-only pages
- [ ] Employee cannot access admin pages
- [ ] Client cannot access internal employee pages
- [ ] HR can only see employee management (not admins)
- [ ] Finance can access finance pages only

### Database Consistency
- [ ] Users exist in `users` table
- [ ] Employees linked to users via `user_id`
- [ ] Employees linked to departments
- [ ] Departments have proper IDs (1-6)
- [ ] No orphaned employee records
- [ ] Holidays table populated with sample data
- [ ] Requests table has proper type values

### API Endpoints
**Test with curl or Postman:**

```bash
# Get all holidays
curl http://localhost/ifms/api/holidays.php?action=list

# Get designations for department 3 (Development)
curl http://localhost/ifms/api/designations.php?dept_id=3

# Get all employees
curl http://localhost/ifms/api/employees.php?action=list

# Get requests for logged-in user
curl http://localhost/ifms/api/requests.php?action=list
```

Each should return valid JSON with `success: true`

---

## 🎨 UI/UX TESTING

### Responsive Design
- [ ] Desktop (1920x1080): All elements visible and aligned
- [ ] Tablet (768x1024): Sidebar collapses to hamburger menu
- [ ] Mobile (375x667): Touch-friendly, readable text

### Form Validation
- [ ] Required fields show error if empty
- [ ] Email validation works (invalid emails rejected)
- [ ] Phone validation works (if configured)
- [ ] Date pickers work and limit to valid dates
- [ ] Success toasts appear after form submission
- [ ] Error toasts appear if submission fails

### Navigation
- [ ] Active links highlighted in sidebar
- [ ] Breadcrumbs work (if present)
- [ ] Back buttons functional
- [ ] No broken links
- [ ] All pages load in < 2 seconds

### Modals
- [ ] Modal opens on button click
- [ ] Modal closes on X button
- [ ] Modal closes on background click
- [ ] Form fields clear when modal closed
- [ ] Can submit form without closing
- [ ] Scrollable if content overflows

---

## ⚠️ KNOWN ISSUES TO VERIFY FIXED

- [ ] **HR Attendance SQL Error**: ✅ FIXED
  - [ ] HR attendance page loads without syntax error
  - [ ] Data displays correctly
  
- [ ] **Finance Invoices SQL Error**: ✅ FIXED
  - [ ] Finance invoices page loads
  - [ ] Shows organization names (not broken table references)
  
- [ ] **Sidebar PHP Syntax Error**: ✅ FIXED
  - [ ] Website loads (no parse errors)
  - [ ] All navigation menus render
  - [ ] Role-based menu sections visible

---

## 📊 TEST SUMMARY TEMPLATE

Use this to document your testing:

```
Date: [Date]
Tester: [Name]
IFMS Version: February 17, 2026

RESULTS:
========

Critical Issues (Block Release):
- [None / List any]

Major Issues (Should Fix):
- [None / List any]

Minor Issues (Nice to Have):
- [None / List any]

Features Fully Working:
✅ Holidays Management
✅ Employee Management (Admin & HR)
✅ Dynamic Designations
✅ Enhanced Requests (Leave/Support/General)
✅ Profile Email/Phone Editing
✅ HR Attendance (Fixed SQL)
✅ Finance Invoices (Fixed SQL)
✅ Sidebar Navigation

Features Partially Working:
⚠️ [List any]

Features Not Yet Implemented:
❌ PDF Exports
❌ Project Detail Pages
❌ Developer Projects/Tasks Pages
❌ Support Staff Module
❌ Client Payment Workflow
❌ Color Scheme Update

Overall Status: [✅ READY FOR RELEASE / ⚠️ NEEDS FIXES / ❌ NOT READY]
```

---

## 🚀 NEXT STEPS

After testing completes:

1. **Document any bugs found**
2. **Fix critical issues immediately**
3. **Prioritize major issues**
4. **Plan next features:**
   - PDF exports (high priority)
   - Project detail pages
   - Developer module
   - Support staff module

---

**Testing Complete!** ✨

Generate test report and share results for team review.
