<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Payslip - <?= htmlspecialchars($payslip['employee']['full_name'] ?? 'Employee') ?></title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;color:#222}
    .container{max-width:700px;margin:0 auto;padding:20px}
    .header{display:flex;justify-content:space-between}
    .table{width:100%;border-collapse:collapse;margin-top:20px}
    .table td{padding:8px;border:1px solid #eee}
    .muted{color:#666;font-size:0.9em}
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div>
        <h2>Payslip</h2>
        <div class="muted">Month: <?= htmlspecialchars($payslip['month']) ?></div>
      </div>
      <div>
        <strong><?= htmlspecialchars($payslip['employee']['full_name'] ?? '') ?></strong><br>
        <div class="muted"><?= htmlspecialchars($payslip['employee']['employee_code'] ?? '') ?></div>
      </div>
    </div>

    <table class="table">
      <tr><td>Basic Salary</td><td style="text-align:right"><?= number_format($payslip['basic'],2) ?></td></tr>
      <tr><td>Deductions</td><td style="text-align:right">-<?= number_format($payslip['deductions'],2) ?></td></tr>
      <tr><td><strong>Net Pay</strong></td><td style="text-align:right"><strong><?= number_format($payslip['net'],2) ?></strong></td></tr>
    </table>
  </div>
</body>
</html>
