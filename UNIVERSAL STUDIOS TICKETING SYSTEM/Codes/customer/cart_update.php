<?php
// cart_update.php
session_start();
if (empty($_SESSION['user']['id']) || ($_SESSION['user']['role'] ?? '') !== 'customer') { header('Location: login.php'); exit; }
require __DIR__ . '/database.php';
$userId = (int)$_SESSION['user']['id'];
$cartId = (int)($_POST['cart_id'] ?? 0);
$qty = max(1, (int)($_POST['qty'] ?? 1));

$stmt = $conn->prepare("UPDATE carts SET qty=? WHERE id=? AND user_id=?");
$stmt->bind_param('iii',$qty,$cartId,$userId);
$stmt->execute();
$stmt->close();
header('Location: cart.php');
