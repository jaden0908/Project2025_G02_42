<?php
session_start();
define('BRAND_NAME', 'Universal Studios');

// ---------- Access Control: staff only ----------
if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
$role = $_SESSION['user']['role'] ?? 'customer';
if ($role !== 'staff') {
  // If admin lands here by mistake, redirect to the admin version
  if ($role === 'admin') { header('Location: manage_customers.php'); exit; }
  header('Location: index.php'); exit;
}

/* ---------- DB + helpers ---------- */
require __DIR__ . '/database.php';
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function nav_active($file){ return basename($_SERVER['PHP_SELF']) === $file ? ' active' : ''; }

/* ---------- Ensure deleted_at exists (one-time) ---------- */
$colCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'deleted_at'");
if ($colCheck && $colCheck->num_rows === 0) {
  $conn->query("ALTER TABLE users ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL");
}

/* ---------- CSRF ---------- */
if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }
$CSRF = $_SESSION['csrf'];
function check_csrf() {
  if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) {
    http_response_code(403);
    die('Invalid CSRF token');
  }
}

/* ---------- Routing & constants ---------- */
$action   = $_GET['action'] ?? 'list';
$PER_PAGE = 10;

/* ===========================================================
   UPDATE CUSTOMER
   =========================================================== */
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();

  $id    = (int)($_POST['id'] ?? 0);
  $name  = trim($_POST['name'] ?? '');
  $email = strtolower(trim($_POST['email'] ?? '')); // normalize
  $is_verified = isset($_POST['is_verified']) ? 1 : 0;

  if ($id <= 0) { http_response_code(400); die('Bad request'); }

  $errors = [];
  if ($name === '') $errors[] = 'Name is required.';
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';

  // Unique email (exclude self)
  $stmt = $conn->prepare("SELECT id FROM users WHERE LOWER(email)=? AND id<>? LIMIT 1");
  $stmt->bind_param('si', $email, $id);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows > 0) $errors[] = 'Email already in use by another account.';
  $stmt->close();

  if ($errors) {
    $_SESSION['flash_error'] = implode(' ', $errors);
    header('Location: staff_manage_customers.php?action=edit&id='.$id); exit;
  }

  $stmt = $conn->prepare("UPDATE users SET name=?, email=?, is_verified=? WHERE id=? AND role='customer'");
  $stmt->bind_param('ssii', $name, $email, $is_verified, $id);
  $stmt->execute();
  $stmt->close();

  $_SESSION['flash_ok'] = 'Customer updated.';
  header('Location: staff_manage_customers.php'); exit;
}

/* ===========================================================
   SOFT DELETE / RESTORE
   =========================================================== */
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();
  $id = (int)($_POST['id'] ?? 0);
  if ($id <= 0) { http_response_code(400); die('Bad request'); }

  $stmt = $conn->prepare("UPDATE users SET deleted_at=NOW() WHERE id=? AND role='customer'");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $stmt->close();

  $_SESSION['flash_ok'] = 'Customer archived.';
  header('Location: staff_manage_customers.php'); exit;
}

if ($action === 'restore' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();
  $id = (int)($_POST['id'] ?? 0);
  if ($id <= 0) { http_response_code(400); die('Bad request'); }

  $stmt = $conn->prepare("UPDATE users SET deleted_at=NULL WHERE id=? AND role='customer'");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $stmt->close();

  $_SESSION['flash_ok'] = 'Customer restored.';
  header('Location: staff_manage_customers.php?view=archived'); exit;
}

/* ===========================================================
   FETCH DATA
   =========================================================== */
