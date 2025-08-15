<?php
// Run this once to add an admin (or do it from PHPMyAdmin)
require_once 'database.php';

$username = 'admin';
$password = password_hash('admin123', PASSWORD_DEFAULT); // Secure password hash

$stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
$stmt->execute([$username, $password]);

echo "Admin created.";
?>
