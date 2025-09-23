<?php
// api/cart_add.php
session_start();
header('Content-Type: application/json');
require __DIR__ . '/../database.php';

if (empty($_SESSION['user']['id']) || ($_SESSION['user']['role'] ?? '') !== 'customer') {
  http_response_code(401);
  echo json_encode(['ok'=>false,'msg'=>'Login as customer first']);
  exit;
}

$userId = (int)$_SESSION['user']['id'];
$packageId = (int)($_POST['package_id'] ?? 0);
$qty = (int)($_POST['qty'] ?? 1);

// Basic validation
if ($packageId <= 0 || $qty <= 0) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'msg'=>'Bad params']);
  exit;
}

// Ensure package exists & is active
$stmt = $conn->prepare("SELECT id FROM packages WHERE id=? AND status='active'");
$stmt->bind_param('i',$packageId);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$exists) {
  http_response_code(404);
  echo json_encode(['ok'=>false,'msg'=>'Package not found']);
  exit;
}

// Insert or increase qty (UPSERT)
$sql = "INSERT INTO carts (user_id, package_id, qty)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE qty = qty + VALUES(qty)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iii', $userId, $packageId, $qty);
$ok = $stmt->execute();
$stmt->close();

echo json_encode(['ok'=>$ok]);
