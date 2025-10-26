<?php
/**
 * sales_report.php
 * Full Sales Report for Admin/Staff:
 * - Filters (date range, status)
 * - KPIs (Total Sales, Orders, Customers, Refunds, AOV)
 * - Daily Sales Bar Chart
 * - Packages Top 10 (by Qty & by Amount)
 * - Orders table with pagination
 * - CSV exports (daily/packages/orders)
 *
 * IMPORTANT: All comments in ENGLISH only.
 */

session_start();
require __DIR__ . '/database.php';

// --- Debug errors while developing (remove on production) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Access control: only admin/staff ---
if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
$role = $_SESSION['user']['role'] ?? 'customer';
if (!in_array($role, ['admin','staff'], true)) { header('Location: index.php'); exit; }

// --- Shared helpers ---
if (!defined('BRAND_NAME')) define('BRAND_NAME', 'Universal Studios');

if (!function_exists('nav_active')) {
  // Return ' active' when current page matches the given file
  function nav_active(string $file): string {
    return basename($_SERVER['PHP_SELF']) === $file ? ' active' : '';
  }
}

// Minimal helper set
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function date_param(string $key, string $fallback): string {
  $v = trim($_GET[$key] ?? $fallback);
  return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : $fallback;
}
function build_where(array &$params, string &$types, string $from, string $to, string $status): string {
  // Build reusable WHERE clause for prepared statements
  $w = [];
  if ($from) { $w[] = 'DATE(o.created_at) >= ?'; $params[]=$from; $types.='s'; }
  if ($to)   { $w[] = 'DATE(o.created_at) <= ?'; $params[]=$to;   $types.='s'; }
  if ($status !== '' && in_array($status, ['pending','paid','cancelled','refunded'], true)) {
    $w[] = 'o.status = ?'; $params[]=$status; $types.='s';
  }
  return $w ? ('WHERE '.implode(' AND ',$w)) : '';
}

// --- Read filters ---
$from   = date_param('from', date('Y-m-01')); // first day of current month
$to     = date_param('to',   date('Y-m-d'));  // today
$status = trim($_GET['status'] ?? 'paid');    // default paid

// --- Export flags ---
$export = $_GET['export'] ?? '';              // 'daily', 'packages', 'orders'

// --- Build WHERE for reuse ---
$params = []; $types = '';
$whereSql = build_where($params, $types, $from, $to, $status);

// =================== KPIs ===================
$kpi = ['sales'=>0.0,'orders'=>0,'customers'=>0,'refunds'=>0.0,'aov'=>0.0];

// Total sales & orders
$sql = "SELECT COALESCE(SUM(o.total_usd),0) AS s, COUNT(*) AS c FROM orders o $whereSql";
$stmt = $conn->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$stmt->close();
$kpi['sales']  = (float)($r['s'] ?? 0);
$kpi['orders'] = (int)($r['c'] ?? 0);
$kpi['aov']    = $kpi['orders'] > 0 ? $kpi['sales'] / $kpi['orders'] : 0.0;

// Unique customers
$sql = "SELECT COUNT(DISTINCT CASE WHEN o.customer_email<>'' THEN o.customer_email ELSE o.customer_name END) AS u
        FROM orders o $whereSql";
$stmt = $conn->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$kpi['customers'] = (int)$stmt->get_result()->fetch_assoc()['u'];
$stmt->close();

// Refunds (meaningful when status is not locked)
if ($status === '') {
  $w2 = $whereSql ? ($whereSql." AND o.status='refunded'") : "WHERE o.status='refunded'";
  $sql = "SELECT COALESCE(SUM(o.total_usd),0) AS r FROM orders o $w2";
  $stmt = $conn->prepare($sql);
  if ($types) $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $kpi['refunds'] = (float)$stmt->get_result()->fetch_assoc()['r'];
  $stmt->close();
} else {
  $kpi['refunds'] = ($status==='refunded') ? $kpi['sales'] : 0.0;
}

// =================== Daily sales (chart) ===================
$dailyRows = [];
$sql = "SELECT DATE(o.created_at) AS d, COALESCE(SUM(o.total_usd),0) AS s, COUNT(*) AS c
        FROM orders o
        $whereSql
        GROUP BY DATE(o.created_at)
        ORDER BY d ASC";
