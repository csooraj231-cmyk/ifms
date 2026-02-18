# PowerShell test runner for bulk attendance
# Usage: Open PowerShell and run: .\run_attendance_bulk_test.ps1 -BaseUrl 'http://localhost/ifms' -Email 'admin@example.com' -Password 'yourpassword' -EmployeeIds '1,2,3' -Date '2026-02-17' -Status 'present'
param(
    [string]$BaseUrl = 'http://localhost/ifms',
    [string]$Email = 'admin@example.com',
    [string]$Password = 'yourpassword',
    [string]$EmployeeIds = '1,2,3',
    [string]$Date = (Get-Date -Format yyyy-MM-dd),
    [string]$Status = 'present'
)

Write-Host "Logging in to $BaseUrl as $Email..."
$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$loginBody = @{ action = 'login'; email = $Email; password = $Password } | ConvertTo-Json
Invoke-RestMethod -Uri "$BaseUrl/api/auth.php?action=login" -Method Post -ContentType 'application/json' -Body $loginBody -WebSession $session -ErrorAction Stop | Out-Null
Write-Host 'Login successful.'

$ids = $EmployeeIds -split ',' | ForEach-Object { [int]$_.Trim() }
$body = @{ employee_ids = $ids; date = $Date; status = $Status } | ConvertTo-Json
Write-Host "Applying bulk attendance: $($body)"
$result = Invoke-RestMethod -Uri "$BaseUrl/api/attendance.php?action=bulk_mark" -Method Post -ContentType 'application/json' -Body $body -WebSession $session -ErrorAction Stop
Write-Host "Result:`n" ($result | ConvertTo-Json -Depth 5)
