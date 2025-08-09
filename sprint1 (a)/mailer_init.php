<?php
require __DIR__ . '/phpmailer/PHPMailer.php';
require __DIR__ . '/phpmailer/SMTP.php';
require __DIR__ . '/phpmailer/Exception.php';
require __DIR__ . '/mail_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Send verification OTP email. Return true on success, or error string on failure.
 */
function sendVerificationOTP(string $toEmail, string $toName, string $otp) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
       $mail->Subject = 'Your Universal Studios verification code';

        $mail->Body    = '
            <p>Hello ' . htmlspecialchars($toName) . ',</p>
            <p>Your verification code is: <strong style="font-size:20px;">' . htmlspecialchars($otp) . '</strong></p>
            <p>This code will expire in 10 minutes.</p>
        ';
        $mail->AltBody = "Your verification code is: $otp (valid for 10 minutes).";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}
function sendPasswordResetOTP(string $toEmail, string $toName, string $otp) {
    // Create PHPMailer instance
    $mail = new PHPMailer(true);
    try {
        // SMTP basic config (same as your verification mail)
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        // From / To
        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your password reset code';
        $mail->Body    = '
            <p>Hello ' . htmlspecialchars($toName) . ',</p>
            <p>Your password reset code is: <strong style="font-size:20px;">' . htmlspecialchars($otp) . '</strong></p>
            <p>This code will expire in 10 minutes.</p>
        ';
        $mail->AltBody = "Your password reset code is: $otp (valid for 10 minutes).";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Return error string for debugging on UI
        return $mail->ErrorInfo;
    }
}
