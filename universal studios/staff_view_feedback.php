<?php
/* ===========================================================
   View Feedback (read-only) - STAFF VERSION
   - List / Search / Filter / Paginate feedbacks
   - Export CSV
   =========================================================== */

session_start();
define('BRAND_NAME', 'Universal Studios');

/* ---------- Access control: staff only ---------- */
if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
$role = $_SESSION['user']['role'] ?? 'customer';
if ($role !== 'staff') {
  if ($role === 'admin') { header('Location: view_feedback.php'); exit; }
  header('Location: index.php'); exit;
}

/* ---------- DB + helpers ---------- */
require __DIR__ . '/database.php';
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function nav_active($file){ return basename($_SERVER['PHP_SELF']) === $file ? ' active' : ''; }

/* ---------- Filters & pagination ---------- */
$q        = trim($_GET['q'] ?? '');
$rating   = isset($_GET['rating']) && $_GET['rating'] !== '' ? (int)$_GET['rating'] : '';
$roleSnap = trim($_GET['role'] ?? '');
$pkg      = trim($_GET['package'] ?? '');
$from     = trim($_GET['from'] ?? '');
$to       = trim($_GET['to'] ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$PER_PAGE = 10;
$offset   = ($page - 1) * $PER_PAGE;

/* ---------- Build WHERE clause ---------- */
$where  = "1=1";
$types  = '';
$params = [];

/* Keyword filter */
if ($q !== '') {
  $where .= " AND (message LIKE CONCAT('%',?,'%') OR guest_name LIKE CONCAT('%',?,'%') OR guest_email LIKE CONCAT('%',?,'%'))";
  $types .= 'sss';
  array_push($params, $q, $q, $q);
}

/* Rating filter */
if ($rating !== '') {
  $where .= " AND rating = ?";
  $types .= 'i';
  $params[] = $rating;
}

/* Role snapshot filter */
if ($roleSnap !== '') {
  $where .= " AND role_snapshot = ?";
  $types .= 's';
  $params[] = $roleSnap;
}

/* Package filter */
if ($pkg !== '') {
  $where .= " AND package_name LIKE CONCAT('%',?,'%')";
  $types .= 's';
  $params[] = $pkg;
}

/* Date range filter */
if ($from !== '') {
  $where .= " AND DATE(created_at) >= ?";
  $types .= 's';
  $params[] = $from;
}
if ($to !== '') {
  $where .= " AND DATE(created_at) <= ?";
  $types .= 's';
  $params[] = $to;
}

/* ---------- KPI statistics ---------- */
$kpi = ['total'=>0,'avg'=>0,'r5'=>0,'r4'=>0,'r3'=>0,'r2'=>0,'r1'=>0];
$sqlKpi = "SELECT 
             COUNT(*) AS total,
             COALESCE(AVG(rating),0) AS avg_rating,
             SUM(CASE WHEN rating=5 THEN 1 ELSE 0 END) AS r5,
             SUM(CASE WHEN rating=4 THEN 1 ELSE 0 END) AS r4,
             SUM(CASE WHEN rating=3 THEN 1 ELSE 0 END) AS r3,
             SUM(CASE WHEN rating=2 THEN 1 ELSE 0 END) AS r2,
             SUM(CASE WHEN rating=1 THEN 1 ELSE 0 END) AS r1
           FROM feedbacks
           WHERE $where";
$stmt = $conn->prepare($sqlKpi);
if ($types) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$stmt->bind_result($kpi['total'], $kpi['avg'], $kpi['r5'], $kpi['r4'], $kpi['r3'], $kpi['r2'], $kpi['r1']);
$stmt->fetch();
$stmt->close();

/* ---------- Count for pagination ---------- */
$sqlCount = "SELECT COUNT(*) FROM feedbacks WHERE $where";
$stmt = $conn->prepare($sqlCount);
if ($types) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();

/* ---------- Fetch page rows ---------- */
$sqlList = "SELECT id,user_id,role_snapshot,package_name,rating,message,guest_name,guest_email,created_at
            FROM feedbacks
            WHERE $where
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sqlList);
if ($types) {
  $types2 = $types . 'ii';
  $params2 = array_merge($params, [$PER_PAGE, $offset]);
  $stmt->bind_param($types2, ...$params2);
} else {
  $stmt->bind_param('ii', $PER_PAGE, $offset);
}
$stmt->execute();
$res = $stmt->get_result();
$rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

/* ---------- Export CSV (stay on staff page!) ---------- */
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=feedbacks.csv');
  $out = fopen('php://output', 'w');
  fputcsv($out, ['id','user_id','role_snapshot','package_name','rating','message','guest_name','guest_email','created_at']);

  $sqlAll = "SELECT id,user_id,role_snapshot,package_name,rating,message,guest_name,guest_email,created_at
             FROM feedbacks WHERE $where ORDER BY created_at DESC";
  $stmt = $conn->prepare($sqlAll);
  if ($types) { $stmt->bind_param($types, ...$params); }
  $stmt->execute();
  $r = $stmt->get_result();
  while ($row = $r->fetch_assoc()) {
    fputcsv($out, $row);
  }
  fclose($out);
  exit;
}

/* Pagination calculation */
$total_pages = max(1, (int)ceil(($total ?? 0) / $PER_PAGE));
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= BRAND_NAME ?> · Staff · View Feedback</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="css/style.css?v=3" rel="stylesheet">
  <script src="https://kit.fontawesome.com/351048854e.js" crossorigin="anonymous"></script>
