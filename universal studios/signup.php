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
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="signup-page">

<div class="signup-wrapper" role="main">
  <!-- LEFT SIDE -->
  <div class="image-side" aria-hidden="true">
    <img src="img/background.jpg" alt="Signup Visual">
  </div>

  <!-- RIGHT SIDE -->
  <div class="form-side">
    <div class="title-area">
      <svg viewBox="0 0 500 200" class="arch-text">
        <path id="curve" d="M50,150 A200,100 0 0,1 450,150" fill="transparent" />
        <text width="500">
          <textPath href="#curve" startOffset="50%" text-anchor="middle">
            UNIVERSAL STUDIOS
          </textPath>
        </text>
      </svg>
      <i class="fa-solid fa-globe globe-icon"></i>
      <h2 class="sign-title">Sign Up</h2>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($name ?? '') ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($email ?? '') ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required minlength="6">
      </div>

      <button type="submit" class="btn btn-primary">Create Account</button>

      <div class="signin-text">
        Already have an account?
        <a href="login.php">Sign In</a>
      </div>
    </form>
  </div>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>