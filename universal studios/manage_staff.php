<?php
/* ===========================================================
   Manage Staff (single-file CRUD) — Sidebar + Full Logic
   - List / Search / Paginate staff users
   - Create, Edit, Soft Delete (Archive) & Restore
   - Admin-set password reset
   - CSRF protection + Prepared Statements + XSS escaping
   - Uses the SAME UI styles as Admin Dashboard (style.css)
   =========================================================== */

session_start();
define('BRAND_NAME', 'Universal Studios');

/* ---------- Access Control: only admin can enter ---------- */
if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
$role = $_SESSION['user']['role'] ?? 'customer';
if ($role !== 'admin') {
  if ($role === 'staff') { header('Location: staff_dashboard.php'); exit; }
  header('Location: index.php'); exit;
}

/* ---------- DB + helpers ---------- */
require __DIR__ . '/database.php';

/** HTML escape helper */
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/** For highlighting active item in sidebar nav */
function nav_active($file){
  return basename($_SERVER['PHP_SELF']) === $file ? ' active' : '';
}

/* ---------- One-time schema helper: ensure deleted_at exists ----------
   We check first to avoid "Duplicate column" errors if it already exists. */
$colCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'deleted_at'");
if ($colCheck && $colCheck->num_rows === 0) {
  $conn->query("ALTER TABLE users ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL");
}

/* ---------- CSRF helpers ---------- */
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
   CREATE STAFF
   =========================================================== */
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();

  $name  = trim($_POST['name'] ?? '');
  $email = strtolower(trim($_POST['email'] ?? '')); // normalize for uniqueness
  $pass  = $_POST['password'] ?? '';
  $is_verified = isset($_POST['is_verified']) ? 1 : 0;

  $errors = [];
  if ($name === '') $errors[] = 'Name is required.';
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
  if (strlen($pass) < 6) $errors[] = 'Password must be at least 6 characters.';

  // Unique email check
  $stmt = $conn->prepare("SELECT id FROM users WHERE LOWER(email)=? LIMIT 1");
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows > 0) $errors[] = 'Email already exists.';
  $stmt->close();

  if ($errors) {
    $_SESSION['flash_error'] = implode(' ', $errors);
    header('Location: manage_staff.php?action=new'); exit;
  }

  $hash = password_hash($pass, PASSWORD_BCRYPT);
  $fixedRole = 'staff';
  $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, is_verified) VALUES (?,?,?,?,?)");
  $stmt->bind_param('ssssi', $name, $email, $hash, $fixedRole, $is_verified);
  $stmt->execute();
  $stmt->close();

  $_SESSION['flash_ok'] = 'Staff created successfully.';
  header('Location: manage_staff.php'); exit;
}

/* ===========================================================
   UPDATE STAFF
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

  // Unique email check excluding self
  $stmt = $conn->prepare("SELECT id FROM users WHERE LOWER(email)=? AND id<>? LIMIT 1");
  $stmt->bind_param('si', $email, $id);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows > 0) $errors[] = 'Email already in use by another account.';
  $stmt->close();

  if ($errors) {
    $_SESSION['flash_error'] = implode(' ', $errors);
    header('Location: manage_staff.php?action=edit&id='.$id); exit;
  }

  $stmt = $conn->prepare("UPDATE users SET name=?, email=?, is_verified=? WHERE id=? AND role='staff'");
  $stmt->bind_param('ssii', $name, $email, $is_verified, $id);
  $stmt->execute();
  $stmt->close();

  $_SESSION['flash_ok'] = 'Staff updated.';
  header('Location: manage_staff.php'); exit;
}

/* ===========================================================
   RESET PASSWORD (admin-set)
   =========================================================== */
if ($action === 'resetpwd' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();
  $id   = (int)($_POST['id'] ?? 0);
  $pass = $_POST['password'] ?? '';

  if ($id <= 0) { http_response_code(400); die('Bad request'); }
  if (strlen($pass) < 6) {
    $_SESSION['flash_error'] = 'Password must be at least 6 characters.';
    header('Location: manage_staff.php?action=edit&id='.$id); exit;
  }

  $hash = password_hash($pass, PASSWORD_BCRYPT);
  $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=? AND role='staff'");
  $stmt->bind_param('si', $hash, $id);
  $stmt->execute();
  $stmt->close();

  $_SESSION['flash_ok'] = 'Password reset successfully.';
  header('Location: manage_staff.php'); exit;
}

/* ===========================================================
   SOFT DELETE / RESTORE
   =========================================================== */
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();
  $id = (int)($_POST['id'] ?? 0);
  if ($id <= 0) { http_response_code(400); die('Bad request'); }
  $stmt = $conn->prepare("UPDATE users SET deleted_at=NOW() WHERE id=? AND role='staff'");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $stmt->close();
  $_SESSION['flash_ok'] = 'Staff archived (soft deleted).';
  header('Location: manage_staff.php'); exit;
}

