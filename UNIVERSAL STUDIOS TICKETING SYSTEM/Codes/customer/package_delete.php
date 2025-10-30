<?php
/* package_delete.php
 * Handle delete for packages (admin or staff)
 */
session_start();
define('BRAND_NAME', 'Universal Studios');

// --- Access control: only admin or staff ---
if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
$role = $_SESSION['user']['role'] ?? 'customer';
if ($role !== 'admin' && $role !== 'staff') {
  header('Location: index.php'); exit;
}

// Decide redirect target after deletion
$back = ($role === 'staff') ? 'staff_managepackage.php' : 'manage_packages.php';

require __DIR__ . '/database.php';

/* ---- CSRF ---- */
$csrfOk = isset($_POST['csrf'], $_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$_POST['csrf']);
if (!$csrfOk) {
  $_SESSION['flash'] = ['type'=>'danger','msg'=>'Security token invalid.'];
  header('Location: ' . $back); exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
  $_SESSION['flash'] = ['type'=>'danger','msg'=>'Invalid package id.'];
  header('Location: ' . $back); exit;
}

/* ---- fetch old image path ---- */
$old = $conn->prepare("SELECT image_path FROM packages WHERE id=?");
$old->bind_param('i', $id);
$old->execute();
$oldRes = $old->get_result();
$imgPath = ($oldRow = $oldRes->fetch_assoc()) ? ($oldRow['image_path'] ?? null) : null;
$old->close();

/* ---- delete package ---- */
$stmt = $conn->prepare("DELETE FROM packages WHERE id=?");
$stmt->bind_param('i', $id);
$ok = $stmt->execute();
$stmt->close();

/* ---- remove old image file if exists ---- */
if ($ok) {
  if ($imgPath) {
    $fs = __DIR__ . '/' . $imgPath;
    if (is_file($fs)) { @unlink($fs); }
  }
  $_SESSION['flash'] = ['type'=>'success','msg'=>'Package deleted.'];
} else {
  $_SESSION['flash'] = ['type'=>'danger','msg'=>'Failed to delete.'];
}

header('Location: ' . $back);
exit;
