<?php
session_start();
require_once 'database.php';

$userId = $_SESSION['user']['id'] ?? null;
if (!$userId) {
    header("Location: login.php");
    exit;
}

$blogId = $_GET['id'] ?? null;
$blog = null;

if (!$blogId) {
    die("Blog ID missing.");
}

// Fetch blog
if ($blogId && $userId) {
    $stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ? AND user_id = ?");
    $stmt->execute([$blogId, $userId]);
    $blog = $stmt->fetch(PDO::FETCH_ASSOC);
}
if (!$blog) {
    echo "<h2 style='color:red;text-align:center;margin-top:40px;'>❌ Blog not found or you don’t have permission to edit it.</h2>";
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $author = $_POST['author'] ?? $blog['author'];

    // Image handling (optional)
    $imagePath = $blog['image_url'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $imageName = basename($_FILES['image']['name']);
        $ext = pathinfo($imageName, PATHINFO_EXTENSION);
        $newName = uniqid('blog_', true) . '.' . $ext;
        $targetPath = $uploadDir . $newName;

        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($ext), $allowed)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $imagePath = $targetPath;
            }
        }
    }

    // Update blog
    $update = $pdo->prepare("UPDATE blogs SET author = ?, title = ?, content = ?, image_url = ? WHERE id = ? AND user_id = ?");
    $update->execute([$author, $title, $content, $imagePath, $blogId, $userId]);

    header("Location: blogs.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Blog - Act of Hearts</title>
  <style>
    body {
      background-color: #fff4f8;
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
    }
    nav {
      background-color: #d6336c;
      padding: 15px 30px;
      display: flex;
      align-items: center;
      color: white;
    }
    nav a {
      color: white;
      text-decoration: none;
      padding: 8px 16px;
      border: 2px solid white;
      border-radius: 8px;
      transition: background 0.3s;
      margin-right: 20px;
    }
    nav a:hover {
      background-color: white;
      color: #d6336c;
    }
    nav h2 {
      margin: 0;
      font-size: 22px;
    }
    .form-container {
      max-width: 600px;
      margin: 40px auto;
      background: white;
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    }
    h2 {
      text-align: center;
      color: #d6336c;
      margin-bottom: 30px;
    }
    input, textarea {
      width: 100%;
      padding: 12px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 10px;
      font-size: 14px;
    }
    button {
      background-color: #e91e63;
      color: white;
      border: none;
      padding: 12px 20px;
      border-radius: 10px;
      font-size: 14px;
      cursor: pointer;
      width: 100%;
      transition: background 0.3s;
    }
    button:hover {
      background-color: #c2185b;
    }
  </style>
</head>
<body>

  <nav>
    <a href="blog_form.php">← Back to Blogs</a>
    <h2>Edit Blog</h2>
   
  </nav>

  <div class="form-container">
    <h2>🛠️ Edit Your Blog</h2>

    <form method="POST" enctype="multipart/form-data">
      <input type="text" name="author" placeholder="Your Name" value="<?= htmlspecialchars($blog['author']) ?>">
      <input type="text" name="title" placeholder="Blog Title" value="<?= htmlspecialchars($blog['title']) ?>" required>
      <textarea name="content" rows="6" required><?= htmlspecialchars($blog['content']) ?></textarea>
      <input type="file" name="image" accept="image/*">
      <?php if (!empty($blog['image_url'])): ?>
        <p>Current Image:</p>
        <img src="<?= $blog['image_url'] ?>" alt="Current Image" style="max-width:100%; border-radius:10px;">
      <?php endif; ?>
      <button type="submit">Update Blog</button>
    </form>
  </div>

</body>
</html>
