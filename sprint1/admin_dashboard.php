<?php
session_start();

// Check if user is logged in and role is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?> (Admin)</h1>
    <p>This is the admin dashboard. You can manage staff, packages, customers, and view reports.</p>
    <a href="logout.php" class="btn btn-danger">Sign Out</a>
</div>
</body>
</html>