</head>
<body>
<div class="layout">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="brand mb-3"><i class="fa-solid fa-globe"></i><?= BRAND_NAME ?></div>
    <div class="nav-sec mb-3">
      <a class="nav-link<?= nav_active('staff_dashboard.php') ?>" href="staff_dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
      <a class="nav-link" href="package.php"><i class="bi bi-card-list me-1"></i>View Packages</a>
      <a class="nav-link<?= nav_active('staff_managepackage.php') ?>" href="staff_managepackage.php"><i class="bi bi-ticket-perforated me-1"></i>Manage Packages</a>
      <a class="nav-link<?= nav_active('staff_manage_customers.php') ?>" href="staff_manage_customers.php"><i class="bi bi-people me-1"></i>View Customers</a>
      <a class="nav-link<?= nav_active('staff_view_feedback.php') ?>" href="staff_view_feedback.php"><i class="bi bi-chat-left-text me-1"></i>View Feedback</a>
    </div>
    <div class="nav-sec">
      <div class="nav-title text-muted small mb-1">Account</div>
      <a class="nav-link" href="profile.php"><i class="bi bi-person me-1"></i>My Profile</a>
      <a class="nav-link" href="index.php"><i class="bi bi-house-door me-1"></i>Back to Home</a>
      <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right me-1"></i>Sign Out</a>
    </div>
  </aside>

  <!-- Main -->
  <main class="main">
    <h3 class="mb-3"><i class="bi bi-chat-left-text me-2"></i>Feedbacks</h3>

    <!-- KPI row -->
    <div class="row g-3 mb-3">
      <div class="col-md-3"><div class="card-soft p-3"><div class="fw-bold">Total</div><div class="fs-4"><?= $kpi['total'] ?></div></div></div>
      <div class="col-md-3"><div class="card-soft p-3"><div class="fw-bold">Average Rating</div><div class="fs-4"><?= number_format($kpi['avg'],1) ?></div></div></div>
      <div class="col-md-6"><div class="card-soft p-3"><div class="fw-bold">Breakdown</div>
        <span class="badge bg-success">5★ <?= $kpi['r5'] ?></span>
        <span class="badge bg-primary">4★ <?= $kpi['r4'] ?></span>
        <span class="badge bg-info text-dark">3★ <?= $kpi['r3'] ?></span>
        <span class="badge bg-warning text-dark">2★ <?= $kpi['r2'] ?></span>
        <span class="badge bg-danger">1★ <?= $kpi['r1'] ?></span>
      </div></div>
    </div>

    <!-- Filter/search form -->
    <form class="row g-2 mb-3" method="get" action="staff_view_feedback.php">
      <div class="col-md-3"><input class="form-control" type="text" name="q" value="<?= e($q) ?>" placeholder="Search..."></div>
      <div class="col-md-2">
        <select class="form-select" name="rating">
          <option value="">Any Rating</option>
          <?php for($i=5;$i>=1;$i--): ?>
            <option value="<?= $i ?>" <?= $rating===$i?'selected':'' ?>><?= $i ?> stars</option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-md-2"><input class="form-control" type="text" name="role" value="<?= e($roleSnap) ?>" placeholder="Role"></div>
      <div class="col-md-2"><input class="form-control" type="text" name="package" value="<?= e($pkg) ?>" placeholder="Package"></div>
      <div class="col-md-2"><input class="form-control" type="date" name="from" value="<?= e($from) ?>"></div>
      <div class="col-md-2"><input class="form-control" type="date" name="to" value="<?= e($to) ?>"></div>
      <div class="col-md-12 text-end">
        <!-- FIX: export stays on staff page and preserves filters -->
        <button class="btn btn-primary btn-pill"><i class="bi bi-search me-1"></i>Search</button>
        <a
          class="btn btn-outline-secondary btn-pill"
          href="staff_view_feedback.php?export=csv&q=<?= urlencode($q) ?>&rating=<?= urlencode($rating) ?>&role=<?= urlencode($roleSnap) ?>&package=<?= urlencode($pkg) ?>&from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>"
        ><i class="bi bi-download me-1"></i>Export CSV</a>
      </div>
    </form>

    <!-- Feedback table -->
    <div class="card-soft p-3">
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr><th>#</th><th>Guest</th><th>Email</th><th>Role</th><th>Package</th><th>Rating</th><th>Message</th><th class="text-end">Date</th></tr>
          </thead>
          <tbody>
          <?php if (!$rows): ?>
            <tr><td colspan="8" class="text-muted">No feedback found.</td></tr>
          <?php else: foreach($rows as $i=>$r): ?>
            <tr>
              <td class="mono"><?= $offset+$i+1 ?></td>
              <td><?= e($r['guest_name']) ?></td>
              <td class="text-muted"><?= e($r['guest_email']) ?></td>
              <td class="text-uppercase"><?= e($r['role_snapshot']) ?></td>
              <td><?= e($r['package_name']) ?></td>
              <td><?= str_repeat('★',(int)$r['rating']) ?></td>
              <td><?= e($r['message']) ?></td>
              <td class="text-end mono"><?= e($r['created_at']) ?></td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <nav>
        <ul class="pagination pagination-sm justify-content-end">
          <?php
          // FIX: pagination base stays on staff page and preserves filters
          $base = 'staff_view_feedback.php?q='.urlencode($q)
                .'&rating='.urlencode($rating)
                .'&role='.urlencode($roleSnap)
                .'&package='.urlencode($pkg)
                .'&from='.urlencode($from)
                .'&to='.urlencode($to)
                .'&page=';
          for($p=1;$p<=$total_pages;$p++): ?>
            <li class="page-item <?= $p===$page?'active':'' ?>"><a class="page-link" href="<?= $base.$p ?>"><?= $p ?></a></li>
          <?php endfor; ?>
        </ul>
      </nav>
    </div>

    <div class="text-center text-muted small mt-4">© <?= date('Y') ?> <?= BRAND_NAME ?> · Staff · View Feedback</div>
  </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
