<?php
session_start();
if (empty($_SESSION['user']['id']) || ($_SESSION['user']['role'] ?? '') !== 'customer') {
  header('Location: login.php');
  exit;
}
define('BRAND_NAME', 'Universal Studios');
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= BRAND_NAME ?> - Order Success</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-light bg-light">
  <div class="container">
    <a class="navbar-brand" href="index.php"><?= BRAND_NAME ?></a>
  </div>
</nav>

<main class="container my-5">
  <div class="alert alert-success">
    <h4 class="alert-heading">Thank you!</h4>
    <p>Your order has been placed successfully.</p>
    <?php if ($orderId): ?>
      <hr>
      <p class="mb-0">Order ID: <strong>#<?= $orderId ?></strong></p>
    <?php endif; ?>
  </div>

  <div class="d-flex gap-2">
    <a class="btn btn-primary" href="package.php">Continue Shopping</a>
    <a class="btn btn-outline-secondary" href="index.php">Home</a>
  </div>
</main>
</body>
</html>
