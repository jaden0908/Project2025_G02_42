<?php
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');

include 'database.php';

$verifyEmail = $_SESSION['verify_email'] ?? '';
$infoMsg = (isset($_GET['sent']) ? "A verification code has been sent to your email." : "");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inputOtp     = trim($_POST['otp'] ?? '');
    $emailFromForm= trim($_POST['email'] ?? '');
    $emailToUse   = $verifyEmail !== '' ? $verifyEmail : $emailFromForm;

    if ($emailToUse === '' || $inputOtp === '') {
        $error = "Please enter both Email and OTP.";
    } else {
        $stmt = $conn->prepare("SELECT id, otp_code, otp_expires, is_verified FROM users WHERE email = ?");
        $stmt->bind_param("s", $emailToUse);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            $error = "No account found for this email.";
        } else {
            $user = $res->fetch_assoc();

            if (intval($user['is_verified']) === 1) {
                $error = "This email is already verified. You can sign in.";
            } else {
                $now = new DateTime();
                $exp = $user['otp_expires'] ? new DateTime($user['otp_expires']) : null;

                if (!$exp || $now > $exp) {
                    $error = "The code has expired. Please resend a new code.";
                } elseif (hash_equals($user['otp_code'] ?? '', $inputOtp)) {
                    $upd = $conn->prepare("
                        UPDATE users
                        SET is_verified = 1, otp_code = NULL, otp_expires = NULL
                        WHERE email = ?
                    ");
                    $upd->bind_param("s", $emailToUse);
                    if ($upd->execute()) {
                        unset($_SESSION['verify_email']);
                        $_SESSION['flash_success'] = "Email verified successfully. You can now sign in.";
                        header("Location: login.php");
                        exit();
                    } else {
                        $error = "Failed to update account. Please try again.";
                    }
                } else {
                    $error = "Invalid OTP. Please check and try again.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification - WaterLand</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body class = "verify-otp-page">
<div class="container mt-5" style="max-width:520px;">
    <h2 class="mb-3">Verify Your Email</h2>

    <?php
    if (!empty($infoMsg)) echo "<div class='alert alert-info'>{$infoMsg}</div>";
    if (!empty($error))   echo "<div class='alert alert-danger'>{$error}</div>";
    if (!empty($_GET['resend'])) echo "<div class='alert alert-success'>A new code has been sent.</div>";
    ?>

    <form method="POST" action="">
        <?php if ($verifyEmail === ''): ?>
        <div class="mb-3">
            <label class="form-label">Email (the one you used to sign up)</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <?php else: ?>
        <div class="mb-2">
            <small class="text-muted">Verifying: <?php echo htmlspecialchars($verifyEmail); ?></small>
        </div>
        <?php endif; ?>

        <div class="mb-3">
            <label class="form-label">Verification Code (OTP)</label>
            <input type="text" name="otp" class="form-control" inputmode="numeric" pattern="\d{6}" maxlength="6" placeholder="6-digit code" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Verify</button>
    </form>

    <div class="text-center mt-3">
        <a href="resend_otp.php" class="btn btn-link">Resend code</a>
    </div>
</div>
</body>
</html>
