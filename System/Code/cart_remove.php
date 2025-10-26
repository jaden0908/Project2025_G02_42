<?php
// cart_remove.php
session_start();
if (empty($_SESSION['user']['id']) || ($_SESSION['user']['role'] ?? '') !== 'customer') { header('Location: login.php'); exit; }
require __DIR__ . '/database.php';
$userId = (int)$_SESSION['user']['id'];
$cartId = (int)($_POST['cart_id'] ?? 0);

$stmt = $conn->prepare("DELETE FROM carts WHERE id=? AND user_id=?");
$stmt->bind_param('ii',$cartId,$userId);
$stmt->execute();
$stmt->close();
header('Location: cart.php');
