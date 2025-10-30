<?php
// login.php
// - Secure login with backward compatibility (password_hash, md5, plaintext)
// - Prevents archived users (deleted_at IS NOT NULL) from signing in
// - Admin bypasses email verification
// - Role-based redirection
// - Shows success message after password reset (?reset=1)

ob_start();
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');

require_once 'database.php'; // must define $conn (mysqli)
// require_once 'mailer_init.php'; // ← 若你要用 PHPMailer 发送 OTP，解开此行并用 sendVerificationOTP()

function match_password(string $input, string $stored): bool {
    $raw  = $input;
    $trim = trim($input);

    if (preg_match('/^\$(2y|2b|2a)\$|^\$argon2i\$|^\$argon2id\$/', $stored)) {
        if (password_verify($raw, $stored)) return true;
        if ($raw !== $trim && password_verify($trim, $stored)) return true;
        return false;
    }
    if (preg_match('/^[a-f0-9]{32}$/i', $stored)) {
        if (hash_equals($stored, md5($raw))) return true;
        if ($raw !== $trim && hash_equals($stored, md5($trim))) return true;
        return false;
    }
    if (hash_equals($stored, $raw)) return true;
    if ($raw !== $trim && hash_equals($stored, $trim)) return true;
    return false;
}

$error   = '';
$success = '';

if (isset($_GET['reset']) && $_GET['reset'] == '1') {
    $success = "Your password was updated. Please sign in.";
}

if (!empty($_SESSION['user'])) {
    $role = $_SESSION['user']['role'] ?? 'customer';
    if     ($role === 'admin')  { header("Location: admin_dashboard.php");  exit(); }
    elseif ($role === 'staff')  { header("Location: staff_dashboard.php");  exit(); }
    else                        { header("Location: index.php");            exit(); }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = "Please enter both email and password.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $stmt = $conn->prepare(
            "SELECT id, name, email, password, role, is_verified
             FROM users
             WHERE email = ? AND deleted_at IS NULL
             LIMIT 1"
        );
        if ($stmt === false) {
            $error = "Database error (prepare failed).";
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res && $res->num_rows === 1) {
                $user = $res->fetch_assoc();

                if (match_password($password, $user['password'])) {

                    $needsVerify = ($user['role'] !== 'admin') && ((int)$user['is_verified'] !== 1);

                    if ($needsVerify) {
                        // --- ensure otp columns exist ---
                        $col1 = $conn->query("SHOW COLUMNS FROM users LIKE 'otp_code'");
                        if ($col1 && $col1->num_rows === 0) {
                            $conn->query("ALTER TABLE users ADD COLUMN otp_code VARCHAR(10) NULL");
                        }
                        $col2 = $conn->query("SHOW COLUMNS FROM users LIKE 'otp_expires'");
                        if ($col2 && $col2->num_rows === 0) {
                            $conn->query("ALTER TABLE users ADD COLUMN otp_expires DATETIME NULL");
                        }

                        // --- generate OTP ---
                        $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                        $expiresAt = date('Y-m-d H:i:s', time() + 10*60);

                        $stmtOtp = $conn->prepare("UPDATE users SET otp_code=?, otp_expires=? WHERE id=?");
                        $stmtOtp->bind_param('ssi', $otp, $expiresAt, $user['id']);
                        $stmtOtp->execute();
                        $stmtOtp->close();

                        $_SESSION['verify_email'] = $user['email'];

                        // --- send OTP email ---
                        // 如果你已接好 PHPMailer，建议用：
                        // $send = sendVerificationOTP($user['email'], $user['name'], $otp);
                        // if ($send !== true) { $error = "Failed to send email: ".htmlspecialchars((string)$send); }
                        // else { header("Location: verify_otp.php?sent=1"); exit(); }

                        // 临时用 PHP mail()（很多环境发不出，建议尽快换上面那套）
                        $subject = "Your Verification Code";
                        $msg = "Hi {$user['name']},\n\nYour verification code is: {$otp}\nThis code will expire in 10 minutes.\n\nThanks.";
                        @mail($user['email'], $subject, $msg, "From: no-reply@yourdomain.com");
                        header("Location: verify_otp.php?sent=1");
                        exit();

                    } else {
                        // ✅ already verified → log in and redirect
                        $_SESSION['user'] = [
                            'id'    => (int)$user['id'],
                            'name'  => $user['name'],
                            'email' => $user['email'],
                            'role'  => $user['role'],
                        ];

                        if ($user['role'] === 'admin') {
                            header("Location: admin_dashboard.php");
                        } elseif ($user['role'] === 'staff') {
                            header("Location: staff_dashboard.php");
                        } else {
                            header("Location: index.php");
                        }
                        exit();
                    }

                } else {
                    $error = "Invalid email or password.";
                }
            } else {
                // maybe archived
                $chk = $conn->prepare("SELECT id FROM users WHERE email=? AND deleted_at IS NOT NULL LIMIT 1");
                $chk->bind_param("s", $email);
                $chk->execute();
                $chk->store_result();
                if ($chk->num_rows > 0) {
                    $error = "Your account has been archived. Please contact support.";
                } else {
                    $error = "Invalid email or password.";
                }
                $chk->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Universal Studios</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css?v=999" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body class="login-page">

  <div class="login-wrapper" role="main">
    <!-- LEFT: fullscreen-cover image (fills left half) -->
    <div class="image-side" aria-hidden="true">
       <img src="img/background.jpg" alt="Login Visual">
    </div>


    <!-- RIGHT: form section -->
    <div class="form-side">
      <div class="form-card" role="region" aria-label="Sign in form">
        <div class="title-area">
          <svg viewBox="0 0 500 200" class="arch-text">
           <path id="curve" d="M50,200 A200,100 0 0,1 450,200" fill="transparent" />
           <text width="500">
              <textPath xlink:href="#curve" startOffset="50%" text-anchor="middle" style="font-size:30px; font-weight:700; fill:#113b4a;">UNIVERSAL STUDIOS</textPath>
           </text>
          </svg>
          <i class="fa-solid fa-globe globe-icon"></i>
        </div>
        <h2 class="sign-title">Sign In</h2>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
          <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input
              id="email"
              type="email"
              name="email"
              class="form-control"
              placeholder="you@example.com"
              required
              value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input
              id="password"
              type="password"
              name="password"
              class="form-control"
              required
              placeholder="Your password">
          </div>

          <button type="submit" class="btn btn-primary btn-submit w-100">Sign In</button>

          <div class="d-flex justify-content-between mt-3">
             <a href="forgot_password.php" class="btn btn-link p-0">Forgot Password?</a>
          </div>

          <div class="signup-text"> Don’t have an account?
             <a href="signup.php">Sign Up</a>
          </div>

         <div class="text-center mt-3">
            <a href="signup_admin_staff.php" class="btn btn-outline-secondary btn-sm mt-1">
                Sign In as Admin/Staff</a>
        </div>

        </form>
      </div>
    </div>
  </div>

<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
