<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user']['id']) || !isset($_GET['post_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user']['id'];
$postId = $_GET['post_id'];

// Add comment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = trim($_POST['comment']);
    if (!empty($text)) {
        $stmt = $pdo->prepare("INSERT INTO comments (user_id, post_id, comment_text) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $postId, $text]);
    }
}

// Fetch comments
$stmt = $pdo->prepare("SELECT c.comment_text, u.name FROM comments c JOIN users u ON c.user_id = u.id WHERE post_id = ? ORDER BY c.created_at DESC");
$stmt->execute([$postId]);
$comments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head><title>Comments</title></head>
<body>
<h2>Comments</h2>
<form method="POST">
    <textarea name="comment" rows="2" required></textarea><br>
    <button type="submit">Add Comment</button>
</form>
<hr>
<?php foreach ($comments as $comment): ?>
    <p><strong><?= htmlspecialchars($comment['name']) ?>:</strong> <?= htmlspecialchars($comment['comment_text']) ?></p>
<?php endforeach; ?>
</body>
</html>
