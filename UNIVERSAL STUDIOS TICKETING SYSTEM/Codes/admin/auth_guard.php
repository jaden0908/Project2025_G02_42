<?php
// auth_guard.php
// - To be included on all protected pages after database.php
// - Checks if the logged-in user is still active (deleted_at IS NULL)

if (!empty($_SESSION['user']['id'])) {
    $uid = (int)$_SESSION['user']['id'];
    $stmt = $conn->prepare("SELECT id, name, email, role FROM users WHERE id=? AND deleted_at IS NULL LIMIT 1");
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    $userRow = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if (!$userRow) {
        // account was archived or deleted → force logout
        session_unset();
        session_destroy();
        header('Location: login.php?msg=archived');
        exit;
    }

    // Optional: refresh session info in case admin updated name/email
    $_SESSION['user']['name']  = $userRow['name'];
    $_SESSION['user']['email'] = $userRow['email'];
    $_SESSION['user']['role']  = $userRow['role'];
}
