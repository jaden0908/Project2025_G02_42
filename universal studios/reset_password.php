<?php
// reset_password.php
// Step 3: After OTP verified, user sets a new password.

session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');

require_once 'database.php';

// Must pass OTP verification first
if (empty($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}
$email = $_SESSION['reset_email'];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pwd  = $_POST['password'] ?? '';
    $cpwd = $_POST['confirm_password'] ?? '';

    // Minimal password rules
    if (strlen($pwd) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($pwd !== $cpwd) {
        $error = "Passwords do not match.";
    } else {
        // Hash and update DB
        $hash = password_hash($pwd, PASSWORD_DEFAULT);

        $upd = $conn->prepare("
            UPDATE users
               SET password = ?, reset_otp = NULL, reset_expires = NULL
             WHERE email = ?
            LIMIT 1
        ");
        $upd->bind_param("ss", $hash, $email);

        if ($upd->execute() && $upd->affected_rows === 1) {
            // Cleanup sessions
            unset($_SESSION['reset_email'], $_SESSION['reset_pending_email']);
            // Back to login with success flag
            header("Location: login.php?reset=1");
            exit();
        } else {
            $error = "Failed to update password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create New Password - WaterLand</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body class ="reset-password-page">
<div class="container mt-5" style="max-width:520px;">
    <h2 class="mb-3">Create a New Password</h2>
    <p class="text-muted">For account: <strong><?= htmlspecialchars($email) ?></strong></p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label">New password</label>
            <input type="password" name="password" class="form-control" required minlength="6" placeholder="At least 6 characters">
        </div>
        <div class="mb-3">
            <label class="form-label">Confirm new password</label>
            <input type="password" name="confirm_password" class="form-control" required minlength="6">
        </div>
        <button type="submit" class="btn btn-primary w-100">Update Password</button>
        <div class="text-center mt-3">
            <a href="login.php" class="btn btn-link">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>
