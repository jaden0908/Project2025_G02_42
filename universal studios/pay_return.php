<?php
/**
 * pay_return.php
 * - ToyyibPay redirects the customer here after payment attempt.
 * - This is a client-side return page (not fully reliable).
 * - We read the query string, update the order status, and show a result page.
 */

require __DIR__ . '/database.php';

// Read GET parameters returned from ToyyibPay
$status  = $_GET['status_id'] ?? '';
$bill    = $_GET['billcode'] ?? '';
$orderId = (int)($_GET['order_id'] ?? 0);

// Map status_id to our order status
if ($status == '1') {
    $newStatus = 'paid';
} elseif ($status == '2') {
    $newStatus = 'cancelled';
} else {
    $newStatus = 'pending';
}

// Update order status in database
$stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=? AND bill_code=?");
$stmt->bind_param("sis", $newStatus, $orderId, $bill);
$stmt->execute();
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head><title>Payment Result</title></head>
<body>
<h1>Payment <?= htmlspecialchars($newStatus) ?></h1>
<p>Order ID: <?= htmlspecialchars($orderId) ?></p>
<p>Bill Code: <?= htmlspecialchars($bill) ?></p>
<a href="orders.php">Back to Orders</a>
</body>
</html>
