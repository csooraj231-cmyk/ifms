<?php
/**
 * IFMS API - Attendance
 */
require_once __DIR__ . '/../config/auth.php';
requireAPI();

$data = getPostData();
$action = $data['action'] ?? $_GET['action'] ?? '';
$db = getDB();

switch ($action) {
    case 'mark':
        // HR and admin can mark attendance
        if (getUserRole() !== 'admin' && !isHREmployee()) {
            jsonResponse(['error' => 'Unauthorized - HR access required'], 403);
        }

        // Accept either employee_id or employee_code
        $empId = isset($data['employee_id']) && $data['employee_id'] ? intval($data['employee_id']) : null;
        $empCode = $data['employee_code'] ?? '';
        $date = $data['date'] ?? date('Y-m-d');
        $status = $data['status'] ?? 'present';

        // Prevent marking attendance for future dates
        if (strtotime($date) > strtotime(date('Y-m-d'))) {
            jsonResponse(['error' => 'Cannot mark attendance for future dates'], 400);
        }

        if (!$empId) {
            if ($empCode) {
                $emp = $db->prepare("SELECT id FROM employees WHERE employee_code = ?");
                $emp->execute([$empCode]);
                $empRow = $emp->fetch();
                if ($empRow) $empId = $empRow['id'];
            }
        }

        if (!$empId) jsonResponse(['error' => 'Employee not found'], 404);

        // If the date is a Sunday or a configured holiday, treat as paid leave and prevent normal attendance marking
        $holidayStmt = $db->prepare("SELECT id FROM holidays WHERE date = ? LIMIT 1");
        $holidayStmt->execute([$date]);
        $isHoliday = $holidayStmt->fetch() ? true : false;

        $isSunday = ((int)date('w', strtotime($date)) === 0);

        if ($isSunday || $isHoliday) {
            $paidStatus = 'paid_leave';
            $existing = $db->query("SELECT id FROM attendance WHERE employee_id = {$empId} AND date = '{$date}'")->fetch();
            if ($existing) {
                $db->prepare("UPDATE attendance SET status = ?, check_in = NULL, check_out = NULL, work_hours = 0 WHERE id = ?")->execute([$paidStatus, $existing['id']]);
            } else {
                $db->prepare("INSERT INTO attendance (employee_id, date, status, check_in, check_out, work_hours) VALUES (?, ?, ?, NULL, NULL, 0)")
                    ->execute([$empId, $date, $paidStatus]);
            }
            // Audit log
            $actorId = getCurrentUser()['id'] ?? null;
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $notes = $data['notes'] ?? null;
            try {
                $db->prepare("INSERT INTO attendance_audit (actor_id, employee_id, action, `date`, status, notes, ip) VALUES (?, ?, ?, ?, ?, ?, ?)")
                    ->execute([$actorId, $empId, 'mark_paid_leave', $date, $paidStatus, $notes, $ip]);
            } catch (PDOException $e) {
                error_log('attendance_audit insert failed: ' . $e->getMessage());
            }

            jsonResponse(['success' => true, 'message' => 'Marked as paid leave (weekend/holiday)']);
        }

        // Check if attendance already exists
        $existing = $db->query("SELECT id FROM attendance WHERE employee_id = {$empId} AND date = '{$date}'")->fetch();

        if ($existing) {
            $db->prepare("UPDATE attendance SET status = ? WHERE id = ?")->execute([$status, $existing['id']]);
            // Audit
            $actorId = getCurrentUser()['id'] ?? null;
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $notes = $data['notes'] ?? null;
            try {
                $db->prepare("INSERT INTO attendance_audit (actor_id, employee_id, action, `date`, status, notes, ip) VALUES (?, ?, ?, ?, ?, ?, ?)")
                    ->execute([$actorId, $empId, 'mark_update', $date, $status, $notes, $ip]);
            } catch (PDOException $e) {
                error_log('attendance_audit insert failed: ' . $e->getMessage());
            }
        }
        else {
            $checkIn = ($status === 'present' || $status === 'late') ? '09:00:00' : null;
            $checkOut = ($status === 'present') ? '18:00:00' : (($status === 'half_day') ? '13:00:00' : null);
            $hours = ($status === 'present') ? 9 : (($status === 'half_day') ? 4.5 : (($status === 'late') ? 8 : 0));

            $stmt = $db->prepare("INSERT INTO attendance (employee_id, date, status, check_in, check_out, work_hours) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$empId, $date, $status, $checkIn, $checkOut, $hours]);
            // Audit
            $actorId = getCurrentUser()['id'] ?? null;
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $notes = $data['notes'] ?? null;
            try {
                $db->prepare("INSERT INTO attendance_audit (actor_id, employee_id, action, `date`, status, notes, ip) VALUES (?, ?, ?, ?, ?, ?, ?)")
                    ->execute([$actorId, $empId, 'mark_create', $date, $status, $notes, $ip]);
            } catch (PDOException $e) {
                error_log('attendance_audit insert failed: ' . $e->getMessage());
            }
        }

        jsonResponse(['success' => true, 'message' => 'Attendance updated']);
        break;

    case 'update_time':
        // HR and admin can update attendance times
        if (getUserRole() !== 'admin' && !isHREmployee()) {
            jsonResponse(['error' => 'Unauthorized - HR access required'], 403);
        }

        // Accept employee_id or employee_code
        $empId = isset($data['employee_id']) && $data['employee_id'] ? intval($data['employee_id']) : null;
        $empCode = $data['employee_code'] ?? '';
        $date = $data['date'] ?? date('Y-m-d');
        $type = $data['type'] ?? ''; // 'check_in' or 'check_out'
        $time = $data['time'] ?? ''; // HH:MM format

        if (!in_array($type, ['check_in', 'check_out'])) {
            jsonResponse(['error' => 'Invalid type'], 400);
        }

        if (!$empId) {
            if ($empCode) {
                $emp = $db->prepare("SELECT id FROM employees WHERE employee_code = ?");
                $emp->execute([$empCode]);
                $empRow = $emp->fetch();
                if ($empRow) $empId = $empRow['id'];
            }
        }
        if (!$empId) jsonResponse(['error' => 'Employee not found'], 404);
        $timeValue = $time . ':00'; // Convert HH:MM to HH:MM:SS

        // Prevent updating times for future dates
        if (strtotime($date) > strtotime(date('Y-m-d'))) {
            jsonResponse(['error' => 'Cannot update attendance times for future dates'], 400);
        }

        // Update attendance record
        $stmt = $db->prepare("UPDATE attendance SET $type = ? WHERE employee_id = ? AND date = ?");
        $stmt->execute([$timeValue, $empId, $date]);

        // Audit time update
        $actorId = getCurrentUser()['id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $detail = $type . ':' . $timeValue;
        try {
            $db->prepare("INSERT INTO attendance_audit (actor_id, employee_id, action, `date`, details, ip) VALUES (?, ?, ?, ?, ?, ?)")
                ->execute([$actorId, $empId, 'update_time', $date, $detail, $ip]);
        } catch (PDOException $e) {
            error_log('attendance_audit insert failed: ' . $e->getMessage());
        }

        jsonResponse(['success' => true, 'message' => 'Time updated']);
        break;

    case 'bulk_mark':
        // HR and admin can mark attendance in bulk
        if (getUserRole() !== 'admin' && !isHREmployee()) {
            jsonResponse(['error' => 'Unauthorized - HR access required'], 403);
        }

        $employeeIds = $data['employee_ids'] ?? [];
        if (!$employeeIds) jsonResponse(['error' => 'No employees selected'], 400);

        // normalize
        if (!is_array($employeeIds)) {
            if (is_string($employeeIds) && strpos($employeeIds, ',') !== false) {
                $employeeIds = array_map('trim', explode(',', $employeeIds));
            } else {
                $employeeIds = [$employeeIds];
            }
        }

        $date = $data['date'] ?? date('Y-m-d');

        // Prevent marking attendance for future dates
        if (strtotime($date) > strtotime(date('Y-m-d'))) {
            jsonResponse(['error' => 'Cannot mark attendance for future dates'], 400);
        }
        $status = $data['status'] ?? 'present';

        $results = ['processed' => 0, 'skipped' => 0];

        $holidayStmt = $db->prepare("SELECT id FROM holidays WHERE date = ? LIMIT 1");
        $holidayStmt->execute([$date]);
        $isHoliday = $holidayStmt->fetch() ? true : false;
        $isSunday = ((int)date('w', strtotime($date)) === 0);

        foreach ($employeeIds as $eid) {
            $eid = intval($eid);
            if (!$eid) { $results['skipped']++; continue; }

            if ($isSunday || $isHoliday) {
                $paidStatus = 'paid_leave';
                $existing = $db->query("SELECT id FROM attendance WHERE employee_id = {$eid} AND date = '{$date}'")->fetch();
                if ($existing) {
                    $db->prepare("UPDATE attendance SET status = ?, check_in = NULL, check_out = NULL, work_hours = 0 WHERE id = ?")->execute([$paidStatus, $existing['id']]);
                } else {
                    $db->prepare("INSERT INTO attendance (employee_id, date, status, check_in, check_out, work_hours) VALUES (?, ?, ?, NULL, NULL, 0)")->execute([$eid, $date, $paidStatus]);
                }
                // Audit per employee
                $actorId = getCurrentUser()['id'] ?? null;
                $ip = $_SERVER['REMOTE_ADDR'] ?? null;
                $notes = $data['notes'] ?? null;
                try {
                    $db->prepare("INSERT INTO attendance_audit (actor_id, employee_id, action, `date`, status, notes, ip) VALUES (?, ?, ?, ?, ?, ?, ?)")
                        ->execute([$actorId, $eid, 'bulk_mark_paid_leave', $date, $paidStatus, $notes, $ip]);
                } catch (PDOException $e) {
                    error_log('attendance_audit insert failed: ' . $e->getMessage());
                }

                $results['processed']++;
                continue;
            }

            $existing = $db->query("SELECT id FROM attendance WHERE employee_id = {$eid} AND date = '{$date}'")->fetch();
            if ($existing) {
                $db->prepare("UPDATE attendance SET status = ? WHERE id = ?")->execute([$status, $existing['id']]);
                $actorId = getCurrentUser()['id'] ?? null;
                $ip = $_SERVER['REMOTE_ADDR'] ?? null;
                $notes = $data['notes'] ?? null;
                try {
                    $db->prepare("INSERT INTO attendance_audit (actor_id, employee_id, action, `date`, status, notes, ip) VALUES (?, ?, ?, ?, ?, ?, ?)")
                        ->execute([$actorId, $eid, 'bulk_update', $date, $status, $notes, $ip]);
                } catch (PDOException $e) {
                    error_log('attendance_audit insert failed: ' . $e->getMessage());
                }
            } else {
                $checkIn = ($status === 'present' || $status === 'late') ? '09:00:00' : null;
                $checkOut = ($status === 'present') ? '18:00:00' : (($status === 'half_day') ? '13:00:00' : null);
                $hours = ($status === 'present') ? 9 : (($status === 'half_day') ? 4.5 : (($status === 'late') ? 8 : 0));

                $stmt = $db->prepare("INSERT INTO attendance (employee_id, date, status, check_in, check_out, work_hours) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$eid, $date, $status, $checkIn, $checkOut, $hours]);
                $actorId = getCurrentUser()['id'] ?? null;
                $ip = $_SERVER['REMOTE_ADDR'] ?? null;
                $notes = $data['notes'] ?? null;
                try {
                    $db->prepare("INSERT INTO attendance_audit (actor_id, employee_id, action, `date`, status, notes, ip) VALUES (?, ?, ?, ?, ?, ?, ?)")
                        ->execute([$actorId, $eid, 'bulk_create', $date, $status, $notes, $ip]);
                } catch (PDOException $e) {
                    error_log('attendance_audit insert failed: ' . $e->getMessage());
                }
            }
            $results['processed']++;
        }

        jsonResponse(['success' => true, 'message' => 'Bulk attendance applied', 'results' => $results]);
        break;

    case 'list':
        // Retrieve attendance records for an employee (HR and admin only)
        if (getUserRole() !== 'admin' && !isHREmployee()) {
            jsonResponse(['error' => 'Unauthorized - HR access required'], 403);
        }

        $empId = $data['employee_id'] ?? $_GET['employee_id'] ?? 0;
        $month = $data['month'] ?? $_GET['month'] ?? date('m');
        $year = $data['year'] ?? $_GET['year'] ?? date('Y');

        if (!$empId) jsonResponse(['error' => 'Employee ID required'], 400);

        $records = $db->query("
            SELECT a.* FROM attendance a
            WHERE a.employee_id = {$empId}
            AND MONTH(a.date) = {$month}
            AND YEAR(a.date) = {$year}
            ORDER BY a.date ASC
        ")->fetchAll();

        jsonResponse(['success' => true, 'data' => $records]);
        break;

    case 'clear':
        // Admin and HR can clear attendance records for selected employees on a given date
        if (getUserRole() !== 'admin' && !isHREmployee()) {
            jsonResponse(['error' => 'Unauthorized - HR access required'], 403);
        }

        $date = $data['date'] ?? $_GET['date'] ?? null;
        $employeeIds = $data['employee_ids'] ?? [];
        if (!$date) jsonResponse(['error' => 'Date required'], 400);

        // Prevent clearing future dates
        if (strtotime($date) > strtotime(date('Y-m-d'))) {
            jsonResponse(['error' => 'Cannot clear attendance for future dates'], 400);
        }

        if (!$employeeIds) jsonResponse(['error' => 'No employees selected'], 400);
        if (!is_array($employeeIds)) {
            if (is_string($employeeIds) && strpos($employeeIds, ',') !== false) {
                $employeeIds = array_map('trim', explode(',', $employeeIds));
            } else {
                $employeeIds = [$employeeIds];
            }
        }

        $ids = array_map('intval', $employeeIds);
        if (empty($ids)) jsonResponse(['error' => 'No valid employees selected'], 400);

        // Build placeholders
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params = array_merge([$date], $ids);
        try {
            $stmt = $db->prepare("DELETE FROM attendance WHERE date = ? AND employee_id IN ({$placeholders})");
            $stmt->execute($params);
            $deleted = $stmt->rowCount();

            // Audit per employee
            $actorId = getCurrentUser()['id'] ?? null;
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            foreach ($ids as $eid) {
                try {
                    $db->prepare("INSERT INTO attendance_audit (actor_id, employee_id, action, `date`, notes, ip) VALUES (?, ?, ?, ?, ?, ?)")
                        ->execute([$actorId, $eid, 'clear_date', $date, $data['notes'] ?? null, $ip]);
                } catch (PDOException $e) {
                    error_log('attendance_audit insert failed: ' . $e->getMessage());
                }
            }

            jsonResponse(['success' => true, 'deleted' => $deleted]);
        } catch (PDOException $e) {
            jsonResponse(['error' => 'Failed to clear attendance: ' . $e->getMessage()], 500);
        }
        break;

    case 'ensure_audit_table':
        // Admin-only utility to create attendance_audit if missing
        if (getUserRole() !== 'admin') jsonResponse(['error' => 'Unauthorized'], 403);
        try {
            $db->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS attendance_audit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    actor_id INT NULL,
    employee_id INT NOT NULL,
    action VARCHAR(64) NOT NULL,
    `date` DATE NULL,
    status VARCHAR(64) NULL,
    notes TEXT NULL,
    details TEXT NULL,
    ip VARCHAR(45) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (employee_id),
    INDEX (actor_id),
    INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
            );
            jsonResponse(['success' => true, 'message' => 'attendance_audit table ensured']);
        } catch (PDOException $e) {
            jsonResponse(['error' => 'Failed to create attendance_audit: ' . $e->getMessage()], 500);
        }
        break;

    case 'fix_missing_times':
        // Admin and HR can fill default check_in/check_out where missing
        if (getUserRole() !== 'admin' && !isHREmployee()) jsonResponse(['error' => 'Unauthorized - HR access required'], 403);
        $date = $data['date'] ?? $_GET['date'] ?? date('Y-m-d');
        $employeeIds = $data['employee_ids'] ?? [];
        if ($employeeIds && !is_array($employeeIds)) {
            if (is_string($employeeIds) && strpos($employeeIds, ',') !== false) $employeeIds = array_map('trim', explode(',', $employeeIds));
            else $employeeIds = [$employeeIds];
        }
        try {
            $where = "date = '" . $date . "' AND (check_in IS NULL OR check_out IS NULL)";
            if (!empty($employeeIds)) {
                $ids = implode(',', array_map('intval', $employeeIds));
                $where .= " AND employee_id IN ({$ids})";
            }
            $rows = $db->query("SELECT id, status FROM attendance WHERE {$where}")->fetchAll();
            $updated = 0;
            foreach ($rows as $r) {
                $checkIn = null; $checkOut = null;
                if (in_array($r['status'], ['present','late'])) $checkIn = '09:00:00';
                if ($r['status'] === 'present') $checkOut = '18:00:00';
                if ($r['status'] === 'half_day') { $checkIn = '09:00:00'; $checkOut = '13:00:00'; }
                // Default fallback
                if ($checkIn === null) $checkIn = '09:00:00';
                if ($checkOut === null) $checkOut = '18:00:00';
                $db->prepare("UPDATE attendance SET check_in = ?, check_out = ? WHERE id = ?")->execute([$checkIn, $checkOut, $r['id']]);
                $updated++;
            }
            jsonResponse(['success' => true, 'updated' => $updated]);
        } catch (PDOException $e) {
            jsonResponse(['error' => 'Failed to fix times: ' . $e->getMessage()], 500);
        }
        break;

    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}
