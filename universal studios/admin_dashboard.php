<?php
session_start();
define('BRAND_NAME', 'Universal Studios');

if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
$role = $_SESSION['user']['role'] ?? 'customer';
if ($role !== 'admin') {
  if ($role === 'staff') { header('Location: staff_dashboard.php'); exit; }
  header('Location: index.php'); exit;
}

require __DIR__ . '/database.php';
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function nav_active($file){
  return basename($_SERVER['PHP_SELF']) === $file ? ' active' : '';
}
/* --------- Stats --------- */
$byRole = ['admin'=>0,'staff'=>0,'customer'=>0];
$res = $conn->query("SELECT role, COUNT(*) c FROM users GROUP BY role");
if ($res) { while ($r = $res->fetch_assoc()) { $byRole[$r['role']] = (int)$r['c']; } }
$verifiedTotal   = (int)$conn->query("SELECT COUNT(*) FROM users WHERE is_verified=1")->fetch_row()[0];
$unverifiedTotal = (int)$conn->query("SELECT COUNT(*) FROM users WHERE is_verified=0")->fetch_row()[0];

$recent = [];
$res2 = $conn->query("SELECT name,email,role,is_verified,created_at FROM users ORDER BY created_at DESC LIMIT 8");
if ($res2) { while ($row = $res2->fetch_assoc()) { $recent[] = $row; } }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= BRAND_NAME ?> · Admin Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Libs -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Page styles (all in one file as requested) -->
  <style>
    :root{
      --bg:#f3f5f9; --ink:#0f172a; --muted:#6b7280;
      --sidebar:#1f283e; --sidebar-accent:#7c4dff; --sidebar-hover:#2a3553;
      --card:#ffffff; --line:#e7eaf0; --shadow:0 8px 24px rgba(15,23,42,.08);
      --c-cyan:#22d3ee; --c-green:#22c55e; --c-orange:#fb923c; --c-red:#ef4444; --c-purple:#8b5cf6; --c-blue:#3b82f6;
    }
    *{box-sizing:border-box}
    body{background:var(--bg); color:var(--ink); font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;}
    a{text-decoration:none}

    /* Shell */
    .layout{display:grid; grid-template-columns: 260px 1fr; min-height:100vh;}
    .sidebar{
      position:sticky; top:0; align-self:start; height:100vh; overflow-y:auto;
      background:var(--sidebar); color:#cbd5e1; padding:18px 14px; box-shadow: inset -1px 0 0 #172036;
    }
    .brand{display:flex; align-items:center; gap:.6rem; color:#fff; font-weight:800; letter-spacing:.3px; margin-bottom:18px}
    .brand i{color:var(--sidebar-accent)}
    .side-search{position:relative; margin-bottom:12px}
    .side-search input{background:#121a2e; border:1px solid #263055; color:#cbd5e1; border-radius:10px; padding:.55rem .9rem; width:100%}
    .nav-sec{margin-top:8px}
    .nav-title{font-size:.75rem; color:#93a4c7; letter-spacing:.08em; text-transform:uppercase; margin:14px 10px 6px;}
    .nav-link{
      display:flex; align-items:center; gap:.6rem; color:#cbd5e1; padding:.6rem .75rem; border-radius:10px; transition:.15s;
    }
    .nav-link i{opacity:.9}
    .nav-link:hover{background:var(--sidebar-hover)}
    .nav-link.active{background:linear-gradient(90deg, rgba(124,77,255,.25), transparent); color:#fff; box-shadow: inset 3px 0 0 var(--sidebar-accent);}
    .nav-link.disabled{opacity:.45; cursor:not-allowed}

    /* Topbar */
    .topbar{display:flex; align-items:center; justify-content:space-between; padding:14px 24px; background:#fff; border-bottom:1px solid var(--line); box-shadow:var(--shadow)}
    .top-right{display:flex; align-items:center; gap:.5rem; color:var(--muted)}
    .top-right .btn{border-radius:10px}

    /* Main area */
    .main{padding:24px}
    .card-soft{background:var(--card); border:1px solid var(--line); border-radius:16px; box-shadow:var(--shadow)}
    .kpi{display:flex; align-items:center; gap:.9rem; padding:1rem}
    .kpi .icon{width:48px; height:48px; border-radius:12px; display:grid; place-items:center; color:#fff; font-size:1.1rem}
    .kpi .meta .label{color:var(--muted)}
    .kpi .meta .value{font-size:1.6rem; font-weight:800}

    .badge-pulse{position:relative}
    .badge-pulse::after{
      content:""; position:absolute; inset:-2px; border-radius:999px; border:2px solid currentColor; opacity:.25; animation:pulse 1.6s infinite;
    }
    @keyframes pulse{0%{transform:scale(.9);opacity:.25}70%{transform:scale(1.2);opacity:0}100%{opacity:0}}

    .panel .card-header{background:#fff; border-bottom:1px solid var(--line); padding:.85rem 1rem}
    .panel .card-header h5{margin:0; font-weight:800}
    .panel .card-body{padding:1rem}

    .btn-pill{border-radius:12px}
    .btn-outline-slate{border:1px solid #cdd5e1; color:var(--ink)}
    .btn-outline-slate:hover{background:#f8fafc}

    /* table */
    .table thead th{color:#64748b; font-weight:700; border-bottom:1px solid var(--line)}
    .table tbody td{vertical-align:middle}
    .mono{font-family:ui-monospace, SFMono-Regular, Menlo, Consolas, monospace}

    /* colors */
    .bg-cyan{background:linear-gradient(180deg, #22d3ee, #06b6d4)}
    .bg-green{background:linear-gradient(180deg, #22c55e, #16a34a)}
    .bg-blue{background:linear-gradient(180deg, #60a5fa, #3b82f6)}
    .bg-purple{background:linear-gradient(180deg, #a78bfa, #8b5cf6)}
    .bg-orange{background:linear-gradient(180deg, #fb923c, #f97316)}
    .bg-red{background:linear-gradient(180deg, #f87171, #ef4444)}
    .shadow-2{box-shadow:0 10px 26px rgba(15,23,42,.10)}

    /* Responsive */
    @media (max-width: 992px){
      .layout{grid-template-columns: 1fr}
      .sidebar{position:relative; height:auto; border-bottom:1px solid #172036; border-right:none}
    }
  </style>
</head>
<body>

<div class="layout">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="brand">
      <i class="bi bi-film"></i><span><?= BRAND_NAME ?></span>
    </div>
    

    <div class="nav-sec">
      <div class="nav-title">Main</div>
      <a class="nav-link<?= nav_active('admin_dashboard.php') ?>" href="admin_dashboard.php">
    <i class="bi bi-speedometer2"></i>Dashboard
  </a>

  <a class="nav-link<?= nav_active('manage_staff.php') ?>" href="manage_staff.php">
    <i class="bi bi-person-badge"></i>Manage Staff
  </a>

    <a class="nav-link<?= nav_active('manage_customers.php') ?>" href="manage_customers.php">
    <i class="bi bi-person-badge"></i>Manage Customer
  </a>

  <a class="nav-link disabled" title="Coming soon">
    <i class="bi bi-ticket-perforated"></i>Manage Packages
  </a>

  <a class="nav-link disabled" title="Coming soon">
    <i class="bi bi-graph-up"></i>Sales Reports
  </a>

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
    
    </div>

    <!-- Main -->
    <main class="main">
      <!-- KPI Row -->
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

     

      <!-- Recent Signups -->
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

      <div class="text-center text-muted small mt-4">© <?= date('Y') ?> <?= BRAND_NAME ?> · Admin Dashboard</div>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
