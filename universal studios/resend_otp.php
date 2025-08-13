<?php
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');

include 'database.php';
require_once 'mailer_init.php';

$verifyEmail = $_SESSION['verify_email'] ?? '';
if ($verifyEmail === '') {
    header("Location: verify_otp.php");
    exit();
}

$stmt = $conn->prepare("SELECT id, name, is_verified FROM users WHERE email = ?");
$stmt->bind_param("s", $verifyEmail);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    header("Location: verify_otp.php");
    exit();
}

$user = $res->fetch_assoc();
if (intval($user['is_verified']) === 1) {
    $_SESSION['flash_success'] = "Your email is already verified. Please sign in.";
    header("Location: login.php");
    exit();
}

$otp       = strval(random_int(100000, 999999));
$expiresAt = (new DateTime('+10 minutes'))->format('Y-m-d H:i:s');

$upd = $conn->prepare("UPDATE users SET otp_code = ?, otp_expires = ? WHERE email = ?");
$upd->bind_param("sss", $otp, $expiresAt, $verifyEmail);
$upd->execute();

$send = sendVerificationOTP($verifyEmail, $user['name'], $otp);
header("Location: verify_otp.php?resend=" . ($send === true ? "1" : "0"));
exit();
