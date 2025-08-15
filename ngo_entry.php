<?php
session_start();
require_once 'database.php';

$userId = $_SESSION['user']['id'] ?? null;

if (!$userId) {
    header("Location: login.php");
    exit;
}

// Check if user is already an NGO
$stmt = $pdo->prepare("SELECT * FROM ngos WHERE user_id = ?");
$stmt->execute([$userId]);

if ($stmt->rowCount() > 0) {
    // Already registered as NGO
    header("Location: ngo_dashboard.php");
    exit;
} else {
    // Not yet registered
    header("Location: ngo_register.php");
    exit;
}
