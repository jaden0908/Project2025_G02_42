<?php
// force_set_admin_password.php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once 'database.php';

header('Content-Type: text/plain; charset=utf-8');

$email = 'admin2@example.com';
$plain = '123456';

// 1) 在本机生成 bcrypt
$hash = password_hash($plain, PASSWORD_BCRYPT);

// 2) 写回数据库（顺便确保角色与验证状态）
$stmt = $conn->prepare("UPDATE `users` SET `password`=?, `role`='admin', `is_verified`=1 WHERE `email`=?");
$stmt->bind_param("ss", $hash, $email);
$stmt->execute();
echo "UPDATE affected rows: {$stmt->affected_rows}\n";

// 3) 重新取出并验证
$stmt = $conn->prepare("SELECT `id`,`email`,`role`,`is_verified`, `password`, CHAR_LENGTH(`password`) AS len FROM `users` WHERE `email`=? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

echo "Email: {$row['email']}\nRole: {$row['role']}\nVerified: {$row['is_verified']}\n";
echo "Hash len: {$row['len']}\nHash: {$row['password']}\n";
echo "password_verify('{$plain}'): ";
var_dump(password_verify($plain, $row['password']));

// 4) 检查是否有重复邮箱（以防 LIMIT 1 读到另一条）
$dup = $conn->query("SELECT COUNT(*) AS c FROM `users` WHERE `email`='{$conn->real_escape_string($email)}'")->fetch_assoc();
echo "Rows with this email: {$dup['c']}\n";