$stmt = $conn->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $dailyRows[] = $row;
$stmt->close();

// =================== Packages TopN ===================
// Only packages that actually sold will appear (JOIN on order_items)
// If you want also zero-sales packages, switch to LEFT JOIN with packages table.
$pkgAllQty = [];   // title => qty
$pkgAllAmt = [];   // title => amount
$sql = "SELECT 
          COALESCE(NULLIF(oi.title,''),'(Untitled)') AS title,
          SUM(oi.qty) AS q,
          SUM(oi.subtotal) AS amt
        FROM order_items oi
        JOIN orders o ON o.id = oi.order_id
        $whereSql
        GROUP BY oi.title
        ORDER BY q DESC";
$stmt = $conn->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
  $title = $row['title'];
  $pkgAllQty[$title] = (int)$row['q'];
  $pkgAllAmt[$title] = (float)$row['amt'];
}
$stmt->close();

$topN = 10;
arsort($pkgAllQty);
arsort($pkgAllAmt);
$pkgQtyTop = array_slice($pkgAllQty, 0, $topN, true);
$pkgAmtTop = array_slice($pkgAllAmt, 0, $topN, true);

// =================== Orders table (pagination) ===================
$page = max(1, (int)($_GET['page'] ?? 1));
$pageSize = (int)($_GET['size'] ?? 10);
if (!in_array($pageSize, [10,20,50,100], true)) $pageSize = 10;

// Count
$sql = "SELECT COUNT(*) AS c FROM orders o $whereSql";
$stmt = $conn->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$totalRows = (int)$stmt->get_result()->fetch_assoc()['c'];
$stmt->close();

// Fetch
$offset = ($page-1) * $pageSize;
$sql = "SELECT o.id,o.status,o.total_usd,o.customer_name,o.customer_email,o.customer_phone,o.payment_channel,o.bill_code,o.created_at
        FROM orders o
        $whereSql
        ORDER BY o.created_at DESC
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if ($types) { $bindTypes = $types.'ii'; $bindParams = $params; $bindParams[]=$pageSize; $bindParams[]=$offset; $stmt->bind_param($bindTypes, ...$bindParams); }
else { $stmt->bind_param('ii', $pageSize, $offset); }
$stmt->execute();
$orderRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// =================== CSV exports ===================
if ($export === 'daily') {
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=daily_sales_'.date('Ymd_His').'.csv');
  $out = fopen('php://output', 'w');
  fputcsv($out, ['Date','Sales USD','Orders']);
  foreach ($dailyRows as $r) fputcsv($out, [$r['d'], $r['s'], $r['c']]);
  fclose($out); exit;
}
if ($export === 'packages') {
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=packages_'.date('Ymd_His').'.csv');
  $out = fopen('php://output', 'w');
  fputcsv($out, ['Package Title','Qty Sold','Total Sales USD']);
  $titles = array_unique(array_merge(array_keys($pkgAllQty), array_keys($pkgAllAmt)));
  foreach ($titles as $t) fputcsv($out, [$t, $pkgAllQty[$t] ?? 0, number_format($pkgAllAmt[$t] ?? 0, 2, '.', '')]);
  fclose($out); exit;
}
if ($export === 'orders') {
  $sql = "SELECT o.id,o.status,o.total_usd,o.customer_name,o.customer_email,o.customer_phone,o.payment_channel,o.bill_code,o.created_at
          FROM orders o $whereSql ORDER BY o.created_at DESC";
  $stmt = $conn->prepare($sql);
  if ($types) $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $all = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=orders_'.date('Ymd_His').'.csv');
  $out = fopen('php://output', 'w');
  fputcsv($out, ['ID','Status','Total USD','Customer Name','Customer Email','Customer Phone','Payment Channel','Bill Code','Created At']);
  foreach ($all as $r) fputcsv($out, [$r['id'],$r['status'],$r['total_usd'],$r['customer_name'],$r['customer_email'],$r['customer_phone'],$r['payment_channel'],$r['bill_code'],$r['created_at']]);
  fclose($out); exit;
}

// =================== JSON for charts ===================
$dailyLabels   = array_column($dailyRows, 'd');
$dailySales    = array_map(fn($x)=>(float)$x['s'], $dailyRows);
$pkgQtyLabels  = array_keys($pkgQtyTop);
$pkgQtyData    = array_values($pkgQtyTop);
$pkgAmtLabels  = array_keys($pkgAmtTop);
$pkgAmtData    = array_values($pkgAmtTop);