if ($action === 'restore' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();
  $id = (int)($_POST['id'] ?? 0);
  if ($id <= 0) { http_response_code(400); die('Bad request'); }
  $stmt = $conn->prepare("UPDATE users SET deleted_at=NULL WHERE id=? AND role='staff'");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $stmt->close();
  $_SESSION['flash_ok'] = 'Staff restored.';
  header('Location: manage_staff.php?view=archived'); exit;
}

/* ===========================================================
   FETCH DATA FOR LIST/EDIT FORMS
   =========================================================== */
$view   = $_GET['view'] ?? 'active'; // active | archived
$search = trim($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $PER_PAGE;

$where  = "role='staff' AND ".($view === 'archived' ? "deleted_at IS NOT NULL" : "deleted_at IS NULL");
$params = [];
$types  = '';

if ($search !== '') {
  // LIKE with placeholders (safe), match name or email
  $where .= " AND (name LIKE CONCAT('%',?,'%') OR email LIKE CONCAT('%',?,'%'))";
  $params[] = $search; $params[] = $search; $types .= 'ss';
}

/* Count rows for pagination */
$sqlCount = "SELECT COUNT(*) FROM users WHERE $where";
$stmt = $conn->prepare($sqlCount);
if ($types) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();

/* Fetch current page */
$sqlList = "SELECT id,name,email,is_verified,created_at,deleted_at
            FROM users
            WHERE $where
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sqlList);
if ($types) {
  $types2 = $types.'ii';
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
    $stmt = $conn->prepare("SELECT id,name,email,is_verified FROM users WHERE id=? AND role='staff' LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $editRow = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    if (!$editRow) { $_SESSION['flash_error'] = 'Staff not found.'; header('Location: manage_staff.php'); exit; }
  }
}

/* Pagination calc */
$total_pages = max(1, (int)ceil(($total ?? 0) / $PER_PAGE));
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= BRAND_NAME ?> · Manage Staff</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Libs -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Global site styles (same as dashboard) -->
  <link href="css/style.css?v=3" rel="stylesheet">
</head>
<body>

