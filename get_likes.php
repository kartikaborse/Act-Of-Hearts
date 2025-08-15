<?php
require_once 'database.php';
header('Content-Type: application/json');

$postId = $_GET['post_id'] ?? null;

if (!$postId) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
  SELECT users.name 
  FROM likes 
  JOIN users ON likes.user_id = users.id 
  WHERE likes.post_id = ?
");
$stmt->execute([$postId]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($users);