$view   = $_GET['view'] ?? 'active'; // active | archived
$search = trim($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $PER_PAGE;

$where  = "role='customer' AND ".($view === 'archived' ? "deleted_at IS NOT NULL" : "deleted_at IS NULL");
$params = [];
$types  = '';

if ($search !== '') {
  $where .= " AND (name LIKE CONCAT('%',?,'%') OR email LIKE CONCAT('%',?,'%'))";
  $params[] = $search; $params[] = $search; $types .= 'ss';
}

/* Count for pagination */
$sqlCount = "SELECT COUNT(*) FROM users WHERE $where";
$stmt = $conn->prepare($sqlCount);
if ($types) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();

/* Page data */
$sqlList = "SELECT id,name,email,is_verified,created_at,deleted_at
            FROM users
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
$result = $stmt->get_result();
$rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

/* For edit form */
$editRow = null;
if ($action === 'edit') {
  $id = max(0, (int)($_GET['id'] ?? 0));
  if ($id) {
    $stmt = $conn->prepare("SELECT id,name,email,is_verified FROM users WHERE id=? AND role='customer' LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $editRow = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    if (!$editRow) { $_SESSION['flash_error'] = 'Customer not found.'; header('Location: staff_manage_customers.php'); exit; }
  }
}

/* Pagination calc */
$total_pages = max(1, (int)ceil(($total ?? 0) / $PER_PAGE));
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= BRAND_NAME ?> · Manage Customers</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Libs -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <link href="css/style.css?v=3" rel="stylesheet">
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
  <!-- Main content -->
  <div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="m-0"><i class="bi bi-people me-2"></i>Manage Customers</h3>
      <div>
        <a href="staff_dashboard.php" class="btn btn-outline-secondary btn-sm btn-pill">
          <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
        </a>
      </div>
    </div>

    <?php if (!empty($_SESSION['flash_ok'])): ?>
      <div class="alert alert-success"><?= e($_SESSION['flash_ok']); unset($_SESSION['flash_ok']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?>
      <div class="alert alert-danger"><?= e($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <div class="row g-3">
      <!-- Left: Table -->
      <div class="col-lg-8">
        <div class="card-soft p-3">
          <form class="row g-2 mb-2" method="get" action="staff_manage_customers.php">
            <input type="hidden" name="view" value="<?= e($view) ?>">
            <div class="col-md-6">
              <input class="form-control" type="text" name="q" value="<?= e($search) ?>" placeholder="Search name or email">
            </div>
            <div class="col-md-6 text-end">
              <div class="btn-group">
                <a class="btn btn-outline-secondary btn-pill<?= $view==='active'?' active':'' ?>" href="staff_manage_customers.php?view=active">Active</a>
                <a class="btn btn-outline-secondary btn-pill<?= $view==='archived'?' active':'' ?>" href="staff_manage_customers.php?view=archived">Archived</a>
              </div>
              <button class="btn btn-primary btn-pill ms-2"><i class="bi bi-search me-1"></i>Search</button>
            </div>
          </form>

          <div class="table-responsive">
            <table class="table align-middle">
              <thead>
                <tr>
                  <th>#</th><th>Name</th><th>Email</th><th>Status</th><th class="text-end">Joined</th><th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
              <?php if (!$rows): ?>
                <tr><td colspan="6" class="text-muted">No customers found.</td></tr>
              <?php else: foreach ($rows as $idx => $r): ?>
                <tr>
                  <td class="mono"><?= ($offset + $idx + 1) ?></td>
                  <td class="fw-semibold"><?= e($r['name']) ?></td>
                  <td class="text-muted"><?= e($r['email']) ?></td>
                  <td>
                    <?php if ((int)$r['is_verified'] === 1): ?>
                      <span class="badge bg-success badge-pill"><i class="bi bi-shield-check me-1"></i>Verified</span>
                    <?php else: ?>
                      <span class="badge bg-secondary badge-pill"><i class="bi bi-shield me-1"></i>Unverified</span>
                    <?php endif; ?>
                    <?php if (!empty($r['deleted_at'])): ?>
                      <span class="badge bg-danger ms-1">Archived</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end mono"><?= e($r['created_at']) ?></td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary btn-pill" href="staff_manage_customers.php?action=edit&id=<?= (int)$r['id'] ?>">
                      <i class="bi bi-pencil-square me-1"></i>Edit
                    </a>
                    <?php if (empty($r['deleted_at'])): ?>
                      <form class="d-inline" method="post" action="staff_manage_customers.php?action=delete" onsubmit="return confirm('Archive this customer?');">
                        <input type="hidden" name="csrf" value="<?= e($CSRF) ?>">
                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                        <button class="btn btn-sm btn-outline-danger btn-pill"><i class="bi bi-archive me-1"></i>Archive</button>
                      </form>
                    <?php else: ?>
                      <form class="d-inline" method="post" action="staff_manage_customers.php?action=restore" onsubmit="return confirm('Restore this customer?');">
                        <input type="hidden" name="csrf" value="<?= e($CSRF) ?>">
                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                        <button class="btn btn-sm btn-outline-success btn-pill"><i class="bi bi-arrow-counterclockwise me-1"></i>Restore</button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <nav>
            <ul class="pagination pagination-sm justify-content-end">
              <?php
              $base = 'staff_manage_customers.php?view='.urlencode($view).'&q='.urlencode($search).'&page=';
              for ($p=1; $p <= $total_pages; $p++):
              ?>
                <li class="page-item <?= $p===$page?'active':'' ?>">
                  <a class="page-link" href="<?= $base.$p ?>"><?= $p ?></a>
                </li>
              <?php endfor; ?>
            </ul>
          </nav>
        </div>
      </div>

      <!-- Right: Edit panel (no Add form) -->
      <div class="col-lg-4">
        <div class="card-soft p-3">
          <?php if ($action === 'edit' && $editRow): ?>
            <h5 class="mb-3"><i class="bi bi-pencil-square me-2"></i>Edit Customer</h5>
            <form method="post" action="staff_manage_customers.php?action=update">
              <input type="hidden" name="csrf" value="<?= e($CSRF) ?>">
              <input type="hidden" name="id" value="<?= (int)$editRow['id'] ?>">

              <div class="mb-2">
                <label class="form-label">Name</label>
                <input class="form-control" name="name" value="<?= e($editRow['name']) ?>" required>
              </div>
              <div class="mb-2">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" name="email" value="<?= e($editRow['email']) ?>" required>
              </div>
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="v1" name="is_verified" <?= $editRow['is_verified'] ? 'checked':'' ?>>
                <label for="v1" class="form-check-label">Verified</label>
              </div>

              <div class="d-flex gap-2">
                <button class="btn btn-primary btn-pill"><i class="bi bi-save me-1"></i>Save</button>
                <a class="btn btn-outline-secondary btn-pill" href="staff_manage_customers.php"><i class="bi bi-x-circle me-1"></i>Cancel</a>
              </div>
            </form>
          <?php else: ?>
            <h5 class="mb-2"><i class="bi bi-info-circle me-2"></i>No customer selected</h5>
            <div class="text-muted small">Click <em>Edit</em> in the table to modify a customer.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="text-center text-muted small mt-4">© <?= date('Y') ?> <?= BRAND_NAME ?> · Manage Customers</div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
