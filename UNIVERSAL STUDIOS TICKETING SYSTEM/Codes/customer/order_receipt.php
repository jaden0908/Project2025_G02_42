<?php
/**
 * order_receipt.php — Printable receipt for a single order
 *
 * Security:
 * - customer: can only see own orders
 * - admin/staff: can see any order
 *
 * Features:
 * - Shows merchant block (your company), customer block, items, totals
 * - Displays Bill Code and Payment Reference (if available)
 * - "Print / Save as PDF" via browser
 * - If dompdf is installed, shows a "Download PDF" button (optional)
 */

session_start();
require __DIR__ . '/database.php';

if (empty($_SESSION['user']['id'])) {
  header('Location: login.php'); exit;
}

$userId   = (int)$_SESSION['user']['id'];
$userRole = $_SESSION['user']['role'] ?? 'customer';
$isAdmin  = in_array($userRole, ['admin','staff'], true);

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// --- Get order id ---
$orderId = (int)($_GET['id'] ?? 0);
if ($orderId <= 0) { http_response_code(400); exit('Invalid order id.'); }

// --- Ownership check for customers ---
if (!$isAdmin) {
  $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM orders WHERE id=? AND user_id=?");
  $stmt->bind_param('ii', $orderId, $userId);
  $stmt->execute();
  $ok = (int)$stmt->get_result()->fetch_assoc()['c'] > 0;
  $stmt->close();
  if (!$ok) { http_response_code(403); exit('Forbidden'); }
}

// --- Fetch order + items ---
$stmt = $conn->prepare("SELECT * FROM orders WHERE id=?");
$stmt->bind_param('i', $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) { http_response_code(404); exit('Order not found.'); }

$itemsStmt = $conn->prepare("SELECT title, unit_price, qty, subtotal FROM order_items WHERE order_id=? ORDER BY id ASC");
$itemsStmt->bind_param('i', $orderId);
$itemsStmt->execute();
$itemsRes = $itemsStmt->get_result();
$items    = $itemsRes->fetch_all(MYSQLI_ASSOC);
$itemsStmt->close();

// --- Your merchant info (customize) ---
$merchant = [
  'name'    => 'Universal Studios (Demo)',
  'addr1'   => '123, Demo Street',
  'addr2'   => 'Kuala Lumpur, Malaysia',
  'email'   => 'support@example.com',
  'phone'   => '+60 12-345 6789',
];

// Amounts
$totalUsd = (float)$order['total_usd'];
define('USD_TO_MYR', 4.70);
$totalMyr = $totalUsd * USD_TO_MYR;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Receipt #<?= e($order['id']) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { background:#f7f7fb; }
  .receipt { max-width: 980px; margin: 24px auto; background:#fff; border-radius:12px; padding:32px; box-shadow:0 10px 30px rgba(0,0,0,.08); }
  .brand { font-weight:700; font-size: 20px; }
  .muted { color:#6c757d; }
  .table td, .table th { vertical-align: middle; }
  .watermark {
     position: absolute; inset: 0; display:flex; align-items:center; justify-content:center;
     font-size: 80px; font-weight: 800; color: rgba(220, 53, 69, .15); transform: rotate(-20deg); pointer-events:none;
  }
  @media print {
    body { background:#fff; }
    .no-print { display:none !important; }
    .receipt { box-shadow:none; margin:0; border-radius:0; }
  }
</style>
</head>
<body>

<div class="receipt position-relative">
  <?php if ($order['status'] !== 'paid'): ?>
    <div class="watermark">UNPAID</div>
  <?php endif; ?>

  <div class="d-flex justify-content-between align-items-start">
    <div>
      <div class="brand">Receipt</div>
      <div class="muted">#<?= e($order['id']) ?> &middot; <?= e($order['created_at']) ?></div>
    </div>
    <div class="text-end">
      <div class="fw-bold"><?= e($merchant['name']) ?></div>
      <div class="muted"><?= e($merchant['addr1']) ?></div>
      <div class="muted"><?= e($merchant['addr2']) ?></div>
      <div class="muted">Email: <?= e($merchant['email']) ?> | Tel: <?= e($merchant['phone']) ?></div>
    </div>
  </div>

  <hr class="my-4">

  <div class="row g-4">
    <div class="col-md-6">
      <div class="fw-semibold mb-1">Billed To</div>
      <div><?= e($order['customer_name']) ?></div>
      <div class="muted"><?= e($order['customer_email']) ?></div>
      <?php if (!empty($order['customer_phone'])): ?>
        <div class="muted"><?= e($order['customer_phone']) ?></div>
      <?php endif; ?>
    </div>
    <div class="col-md-6 text-md-end">
      <div><span class="muted">Status:</span> 
        <span class="badge <?= $order['status']==='paid'?'bg-success':'bg-warning' ?>"><?= e($order['status']) ?></span>
      </div>
      <?php if (!empty($order['bill_code'])): ?>
        <div><span class="muted">Bill Code:</span> <span class="text-monospace"><?= e($order['bill_code']) ?></span></div>
      <?php endif; ?>
      <?php if (!empty($order['payment_ref'])): ?>
        <div><span class="muted">Payment Ref:</span> <span class="text-monospace"><?= e($order['payment_ref']) ?></span></div>
      <?php endif; ?>
      <div><span class="muted">Updated:</span> <?= e($order['updated_at']) ?></div>
    </div>
  </div>

  <div class="table-responsive my-4">
    <table class="table">
      <thead class="table-light">
        <tr>
          <th>Item</th>
          <th class="text-end">Unit (USD)</th>
          <th class="text-center">Qty</th>
          <th class="text-end">Subtotal (USD)</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td><?= e($it['title']) ?></td>
          <td class="text-end">$<?= number_format((float)$it['unit_price'], 2) ?></td>
          <td class="text-center"><?= (int)$it['qty'] ?></td>
          <td class="text-end">$<?= number_format((float)$it['subtotal'], 2) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <th colspan="3" class="text-end">Total (USD)</th>
          <th class="text-end">$<?= number_format($totalUsd, 2) ?></th>
        </tr>
        <tr>
          <th colspan="3" class="text-end">Total (MYR, rate <?= number_format(USD_TO_MYR,2) ?>)</th>
          <th class="text-end">RM <?= number_format($totalMyr, 2) ?></th>
        </tr>
      </tfoot>
    </table>
  </div>

  <div class="muted small">
    * This is a computer-generated receipt for your records.  
    * For official tax invoice, please contact our support.
  </div>

  <div class="d-flex justify-content-between mt-4 no-print">
    <div>
      <a class="btn btn-outline-secondary" href="orders.php"><i class="bi bi-arrow-left"></i> Back to Orders</a>
    </div>
    <div class="d-flex gap-2">
      <?php if (class_exists('\Dompdf\Dompdf')): ?>
        <a class="btn btn-outline-primary" href="receipt_pdf.php?id=<?= e($order['id']) ?>"><i class="bi bi-filetype-pdf"></i> Download PDF</a>
      <?php endif; ?>
      <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer"></i> Print / Save PDF</button>
    </div>
  </div>
</div>

<!-- optional icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</body>
</html>
