<?php
/* package_save.php
 * Handle create/update for packages (admin or staff)
 */
session_start();
define('BRAND_NAME', 'Universal Studios');

// --- Access control: allow admin or staff only ---
if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
$role = $_SESSION['user']['role'] ?? 'customer';
if ($role !== 'admin' && $role !== 'staff') {
  header('Location: index.php');
  exit;
}

// Decide where to return after handling (staff vs admin list page)
$back = ($role === 'staff') ? 'staff_managepackage.php' : 'manage_packages.php';

require __DIR__ . '/database.php';

/* ---- CSRF ---- */
$csrfOk = isset($_POST['csrf'], $_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$_POST['csrf']);
if (!$csrfOk) {
  $_SESSION['flash'] = ['type'=>'danger','msg'=>'Security token invalid. Please try again.'];
  header('Location: ' . $back); exit;
}

/* ---- read & validate fields ---- */
$id         = isset($_POST['id']) ? (int)$_POST['id'] : 0; // 0 = create
$title      = trim($_POST['title'] ?? '');
$short_desc = trim($_POST['short_desc'] ?? '');
$price_usd  = trim($_POST['price_usd'] ?? '0');
$status     = $_POST['status'] ?? 'active';

$errors = [];
if ($title === '') $errors[] = 'Title is required.';
if (!is_numeric($price_usd) || (float)$price_usd < 0) $errors[] = 'Price must be a non-negative number.';
if (!in_array($status, ['active','inactive'], true)) $errors[] = 'Invalid status.';

$imagePath = null;

/* ---- optional image upload ---- */
if (!empty($_FILES['image']['name'])) {
  $f = $_FILES['image'];
  if ($f['error'] === UPLOAD_ERR_OK) {
    if ($f['size'] > 2*1024*1024) {
      $errors[] = 'Image too large (max 2MB).';
    } else {
      $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
      if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) {
        $errors[] = 'Image type must be jpg, png, or webp.';
      } else {
        $dir = __DIR__ . '/uploads/packages';
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        $basename = 'pkg_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destFs = $dir . '/' . $basename;
        if (move_uploaded_file($f['tmp_name'], $destFs)) {
          $imagePath = 'uploads/packages/' . $basename; // relative path for web
        } else {
          $errors[] = 'Failed to save uploaded image.';
        }
      }
    }
  } elseif ($f['error'] !== UPLOAD_ERR_NO_FILE) {
    $errors[] = 'Upload error code: '.$f['error'];
  }
}

if ($errors) {
  $_SESSION['flash'] = ['type'=>'danger','msg'=>implode(' ', $errors)];
  header('Location: ' . $back); exit;
}

/* ---- create or update ---- */
if ($id === 0) {
  // create
  $sql = "INSERT INTO packages (title, short_desc, price_usd, status, image_path)
          VALUES (?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $price = (float)$price_usd;
  $img = $imagePath; // may be null
  $stmt->bind_param('ssdss', $title, $short_desc, $price, $status, $img);
  $ok = $stmt->execute();
  $stmt->close();

  $_SESSION['flash'] = $ok
    ? ['type'=>'success','msg'=>'Package created.']
    : ['type'=>'danger','msg'=>'Failed to create package.'];

} else {
  // update (keep old image if none uploaded)
  if ($imagePath !== null) {
    $sql = "UPDATE packages SET title=?, short_desc=?, price_usd=?, status=?, image_path=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $price = (float)$price_usd;
    $stmt->bind_param('ssdssi', $title, $short_desc, $price, $status, $imagePath, $id);
  } else {
    $sql = "UPDATE packages SET title=?, short_desc=?, price_usd=?, status=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $price = (float)$price_usd;
    $stmt->bind_param('ssdsi', $title, $short_desc, $price, $status, $id);
  }
  $ok = $stmt->execute();
  $stmt->close();

  $_SESSION['flash'] = $ok
    ? ['type'=>'success','msg'=>'Package updated.']
    : ['type'=>'danger','msg'=>'Failed to update package.'];
}

// Return to the appropriate listing page
header('Location: ' . $back);
exit;
