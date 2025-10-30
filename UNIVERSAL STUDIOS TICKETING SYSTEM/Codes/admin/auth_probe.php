<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once 'database.php';

function match_password(string $input, string $stored): bool {
    $raw  = $input; $trim = trim($input);
    if (preg_match('/^\$(2y|2b|2a)\$|^\$argon2i\$|^\$argon2id\$/', $stored)) {
        return password_verify($raw, $stored) || ($raw!==$trim && password_verify($trim, $stored));
    }
    if (preg_match('/^[a-f0-9]{32}$/i', $stored)) {
        return hash_equals($stored, md5($raw)) || ($raw!==$trim && hash_equals($stored, md5($trim)));
    }
    return hash_equals($stored, $raw) || ($raw!==$trim && hash_equals($stored, $trim));
}

$email = 'admin2@example.com';
$plain = '123456';

$stmt = $conn->prepare("SELECT id,email,role,is_verified,CHAR_LENGTH(password) len,password FROM users WHERE email=? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

header('Content-Type: text/plain; charset=utf-8');
echo "DATABASE(): ".$conn->query("SELECT DATABASE() db")->fetch_assoc()['db']."\n";
if (!$row) { echo "No such user.\n"; exit; }
echo "Email: {$row['email']}\nRole: {$row['role']}\nVerified: {$row['is_verified']}\nHash len: {$row['len']}\nHash prefix: ".substr($row['password'],0,4)."\n";
echo "match_password('{$plain}'): ".(match_password($plain,$row['password'])?'TRUE':'FALSE')."\n";
