<?php
// verify_reset_otp.php
// Step 2: User enters the 6-digit reset code received via email.

session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');

require_once 'database.php';

$error = '';
$info  = '';

// Must have email from Step 1
if (empty($_SESSION['reset_pending_email'])) {
    header("Location: forgot_password.php");
    exit();
}
$email = $_SESSION['reset_pending_email'];

// Optional info after sending/resending
if (isset($_GET['sent'])) {
    $info = "We sent a reset code to your email.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
    $otp = trim($_POST['otp']);

    // Validate OTP format: 6 digits
    if ($otp === '' || !preg_match('/^\d{6}$/', $otp)) {
        $error = "Please enter the 6-digit code.";
    } else {
        // Fetch stored OTP & expiry
        $stmt = $conn->prepare("SELECT id, reset_otp, reset_expires FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();

            $now = new DateTime('now');
            $exp = new DateTime($user['reset_expires'] ?? '1970-01-01 00:00:00');

            // Compare OTP and check not expired
            if ($user['reset_otp'] === $otp && $exp >= $now) {
                // OK → grant permission to set new password
                $_SESSION['reset_email'] = $email;
                header("Location: reset_password.php");
                exit();
            } else {
                $error = "Invalid or expired code. Please try again.";
            }
        } else {
            // Unexpected: user missing → restart flow
            header("Location: forgot_password.php");
            exit();
        }
    }
}

// Resend code (optional)
if (isset($_GET['resend']) && $_GET['resend'] === '1') {
    require_once 'mailer_init.php';
    $q = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
    $q->bind_param("s", $email);
    $q->execute();
    $r = $q->get_result();
    if ($r->num_rows === 1) {
        $user = $r->fetch_assoc();
        $otp       = strval(random_int(100000, 999999));
        $expiresAt = (new DateTime('+10 minutes'))->format('Y-m-d H:i:s');

        $upd = $conn->prepare("UPDATE users SET reset_otp = ?, reset_expires = ? WHERE id = ?");
        $upd->bind_param("ssi", $otp, $expiresAt, $user['id']);
        if ($upd->execute()) {
            $mailResult = sendPasswordResetOTP($email, $user['name'], $otp);
            if ($mailResult === true) {
                header("Location: verify_reset_otp.php?sent=1");
                exit();
            } else {
                $error = "Couldn't resend code. Error: " . htmlspecialchars($mailResult);
            }
        } else {
            $error = "Couldn't generate a new code. Please try again.";
        }
    } else {
        header("Location: forgot_password.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Reset Code - WaterLand</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">

</head>
<body class = "reset-otp-page">
<div class="container mt-5" style="max-width:520px;">
    <h2 class="mb-3">Enter Reset Code</h2>
    <p class="text-muted">We sent a 6-digit code to <strong><?= htmlspecialchars($email) ?></strong>. Check your inbox (and spam).</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <?php if (!empty($info)): ?>
        <div class="alert alert-info"><?= $info ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label">6-digit code</label>
            <input type="text" name="otp" class="form-control" required maxlength="6" pattern="\d{6}" placeholder="e.g. 123456">
        </div>
        <button type="submit" class="btn btn-primary w-100">Verify Code</button>

        <div class="text-center mt-3">
            <a href="verify_reset_otp.php?resend=1" class="btn btn-link">Resend Code</a>
            <span class="text-muted">&nbsp;|&nbsp;</span>
            <a href="forgot_password.php" class="btn btn-link">Use a different email</a>
        </div>
    </form>
</div>
</body>
</html>
