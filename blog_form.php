<?php
session_start();
require_once 'database.php';

$userId = $_SESSION['user']['id'] ?? null;
if (!$userId) {
    header("Location: login.php");
    exit;
}

$imagePath = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['content'])) {
    $author = htmlspecialchars($_POST['author'] ?? 'Anonymous');
    $title = htmlspecialchars($_POST['title']);
    $content = htmlspecialchars($_POST['content']);

    // ✅ Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $imageName = basename($_FILES['image']['name']);
        $imageExt = pathinfo($imageName, PATHINFO_EXTENSION);
        $uniqueName = uniqid('blog_', true) . '.' . $imageExt;
        $targetFile = $uploadDir . $uniqueName;

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($imageExt), $allowedTypes)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $imagePath = $targetFile;
            }
        }
    }

    // Insert into DB
    $stmt = $pdo->prepare("INSERT INTO blogs (author, title, content, image_url, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$author, $title, $content, $imagePath]);
    header("Location: blogs.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add New Blog - Act of Hearts</title>
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
    <a href="blogs.php">← Blogs</a>
    <h2>Add Blog Post</h2>

  </nav>

  <div class="form-container">
   <h2>✍️ Share a New Blog</h2>

     <form method="POST" enctype="multipart/form-data">
      <input type="text" name="author" placeholder="Your Name (optional)">
      <input type="text" name="title" placeholder="Blog Title" required>
      <textarea name="content" rows="6" placeholder="Write your blog or article..." required></textarea>
      <input type="file" name="image" accept="image/*">
      <button type="submit">Post Blog</button>
    </form>
  </div>

</body>
</html>




