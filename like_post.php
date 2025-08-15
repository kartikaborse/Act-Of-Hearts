<?php
session_start();
require_once 'database.php';
header('Content-Type: application/json');

$userId = $_SESSION['user']['id'] ?? null;
$postId = $_POST['post_id'] ?? null;

if (!$userId || !$postId) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

// Check if already liked
$stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND post_id = ?");
$stmt->execute([$userId, $postId]);

if ($like = $stmt->fetch()) {
    // Unlike the post
    $delete = $pdo->prepare("DELETE FROM likes WHERE id = ?");
    $delete->execute([$like['id']]);

    // Get updated count
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
    $countStmt->execute([$postId]);
    $likeCount = $countStmt->fetchColumn();

    echo json_encode(['success' => true, 'liked' => false, 'like_count' => $likeCount]);
    exit;
}

// Like the post
$insert = $pdo->prepare("INSERT INTO likes (user_id, post_id, created_at) VALUES (?, ?, NOW())");
$insert->execute([$userId, $postId]);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
$countStmt->execute([$postId]);
$likeCount = $countStmt->fetchColumn();

echo json_encode(['success' => true, 'liked' => true, 'like_count' => $likeCount]);
