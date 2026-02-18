<?php
/**
 * IFMS API - Payroll Generation
 */
require_once __DIR__ . '/../config/auth.php';
requireAPI();

$data = getPostData();
$action = $data['action'] ?? $_GET['action'] ?? '';
$db = getDB();

switch ($action) {
    case 'generate':
        // Finance and admin can generate payroll
        if (getUserRole() !== 'admin' && !isFinanceEmployee()) {
            jsonResponse(['error' => 'Unauthorized - Finance access required'], 403);
        }

        $month = intval($data['month'] ?? date('m'));
        $year = intval($data['year'] ?? date('Y'));

        // Get all active employees
        $employees = $db->query("SELECT e.* FROM employees e WHERE e.is_active = 1")->fetchAll();

        // Count working days in the month (exclude weekends)
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $workingDays = 0;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dayOfWeek = date('N', mktime(0, 0, 0, $month, $d, $year));
            if ($dayOfWeek < 6)
                $workingDays++; // Mon-Fri
        }

        // Subtract holidays
        $holidayCount = $db->query("SELECT COUNT(*) FROM holidays WHERE MONTH(date) = {$month} AND YEAR(date) = {$year}")->fetchColumn();
        $workingDays = max(1, $workingDays - $holidayCount);

        $count = 0;
        foreach ($employees as $emp) {
            // Check if payroll already generated
            $exists = $db->query("SELECT id FROM payroll WHERE employee_id = {$emp['id']} AND month = {$month} AND year = {$year}")->fetch();
            if ($exists)
                continue;

            // Count attendance
            $att = $db->query("SELECT status, COUNT(*) as cnt FROM attendance WHERE employee_id = {$emp['id']} AND MONTH(date) = {$month} AND YEAR(date) = {$year} GROUP BY status")->fetchAll();
            $attMap = array_column($att, 'cnt', 'status');

            $present = ($attMap['present'] ?? 0) + ($attMap['late'] ?? 0);
            $halfDay = $attMap['half_day'] ?? 0;
            $absent = $attMap['absent'] ?? 0;
            $leave = $attMap['on_leave'] ?? 0;

            $effectiveDays = $present + ($halfDay * 0.5);
            $payRatio = $workingDays > 0 ? $effectiveDays / $workingDays : 1;

            // Calculate base monthly salary based on salary_type
            $salaryType = $emp['salary_type'] ?? 'monthly';
            $monthlyBase = $emp['base_salary'];
            
            if ($salaryType === 'annual') {
                // For annual salary, divide by 12 to get monthly equivalent
                $monthlyBase = $emp['base_salary'] / 12;
            } elseif ($salaryType === 'hourly') {
                // For hourly, multiply rate by hours worked
                // For now, use working days * 8 hours/day as estimate
                $monthlyBase = $emp['base_salary'] * $effectiveDays * 8;
            }
            // For 'monthly', use base_salary as-is

            // Calculate salary
            $grossSalary = $monthlyBase + $emp['hra'] + $emp['da'] + $emp['special_allowance'];
            $proRatedGross = round($grossSalary * $payRatio);

            // Standard deductions (12% PF, 2% PT)
            $pf = round($monthlyBase * 0.12 * $payRatio);
            $pt = 200; // Professional tax
            $totalDeductions = $pf + $pt;

            $netSalary = $proRatedGross - $totalDeductions;

            $stmt = $db->prepare("INSERT INTO payroll (employee_id, month, year, total_working_days, days_present, days_absent, days_half, days_leave, base_salary, hra, da, special_allowance, gross_salary, pf_deduction, tax_deduction, total_deductions, net_salary, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'generated')");
            $stmt->execute([
                $emp['id'], $month, $year, $workingDays,
                $present, $absent, $halfDay, $leave,
                round($monthlyBase * $payRatio),
                round($emp['hra'] * $payRatio),
                round($emp['da'] * $payRatio),
                round($emp['special_allowance'] * $payRatio),
                $proRatedGross, $pf, $pt, $totalDeductions, $netSalary
            ]);
            $count++;
        }

        jsonResponse(['success' => true, 'message' => "Payroll generated for {$count} employees", 'count' => $count]);
        break;

    case 'download':
        // Allow employee to download own payslip, finance/admin to download any, HR to download any
        requireAPI();
        $payrollId = intval($data['id'] ?? $_GET['id'] ?? 0);
        if (!$payrollId) jsonResponse(['error' => 'Payroll ID is required'], 400);

        $db = getDB();
        $pay = $db->query("SELECT p.*, e.id as emp_id, u.full_name, u.email FROM payroll p JOIN employees e ON p.employee_id = e.id JOIN users u ON e.user_id = u.id WHERE p.id = {$payrollId}")->fetch();
        if (!$pay) jsonResponse(['error' => 'Payslip not found'], 404);

        $current = getCurrentUser();
        $allowed = false;
        if (getUserRole() === 'admin' || isFinanceEmployee() || isHREmployee()) $allowed = true;
        if ($current && isset($current['employee_id']) && $current['employee_id'] == $pay['employee_id']) $allowed = true;
        if (!$allowed) jsonResponse(['error' => 'Unauthorized'], 403);

        // If mPDF is available, render as PDF and force download
        if (class_exists('\\Mpdf\\Mpdf')) {
            $payslip = [
                'employee' => ['full_name' => $pay['full_name'], 'employee_code' => $pay['employee_id']],
                'month' => $pay['year'] . '-' . str_pad($pay['month'], 2, '0', STR_PAD_LEFT),
                'basic' => $pay['base_salary'] ?? $pay['gross_salary'],
                'deductions' => $pay['total_deductions'],
                'net' => $pay['net_salary'],
            ];

            // Render template
            ob_start();
            include __DIR__ . '/../templates/pdf_payslip.php';
            $html = ob_get_clean();

            $mpdf = new \Mpdf\Mpdf();
            $filename = 'payslip-' . $pay['year'] . '-' . str_pad($pay['month'],2,'0',STR_PAD_LEFT) . '-' . preg_replace('/[^A-Za-z0-9_-]/','', strtolower($pay['full_name'])) . '.pdf';
            $mpdf->WriteHTML($html);
            $mpdf->Output($filename, 'D');
            exit;
        }

        // Fallback: Build simple printable HTML payslip and return as downloadable file
        $html = '<!doctype html><html><head><meta charset="utf-8"><title>Payslip</title><style>body{font-family:Arial,Helvetica,sans-serif;padding:20px} .h{font-size:18px;font-weight:700;margin-bottom:6px} .row{display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #eee}</style></head><body>';
        $html .= "<div class=\"h\">Payslip - " . date('F Y', mktime(0,0,0,$pay['month'],1,$pay['year'])) . "</div>";
        $html .= "<div class=\"row\"><div><strong>Employee</strong><div>{$pay['full_name']}</div></div><div><strong>Employee ID</strong><div>{$pay['employee_id']}</div></div></div>";
        $html .= "<div class=\"row\"><div><strong>Gross Salary</strong></div><div>₹" . number_format($pay['gross_salary']) . "</div></div>";
        $html .= "<div class=\"row\"><div><strong>Total Deductions</strong></div><div>₹" . number_format($pay['total_deductions']) . "</div></div>";
        $html .= "<div class=\"row\" style=\"font-weight:700;\"><div>Net Salary</div><div>₹" . number_format($pay['net_salary']) . "</div></div>";
        $html .= "<div style=\"margin-top:24px;font-size:12px;color:#666\">Generated on: " . date('Y-m-d H:i') . "</div>";
        $html .= '</body></html>';

        header('Content-Type: text/html');
        $filename = 'payslip-' . $pay['year'] . '-' . str_pad($pay['month'],2,'0',STR_PAD_LEFT) . '-' . preg_replace('/[^A-Za-z0-9_-]/','', strtolower($pay['full_name'])) . '.html';
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $html;
        exit;
        break;

    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}
