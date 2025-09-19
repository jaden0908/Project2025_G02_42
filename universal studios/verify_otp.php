<?php
// verify_otp.php
// - Verify email with 6-digit OTP stored on users(otp_code, otp_expires, otp_sent_at)
// - Resend via GET ?resend=1 using sendVerificationOTP() from mailer_init.php
// - On success: set is_verified=1, clear OTP fields, redirect to login
// - Includes 60s resend cooldown and deleted_at checks

ob_start();
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');

require_once __DIR__ . '/database.php';       // $conn (mysqli)
require_once __DIR__ . '/mailer_init.php';    // uses sendVerificationOTP()

// ---------- helpers ----------
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// ---------- ensure columns exist (idempotent) ----------
$c1 = $conn->query("SHOW COLUMNS FROM users LIKE 'otp_code'");
if ($c1 && $c1->num_rows === 0) {
  $conn->query("ALTER TABLE users ADD COLUMN otp_code VARCHAR(10) NULL");
}
$c2 = $conn->query("SHOW COLUMNS FROM users LIKE 'otp_expires'");
if ($c2 && $c2->num_rows === 0) {
  $conn->query("ALTER TABLE users ADD COLUMN otp_expires DATETIME NULL");
}
$c3 = $conn->query("SHOW COLUMNS FROM users LIKE 'otp_sent_at'");
if ($c3 && $c3->num_rows === 0) {
  $conn->query("ALTER TABLE users ADD COLUMN otp_sent_at DATETIME NULL");
}

// ---------- state ----------
$sessionEmail = trim($_SESSION['verify_email'] ?? '');
$displayInfo  = '';
$error        = '';
$success      = '';

// info banner when redirected from login
if (isset($_GET['sent']) && $_GET['sent'] == '1') {
  $displayInfo = "A verification code has been sent to your email.";
}

// =====================================================================
// RESEND FLOW (?resend=1) — uses sendVerificationOTP()
// =====================================================================
if (isset($_GET['resend']) && $_GET['resend'] == '1') {
  if ($sessionEmail === '') {
    $error = "Missing email in session. Please sign in again.";
  } else {
    // get name + previous sent time
    $stmtInfo = $conn->prepare("SELECT name, otp_sent_at FROM users WHERE email=? AND deleted_at IS NULL LIMIT 1");
    $stmtInfo->bind_param('s', $sessionEmail);
    $stmtInfo->execute();
    $rowInfo = $stmtInfo->get_result()->fetch_assoc();
    $stmtInfo->close();

    if (!$rowInfo) {
      $error = "Account not found or has been archived.";
    } else {
      $name   = $rowInfo['name'] ?? '';
      $now    = time();
      $last   = !empty($rowInfo['otp_sent_at']) ? strtotime($rowInfo['otp_sent_at']) : 0;
      $cooldownSeconds = 60;

      if ($last && ($now - $last) < $cooldownSeconds) {
        $error = "Please wait ".($cooldownSeconds - ($now - $last))." seconds before resending.";
      } else {
        // generate OTP
        $otp       = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', $now + 10*60);
        $sentAt    = date('Y-m-d H:i:s', $now);

        // save OTP + timestamps
        $up = $conn->prepare("UPDATE users SET otp_code=?, otp_expires=?, otp_sent_at=? WHERE email=? AND deleted_at IS NULL LIMIT 1");
        $up->bind_param('ssss', $otp, $expiresAt, $sentAt, $sessionEmail);
        $up->execute();
        $okUpdate = ($up->affected_rows >= 0);
        $up->close();

        if ($okUpdate) {
          // send via SMTP
          $send = sendVerificationOTP($sessionEmail, $name, $otp);
          if ($send === true) {
            $success = "A new code has been sent.";
          } else {
            $error = "Failed to send email: " . e((string)$send);
          }
        } else {
          $error = "Unable to update OTP for this account.";
        }
      }
    }
  }
}

// =====================================================================
// VERIFY FLOW (POST)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $inputOtp = trim($_POST['otp'] ?? '');
  $emailFromForm = trim($_POST['email'] ?? '');
  $emailToUse = $sessionEmail !== '' ? $sessionEmail : $emailFromForm;

  if ($emailToUse === '' || $inputOtp === '') {
    $error = "Please enter both Email and OTP.";
  } elseif (!preg_match('/^\d{6}$/', $inputOtp)) {
    $error = "Please enter the 6-digit code.";
  } else {
    $q = $conn->prepare("SELECT id,name,email,role,is_verified,otp_code,otp_expires FROM users WHERE email=? AND deleted_at IS NULL LIMIT 1");
    $q->bind_param('s', $emailToUse);
    $q->execute();
    $res  = $q->get_result();
    $user = $res ? $res->fetch_assoc() : null;
    $q->close();

    if (!$user) {
      $error = "No active account found for this email.";
    } elseif ((int)$user['is_verified'] === 1) {
      $error = "This email is already verified. You can sign in.";
    } else {
      $nowTs = time();
      $expTs = !empty($user['otp_expires']) ? strtotime($user['otp_expires']) : 0;

      if (empty($user['otp_code']) || !hash_equals($user['otp_code'], $inputOtp)) {
        $error = "Invalid OTP. Please check and try again.";
      } elseif ($expTs > 0 && $nowTs > $expTs) {
        $error = "The code has expired. Please resend a new code.";
      } else {
        // success → verify & clear OTP
        $u = $conn->prepare("UPDATE users SET is_verified=1, otp_code=NULL, otp_expires=NULL, otp_sent_at=NULL WHERE email=? LIMIT 1");
        $u->bind_param('s', $emailToUse);
        if ($u->execute()) {
          $u->close();
          unset($_SESSION['verify_email']);
          $_SESSION['flash_success'] = "Email verified successfully. You can now sign in.";
          header("Location: login.php");
          exit();
        } else {
          $u->close();
          $error = "Failed to update account. Please try again.";
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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css?v=2" rel="stylesheet">
</head>
<body class="verify-otp-page">
<div class="container mt-5" style="max-width:520px;">
  <h2 class="mb-3">Verify Your Email</h2>

  <?php if (!empty($displayInfo)): ?>
    <div class="alert alert-info"><?= e($displayInfo) ?></div>
  <?php endif; ?>
  <?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <?php if ($sessionEmail === ''): ?>
      <div class="mb-3">
        <label class="form-label">Email (the one you used to sign up)</label>
        <input type="email" name="email" class="form-control" required>
      </div>
    <?php else: ?>
      <div class="mb-2">
        <small class="text-muted">Verifying: <?= e($sessionEmail) ?></small>
      </div>
    <?php endif; ?>

    <div class="mb-3">
      <label class="form-label">Verification Code (OTP)</label>
      <input type="text" name="otp" class="form-control" inputmode="numeric" pattern="\d{6}" maxlength="6" placeholder="6-digit code" required>
    </div>

    <button type="submit" class="btn btn-primary w-100">Verify</button>
  </form>

  <div class="text-center mt-3">
    <a href="verify_otp.php?resend=1" class="btn btn-link">Resend code</a>
  </div>
</div>
</body>
</html>
