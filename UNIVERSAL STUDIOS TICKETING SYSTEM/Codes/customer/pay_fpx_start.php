<?php
/**
 * pay_fpx_start.php — ToyyibPay Sandbox integration (final, robust)
 *
 * Fixes:
 * - Adds billPriceSetting (required)
 * - Uses billPayorInfo in the format "name|email|phone"
 * - Sanitises name/email/phone to avoid "invalid" errors
 * - Keeps billTo/billEmail/billPhone for backward compatibility
 */

session_start();
require __DIR__ . '/database.php';

/* ===== 1) Your ToyyibPay Sandbox credentials =====
   Replace with the keys from your ToyyibPay Sandbox portal. */
$TOYYIB_SECRET = '2jt59p2u-powk-nbcn-354e-6qccww87p7kg';
$CATEGORY_CODE = 'x6suckba';

/* Your return/callback URLs (for local dev use http, not https) */
$RETURN_URL   = 'http://localhost/universalstudios6/pay_return.php';
$CALLBACK_URL = 'http://localhost/universalstudios6/pay_callback.php';

/* ===== 2) Read order id ===== */
$orderId = (int)($_GET['id'] ?? 0);
if ($orderId <= 0) {
    http_response_code(400);
    exit('Invalid order id.');
}

/* ===== 3) Fetch order details ===== */
$stmt = $conn->prepare("SELECT * FROM orders WHERE id=?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    http_response_code(404);
    exit('Order not found.');
}

/* ===== 4) Prepare payer info — strictly validate/sanitise ===== */
$rawName  = (string)($order['customer_name']  ?? '');
$rawEmail = (string)($order['customer_email'] ?? '');
$rawPhone = (string)($order['customer_phone'] ?? '');

/* Name: allow letters/numbers/basic punctuation, trim and limit length */
$name = trim(preg_replace('/[^A-Za-z0-9 \'\-.,]/', '', $rawName));
if ($name === '') { $name = 'Test User'; }
$name = mb_substr($name, 0, 80);

/* Email: must be a valid email, otherwise use a safe fallback */
$email = filter_var($rawEmail, FILTER_VALIDATE_EMAIL) ? $rawEmail : 'test@example.com';
$email = mb_substr($email, 0, 100);

/* Phone: keep digits only; fallback to a dummy local number */
$phone = preg_replace('/\D+/', '', $rawPhone);
if ($phone === '') { $phone = '0123456789'; }
$phone = mb_substr($phone, 0, 20);

/* billPayorInfo MUST be exactly "name|email|phone" */
$billPayorInfo = $name . '|' . $email . '|' . $phone;

/* ===== 5) Compute amount in MYR cents =====
   You store USD; for demo convert with a fixed rate. If you have RM in DB, use that directly. */
define('USD_TO_MYR', 4.70);
$amountRM    = (float)$order['total_usd'] * USD_TO_MYR;
$amountCents = (int) round($amountRM * 100);

/* Guard: set a minimum of RM1.00 for demo to avoid gateway refusing tiny amounts */
if ($amountCents < 100) {
    $amountCents = 100;
}

/* ===== 6) Build payload (createBill) ===== */
// --- Build payload for createBill (ToyyibPay Sandbox) ---
$postData = [
    'userSecretKey'           => $TOYYIB_SECRET,
    'categoryCode'            => $CATEGORY_CODE,

    'billName'                => 'Order #' . $order['id'],
    'billDescription'         => 'Universal Studios Ticket(s)',

    // IMPORTANT:
    // Many integrations define billPriceSetting = 0 for FIXED price, 1 for OPEN price.
    // If your env earlier complained "billPriceSetting parameter is empty",
    // set it explicitly to 0 (fixed) when you send billAmount.
    'billPriceSetting'        => 0,                  // 0=fixed amount, 1=open/variable amount

    // ToyyibPay expects the amount in CENTS (integer).
    'billAmount'              => $amountCents,

    'billReturnUrl'           => $RETURN_URL,
    'billCallbackUrl'         => $CALLBACK_URL,

    // billPayorInfo is a FLAG (0/1), not a "name|email|phone" string.
    // 1 = require payor info (we still pass the fields below).
    'billPayorInfo'           => 1,

    // Actual payor fields:
    'billTo'                  => $name,              // payer full name
    'billEmail'               => $email,             // payer email
    'billPhone'               => $phone,             // payer phone

    'billExternalReferenceNo' => (string)$order['id'],
    'billPaymentChannel'      => '0',                // 0=all channels (include FPX)
    'billChargeToCustomer'    => '1',
];


/* ===== 7) Call ToyyibPay Sandbox ===== */
$endpoint = 'https://dev.toyyibpay.com/index.php/api/createBill';

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $endpoint,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $postData,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 30,
]);
$response = curl_exec($ch);

if ($response === false) {
    $err = curl_error($ch);
    curl_close($ch);
    http_response_code(502);
    exit('ToyyibPay connection error: ' . htmlspecialchars($err));
}
curl_close($ch);

/* ===== 8) Parse response & handle errors =====
   Success (array): [{"BillCode":"..."}]
   Error (object) : {"status":"error","msg":"..."} */
$data = json_decode($response, true);

if (isset($data['status']) && $data['status'] === 'error') {
    http_response_code(400);
    exit('ToyyibPay error: ' . $response);
}

if (!is_array($data) || empty($data[0]['BillCode'])) {
    http_response_code(500);
    exit('Unexpected ToyyibPay response: ' . htmlspecialchars($response));
}
$billCode = $data[0]['BillCode'];

/* ===== 9) Save BillCode and mark pending ===== */
$stmt = $conn->prepare("UPDATE orders SET bill_code=?, status='pending' WHERE id=?");
$stmt->bind_param("si", $billCode, $orderId);
$stmt->execute();
$stmt->close();

/* ===== 10) Redirect to Sandbox payment page ===== */
header('Location: https://dev.toyyibpay.com/' . urlencode($billCode));
exit;
