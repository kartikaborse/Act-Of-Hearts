<?php
session_start();
require_once 'database.php';

$userId = $_SESSION['user']['id'] ?? null;
$postId = $_POST['post_id'] ?? null;

if (!$userId || !$postId) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid access.']);
    exit;
}

// Check ownership
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->execute([$postId, $userId]);
$post = $stmt->fetch();

if (!$post) {
    echo json_encode(['status' => 'error', 'message' => 'Post not found or unauthorized.']);
    exit;
}

// Optional: Delete associated comments and likes first
$pdo->prepare("DELETE FROM comments WHERE post_id = ?")->execute([$postId]);
$pdo->prepare("DELETE FROM likes WHERE post_id = ?")->execute([$postId]);

// Delete post
$pdo->prepare("DELETE FROM posts WHERE id = ?")->execute([$postId]);

echo json_encode(['status' => 'success', 'message' => 'Post deleted successfully.']);
