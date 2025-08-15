<?php
session_start();
require_once 'database.php';

$userId = $_SESSION['user']['id'] ?? null;
$postId = $_POST['post_id'] ?? null;
$content = trim($_POST['content'] ?? '');

if (!$userId || !$postId || empty($content)) {
    header('Location: dashboard.php');
    exit;
}

$stmt = $pdo->prepare("INSERT INTO comments (user_id, post_id, content, created_at) VALUES (?, ?, ?, NOW())");
$stmt->execute([$userId, $postId, $content]);

header("Location: dashboard.php");
exit;
