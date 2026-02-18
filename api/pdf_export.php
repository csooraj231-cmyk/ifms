<?php
/**
 * PDF Export API
 * Actions:
 *  - action=invoice&id=INVOICE_ID   => generate invoice PDF/HTML
 *  - action=payslip&id=EMP_ID&month=YYYY-MM => generate payslip PDF/HTML
 *
 * Requires mPDF (recommended): composer require mpdf/mpdf
 * If mPDF isn't installed the endpoint will return rendered HTML for preview.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

header_remove();

$action = $_GET['action'] ?? $_POST['action'] ?? null;
if (!$action) {
    http_response_code(400);
    echo 'Missing action';
    exit;
}

function render_template($tplPath, $data = []) {
    extract($data);
    ob_start();
    include $tplPath;
    return ob_get_clean();
}

try {
    if ($action === 'invoice') {
        $id = intval($_GET['id'] ?? 0);
        if (!$id) throw new Exception('Missing invoice id');

        // Fetch invoice and organization
        $stmt = $db->prepare("SELECT i.*, o.name as organization_name, o.address as organization_address FROM invoices i LEFT JOIN organizations o ON i.organization_id = o.id WHERE i.id = ? LIMIT 1");
        $stmt->execute([$id]);
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$invoice) throw new Exception('Invoice not found');

        $html = render_template(__DIR__ . '/../templates/pdf_invoice.php', ['invoice' => $invoice]);

        if (class_exists('\Mpdf\\Mpdf')) {
            $mpdf = new \Mpdf\Mpdf();
            $mpdf->WriteHTML($html);
            $mpdf->Output('invoice_' . $invoice['id'] . '.pdf', 'I');
            exit;
        }

        // Fallback: return HTML preview
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit;

    } elseif ($action === 'payslip') {
        $empId = intval($_GET['id'] ?? 0);
        $month = $_GET['month'] ?? date('Y-m');
        if (!$empId) throw new Exception('Missing employee id');

        // Fetch employee and payroll summary (simple implementation)
        $stmt = $db->prepare("SELECT e.*, u.email as user_email FROM employees e LEFT JOIN users u ON e.user_id = u.id WHERE e.id = ? LIMIT 1");
        $stmt->execute([$empId]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$employee) throw new Exception('Employee not found');

        // Minimal payslip data
        $payslip = [
            'employee' => $employee,
            'month' => $month,
            'basic' => $employee['salary'] ?? 0,
            'deductions' => 0,
            'net' => $employee['salary'] ?? 0,
        ];

        $html = render_template(__DIR__ . '/../templates/pdf_payslip.php', ['payslip' => $payslip]);

        if (class_exists('\Mpdf\\Mpdf')) {
            $mpdf = new \Mpdf\Mpdf();
            $mpdf->WriteHTML($html);
            $mpdf->Output('payslip_' . $empId . '_' . str_replace('-', '_', $month) . '.pdf', 'I');
            exit;
        }

        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit;
    } else {
        http_response_code(400);
        echo 'Unknown action';
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo 'Error: ' . htmlspecialchars($e->getMessage());
    exit;
}

?>
