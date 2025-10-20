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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Result</title>
    <style>
        /* General Body Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
            color: #333;
        }

        /* Container for centering content */
        .container {
            width: 100%;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Heading styles */
        h1 {
            color: #28a745; /* Green for success */
            font-size: 24px;
            text-align: center;
            margin-bottom: 20px;
        }

        /* Paragraph Styles */
        p {
            font-size: 16px;
            line-height: 1.5;
            margin: 10px 0;
            text-align: center;
        }

        /* Link styles */
        a {
            display: inline-block;
            background-color: #007bff; /* Blue background */
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            text-align: center;
            font-weight: bold;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }

        a:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }

        /* Success message (Green) */
        .status-paid {
            color: #28a745;
        }

        /* Failure message (Red) */
        .status-cancelled {
            color: #dc3545;
        }

        /* Pending message (Yellow) */
        .status-pending {
            color: #ffc107;
        }

        /* Centering the link */
        .center-link {
            text-align: center;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Payment 
            <?php 
                echo htmlspecialchars($newStatus);
            ?>
        </h1>
        <p>Order ID: <?= htmlspecialchars($orderId) ?></p>
        <p>Bill Code: <?= htmlspecialchars($bill) ?></p>

        <p class="status-<?= $newStatus; ?>">Payment Status: <?= htmlspecialchars($newStatus) ?></p>

        <!-- Centered Back to Orders Link -->
        <div class="center-link">
            <a href="orders.php">Back to Orders</a>
        </div>
    </div>
</body>
</html>
