<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Invoice #<?= htmlspecialchars($invoice['id']) ?></title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;color:#222}
    .container{max-width:800px;margin:0 auto;padding:20px}
    .header{display:flex;justify-content:space-between;align-items:center}
    .items{width:100%;border-collapse:collapse;margin-top:20px}
    .items th,.items td{border:1px solid #ddd;padding:8px}
    .right{text-align:right}
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div>
        <h1>Invoice #<?= htmlspecialchars($invoice['id']) ?></h1>
        <div><?= htmlspecialchars($invoice['organization_name'] ?? 'Organization') ?></div>
        <div><?= nl2br(htmlspecialchars($invoice['organization_address'] ?? '')) ?></div>
      </div>
      <div>
        <strong>Date:</strong> <?= htmlspecialchars($invoice['date'] ?? date('Y-m-d')) ?><br>
        <strong>Due:</strong> <?= htmlspecialchars($invoice['due_date'] ?? '') ?>
      </div>
    </div>

    <table class="items">
      <thead>
        <tr><th>Description</th><th class="right">Amount</th></tr>
      </thead>
      <tbody>
        <tr>
          <td><?= nl2br(htmlspecialchars($invoice['description'] ?? 'Invoice')) ?></td>
          <td class="right"><?= number_format($invoice['amount'] ?? 0, 2) ?></td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <th class="right">Total</th>
          <th class="right"><?= number_format($invoice['amount'] ?? 0, 2) ?></th>
        </tr>
      </tfoot>
    </table>
  </div>
</body>
</html>
