<?php
session_start();
require_once 'database.php';

header('Content-Type: application/json');

$userId = $_SESSION['user']['id'] ?? null;
if (!$userId) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

// Total posts
$posts = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE user_id = ?");
$posts->execute([$userId]);
$postCount = $posts->fetchColumn();

// Total likes on user's posts
$likes = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id IN (SELECT id FROM posts WHERE user_id = ?)");
$likes->execute([$userId]);
$likeCount = $likes->fetchColumn();

// Comments made by user
$comments = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE user_id = ?");
$comments->execute([$userId]);
$commentCount = $comments->fetchColumn();

// Volunteering hours — placeholder, defaulting to 0
$volHours = 0; // You can replace this by fetching from a `volunteering_log` table

echo json_encode([
    'posts' => $postCount,
    'likes' => $likeCount,
    'comments' => $commentCount,
    'hours' => $volHours
]);
