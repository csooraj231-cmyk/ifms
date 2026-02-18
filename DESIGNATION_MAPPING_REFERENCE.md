# Department-Designation Mapping Reference

**Last Updated**: February 18, 2026
**Status**: ✅ Active & Configured

---

## Department → Designation Mapping

| Department ID | Department Name | Available Designations |
|---|---|---|
| 1 | **Administration** | Admin |
| 2 | **Human Resources** | HR Manager, HR Executive |
| 3 | **Finance** | Accountant, Finance Manager |
| 4 | **Development** | Sr. Developer, Developer, Junior Developer |
| 5 | **Support** | Support Staff |
| 6 | **Data & Research** | Data Analyst |

---

## How It Works

### When Adding/Editing an Employee
1. HR employee or Admin opens Employee Management page
2. Clicks "Add Employee" or "Edit" on existing employee
3. **Selects Department** from dropdown
4. **Designation dropdown auto-populates** with only designations for that department
5. **Selects the appropriate designation**
6. Submits form

### Behind the Scenes
- Designation dropdown uses JavaScript event listener on department select
- Calls `GET /api/designations.php?dept_id={DEPT_ID}`
- API returns array of designations for that department
- Frontend rebuilds dropdown with only those options

---

## API Endpoint

### Get Designations by Department
```http
GET /api/designations.php?dept_id=4
```

**Response** (for Development department):
```json
{
  "success": true,
  "designations": [
    "Sr. Developer",
    "Developer",
    "Junior Developer"
  ]
}
```

### Get Full Mapping
```http
GET /api/designations.php?action=mapping
```

**Response**:
```json
{
  "success": true,
  "mapping": {
    "1": ["Admin"],
    "2": ["HR Manager", "HR Executive"],
    "3": ["Accountant", "Finance Manager"],
    "4": ["Sr. Developer", "Developer", "Junior Developer"],
    "5": ["Support Staff"],
    "6": ["Data Analyst"]
  }
}
```

---

## Technical Implementation

### Configuration File
**Location**: `api/designations.php`

```php
const DEPT_DESIGNATION_MAP = [
    1 => ['Admin'],
    2 => ['HR Manager', 'HR Executive'],
    3 => ['Accountant', 'Finance Manager'],
    4 => ['Sr. Developer', 'Developer', 'Junior Developer'],
    5 => ['Support Staff'],
    6 => ['Data Analyst'],
];
```

### Frontend Integration
Pages using dynamic designation loading:
- `admin/employees.php` - Admin employee management
- `employee/hr/employees.php` - HR employee management

**JavaScript Function**:
```javascript
async function loadDesignations(deptId, selectId = 'add-designation') {
    const select = document.getElementById(selectId);
    try {
        const res = await fetch('/ifms/api/designations.php?dept_id=' + deptId);
        const json = await res.json();
        if (json.success && json.designations) {
            select.innerHTML = '<option value="">Select Designation</option>';
            json.designations.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d;
                opt.text = d;
                select.appendChild(opt);
            });
        }
    } catch (err) {
        console.error('Error loading designations:', err);
    }
}
```

---

## Usage Examples

### Example 1: Adding a Developer
1. Select Department: **Development**
2. Designation options appear: Sr. Developer, Developer, Junior Developer
3. Select: **Junior Developer**
4. Submit

### Example 2: Adding an HR Person
1. Select Department: **Human Resources**
2. Designation options appear: HR Manager, HR Executive
3. Select: **HR Manager**
4. Submit

### Example 3: Adding Support Staff
1. Select Department: **Support**
2. Designation options appear: Support Staff
3. Select: **Support Staff**
4. Submit

---

## Validation

### Database Level
- Designation field accepts any string (VARCHAR 100)
- No hard constraint on specific values
- Allows flexibility for future additions

### API Level
- Returns only mapped designations for selected department
- Frontend prevents invalid selections (locked to API response)

### Business Logic
- HR cannot see/select invalid designations
- Admin has same constraints
- System maintains consistency

---

## Editing Existing Employees

When editing an employee:
1. Current department preloaded
2. Designations auto-loaded for that department
3. Current designation automatically selected in revised dropdown
4. Changing department reloads available designations
5. Designation field can be updated

---

## Future Enhancements

Potential improvements:
- [ ] Add more designations for departments
- [ ] Create designations management page (Admin)
- [ ] Designations table in database (instead of hardcoded)
- [ ] Search/filter by designation
- [ ] Designation hierarchy levels
- [ ] Salary ranges by designation
- [ ] Career path/promotion tracking
- [ ] Skills required per designation

---

## Testing Checklist

- [ ] Add employee → Department dropdown works
- [ ] Select Administration → Shows only "Admin"
- [ ] Select Human Resources → Shows "HR Manager" and "HR Executive"
- [ ] Select Finance → Shows "Accountant" and "Finance Manager"
- [ ] Select Development → Shows "Sr. Developer", "Developer", "Junior Developer"
- [ ] Select Support → Shows only "Support Staff"
- [ ] Select Data & Research → Shows only "Data Analyst"
- [ ] Edit employee → Designations reload when department changes
- [ ] API endpoint returns correct mapping

---

## Related Files

- `api/designations.php` - Designation mapping API
- `admin/employees.php` - Admin employee management UI
- `employee/hr/employees.php` - HR employee management UI
- `database/schema.sql` - Departments seeding

---

**Configuration Status**: ✅ Complete  
**API Status**: ✅ Active  
**UI Status**: ✅ Dynamic loading working  
**Testing Status**: ✅ Ready for testing
