<?php
session_start();
define('BRAND_NAME', 'Universal Studios');

if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
$role = $_SESSION['user']['role'] ?? 'customer';
if ($role !== 'staff') {
  if ($role === 'admin') { header('Location: admin_dashboard.php'); exit; }
  header('Location: index.php'); exit;
}

require __DIR__ . '/database.php';
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/* --------- Stats (customers only) --------- */
$totCustomers = (int)$conn->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetch_row()[0];
$verCustomers = (int)$conn->query("SELECT COUNT(*) FROM users WHERE role='customer' AND is_verified=1")->fetch_row()[0];
$pendingVer   = max(0, $totCustomers - $verCustomers);

/* --------- Recent customers --------- */
$recent = [];
$res = $conn->query("SELECT name,email,is_verified,created_at FROM users WHERE role='customer' ORDER BY created_at DESC LIMIT 5");
if ($res) { while ($row = $res->fetch_assoc()) { $recent[] = $row; } }
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

  <!-- Page styles (all-in-one) -->
  <style>
    :root{
      --bg:#f3f5f9; --ink:#0f172a; --muted:#6b7280;
      --sidebar:#1f283e; --sidebar-accent:#7c4dff; --sidebar-hover:#2a3553;
      --card:#ffffff; --line:#e7eaf0; --shadow:0 8px 24px rgba(15,23,42,.08);
      --c-cyan:#22d3ee; --c-green:#22c55e; --c-orange:#fb923c; --c-yellow:#f59e0b;
      --c-purple:#8b5cf6;
    }
    *{box-sizing:border-box}
    body{background:var(--bg); color:var(--ink); font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif}
    a{text-decoration:none}

    /* Shell */
    .layout{display:grid; grid-template-columns:260px 1fr; min-height:100vh}
    .sidebar{position:sticky; top:0; align-self:start; height:100vh; overflow-y:auto; background:var(--sidebar); color:#cbd5e1; padding:18px 14px; box-shadow:inset -1px 0 0 #172036}
    .brand{display:flex; align-items:center; gap:.6rem; color:#fff; font-weight:800; letter-spacing:.3px; margin-bottom:18px}
    .brand i{color:var(--sidebar-accent)}
    .side-search{position:relative; margin-bottom:12px}
    .side-search input{background:#121a2e; border:1px solid #263055; color:#cbd5e1; border-radius:10px; padding:.55rem .9rem; width:100%}
    .nav-sec{margin-top:8px}
    .nav-title{font-size:.75rem; color:#93a4c7; letter-spacing:.08em; text-transform:uppercase; margin:14px 10px 6px}
    .nav-link{display:flex; align-items:center; gap:.6rem; color:#cbd5e1; padding:.6rem .75rem; border-radius:10px; transition:.15s}
    .nav-link:hover{background:var(--sidebar-hover)}
    .nav-link.active{background:linear-gradient(90deg, rgba(124,77,255,.25), transparent); color:#fff; box-shadow:inset 3px 0 0 var(--sidebar-accent)}
    .nav-link.disabled{opacity:.45; cursor:not-allowed}

    /* Topbar */
    .topbar{display:flex; align-items:center; justify-content:space-between; padding:14px 24px; background:#fff; border-bottom:1px solid var(--line); box-shadow:var(--shadow)}
    .top-right{display:flex; align-items:center; gap:.5rem; color:var(--muted)}
    .top-right .btn{border-radius:10px}

    /* Main */
    .main{padding:24px}
    .card-soft{background:var(--card); border:1px solid var(--line); border-radius:16px; box-shadow:var(--shadow)}
    .kpi{display:flex; align-items:center; gap:.9rem; padding:1rem}
    .kpi .icon{width:48px; height:48px; border-radius:12px; display:grid; place-items:center; color:#fff; font-size:1.1rem}
    .kpi .meta .label{color:var(--muted)}
    .kpi .meta .value{font-size:1.6rem; font-weight:800}
    .shadow-2{box-shadow:0 10px 26px rgba(15,23,42,.10)}

    .panel .card-header{background:#fff; border-bottom:1px solid var(--line); padding:.85rem 1rem}
    .panel .card-header h5{margin:0; font-weight:800}
    .panel .card-body{padding:1rem}

    .btn-pill{border-radius:12px}
    .btn-outline-slate{border:1px solid #cdd5e1; color:var(--ink)}
    .btn-outline-slate:hover{background:#f8fafc}

    /* Table */
    .table thead th{color:#64748b; font-weight:700; border-bottom:1px solid var(--line)}
    .table tbody td{vertical-align:middle}
    .mono{font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace}

    /* Colors */
    .bg-cyan{background:linear-gradient(180deg,#22d3ee,#06b6d4)}
    .bg-green{background:linear-gradient(180deg,#22c55e,#16a34a)}
    .bg-yellow{background:linear-gradient(180deg,#fbbf24,#f59e0b)}
    .bg-purple{background:linear-gradient(180deg,#a78bfa,#8b5cf6)}
  </style>
</head>
<body>

<div class="layout">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="brand"><i class="bi bi-film"></i><span><?= BRAND_NAME ?></span></div>
    <div class="side-search"><input type="text" placeholder="Search…"></div>

    <div class="nav-sec">
      <div class="nav-title">Main</div>
      <a class="nav-link active" href="staff_dashboard.php"><i class="bi bi-speedometer2"></i>Dashboard</a>
      <a class="nav-link" href="package.php"><i class="bi bi-ticket-perforated"></i>View Packages</a>
      <a class="nav-link disabled" title="Coming soon"><i class="bi bi-plus-square"></i>Add Package</a>
      <a class="nav-link disabled" title="Coming soon"><i class="bi bi-pencil-square"></i>Edit/Delete Package</a>
      <a class="nav-link disabled" title="Coming soon"><i class="bi bi-people"></i>Customers</a>
    </div>

    <div class="nav-sec">
      <div class="nav-title">Account</div>
      <a class="nav-link" href="profile.php"><i class="bi bi-person"></i>My Profile</a>
      <a class="nav-link" href="index.php"><i class="bi bi-house-door"></i>Back to Home</a>
      <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i>Sign Out</a>
    </div>
  </aside>

  <!-- Content -->
  <div>
    <!-- Topbar -->
    <div class="topbar">
      <div class="fw-bold">Material Dashboard</div>
      <div class="top-right">
        <span class="text-muted d-none d-sm-inline">Signed in as <strong><?= e($_SESSION['user']['name']) ?></strong></span>
        <a href="logout.php" class="btn btn-outline-slate btn-sm btn-pill"><i class="bi bi-box-arrow-right me-1"></i>Sign Out</a>
      </div>
    </div>

    <!-- Main -->
    <main class="main">
      <!-- KPI Row -->
      <div class="row g-3">
        <div class="col-sm-6 col-lg-4">
          <div class="card-soft kpi shadow-2">
            <div class="icon bg-cyan"><i class="bi bi-people"></i></div>
            <div class="meta">
              <div class="label">Total Customers</div>
              <div class="value"><?= number_format($totCustomers) ?></div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-4">
          <div class="card-soft kpi shadow-2">
            <div class="icon bg-green"><i class="bi bi-shield-check"></i></div>
            <div class="meta">
              <div class="label">Verified</div>
              <div class="value"><?= number_format($verCustomers) ?></div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-4">
          <div class="card-soft kpi shadow-2">
            <div class="icon bg-yellow"><i class="bi bi-hourglass-split"></i></div>
            <div class="meta">
              <div class="label">Pending Verify</div>
              <div class="value"><?= number_format($pendingVer) ?></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="row g-3 mt-1">
        <div class="col-lg-8">
          <div class="card-soft panel">
            <div class="card-header"><h5><i class="bi bi-lightning-charge me-2 text-warning"></i>Quick Actions</h5></div>
            <div class="card-body d-flex flex-wrap gap-2">
              <a href="package.php" class="btn btn-primary btn-pill"><i class="bi bi-ticket-perforated me-1"></i>View Packages</a>
              <a class="btn btn-outline-slate btn-pill disabled" title="Coming soon"><i class="bi bi-plus-square me-1"></i>Add Package</a>
              <a class="btn btn-outline-slate btn-pill disabled" title="Coming soon"><i class="bi bi-people me-1"></i>View Customers</a>
              <a href="profile.php" class="btn btn-outline-slate btn-pill"><i class="bi bi-person me-1"></i>My Profile</a>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card-soft p-3 d-flex align-items-center justify-content-between">
            <div>
              <div class="fw-bold">Today’s Focus</div>
              <div class="text-muted small">Verify pending customers & assist sales</div>
            </div>
            <span class="badge bg-warning text-dark"><?= number_format($pendingVer) ?> pending</span>
          </div>
        </div>
      </div>

      <!-- Recent Customers -->
      <div class="card-soft panel mt-3">
        <div class="card-header"><h5><i class="bi bi-clock-history me-2 text-primary"></i>Recent Customers</h5></div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table align-middle">
              <thead>
                <tr><th>Name</th><th>Email</th><th>Status</th><th class="text-end">Joined</th></tr>
              </thead>
              <tbody>
                <?php if (!$recent): ?>
                  <tr><td colspan="4" class="text-muted">No data</td></tr>
                <?php else: foreach ($recent as $r): ?>
                  <tr>
                    <td class="fw-semibold"><?= e($r['name']) ?></td>
                    <td class="text-muted"><?= e($r['email']) ?></td>
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
