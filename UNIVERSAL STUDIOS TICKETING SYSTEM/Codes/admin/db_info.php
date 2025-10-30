<?php
require_once 'database.php';
header('Content-Type: text/plain; charset=utf-8');
echo "host_info: {$conn->host_info}\n";
echo "server_info: {$conn->server_info}\n";
$r = $conn->query("SELECT DATABASE() AS db")->fetch_assoc();
echo "DATABASE(): {$r['db']}\n";
