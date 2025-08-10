<?php
// signup.php
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur'); // keep OTP expiry consistent

include 'database.php';           // provides $conn (mysqli)
require_once 'mailer_init.php';   // PHPMailer + sendVerificationOTP()

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name        = trim($_POST['name'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $rawPassword = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $rawPassword === '') {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check duplicate
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $exists = $check->get_result();

        if ($exists->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            $passwordHash = password_hash($rawPassword, PASSWORD_DEFAULT);
            $role         = 'customer';
            $is_verified  = 0;

            // 6-digit OTP + expiry 10 minutes
            $otp        = strval(random_int(100000, 999999));
            $expiresAt  = (new DateTime('+10 minutes'))->format('Y-m-d H:i:s');

            // Insert (NOTICE: using your columns: otp_expires)
            $stmt = $conn->prepare("
                INSERT INTO users (name, email, password, role, is_verified, otp_code, otp_expires)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            // types: s s s s i s s
            $stmt->bind_param("ssssiss", $name, $email, $passwordHash, $role, $is_verified, $otp, $expiresAt);

            if ($stmt->execute()) {
                // Send OTP email
                $mailResult = sendVerificationOTP($email, $name, $otp);

                if ($mailResult === true) {
                    $_SESSION['verify_email'] = $email;
                    header("Location: verify_otp.php?sent=1");
                    exit();
                } else {
                    // Rollback: delete the row if email failed
                    $userId = $stmt->insert_id;
                    $del = $conn->prepare("DELETE FROM users WHERE id = ?");
                    $del->bind_param("i", $userId);
                    $del->execute();

                    $error = "Failed to send verification email. Error: " . htmlspecialchars($mailResult);
                }
            } else {
                $error = "Signup failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - WaterLand</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width:520px;">
    <h2 class="mb-4">Sign Up</h2>
    <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($name ?? ''); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required minlength="6" placeholder="At least 6 characters">
        </div>
        <button type="submit" class="btn btn-primary w-100">Create Account</button>
        <div class="text-center mt-3">
            <a href="login.php" class="btn btn-link">Already have an account? Sign In</a>
        </div>
    </form>
</div>
</body>
</html>
