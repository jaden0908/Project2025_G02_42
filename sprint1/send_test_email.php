<?php
require_once 'mailer_init.php';


$to = 'leexingjue0908@gmail.com';

$result = sendVerificationOTP($to, 'Test User', '123456'); // 复用发OTP函数
if ($result === true) {
    echo "SMTP OK: test mail sent to $to";
} else {
    echo "SMTP ERROR: " . htmlspecialchars($result);
}
