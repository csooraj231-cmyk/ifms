# Attendance Bulk API Test

This document contains examples to test the HR/Admin bulk attendance API on a local IFMS installation.

Prerequisites:
- The web server running at `http://localhost/ifms` (adjust URL if different).
- `curl` installed (Windows 10+ includes `curl`) or PowerShell available.
- A valid admin or HR user account.

1) Login and save cookies (cURL)

```bash
curl -c cookiejar.txt -X POST "http://localhost/ifms/api/auth.php?action=login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"yourpassword"}'
```

2) Bulk-mark attendance for multiple employees (cURL)

```bash
curl -b cookiejar.txt -X POST "http://localhost/ifms/api/attendance.php?action=bulk_mark" \
  -H "Content-Type: application/json" \
  -d '{"employee_ids":[1,2,3], "date":"2026-02-17", "status":"present"}'
```

3) PowerShell equivalent (login + bulk)

```powershell
# Login and store session cookie
Invoke-RestMethod -Uri "http://localhost/ifms/api/auth.php?action=login" -Method Post -ContentType 'application/json' -Body (@{ email = 'admin@example.com'; password = 'yourpassword' } | ConvertTo-Json) -SessionVariable IFMSSession

# Bulk mark
Invoke-RestMethod -Uri "http://localhost/ifms/api/attendance.php?action=bulk_mark" -Method Post -ContentType 'application/json' -Body (@{ employee_ids = @(1,2,3); date = '2026-02-17'; status = 'present' } | ConvertTo-Json) -WebSession $IFMSSession
```

Notes:
- Adjust employee IDs, server URL and credentials as needed.
- The API uses session cookies; the `cookiejar.txt` or PowerShell session must be sent with the bulk request.
- On holidays or weekends, the default behavior is to mark `paid_leave` — confirm by sending `status: "paid_leave"`.
