<?php
/**
 * cart.php — Server-side cart page (DB-backed)
 * Each customer sees only their own cart items.
 * - Requires login with role=customer
 * - Reads from carts table joined with packages
 * - Provides update qty / remove / totals
 */

session_start();
require __DIR__ . '/database.php';

if (empty($_SESSION['user']['id']) || ($_SESSION['user']['role'] ?? '') !== 'customer') {
    // Redirect guest/staff/admin to login
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user']['id'];

// Fetch cart items for this user
$sql = "SELECT c.id AS cart_id, c.qty, p.id AS package_id, p.title, p.price_usd
        FROM carts c
        JOIN packages p ON p.id = c.package_id
        WHERE c.user_id = ?
        ORDER BY c.updated_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
define('BRAND_NAME', 'Universal Studios');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= BRAND_NAME ?> - Cart</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
  <link href="css/style.css" rel="stylesheet">
</head>
<body>

<!-- Simple header -->
<nav class="navbar navbar-light bg-light">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <i class="fas fa-film text-primary me-2"></i><strong><?= BRAND_NAME ?></strong>
    </a>
    <div class="d-flex gap-2">
      <a href="package.php" class="btn btn-outline-primary btn-sm"><i class="fas fa-arrow-left me-1"></i> Continue Shopping</a>
    </div>
  </div>
</nav>

<main class="container my-4">

  <h1 class="h3 mb-4">Your Cart</h1>

  <?php if (!$rows): ?>
    <!-- Empty state -->
    <div class="alert alert-info">
      <i class="far fa-frown me-2"></i>Your cart is empty.
      <a href="package.php" class="btn btn-primary btn-sm ms-2">Browse Packages</a>
    </div>
  <?php else: ?>
    <!-- Cart table -->
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>Package</th>
                <th class="text-end">Unit Price</th>
                <th style="width:150px;" class="text-center">Quantity</th>
                <th class="text-end">Subtotal</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
            <?php $total = 0.0; $totalQty = 0; ?>
            <?php foreach ($rows as $r): 
              $price = (float)$r['price_usd'];
              $qty = (int)$r['qty'];
              $sub = $price * $qty;
              $total += $sub;
              $totalQty += $qty;
            ?>
              <tr>
                <td><?= e($r['title']) ?></td>
                <td class="text-end">$<?= number_format($price,2) ?></td>
                <td class="text-center">
                  <form class="d-flex gap-2 justify-content-center" method="post" action="cart_update.php">
                    <input type="hidden" name="cart_id" value="<?= (int)$r['cart_id'] ?>">
                    <input type="number" min="1" name="qty" value="<?= $qty ?>" class="form-control form-control-sm" style="width:80px;">
                    <button class="btn btn-sm btn-outline-primary">Update</button>
                  </form>
                </td>
                <td class="text-end">$<?= number_format($sub,2) ?></td>
                <td class="text-center">
                  <form method="post" action="cart_remove.php" onsubmit="return confirm('Remove this item?');">
                    <input type="hidden" name="cart_id" value="<?= (int)$r['cart_id'] ?>">
                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-times"></i></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr>
                <th colspan="2" class="text-end">Total Items:</th>
                <th class="text-center"><?= $totalQty ?></th>
                <th class="text-end">$<?= number_format($total,2) ?></th>
                <th></th>
              </tr>
            </tfoot>
          </table>
        </div>

        <div class="d-flex justify-content-end mt-3">
          <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
        </div>
      </div>
    </div>
  <?php endif; ?>

</main>

<footer class="border-top py-4 mt-5">
  <div class="container small text-muted d-flex justify-content-between">
    <span><i class="fas fa-film text-primary me-2"></i><?= BRAND_NAME ?></span>
    <span>&copy; <?= date('Y') ?> All rights reserved.</span>
  </div>
</footer>

</body>
</html>
