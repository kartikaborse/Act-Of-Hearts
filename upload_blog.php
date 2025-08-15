<?php
require_once 'database.php';

$author = $_POST['author'];
$title = $_POST['title'];
$content = $_POST['content'];
$imagePath = '';

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $imageName = time() . '_' . basename($_FILES['image']['name']);
    $targetDir = 'uploads/';
    $targetFile = $targetDir . $imageName;

    // Create the uploads directory if not exists
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
        $imagePath = $targetFile;
    }
}

// Insert blog into DB
$stmt = $pdo->prepare("INSERT INTO blogs (author, title, content, image_url) VALUES (?, ?, ?, ?)");
$stmt->execute([$author, $title, $content, $imagePath]);

header("Location: blogs.php");
exit;
?>
