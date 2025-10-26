<?php
// login.php
// Full, drop-in Sign In page with:
// - Secure login using password_hash/password_verify
// - Blocks login if email is not verified (is_verified=1 required)
// - Redirects by role (customer/staff/admin)
// - Shows success message after password reset (?reset=1)
// - Includes "Forgot Password?" link to start email OTP reset flow

session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');

require_once 'database.php'; // must define $conn (mysqli)

// ------------------------------------------------------------------
// UI messages
// ------------------------------------------------------------------
$error   = '';
$success = '';

// Show success banner after password reset
if (isset($_GET['reset']) && $_GET['reset'] == '1') {
    $success = "Your password was updated. Please sign in.";
}

// If already logged in, you may redirect user away from login page (optional)
if (!empty($_SESSION['user'])) {
    // You can change this behavior if you want to allow re-login
    $role = $_SESSION['user']['role'] ?? 'customer';
    if     ($role === 'admin')  { header("Location: admin_dashboard.php");  exit(); }
    elseif ($role === 'staff')  { header("Location: staff_dashboard.php");  exit(); }
    else                        { header("Location: index.php");            exit(); }
}

// ------------------------------------------------------------------
// Handle login POST
// ------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1) Read inputs
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // 2) Basic validation
    if ($email === '' || $password === '') {
        $error = "Please enter both email and password.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // 3) Lookup user
        $stmt = $conn->prepare("SELECT id, name, email, password, role, is_verified FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        // 4) Verify existence
        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();

            // 5) Verify password
            if (password_verify($password, $user['password'])) {

                // 6) Require email verification before login
                if ((int)$user['is_verified'] !== 1) {
                    // keep email in session so verify_otp.php knows which account to verify
                    $_SESSION['verify_email'] = $user['email'];
                    $error = "Your email is not verified yet. Please check your inbox or verify now.";
                    // Tip: your signup flow already redirects to verify_otp.php after registration.
                    // If you have a manual verification page, show a helpful link:
                    // $error .= ' <a href="verify_otp.php" class="alert-link">Verify Email</a>';
                } else {
                    // 7) Success → store minimal info in session
                    $_SESSION['user'] = [
                        'id'    => (int)$user['id'],
                        'name'  => $user['name'],
                        'email' => $user['email'],
                        'role'  => $user['role'],
                    ];

                    // 8) Redirect by role
                    if ($user['role'] === 'admin') {
                        header("Location: admin_dashboard.php");
                    } elseif ($user['role'] === 'staff') {
                        header("Location: staff_dashboard.php");
                    } else {
                        header("Location: index.php"); // customer
                    }
                    exit();
                }
            } else {
                // Wrong password (use generic message to avoid user enumeration)
                $error = "Invalid email or password.";
            }
        } else {
            // Email not found (use same generic message)
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - WaterLand</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS (adjust path if needed) -->
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
        </form>
      </div>
    </div>
  </div>

<!-- Optional: Bootstrap JS (if you use any BS JS components) -->
<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
