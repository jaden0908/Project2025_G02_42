<?php
session_start();

// Check if user is logged in and role is staff
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Dashboard</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?> (Staff)</h1>
    <p>This is the staff dashboard. You can manage packages, customers, and more.</p>
    <a href="logout.php" class="btn btn-danger">Sign Out</a>
</div>
</body>
</html>
