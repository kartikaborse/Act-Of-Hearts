<?php
session_start();
require_once 'database.php';

$userId = $_SESSION['user']['id'] ?? null;
$commentId = $_POST['comment_id'] ?? null;

if (!$userId || !$commentId) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid access.']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM comments WHERE id = ? AND user_id = ?");
$stmt->execute([$commentId, $userId]);
$comment = $stmt->fetch();

if (!$comment) {
    echo json_encode(['status' => 'error', 'message' => 'Comment not found or unauthorized.']);
    exit;
}

$pdo->prepare("DELETE FROM comments WHERE id = ?")->execute([$commentId]);

echo json_encode(['status' => 'success', 'message' => 'Comment deleted successfully.']);
