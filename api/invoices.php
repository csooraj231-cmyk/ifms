<?php
/**
 * IFMS API - Invoices download
 */
require_once __DIR__ . '/../config/auth.php';
requireAPI();

$data = getPostData();
$action = $data['action'] ?? $_GET['action'] ?? '';
$db = getDB();

switch ($action) {
    case 'download':
        $id = intval($data['id'] ?? $_GET['id'] ?? 0);
        if (!$id) jsonResponse(['error' => 'Invoice ID required'], 400);

        $inv = $db->query("SELECT i.*, o.name as org_name FROM invoices i JOIN organizations o ON i.organization_id = o.id WHERE i.id = {$id}")->fetch();
        if (!$inv) jsonResponse(['error' => 'Invoice not found'], 404);

        $user = getCurrentUser();
        $allowed = false;
        if (getUserRole() === 'admin' || isFinanceEmployee()) $allowed = true;
        // If client, ensure organization matches
        if (getUserRole() === 'client' && isset($user['organization_id']) && $user['organization_id'] == $inv['organization_id']) $allowed = true;
        if (!$allowed) jsonResponse(['error' => 'Unauthorized'], 403);

        $html = '<!doctype html><html><head><meta charset="utf-8"><title>Invoice</title><style>body{font-family:Arial,sans-serif;padding:20px} .h{font-weight:700;font-size:18px} .row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #eee}</style></head><body>';
        $html .= "<div class=\"h\">Invoice: {$inv['invoice_number']}</div>";
        $html .= "<div class=\"row\"><div><strong>Client</strong><div>{$inv['org_name']}</div></div><div><strong>Date</strong><div>" . date('d M Y', strtotime($inv['issue_date'])) . "</div></div></div>";
        $html .= "<div class=\"row\"><div><strong>Total</strong></div><div>₹" . number_format($inv['total_amount']) . "</div></div>";
        $html .= '</body></html>';

        header('Content-Type: text/html');
        $filename = 'invoice-' . ($inv['invoice_number'] ?? $inv['id']) . '.html';
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $html;
        exit;
        break;

    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}