<div class="layout"><!-- same grid shell as dashboard -->

  <!-- Sidebar (same markup/classes as dashboard for uniform look) -->
  <aside class="sidebar">
    <div class="brand">
      <i class="bi bi-film"></i><span><?= BRAND_NAME ?></span>
    </div>

    <div class="nav-sec">
      <div class="nav-title">Main</div>

      <a class="nav-link<?= nav_active('admin_dashboard.php') ?>" href="admin_dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
      <a class="nav-link<?= nav_active('manage_staff.php') ?>" href="manage_staff.php"><i class="bi bi-person-badge me-1"></i>Manage Staff</a>
      <a class="nav-link<?= nav_active('manage_customers.php') ?>" href="manage_customers.php"><i class="bi bi-people me-1"></i>Manage Customers</a>
      <a class="nav-link<?= nav_active('view_feedback.php') ?>" href="view_feedback.php"><i class="bi bi-chat-left-text me-1"></i>View Feedback</a>
      <a class="nav-link disabled" title="Coming soon"><i class="bi bi-ticket-perforated"></i>Manage Packages</a>
      <a class="nav-link disabled" title="Coming soon"><i class="bi bi-graph-up"></i>Sales Reports</a>
    </div>

    <div class="nav-sec">
      <div class="nav-title">Account</div>
      <a class="nav-link" href="profile.php"><i class="bi bi-person"></i>My Profile</a>
      <a class="nav-link" href="index.php"><i class="bi bi-house-door"></i>Back to Home</a>
      <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i>Sign Out</a>
    </div>
  </aside>

  <!-- Main content (use .main so it picks same padding etc.) -->
  <main class="main">

    <!-- Header row -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="m-0"><i class="bi bi-person-badge me-2"></i>Manage Staff</h4>
      <a href="admin_dashboard.php" class="btn btn-outline-secondary btn-pill">
        <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
      </a>
    </div>

    <!-- Flash messages -->
    <?php if (!empty($_SESSION['flash_ok'])): ?>
      <div class="alert alert-success"><?= e($_SESSION['flash_ok']); unset($_SESSION['flash_ok']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?>
      <div class="alert alert-danger"><?= e($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <div class="row g-3">
      <!-- Left: table -->
      <div class="col-lg-8">
        <div class="card-soft p-3">
          <!-- Search & view switch -->
          <form class="row g-2 mb-2" method="get" action="manage_staff.php">
            <input type="hidden" name="view" value="<?= e($view) ?>">
            <div class="col-md-6">
              <input class="form-control" type="text" name="q" value="<?= e($search) ?>" placeholder="Search name or email">
            </div>
            <div class="col-md-6 text-end">
              <div class="btn-group">
                <a class="btn btn-outline-secondary btn-pill<?= $view==='active'?' active':'' ?>" href="manage_staff.php?view=active">Active</a>
                <a class="btn btn-outline-secondary btn-pill<?= $view==='archived'?' active':'' ?>" href="manage_staff.php?view=archived">Archived</a>
              </div>
              <button class="btn btn-primary btn-pill ms-2"><i class="bi bi-search me-1"></i>Search</button>
            </div>
          </form>

          <!-- Table -->
          <div class="table-responsive">
            <table class="table align-middle">
              <thead>
                <tr>
                  <th>#</th><th>Name</th><th>Email</th><th>Status</th><th class="text-end">Joined</th><th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
              <?php if (!$rows): ?>
                <tr><td colspan="6" class="text-muted">No staff found.</td></tr>
              <?php else: foreach ($rows as $idx => $r): ?>
                <tr>
                  <td class="mono"><?= ($offset + $idx + 1) ?></td>
                  <td class="fw-semibold"><?= e($r['name']) ?></td>
                  <td class="text-muted"><?= e($r['email']) ?></td>
                  <td>
                    <?php if ((int)$r['is_verified'] === 1): ?>
                      <span class="badge bg-success"><i class="bi bi-shield-check me-1"></i>Verified</span>
                    <?php else: ?>
                      <span class="badge bg-secondary"><i class="bi bi-shield me-1"></i>Unverified</span>
                    <?php endif; ?>
                    <?php if (!empty($r['deleted_at'])): ?>
                      <span class="badge bg-danger ms-1">Archived</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end mono"><?= e($r['created_at']) ?></td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary btn-pill" href="manage_staff.php?action=edit&id=<?= (int)$r['id'] ?>">
                      <i class="bi bi-pencil-square me-1"></i>Edit
                    </a>
                    <?php if (empty($r['deleted_at'])): ?>
                      <form class="d-inline" method="post" action="manage_staff.php?action=delete" onsubmit="return confirm('Archive this staff user?');">
                        <input type="hidden" name="csrf" value="<?= e($CSRF) ?>">
                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                        <button class="btn btn-sm btn-outline-danger btn-pill"><i class="bi bi-archive me-1"></i>Archive</button>
                      </form>
                    <?php else: ?>
                      <form class="d-inline" method="post" action="manage_staff.php?action=restore" onsubmit="return confirm('Restore this staff user?');">
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
              $base = 'manage_staff.php?view='.urlencode($view).'&q='.urlencode($search).'&page=';
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

      <!-- Right: Create / Edit panel -->
      <div class="col-lg-4">
        <div class="card-soft p-3">
          <?php if ($action === 'edit' && $editRow): ?>
            <h5 class="mb-3"><i class="bi bi-pencil-square me-2"></i>Edit Staff</h5>
            <form method="post" action="manage_staff.php?action=update" class="mb-3">
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
                <a class="btn btn-outline-secondary btn-pill" href="manage_staff.php"><i class="bi bi-x-circle me-1"></i>Cancel</a>
              </div>
            </form>

            <hr>
            <h6 class="mb-2"><i class="bi bi-key me-2"></i>Reset Password</h6>
            <form method="post" action="manage_staff.php?action=resetpwd" onsubmit="return confirm('Set a new password for this user?');">
              <input type="hidden" name="csrf" value="<?= e($CSRF) ?>">
              <input type="hidden" name="id" value="<?= (int)$editRow['id'] ?>">
              <div class="input-group mb-2">
                <input class="form-control" type="password" name="password" placeholder="New password (min 6 chars)" minlength="6" required>
                <button class="btn btn-outline-primary btn-pill">Update</button>
              </div>
            </form>

          <?php else: ?>
            <h5 class="mb-3"><i class="bi bi-person-plus me-2"></i>Add New Staff</h5>
            <form method="post" action="manage_staff.php?action=create">
              <input type="hidden" name="csrf" value="<?= e($CSRF) ?>">
              <div class="mb-2">
                <label class="form-label">Name</label>
                <input class="form-control" name="name" minlength="2" placeholder="Full name" required>
              </div>
              <div class="mb-2">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" name="email" placeholder="staff@example.com" required>
              </div>
              <div class="mb-2">
                <label class="form-label">Password</label>
                <input class="form-control" type="password" name="password" minlength="6" placeholder="Min 6 characters" required>
              </div>
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="v2" name="is_verified">
                <label for="v2" class="form-check-label">Verified</label>
              </div>
              <button class="btn btn-success btn-pill w-100"><i class="bi bi-check2-circle me-1"></i>Create Staff</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="text-center text-muted small mt-4">© <?= date('Y') ?> <?= BRAND_NAME ?> · Manage Staff</div>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
cdkmddmkvakvk,vd,avlvaavml