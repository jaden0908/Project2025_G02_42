<?php
/**
 * checkout.php — Create order from current user's cart
 * - Requires login (role = customer)
 * - Shows order summary + simple customer info form
 * - On POST: create order + order_items, clear cart, redirect to success page
 */

session_start();
require __DIR__ . '/database.php';

if (empty($_SESSION['user']['id']) || ($_SESSION['user']['role'] ?? '') !== 'customer') {
  header('Location: login.php');
  exit;
}

$userId = (int)$_SESSION['user']['id'];
define('BRAND_NAME', 'Universal Studios');

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/** Load cart rows (join packages) */
function loadCart(mysqli $conn, int $userId): array {
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
  return $rows;
}

/** Compute totals */
function computeTotals(array $rows): array {
  $totalQty = 0;
  $totalUsd = 0.0;
  foreach ($rows as $r) {
    $qty   = max(1, (int)$r['qty']);
    $price = (float)$r['price_usd'];
    $totalQty += $qty;
    $totalUsd += $qty * $price;
  }
  return [$totalQty, $totalUsd];
}

/** Place order inside a DB transaction */
function placeOrder(mysqli $conn, int $userId, string $name, string $email, string $phone, array $rows): int {
  [$totalQty, $totalUsd] = computeTotals($rows);
  if ($totalQty === 0) return 0;

  $conn->begin_transaction();
  try {
    // 1) Insert order
    $sql = "INSERT INTO orders (user_id, status, total_usd, customer_name, customer_email, customer_phone)
            VALUES (?, 'pending', ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('idsss', $userId, $totalUsd, $name, $email, $phone);
    $stmt->execute();
    $orderId = (int)$stmt->insert_id;
    $stmt->close();

    // 2) Insert order items
    $sqlItem = "INSERT INTO order_items (order_id, package_id, title, unit_price, qty, subtotal)
                VALUES (?, ?, ?, ?, ?, ?)";
    $stmtI = $conn->prepare($sqlItem);

    foreach ($rows as $r) {
      $qty   = max(1, (int)$r['qty']);
      $price = (float)$r['price_usd'];
      $sub   = $qty * $price;
      $pkgId = (int)$r['package_id'];
      $title = (string)$r['title'];
      $stmtI->bind_param('iisdis', $orderId, $pkgId, $title, $price, $qty, $sub);
      $stmtI->execute();
    }
    $stmtI->close();

    // 3) Clear cart for this user
    $stmtC = $conn->prepare("DELETE FROM carts WHERE user_id=?");
    $stmtC->bind_param('i', $userId);
    $stmtC->execute();
    $stmtC->close();

    $conn->commit();
    return $orderId;
  } catch (Throwable $e) {
    $conn->rollback();
    // Optional: log error
    return 0;
  }
}

// Handle POST (place order)
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name  = trim($_POST['name']  ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');

  // Minimal validation
  if ($name === '')  $errors[] = 'Name is required.';
  if ($email === '') $errors[] = 'Email is required.';

  $cartRows = loadCart($conn, $userId);
  if (!$cartRows) $errors[] = 'Your cart is empty.';

  if (!$errors) {
    $orderId = placeOrder($conn, $userId, $name, $email, $phone, $cartRows);
    if ($orderId > 0) {
  header('Location: pay_fpx_start.php?id=' . $orderId);
  exit;
}
 else {
      $errors[] = 'Failed to place the order. Please try again.';
    }
  }
}

// GET: load cart to show summary
$cartRows = loadCart($conn, $userId);
[$totalQty, $totalUsd] = computeTotals($cartRows);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= BRAND_NAME ?> - Checkout</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
  <link href="css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-light bg-light">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <i class="fas fa-film text-primary me-2"></i><strong><?= BRAND_NAME ?></strong>
    </a>
    <div class="d-flex gap-2">
      <a href="cart.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back to Cart</a>
    </div>
  </div>
</nav>

<main class="container my-4">
  <h1 class="h3 mb-4">Checkout</h1>

  <?php if ($errors): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errors as $eMsg): ?>
          <li><?= e($eMsg) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if (!$cartRows): ?>
    <div class="alert alert-info">
      Your cart is empty. <a href="package.php" class="btn btn-primary btn-sm ms-2">Browse Packages</a>
    </div>
  <?php else: ?>
    <div class="row g-4">
      <!-- Left: Customer info -->
      <div class="col-lg-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title mb-3">Customer Information</h5>
            <form method="post">
              <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="<?= e($_POST['name'] ?? $_SESSION['user']['name'] ?? '') ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= e($_POST['email'] ?? $_SESSION['user']['email'] ?? '') ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Phone (optional)</label>
                <input type="text" name="phone" class="form-control" value="<?= e($_POST['phone'] ?? '') ?>">
              </div>

              <button class="btn btn-primary w-100">Place Order</button>
            </form>
          </div>
        </div>
      </div>

      <!-- Right: Order summary -->
      <div class="col-lg-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title mb-3">Order Summary</h5>

            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th>Package</th>
                    <th class="text-end">Unit</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Subtotal</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach ($cartRows as $r):
                  $qty   = max(1, (int)$r['qty']);
                  $price = (float)$r['price_usd'];
                  $sub   = $qty * $price;
                ?>
                  <tr>
                    <td><?= e($r['title']) ?></td>
                    <td class="text-end">$<?= number_format($price, 2) ?></td>
                    <td class="text-center"><?= $qty ?></td>
                    <td class="text-end">$<?= number_format($sub, 2) ?></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                  <tr>
                    <th colspan="2" class="text-end">Total Items:</th>
                    <th class="text-center"><?= $totalQty ?></th>
                    <th class="text-end">$<?= number_format($totalUsd, 2) ?></th>
                  </tr>
                </tfoot>
              </table>
            </div>

            <div class="small text-muted">
              * Prices are in USD. This is a demo checkout; integrate payment later.
            </div>
          </div>
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
