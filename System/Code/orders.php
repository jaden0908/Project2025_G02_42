<?php
/**
 * orders.php — Professional order list with filters, pagination and CSV export
 *
 * Modified: removed "View" and "Items" buttons + removed Items modal & JS
 *
 * Requirements:
 * - PHP 7.4+, mysqli extension.
 * - database.php defines $conn (mysqli) and session with $_SESSION['user'].
 * - orders table has: id,user_id,status,total_usd,customer_name,customer_email,customer_phone,created_at,updated_at,bill_code (optional).
 * - order_items table: order_id, title, unit_price, qty, subtotal. (Note: items modal removed here.)
 */

session_start();
require __DIR__ . '/database.php';

if (empty($_SESSION['user']['id'])) {
  header('Location: login.php');
  exit;
}

$userId   = (int)$_SESSION['user']['id'];
$userRole = $_SESSION['user']['role'] ?? 'customer';
$isAdmin  = in_array($userRole, ['admin','staff'], true);

// Simple escaping helper
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// ----------- Read filters (from GET) -----------
$q        = trim($_GET['q'] ?? '');
$status   = trim($_GET['status'] ?? '');              // '', pending, paid, cancelled, refunded
$dateFrom = trim($_GET['from'] ?? '');                // yyyy-mm-dd
$dateTo   = trim($_GET['to'] ?? '');                  // yyyy-mm-dd
$page     = max(1, (int)($_GET['page'] ?? 1));
$pageSize = (int)($_GET['size'] ?? 10);
if (!in_array($pageSize, [10,20,50,100], true)) $pageSize = 10;

$export   = isset($_GET['export']) && $_GET['export']==='csv';

// ----------- Build WHERE clauses safely -----------
// We build dynamic WHERE conditions and parallel arrays for binding
$where = [];
$params = [];
$types  = '';

if (!$isAdmin) {
  // Non-admin sees only their own orders
  $where[] = 'o.user_id = ?';
  $params[] = $userId;
  // user_id is integer
  $types   .= 'i';
}

if ($q !== '') {
  // Keyword search (id exact match, or name/email/bill_code LIKE)
  $where[] = '(o.id = ? OR o.customer_name LIKE ? OR o.customer_email LIKE ? OR o.bill_code LIKE ?)';
  $params[] = ctype_digit($q) ? (int)$q : 0;  $types .= 'i';
  $kw = '%'.$q.'%';
  $params[] = $kw; $types .= 's';
  $params[] = $kw; $types .= 's';
  $params[] = $kw; $types .= 's';
}

if ($status !== '' && in_array($status, ['pending','paid','cancelled','refunded'], true)) {
  $where[] = 'o.status = ?';
  $params[] = $status;
  $types   .= 's';
}

if ($dateFrom !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
  $where[] = 'DATE(o.created_at) >= ?';
  $params[] = $dateFrom;
  $types   .= 's';
}
if ($dateTo !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
  $where[] = 'DATE(o.created_at) <= ?';
  $params[] = $dateTo;
  $types   .= 's';
}

$whereSql = $where ? ('WHERE '.implode(' AND ', $where)) : '';

// ----------- Count total rows -----------
// Use prepared statement to count total results for pagination
$sqlCount = "SELECT COUNT(*) AS c FROM orders o $whereSql";
$stmt = $conn->prepare($sqlCount);
if ($types) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$totalRows = (int)$stmt->get_result()->fetch_assoc()['c'];
$stmt->close();

// ----------- Fetch page data -----------
$offset = ($page - 1) * $pageSize;
$sql = "SELECT o.id, o.user_id, o.status, o.total_usd, o.customer_name, o.customer_email, o.customer_phone,
               o.bill_code, o.created_at, o.updated_at
        FROM orders o
        $whereSql
        ORDER BY o.created_at DESC
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

