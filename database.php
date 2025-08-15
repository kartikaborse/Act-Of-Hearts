<?php
// db_connect.php

$host = 'localhost';
$db   = 'actofhearts'; // change this to your database name
$user = 'root';        // change if needed
$pass = '';            // change if your DB has a password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
  $pdo = new PDO($dsn, $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}
?>