$jDailyLabels  = json_encode($dailyLabels,  JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
$jDailySales   = json_encode($dailySales,   JSON_UNESCAPED_SLASHES);
$jPkgQtyLabels = json_encode($pkgQtyLabels, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
$jPkgQtyData   = json_encode($pkgQtyData,   JSON_UNESCAPED_SLASHES);
$jPkgAmtLabels = json_encode($pkgAmtLabels, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
$jPkgAmtData   = json_encode($pkgAmtData,   JSON_UNESCAPED_SLASHES);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Sales Report · <?= e(BRAND_NAME) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Your global styles (must include .layout/.sidebar/.topbar/.main) -->
  <link href="css/style.css?v=2" rel="stylesheet">

  <style>
    /* Page-local cosmetics matching your dashboard */
    body { background:#f7f7fb; }
    .card-soft { border:0; border-radius:16px; background:#fff; box-shadow:0 10px 30px rgba(0,0,0,.06); }
    .kpi .value { font-size:28px; font-weight:800; }
    .kpi .label { color:#6c7a89; text-transform:uppercase; font-size:12px; letter-spacing:.04em; }
    .icon-badge { width:46px; height:46px; display:flex; align-items:center; justify-content:center; border-radius:12px; background:#eef0ff; }
    .chart-wrap { height: 360px; }
    .chart-wrap-tall { height: 420px; }
    .truncate { max-width:260px; overflow:hidden; white-space:nowrap; text-overflow:ellipsis; }
    .sidebar .nav-link.active { background:rgba(99,102,241,.12); color:#6366f1; }
  </style>
</head>
<body>

<div class="layout">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="brand"><i class="bi bi-film"></i><span><?= BRAND_NAME ?></span></div>

    <div class="nav-sec">
      <div class="nav-title">Main</div>
      <a class="nav-link<?= nav_active('admin_dashboard.php') ?>" href="admin_dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
      <a class="nav-link<?= nav_active('manage_staff.php') ?>" href="manage_staff.php"><i class="bi bi-person-badge me-1"></i>Manage Staff</a>
      <a class="nav-link<?= nav_active('manage_customers.php') ?>" href="manage_customers.php"><i class="bi bi-people me-1"></i>Manage Customers</a>
      <a class="nav-link<?= nav_active('view_feedback.php') ?>" href="view_feedback.php"><i class="bi bi-chat-left-text me-1"></i>View Feedback</a>
      <a class="nav-link<?= nav_active('manage_packages.php') ?>" href="manage_packages.php"><i class="bi bi-ticket-perforated me-1"></i>Manage Packages</a>
      <a class="nav-link<?= nav_active('sales_report.php') ?>" href="sales_report.php"><i class="bi bi-graph-up me-1"></i>Sales Report</a>
    </div>

    <div class="nav-sec">
      <div class="nav-title">Account</div>
      <a class="nav-link<?= nav_active('profile.php') ?>" href="profile.php"><i class="bi bi-person me-1"></i>My Profile</a>
      <a class="nav-link" href="index.php"><i class="bi bi-house-door me-1"></i>Back to Home</a>
      <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right me-1"></i>Sign Out</a>
    </div>
  </aside>

  <!-- Content -->
  <div>
    <div class="topbar"><div class="fw-bold">Sales Report</div></div>

    <main class="main container-fluid">
      <!-- Filters -->
      <form class="card-soft p-3 mb-3" method="get">
        <div class="row g-3 align-items-end">
          <div class="col-6 col-md-3">
            <label class="form-label">From</label>
            <input type="date" name="from" class="form-control" value="<?= e($from) ?>">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">To</label>
            <input type="date" name="to" class="form-control" value="<?= e($to) ?>">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <?php
                $opts = [''=>'All','paid'=>'paid','pending'=>'pending','cancelled'=>'cancelled','refunded'=>'refunded'];
                foreach ($opts as $k=>$v) echo '<option value="'.e($k).'"'.(($status===$k)?' selected':'').'>'.e($v).'</option>';
              ?>
            </select>
          </div>
          <div class="col-12 d-flex justify-content-between">
            <div class="small text-muted">
              Range: <?= e($from) ?> → <?= e($to) ?><?= $status!=='' ? ' · Status: '.e($status) : '' ?>
            </div>
            <div class="text-end">
              <a href="sales_report.php" class="btn btn-outline-secondary">Reset</a>
              <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Apply</button>
            </div>
          </div>
        </div>
      </form>

      <!-- KPI Row -->
      <div class="row g-3 mb-3 kpi">
        <div class="col-sm-6 col-lg-2">
          <div class="card-soft p-3 d-flex align-items-center gap-3">
            <div class="icon-badge"><i class="bi bi-cash-coin"></i></div>
            <div><div class="label">Total Sales (USD)</div><div class="value">$<?= number_format($kpi['sales'], 2) ?></div></div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-2">
          <div class="card-soft p-3 d-flex align-items-center gap-3">
            <div class="icon-badge"><i class="bi bi-receipt"></i></div>
            <div><div class="label">Orders</div><div class="value"><?= number_format($kpi['orders']) ?></div></div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-2">
          <div class="card-soft p-3 d-flex align-items-center gap-3">
            <div class="icon-badge"><i class="bi bi-people"></i></div>
            <div><div class="label">Customers</div><div class="value"><?= number_format($kpi['customers']) ?></div></div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-2">
          <div class="card-soft p-3 d-flex align-items-center gap-3">
            <div class="icon-badge"><i class="bi bi-arrow-counterclockwise"></i></div>
            <div><div class="label">Refunds (USD)</div><div class="value">$<?= number_format($kpi['refunds'], 2) ?></div></div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-2">
          <div class="card-soft p-3 d-flex align-items-center gap-3">
            <div class="icon-badge"><i class="bi bi-calculator"></i></div>
            <div><div class="label">AOV</div><div class="value">$<?= number_format($kpi['aov'], 2) ?></div></div>
          </div>
        </div>
      </div>

      <!-- Daily Sales Bar Chart + Export -->
      <div class="card-soft p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Daily Sales (USD)</h5>
          <a class="btn btn-outline-secondary btn-sm" href="?<?= e(http_build_query(array_merge($_GET,['export'=>'daily']))) ?>">
            <i class="bi bi-filetype-csv me-1"></i>Export CSV
          </a>
        </div>
        <div class="chart-wrap"><canvas id="dailyChart"></canvas></div>
      </div>

      <!-- Packages Top10 (Qty & Amount) + Export -->
      <div class="card-soft p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0"><i class="bi bi-box-seam me-2 text-success"></i>Packages — Top 10 by Qty & Amount</h5>
          <a class="btn btn-outline-secondary btn-sm" href="?<?= e(http_build_query(array_merge($_GET,['export'=>'packages']))) ?>">
            <i class="bi bi-filetype-csv me-1"></i>Export All Packages (CSV)
          </a>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <div class="text-muted small mb-1">Top 10 by Quantity</div>
            <div class="chart-wrap-tall"><canvas id="pkgQtyChart"></canvas></div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small mb-1">Top 10 by Sales Amount (USD)</div>
            <div class="chart-wrap-tall"><canvas id="pkgAmtChart"></canvas></div>
          </div>
        </div>
      </div>

      <!-- Orders Table + Export -->
      <div class="card-soft">
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
          <h5 class="mb-0"><i class="bi bi-table me-2 text-primary"></i>Orders</h5>
          <a class="btn btn-outline-secondary btn-sm" href="?<?= e(http_build_query(array_merge($_GET,['export'=>'orders']))) ?>">
            <i class="bi bi-filetype-csv me-1"></i>Export Orders (CSV)
          </a>
        </div>
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width:80px;">ID</th>
                <th>Status</th>
                <th class="text-end">Total (USD)</th>
                <th>Customer</th>
                <th>Contact</th>
                <th>Channel</th>
                <th>Bill Code</th>
                <th>Created</th>
              </tr>
            </thead>
            <tbody>
            <?php if (!$orderRows): ?>
              <tr><td colspan="8" class="text-center text-muted py-4">No orders found.</td></tr>
            <?php else: foreach ($orderRows as $r): ?>
              <tr>
                <td>#<?= e($r['id']) ?></td>
                <td>
                  <?php $map=['pending'=>'warning','paid'=>'success','cancelled'=>'danger','refunded'=>'secondary']; $cls=$map[$r['status']] ?? 'light'; ?>
                  <span class="badge bg-<?= e($cls) ?> text-uppercase"><?= e($r['status']) ?></span>
                </td>
                <td class="text-end">$<?= number_format((float)$r['total_usd'], 2) ?></td>
                <td>
                  <div class="fw-semibold truncate" title="<?= e($r['customer_name']) ?>"><?= e($r['customer_name']) ?></div>
                  <div class="text-muted small"><?= e($r['customer_email']) ?></div>
                </td>
                <td class="text-muted small"><?= e($r['customer_phone']) ?></td>
                <td class="text-muted small"><?= e($r['payment_channel'] ?: '-') ?></td>
                <td class="text-monospace small"><?= e($r['bill_code'] ?: '-') ?></td>
                <td class="text-muted small"><?= e($r['created_at']) ?></td>
              </tr>
            <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>

        <?php
          $totalPages = max(1, (int)ceil($totalRows / $pageSize));
          $qs = $_GET; unset($qs['page']); $baseQS = http_build_query($qs);
          $pageLink = function($p,$lbl,$disabled=false,$active=false,$base='') {
            $cls = 'page-item'.($disabled?' disabled':'').($active?' active':'');
            $href = $disabled ? '#' : ('?'.$base.'&page='.$p);
            return '<li class="'.$cls.'"><a class="page-link" href="'.$href.'">'.$lbl.'</a></li>';
          };
        ?>
        <div class="d-flex justify-content-between align-items-center p-3">
          <div class="text-muted small">Showing <strong><?= count($orderRows) ?></strong> of <strong><?= number_format($totalRows) ?></strong> result(s)</div>
          <nav>
            <ul class="pagination mb-0">
              <?= $pageLink(max(1,$page-1),'« Prev',$page<=1,false,$baseQS) ?>
              <?php for ($p=max(1,$page-2); $p<=min($totalPages,$page+2); $p++) echo $pageLink($p,(string)$p,false,$p==$page,$baseQS); ?>
              <?= $pageLink(min($totalPages,$page+1),'Next »',$page>=$totalPages,false,$baseQS) ?>
            </ul>
          </nav>
        </div>
      </div>

      <div class="text-center text-muted small my-4">© <?= date('Y') ?> <?= e(BRAND_NAME) ?> · Sales Report</div>
    </main>
  </div>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
// Daily Sales Bar Chart
(() => {
  const ctx = document.getElementById('dailyChart');
  if (!ctx) return;
  const labels = <?= $jDailyLabels ?>;
  const data   = <?= $jDailySales  ?>;
  new Chart(ctx, {
    type: 'bar',
    data: { labels, datasets: [{ label: 'Sales (USD)', data, backgroundColor: 'rgba(99,102,241,0.85)', borderRadius: 6 }] },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: { ticks: { autoSkip: true, maxRotation: 0, minRotation: 0 }, grid: { display: false } },
        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.06)' } }
      },
      plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
      layout: { padding: 10 }
    }
  });
})();

// Packages Top10 by Quantity (horizontal)
(() => {
  const ctx = document.getElementById('pkgQtyChart');
  if (!ctx) return;
  const labels = <?= $jPkgQtyLabels ?>;
  const data   = <?= $jPkgQtyData   ?>;
  new Chart(ctx, {
    type: 'bar',
    data: { labels, datasets: [{ label: 'Qty Sold', data, backgroundColor: 'rgba(34,197,94,0.85)', borderRadius: 6 }] },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.06)' } },
        y: { ticks: { autoSkip: false }, grid: { display: false } }
      },
      plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
      layout: { padding: 10 }
    }
  });
})();

// Packages Top10 by Amount (horizontal)
(() => {
  const ctx = document.getElementById('pkgAmtChart');
  if (!ctx) return;
  const labels = <?= $jPkgAmtLabels ?>;
  const data   = <?= $jPkgAmtData   ?>;
  new Chart(ctx, {
    type: 'bar',
    data: { labels, datasets: [{ label: 'Sales (USD)', data, backgroundColor: 'rgba(59,130,246,0.85)', borderRadius: 6 }] },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.06)' } },
        y: { ticks: { autoSkip: false }, grid: { display: false } }
      },
      plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
      layout: { padding: 10 }
    }
  });
})();
</script>
</body>
</html>
