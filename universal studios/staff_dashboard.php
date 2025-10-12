<?php
// staff_dashboard.php
// Staff dashboard page mirroring the admin layout but with staff-specific navigation.

session_start();
define('BRAND_NAME', 'Universal Studios');

// ---- Access control: only staff can view this page ----
if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
$role = $_SESSION['user']['role'] ?? 'customer';
if ($role !== 'staff') {
  if ($role === 'admin') { header('Location: admin_dashboard.php'); exit; }
  header('Location: index.php'); exit;
}

// ---- DB + helpers ----
require __DIR__ . '/database.php';
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function nav_active($file){ return basename($_SERVER['PHP_SELF']) === $file ? ' active' : ''; }

/* --------- Stats (same structure as admin for a consistent look) --------- */
// Count users by role
$byRole = ['admin'=>0,'staff'=>0,'customer'=>0];
$res = $conn->query("SELECT role, COUNT(*) c FROM users GROUP BY role");
if ($res) { while ($r = $res->fetch_assoc()) { $byRole[$r['role']] = (int)$r['c']; } }

// Global verifications (all users)
$verifiedTotal   = (int)$conn->query("SELECT COUNT(*) FROM users WHERE is_verified=1")->fetch_row()[0];
$unverifiedTotal = (int)$conn->query("SELECT COUNT(*) FROM users WHERE is_verified=0")->fetch_row()[0];

// Recent signups (all users like admin page)
$recent = [];
$res2 = $conn->query("SELECT name,email,role,is_verified,created_at 
                      FROM users 
                      WHERE role IN ('staff','customer') 
                      ORDER BY created_at DESC 
                      LIMIT 8");

if ($res2) { while ($row = $res2->fetch_assoc()) { $recent[] = $row; } }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= BRAND_NAME ?> · Staff Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Libs -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Your global styles -->
  <link href="css/style.css?v=2" rel="stylesheet">

  <script src="https://kit.fontawesome.com/351048854e.js" crossorigin="anonymous"></script>
</head>

<body>

<div class="layout">
  <!-- Sidebar (STAFF VERSION) -->
  <aside class="sidebar">
    <div class="brand mb-3"><i class="bi bi-film me-2"></i><?= BRAND_NAME ?></div>

    <div class="nav-sec mb-3">
      <div class="nav-title text-muted small mb-1">Main</div>

      <!-- Dashboard -->
      <a class="nav-link<?= nav_active('staff_dashboard.php') ?>" href="staff_dashboard.php">
        <i class="bi bi-speedometer2 me-1"></i>Dashboard
      </a>

      <!-- Staff functions -->
      <a class="nav-link" href="package.php"><i class="bi bi-card-list me-1"></i>View Packages</a>
      <a class="nav-link<?= nav_active('staff_managepackage.php') ?>" href="staff_managepackage.php"><i class="bi bi-ticket-perforated me-1"></i>Manage Packages</a>
      <a class="nav-link<?= nav_active('staff_manage_customers.php') ?>" href="staff_manage_customers.php"><i class="bi bi-people me-1"></i>View Customers</a>
      <a class="nav-link<?= nav_active('staff_view_feedback.php') ?>" href="staff_view_feedback.php"><i class="bi bi-chat-left-text me-1"></i>View Feedback</a>

      <!-- Sales Report (this page) -->
      <a class="nav-link<?= nav_active('staff_sales_report.php') ?>" href="staff_sales_report.php">
        <i class="bi bi-graph-up me-1"></i>Sales Report
      </a>
    </div>

    <div class="nav-sec">
      <div class="nav-title text-muted small mb-1">Account</div>
      <a class="nav-link" href="profile.php"><i class="bi bi-person me-1"></i>My Profile</a>
      <a class="nav-link" href="index.php"><i class="bi bi-house-door me-1"></i>Back to Home</a>
      <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right me-1"></i>Sign Out</a>
    </div>
  </aside>

  <!-- Content -->
  <div>
    <!-- Topbar -->
    <div class="topbar">
      <div class="fw-bold">Material Dashboard</div>
    </div>

    <!-- Main -->
    <main class="main">
      <!-- KPI Row (same card structure as admin for visual parity) -->
      <div class="row g-3">
        <div class="col-sm-6 col-lg-3">
          <div class="card-soft kpi shadow-2">
            <div class="icon bg-cyan"><i class="bi bi-people"></i></div>
            <div class="meta">
              <div class="label">Customers</div>
              <div class="value"><?= number_format($byRole['customer']) ?></div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card-soft kpi shadow-2">
            <div class="icon bg-purple"><i class="bi bi-person-badge"></i></div>
            <div class="meta">
              <div class="label">Staff</div>
              <div class="value"><?= number_format($byRole['staff']) ?></div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card-soft kpi shadow-2">
            <div class="icon bg-blue"><i class="bi bi-shield-lock"></i></div>
            <div class="meta">
              <div class="label">Admins</div>
              <div class="value"><?= number_format($byRole['admin']) ?></div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card-soft kpi shadow-2">
            <div class="icon bg-green"><i class="bi bi-shield-check"></i></div>
            <div class="meta">
              <div class="label">Verified</div>
              <div class="value"><?= number_format($verifiedTotal) ?></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Signups (same table as admin) -->
      <div class="card-soft panel mt-3">
        <div class="card-header"><h5><i class="bi bi-clock-history me-2 text-primary"></i>Recent Signups</h5></div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table align-middle">
              <thead>
              <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th class="text-end">Joined</th></tr>
              </thead>
              <tbody>
              <?php if (!$recent): ?>
                <tr><td colspan="5" class="text-muted">No data</td></tr>
              <?php else: foreach ($recent as $r): ?>
                <tr>
                  <td class="fw-semibold"><?= e($r['name']) ?></td>
                  <td class="text-muted"><?= e($r['email']) ?></td>
                  <td class="text-uppercase"><?= e($r['role']) ?></td>
                  <td>
                    <?php if ((int)$r['is_verified'] === 1): ?>
                      <span class="badge bg-success"><i class="bi bi-shield-check me-1"></i>Verified</span>
                    <?php else: ?>
                      <span class="badge bg-secondary"><i class="bi bi-shield me-1"></i>Unverified</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end mono"><?= e($r['created_at']) ?></td>
                </tr>
              <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="text-center text-muted small mt-4">© <?= date('Y') ?> <?= BRAND_NAME ?> · Staff Dashboard</div>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
