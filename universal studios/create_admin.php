<?php
// create_admin.php  —— 一键创建/升级 Admin 账号
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once 'database.php';
header('Content-Type: text/plain; charset=utf-8');

// ==== 1) 填你的新管理员账号 ====
$email = 'newadmin@example.com';
$plain = 'ChangeMe#123';  // 可改。初次给同事后让他登录后改密

// ==== 2) 现场生成 bcrypt ====
$hash = password_hash($plain, PASSWORD_BCRYPT);

// ==== 3) 插入（若已存在则升级为 admin 并改密）====
$sql = "INSERT INTO `users` (`name`,`email`,`password`,`role`,`is_verified`,`created_at`)
        VALUES (?, ?, ?, 'admin', 1, NOW())
        ON DUPLICATE KEY UPDATE
          `password`=VALUES(`password`),
          `role`='admin',
          `is_verified`=1";
$stmt = $conn->prepare($sql);
$name = 'System Admin';
$stmt->bind_param("sss", $name, $email, $hash);
$stmt->execute();

// ==== 4) 读回核验 ====
$r = $conn->prepare("SELECT `email`,`role`,`is_verified`,CHAR_LENGTH(`password`) len
                     FROM `users` WHERE `email`=? LIMIT 1");
$r->bind_param("s", $email);
$r->execute();
$row = $r->get_result()->fetch_assoc();

echo "DATABASE(): ".$conn->query("SELECT DATABASE() db")->fetch_assoc()['db']."\n";
echo "Email: {$row['email']}\nRole: {$row['role']}\nVerified: {$row['is_verified']}\nHash len: {$row['len']}\n";
echo "password_verify: "; var_dump(password_verify($plain, $hash));

echo "\n== PLEASE CHANGE PASSWORD & EMAIL RIGHT NOW!==\n";
echo "Email: {$email}\nPassword: {$plain}\n";
