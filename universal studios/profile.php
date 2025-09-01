<?php
session_start();
define('BRAND_NAME', 'Universal Studios');
if (empty($_SESSION['user'])) {
    $_SESSION['flash'] = ['type' => 'warning', 'msg' => 'Please sign in to access your profile.'];
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/mailer_init.php';

/* -------------------- helpers -------------------- */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function verify_csrf($t): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$t);
}
function set_flash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}
function issue_email_otp(mysqli $conn, int $userId, int $minutes = 10): ?string {
    $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expires = (new DateTime('now', new DateTimeZone('Asia/Kuala_Lumpur')))
        ->add(new DateInterval('PT' . $minutes . 'M'))
        ->format('Y-m-d H:i:s');
    $stmt = $conn->prepare("UPDATE users SET otp_code = ?, otp_expires = ? WHERE id = ?");
    $stmt->bind_param("ssi", $code, $expires, $userId);
    return $stmt->execute() ? $code : null;
}

/* -------------------- load user -------------------- */
$uid = (int)($_SESSION['user']['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$user) { session_destroy(); header('Location: login.php'); exit; }

$_SESSION['user']['name']        = $user['name'];
$_SESSION['user']['email']       = $user['email'];
$_SESSION['user']['role']        = $user['role'];
$_SESSION['user']['is_verified'] = (int)$user['is_verified'];

/* -------------------- actions -------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $token  = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) { set_flash('danger', 'Security check failed.'); header('Location: profile.php'); exit; }

    /* Update profile */
    if ($action === 'update_profile') {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash('warning', 'Please provide valid name and email.');
            header('Location: profile.php'); exit;
        }
        $chk = $conn->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
        $chk->bind_param("si", $email, $uid);
        $chk->execute(); $exists = $chk->get_result()->fetch_assoc(); $chk->close();
        if ($exists) { set_flash('warning', 'Email already in use.'); header('Location: profile.php'); exit; }

        $emailChanged = (strcasecmp($email, $user['email']) !== 0);
        if ($emailChanged) {
            $otp  = issue_email_otp($conn, $uid, 10);
            $stmt = $conn->prepare("UPDATE users SET name=?, email=?, is_verified=0 WHERE id=?");
            $stmt->bind_param("ssi", $name, $email, $uid);
            $ok = $stmt->execute(); $stmt->close();
            if ($ok && $otp) { 
                $_SESSION['verify_email'] = $email;
                sendVerificationOTP($email, $name, $otp);
                header("Location: verify_otp.php?email=".urlencode($email)."&sent=1");
                exit;
            } else {
                set_flash('danger', 'Profile update failed.');
            }
        } else {
            $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=?");
            $stmt->bind_param("ssi", $name, $email, $uid);
            $ok = $stmt->execute(); $stmt->close();
            set_flash($ok ? 'success' : 'danger', $ok ? 'Profile updated.' : 'Failed to update.');
        }
        header('Location: profile.php'); exit;
    }

    /* Change password */
    if ($action === 'change_password') {
        $cur     = (string)($_POST['current_password'] ?? '');
        $new     = (string)($_POST['new_password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');

        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) { set_flash('danger', 'Account not found.'); header('Location: profile.php'); exit; }
        if (!password_verify($cur, $row['password'])) {
            set_flash('danger', 'Current password is incorrect.'); header('Location: profile.php'); exit;
        }
        if ($new !== $confirm) {
            set_flash('warning', 'New passwords do not match.'); header('Location: profile.php'); exit;
        }
        if (strlen($new) < 6) {
            set_flash('warning', 'New password must be at least 6 characters.'); header('Location: profile.php'); exit;
        }

        $hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $hash, $uid);
        $ok = $stmt->execute(); $stmt->close();

        set_flash($ok ? 'success' : 'danger', $ok ? 'Password changed successfully.' : 'Failed to update password.');
        header('Location: profile.php'); exit;
    }

    /* Resend verification */
    if ($action === 'resend_otp') {
        if ((int)$user['is_verified']) { set_flash('info', 'Email already verified.'); header('Location: profile.php'); exit; }
        $otp = issue_email_otp($conn, $uid, 10);
        $ok  = $otp && sendVerificationOTP($user['email'], $user['name'], $otp);
        $_SESSION['verify_email'] = $user['email'];
        if ($ok) {
            header("Location: verify_otp.php?email=".urlencode($user['email'])."&sent=1");
        } else {
            set_flash('danger','Send failed.');
            header('Location: profile.php');
        }
        exit;
    }
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?= BRAND_NAME ?> - Profile</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { background: #f4f6f8; font-family: 'Segoe UI', sans-serif; }
.profile-header { text-align: center; padding: 30px 0; background: linear-gradient(135deg, #007bff, #00c6ff); color: white; border-radius: 12px; margin-bottom: 30px; }
.profile-header img { width: 90px; height: 90px; border-radius: 50%; border: 3px solid white; }
.card-custom { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 6px 20px rgba(0,0,0,0.06); margin-bottom: 25px; }
.badge-status { font-size: 0.85rem; padding: 5px 10px; border-radius: 50px; }
</style>
</head>
<body>
<div class="container py-4">
    <div class="profile-header">
        <h3 class="mt-3"><?= htmlspecialchars($user['name']) ?></h3>
        <p><?= htmlspecialchars($user['email']) ?>
            <span class="badge-status <?= $user['is_verified'] ? 'bg-success' : 'bg-warning' ?>">
                <?= $user['is_verified'] ? 'Verified' : 'Unverified' ?>
            </span>
        </p>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['msg']) ?></div>
    <?php endif; ?>

    <div class="card-custom">
        <h5><i class="fa-solid fa-user"></i> Account Overview</h5><hr>
        <p><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></p>
        <p><strong>Member Since:</strong> <?= htmlspecialchars($user['created_at']) ?></p>
    </div>

    <div class="card-custom">
        <h5><i class="fa-solid fa-id-card"></i> Personal Information</h5><hr>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="update_profile">
            <div class="mb-3">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                <small class="text-muted">Changing your email will require re-verification.</small>
            </div>
            <button class="btn btn-primary">Save Changes</button>
        </form>
    </div>

    <div class="card-custom">
        <h5><i class="fa-solid fa-lock"></i> Change Password</h5><hr>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="change_password">
            <div class="mb-3">
                <label>Current Password</label>
                <input type="password" name="current_password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>New Password</label>
                <input type="password" name="new_password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button class="btn btn-danger">Update Password</button>
        </form>
    </div>

    <?php if (!(int)$user['is_verified']): ?>
    <div class="card-custom text-center">
        <p>Your email is not verified. Enter the code after you resend it.</p>
        <div class="d-flex gap-2 justify-content-center">
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="resend_otp">
                <button class="btn btn-warning">Resend Verification Code</button>
            </form>
            <a class="btn btn-outline-primary" 
               href="verify_otp.php?email=<?= urlencode($user['email']) ?>&sent=1">
               Enter Code
            </a>
        </div>
    </div>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="index.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Back to Home</a>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
