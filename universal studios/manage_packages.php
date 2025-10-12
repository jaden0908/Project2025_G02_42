<?php
/* manage_packages.php
 * Admin-only CRUD list page for packages
 * - List all packages with status and price
 * - Add / Edit via Bootstrap modal (POST to package_save.php)
 * - Delete via small POST form (POST to package_delete.php)
 */
session_start();
define('BRAND_NAME', 'Universal Studios');

if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
$role = $_SESSION['user']['role'] ?? 'customer';
if ($role !== 'admin' && $role !== 'staff') {
  header('Location: index.php'); 
  exit;
}


require __DIR__ . '/database.php';

/* ---------- utilities ---------- */
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function nav_active($file){ return basename($_SERVER['PHP_SELF']) === $file ? ' active' : ''; }
function csrf_token(): string {
  if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
  return $_SESSION['csrf_token'];
}

/* ---------- fetch packages ---------- */
$rows = [];
$qq = "SELECT id,title,short_desc,price_usd,status,image_path,created_at,updated_at
       FROM packages ORDER BY created_at DESC";
$res = $conn->query($qq);
if ($res) { while ($r = $res->fetch_assoc()) { $rows[] = $r; } }

/* ---------- flash helper ---------- */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= BRAND_NAME ?> · Manage Packages</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="css/style.css?v=2" rel="stylesheet">
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
       <a class="nav-link<?= nav_active('sales_report.php') ?>" href="sales_report.php"><i class="bi bi-ticket-perforated me-1"></i>Sales Report</a>
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
    <div class="topbar">
      <div class="fw-bold">Manage Packages</div>
      <div>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal"
                onclick="openCreate()">+ Add Package</button>
      </div>
    </div>

    <main class="main">

      <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type'] ?? 'info') ?>"><?= e($flash['msg'] ?? '') ?></div>
      <?php endif; ?>

      <div class="card-soft panel">
        <div class="card-header"><h5><i class="bi bi-ticket-perforated me-2 text-primary"></i>All Packages</h5></div>
        <div class="card-body table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th style="width:56px">#</th>
                <th>Title</th>
                <th>Short Description</th>
                <th class="text-end" style="width:140px">Price (USD)</th>
                <th style="width:120px">Status</th>
                <th class="text-end" style="width:170px">Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
              <tr><td colspan="6" class="text-muted">No packages yet.</td></tr>
            <?php else: foreach ($rows as $i => $r): ?>
              <tr
                data-id="<?= (int)$r['id'] ?>"
                data-title="<?= e($r['title']) ?>"
                data-desc="<?= e($r['short_desc']) ?>"
                data-price="<?= e($r['price_usd']) ?>"
                data-status="<?= e($r['status']) ?>"
              >
                <td class="mono"><?= (int)$r['id'] ?></td>
                <td class="fw-semibold"><?= e($r['title']) ?></td>
                <td class="text-muted"><?= e($r['short_desc']) ?></td>
                <td class="text-end mono">$<?= number_format((float)$r['price_usd'], 2) ?></td>
                <td>
                  <?php if ($r['status']==='active'): ?>
                    <span class="badge bg-success">Active</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Inactive</span>
                  <?php endif; ?>
                </td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-primary me-1" onclick="openEdit(this)">
                    <i class="bi bi-pencil-square"></i> Edit
                  </button>
                  <form action="package_delete.php" method="post" class="d-inline"
                        onsubmit="return confirm('Delete this package?');">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger">
                      <i class="bi bi-trash"></i> Delete
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="text-center text-muted small mt-4">© <?= date('Y') ?> <?= BRAND_NAME ?> · Admin</div>
    </main>
  </div>
</div>

<!-- Edit/Create Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form action="package_save.php" method="post" enctype="multipart/form-data" id="pkgForm">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id" id="f-id" value="">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Add Package</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-7">
              <label class="form-label">Title *</label>
              <input type="text" class="form-control" name="title" id="f-title" required maxlength="120">
            </div>
            <div class="col-md-5">
              <label class="form-label">Price (USD) *</label>
              <input type="number" step="0.01" min="0" class="form-control" name="price_usd" id="f-price" required>
            </div>
            <div class="col-12">
              <label class="form-label">Short Description</label>
              <textarea class="form-control" name="short_desc" id="f-desc" rows="3" maxlength="255"></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Status *</label>
              <select class="form-select" name="status" id="f-status" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Cover Image (optional)</label>
              <input type="file" class="form-control" name="image" accept=".jpg,.jpeg,.png,.webp">
              <div class="form-text">Max 2MB; jpg/png/webp</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary" type="submit"><i class="bi bi-save me-1"></i>Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let modal;
document.addEventListener('DOMContentLoaded', () => {
  modal = new bootstrap.Modal(document.getElementById('editModal'));
});

function openCreate(){
  document.getElementById('modalTitle').innerText = 'Add Package';
  document.getElementById('f-id').value = '';
  document.getElementById('f-title').value = '';
  document.getElementById('f-desc').value = '';
  document.getElementById('f-price').value = '';
  document.getElementById('f-status').value = 'active';
}

function openEdit(btn){
  const tr = btn.closest('tr');
  document.getElementById('modalTitle').innerText = 'Edit Package';
  document.getElementById('f-id').value    = tr.dataset.id;
  document.getElementById('f-title').value = tr.dataset.title;
  document.getElementById('f-desc').value  = tr.dataset.desc;
  document.getElementById('f-price').value = tr.dataset.price;
  document.getElementById('f-status').value= tr.dataset.status;
  modal.show();
}
</script>
</body>
</html>
