<?php
/**
 * pay_callback.php — Server-to-server notification from ToyyibPay.
 * - Updates order status.
 * - Tries to capture a payment reference number to show on receipt.
 * - Always return 200 OK once processed.
 */
require __DIR__ . '/database.php';

// Read POST data from ToyyibPay (field names may vary by account setup)
$orderId = (int)($_POST['order_id'] ?? 0);
$bill    = $_POST['billcode'] ?? '';
$status  = $_POST['status'] ?? $_POST['status_id'] ?? '';

// Try to capture a payment reference if provided by gateway
$paymentRef = $_POST['refno'] 
           ?? $_POST['transaction_id'] 
           ?? $_POST['txnid'] 
           ?? $_POST['fpx_sellerOrderNo'] 
           ?? $_POST['fpx_txn_id'] 
           ?? '';

if ($status == '1')      $newStatus = 'paid';
elseif ($status == '2')  $newStatus = 'cancelled';
else                     $newStatus = 'pending';

if ($orderId > 0 && $bill !== '') {
    if ($paymentRef !== '') {
        $stmt = $conn->prepare("UPDATE orders SET status=?, payment_ref=? WHERE id=? AND bill_code=?");
        $stmt->bind_param("ssis", $newStatus, $paymentRef, $orderId, $bill);
    } else {
        $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=? AND bill_code=?");
        $stmt->bind_param("sis", $newStatus, $orderId, $bill);
    }
    $stmt->execute();
    $stmt->close();
}

http_response_code(200);
