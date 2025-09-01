<?php
// login.php
// - Secure login (password_hash/password_verify) + MD5/plaintext backward-compat
// - Admin can bypass email verification
// - Redirects by role (customer/staff/admin)
// - Shows success message after password reset (?reset=1)

ob_start(); // 防止任何意外输出破坏 header()
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');

require_once 'database.php'; // must define $conn (mysqli)

// ---------------- Password matcher (兼容多种历史格式) ----------------
function match_password(string $input, string $stored): bool {
    $raw  = $input;
    $trim = trim($input);

    // 1) password_hash 系列（bcrypt/argon2）
    if (preg_match('/^\$(2y|2b|2a)\$|^\$argon2i\$|^\$argon2id\$/', $stored)) {
        if (password_verify($raw, $stored)) return true;
        if ($raw !== $trim && password_verify($trim, $stored)) return true;
        return false;
    }
    // 2) 旧 MD5（32位十六进制）
    if (preg_match('/^[a-f0-9]{32}$/i', $stored)) {
        if (hash_equals($stored, md5($raw))) return true;
        if ($raw !== $trim && hash_equals($stored, md5($trim))) return true;
        return false;
    }
    // 3) 兜底：明文对比（仅为兼容历史数据，尽快迁移）
    if (hash_equals($stored, $raw)) return true;
    if ($raw !== $trim && hash_equals($stored, $trim)) return true;

    return false;
}

// ---------------- UI messages ----------------
$error   = '';
$success = '';

// 密码重置成功提示
if (isset($_GET['reset']) && $_GET['reset'] == '1') {
    $success = "Your password was updated. Please sign in.";
}

// 已登录则按角色跳转
if (!empty($_SESSION['user'])) {
    $role = $_SESSION['user']['role'] ?? 'customer';
    if     ($role === 'admin')  { header("Location: admin_dashboard.php");  exit(); }
    elseif ($role === 'staff')  { header("Location: staff_dashboard.php");  exit(); }
    else                        { header("Location: index.php");            exit(); }
}

// ---------------- Handle POST ----------------
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
             WHERE email = ?
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
                    // 非 admin 才要求邮箱验证
                    $needsVerify = ($user['role'] !== 'admin') && ((int)$user['is_verified'] !== 1);

                    if ($needsVerify) {
                        $_SESSION['verify_email'] = $user['email'];
                        $error = "Your email is not verified yet. Please check your inbox or verify now.";
                    } else {
                        // 登录成功 → 写入会话并按角色跳转
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
                $error = "Invalid email or password.";
            }
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
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css?v=999" rel="stylesheet">
</head>
<body class="login-page">
<div class="container mt-5" style="max-width:520px;">
    <h2 class="mb-4 text-center">Sign In</h2>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label">Email address</label>
            <input
                type="email"
                name="email"
                class="form-control"
                placeholder="you@example.com"
                required
                value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input
                type="password"
                name="password"
                class="form-control"
                required
                placeholder="Your password">
        </div>

        <button type="submit" class="btn btn-primary w-100">Sign In</button>

        <div class="d-flex justify-content-between mt-3">
            <a href="forgot_password.php" class="btn btn-link p-0">Forgot Password?</a>
            <a href="signup.php" class="btn btn-link p-0">Don't have an account? Sign Up</a>
        </div>

        <div class="text-center mt-3">
            <a href="signup_admin_staff.php" class="btn btn-outline-secondary btn-sm mt-1">
                Sign In as Admin/Staff
            </a>
        </div>
    </form>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
