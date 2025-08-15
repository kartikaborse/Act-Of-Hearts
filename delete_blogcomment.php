<?php
session_start();
require_once 'database.php';

$userId = $_SESSION['user']['id'] ?? null;
$commentId = $_POST['comment_id'] ?? null;

if (!$userId || !$commentId) {
    echo json_encode(['status' => 'error']);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM blog_comments WHERE id = ? AND user_id = ?");
$stmt->execute([$commentId, $userId]);

echo json_encode(['status' => 'deleted']);
