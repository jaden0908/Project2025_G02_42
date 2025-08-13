<?php
// forgot_password.php
// Step 1: User enters email, system emails a 6-digit OTP for password reset.

session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');

require_once 'database.php';     // provides $conn (mysqli)
require_once 'mailer_init.php';  // provides sendPasswordResetOTP()

$info = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Read and validate email
    $email = trim($_POST['email'] ?? '');
    $genericMsg = "If that email exists and is verified, we've sent a reset code."; 
    // Use generic message to avoid email enumeration

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // 2) Look up user by email
        $stmt = $conn->prepare("SELECT id, name, is_verified FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();

            // Only verified accounts can reset password
            if ((int)$user['is_verified'] === 1) {
                // 3) Generate OTP and expiry
                $otp       = strval(random_int(100000, 999999));
                $expiresAt = (new DateTime('+10 minutes'))->format('Y-m-d H:i:s');

                // 4) Save OTP to DB
                $upd = $conn->prepare("UPDATE users SET reset_otp = ?, reset_expires = ? WHERE id = ?");
                $upd->bind_param("ssi", $otp, $expiresAt, $user['id']);

                if ($upd->execute()) {
                    // 5) Send email
                    $mailResult = sendPasswordResetOTP($email, $user['name'], $otp);

                    if ($mailResult === true) {
                        // Keep email in session for the next step
                        $_SESSION['reset_pending_email'] = $email;
                        header("Location: verify_reset_otp.php?sent=1");
                        exit();
                    } else {
                        $error = "We couldn't send the reset code. Error: " . htmlspecialchars($mailResult);
                    }
                } else {
                    $error = "Failed to start password reset. Please try again.";
                }
            } else {
                // Not verified → keep response generic
                $info = $genericMsg;
            }
        } else {
            // Email not found → keep response generic
            $info = $genericMsg;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - WaterLand</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width:520px;">
    <h2 class="mb-3">Forgot Password</h2>
    <p class="text-muted">Enter your account email. We'll send a 6-digit code to verify it's you.</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <?php if (!empty($info)): ?>
        <div class="alert alert-info"><?= $info ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label">Email address</label>
            <input type="email" name="email" class="form-control" required placeholder="you@example.com">
        </div>
        <button type="submit" class="btn btn-primary w-100">Send Reset Code</button>
        <div class="text-center mt-3">
            <a href="login.php" class="btn btn-link">Back to Sign In</a>
        </div>
    </form>
</div>
</body>
</html>
