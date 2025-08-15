<?php
session_start();
require_once 'database.php';

header('Content-Type: application/json');

$userId = $_SESSION['user']['id'] ?? null;

if (!$userId) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'Image upload failed.']);
    exit;
}

$caption = $_POST['caption'] ?? ''; // Optional caption
$image = $_FILES['image'];
$ext = pathinfo($image['name'], PATHINFO_EXTENSION);
$filename = 'uploads/' . uniqid('post_') . '.' . $ext;

if (!move_uploaded_file($image['tmp_name'], $filename)) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save image.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, caption, image_url, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$userId, $caption, $filename]);

    echo json_encode(['status' => 'success', 'message' => 'Post uploaded!']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

