<?php
session_start();
require_once 'database.php';

header('Content-Type: application/json');

$userId = $_SESSION['user']['id'] ?? null;
$blogId = $_POST['blog_id'] ?? null;
$comment = trim($_POST['comment'] ?? '');

if (!$userId || !$blogId || !$comment) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO blog_comments (user_id, blog_id, comment) VALUES (?, ?, ?)");
if ($stmt->execute([$userId, $blogId, $comment])) {
    echo json_encode(['status' => 'success', 'comment' => htmlspecialchars($comment)]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save comment']);
}
