<?php
session_start();
require_once 'database.php';

header('Content-Type: application/json');

$userId = $_SESSION['user']['id'] ?? null;
$blogId = $_POST['blog_id'] ?? null;

if (!$userId || !$blogId) {
    echo json_encode(['status' => 'error']);
    exit;
}

// Check if already liked
$stmt = $pdo->prepare("SELECT * FROM blog_likes WHERE user_id = ? AND blog_id = ?");
$stmt->execute([$userId, $blogId]);
$alreadyLiked = $stmt->fetch();

if ($alreadyLiked) {
    $pdo->prepare("DELETE FROM blog_likes WHERE user_id = ? AND blog_id = ?")->execute([$userId, $blogId]);
    $pdo->prepare("UPDATE blogs SET likes = likes - 1 WHERE id = ?")->execute([$blogId]);
    echo json_encode(['status' => 'unliked']);
} else {
    $pdo->prepare("INSERT INTO blog_likes (user_id, blog_id) VALUES (?, ?)")->execute([$userId, $blogId]);
    $pdo->prepare("UPDATE blogs SET likes = likes + 1 WHERE id = ?")->execute([$blogId]);
    echo json_encode(['status' => 'liked']);
}
?>
