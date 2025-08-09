<?php
$servername = "localhost";  // Database server, usually localhost
$username   = "root";       // Default XAMPP username
$password   = "";           // Default XAMPP password is empty
$dbname     = "waterland";  // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