if ($types) {
  // When there are filter param types, append two integers for LIMIT/OFFSET
  $typesPage = $types . 'ii';
  $paramsPage = $params;
  $paramsPage[] = $pageSize;
  $paramsPage[] = $offset;
  $stmt->bind_param($typesPage, ...$paramsPage);
} else {
  // No filters, bind only the two paging ints
  $stmt->bind_param('ii', $pageSize, $offset);
}
$stmt->execute();
$res = $stmt->get_result();
$rows = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ----------- CSV export -----------
if ($export) {
  // Note: exporting only the current page (same as before). If you want full export,
  // re-run the query without LIMIT/OFFSET.
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=orders_export_'.date('Ymd_His').'.csv');
  $out = fopen('php://output', 'w');
  fputcsv($out, ['ID','User ID','Status','Total USD','Customer Name','Customer Email','Customer Phone','Bill Code','Created At','Updated At']);
  foreach ($rows as $r) {
    fputcsv($out, [
      $r['id'],
      $r['user_id'],
      $r['status'],
      $r['total_usd'],
      $r['customer_name'],
      $r['customer_email'],
      $r['customer_phone'],
      $r['bill_code'],
      $r['created_at'],
      $r['updated_at']
    ]);
  }
  fclose($out);
  exit;
}

// helper: status badge generator (returns HTML string)
function statusBadge(string $s): string {
  $map = [
    'pending'   => 'warning',
    'paid'      => 'success',
    'cancelled' => 'danger',
    'refunded'  => 'secondary',
  ];
  $cls = $map[$s] ?? 'light';
  // Uppercase status text for visual consistency
  return '<span class="badge bg-'.$cls.' text-uppercase">'.htmlspecialchars($s, ENT_QUOTES, 'UTF-8').'</span>';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Orders — Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body { background:#f7f7fb; }
    .card { border:0; box-shadow: 0 8px 24px rgba(0,0,0,0.06); }
    .table thead th { white-space: nowrap; }
    .stat { font-weight:600; }
    .truncate { max-width:240px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:inline-block; vertical-align:bottom;}
  </style>
</head>
<body>
<nav class="navbar navbar-light bg-white border-bottom">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <i class="fa-solid fa-film text-primary me-2"></i><strong>Universal Studios</strong>
    </a>
    <div class="d-flex align-items-center gap-3">
      <span class="text-muted small">Signed in as <strong><?= e($_SESSION['user']['name'] ?? 'User') ?></strong> (<?= e($userRole) ?>)</span>
      <div class="d-flex gap-2">
        <a href="package.php" class="btn btn-outline-secondary btn-sm">Package</a>
        <a href="cart.php" class="btn btn-outline-secondary btn-sm">Cart</a>
      </div>
    </div>
  </div>
</nav>

<main class="container my-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Orders</h1>
    <div class="text-muted small">
      <span class="me-3">Total: <span class="stat"><?= number_format($totalRows) ?></span></span>
      <a href="?<?= e(http_build_query(array_merge($_GET,['export'=>'csv','page'=>1]))) ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-file-csv me-1"></i>Export CSV
      </a>
    </div>
  </div>

  <!-- Filters -->
  <form class="card mb-3" method="get">
    <div class="card-body row g-3 align-items-end">
      <div class="col-12 col-md-4">
        <label class="form-label">Keyword</label>
        <input type="text" name="q" class="form-control" placeholder="Order ID / Name / Email / Bill Code" value="<?= e($q) ?>">
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <?php
            $opts = [''=>'All','pending'=>'pending','paid'=>'paid','cancelled'=>'cancelled','refunded'=>'refunded'];
            foreach ($opts as $k=>$v) echo '<option value="'.e($k).'"'.(($status===$k)?' selected':'').'>'.e($v).'</option>';
          ?>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label">From</label>
        <input type="date" name="from" class="form-control" value="<?= e($dateFrom) ?>">
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label">To</label>
        <input type="date" name="to" class="form-control" value="<?= e($dateTo) ?>">
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label">Page Size</label>
        <select name="size" class="form-select">
          <?php foreach ([10,20,50,100] as $s) echo '<option '.($pageSize===$s?'selected':'').' value="'.$s.'">'.$s.'</option>'; ?>
        </select>
      </div>
      <div class="col-12 text-end">
        <a href="orders.php" class="btn btn-outline-secondary">Reset</a>
        <button class="btn btn-primary"><i class="fa-solid fa-magnifying-glass me-1"></i>Apply</button>
      </div>
    </div>
  </form>

  <!-- Orders table -->
  <div class="card">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width:80px;">ID</th>
            <?php if ($isAdmin): ?><th style="width:90px;">User</th><?php endif; ?>
            <th>Status</th>
            <th class="text-end">Total (USD)</th>
            <th>Customer</th>
            <th>Contact</th>
            <th>Bill Code</th>
            <th>Created</th>
            <th style="width:160px;" class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="<?= $isAdmin?9:8 ?>" class="text-center text-muted py-4">No orders found.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr>
            <td>#<?= e($r['id']) ?></td>
            <?php if ($isAdmin): ?><td><?= e($r['user_id']) ?></td><?php endif; ?>
            <td><?= statusBadge($r['status']) ?></td>
            <td class="text-end">$<?= number_format((float)$r['total_usd'], 2) ?></td>
            <td>
              <div class="fw-semibold truncate" title="<?= e($r['customer_name']) ?>"><?= e($r['customer_name']) ?></div>
              <div class="text-muted small"><?= e($r['customer_email']) ?></div>
            </td>
            <td class="text-muted small"><?= e($r['customer_phone']) ?></td>
            <td><span class="text-monospace small"><?= e($r['bill_code'] ?? '') ?></span></td>
            <td class="text-muted small"><?= e($r['created_at']) ?></td>
            <td class="text-end">
              <div class="btn-group">
                <!-- VIEW button removed per request -->

                <!-- Pay Now: only for pending orders (keeps original behavior) -->
                <?php if ($r['status']==='pending'): ?>
                <a class="btn btn-primary btn-sm" href="pay_fpx_start.php?id=<?= e($r['id']) ?>">
                  <i class="fa-solid fa-credit-card me-1"></i>Pay Now
                </a>
                <?php endif; ?>

                <!-- Items button removed per request (modal & AJAX removed) -->

                <!-- Receipt: for paid orders -->
                <?php if ($r['status']==='paid'): ?>
                <a class="btn btn-outline-success btn-sm" href="order_receipt.php?id=<?= e($r['id']) ?>" target="_blank">
                  <i class="fa-solid fa-file-invoice-dollar me-1"></i>Receipt
                </a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php
      $totalPages = max(1, (int)ceil($totalRows / $pageSize));
      $qs = $_GET; unset($qs['page']); $baseQS = http_build_query($qs);
      function pageLink($p,$lbl,$disabled=false,$active=false,$baseQS=''){
        $cls = 'page-item'.($disabled?' disabled':'').($active?' active':'');
        $href = $disabled ? '#' : ('?'.$baseQS.'&page='.$p);
        return '<li class="'.$cls.'"><a class="page-link" href="'.$href.'">'.$lbl.'</a></li>';
      }
    ?>
    <div class="card-body d-flex justify-content-between align-items-center">
      <div class="text-muted small">
        Showing <strong><?= count($rows) ?></strong> of <strong><?= number_format($totalRows) ?></strong> result(s)
      </div>
      <nav>
        <ul class="pagination mb-0">
          <?= pageLink(max(1,$page-1),'« Prev',$page<=1,false,$baseQS) ?>
          <?php
            // compact pagination window around current page
            for ($p=max(1,$page-2); $p<=min($totalPages,$page+2); $p++){
              echo pageLink($p,(string)$p,false,$p==$page,$baseQS);
            }
          ?>
          <?= pageLink(min($totalPages,$page+1),'Next »',$page>=$totalPages,false,$baseQS) ?>
        </ul>
      </nav>
    </div>
  </div>
</main>

<!-- Removed: Items Modal HTML and the related JS AJAX loader -->
<!-- The rest of the page has no JS dependency for items. -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
