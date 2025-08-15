<?php
session_start();
require_once 'database.php';

$profileId = $_SESSION['user']['id'] ?? null;

if (!$profileId) {
    header("Location: login.php");
    exit();
}

// Fetch or create profile
$stmt = $pdo->prepare("SELECT * FROM profile WHERE id = ?");
$stmt->execute([$profileId]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

// Auto-create profile if it doesn't exist
if (!$profile) {
    $stmt = $pdo->prepare("INSERT INTO profile (id, name, email) VALUES (?, ?, ?)");
    $stmt->execute([
        $profileId,
        $_SESSION['user']['name'] ?? '',
        $_SESSION['user']['email'] ?? ''
    ]);
    // Fetch new profile
    $stmt = $pdo->prepare("SELECT * FROM profile WHERE id = ?");
    $stmt->execute([$profileId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $bio = $_POST['bio'] ?? '';
    $location = $_POST['location'] ?? '';
    $role = $_POST['role'] ?? '';
    $services = $_POST['services'] ?? '';
    $hashtags = $_POST['hashtags'] ?? '';

    $profileImagePath = $profile['profile_image'] ?? '';

    // Handle new image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
            $imageName = time() . '_' . basename($_FILES['profile_image']['name']);
            $target = 'uploads/' . $imageName;

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target)) {
                $profileImagePath = $target;
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Only JPG, JPEG, and PNG files are allowed.";
        }
    }

    if (empty($error)) {
        $stmt = $pdo->prepare("UPDATE profile SET name=?, email=?, phone=?, bio=?, location=?, profile_image=?, hashtags=?, role=?, services=? WHERE id=?");
        $stmt->execute([$name, $email, $phone, $bio, $location, $profileImagePath, $hashtags, $role, $services, $profileId]);
        $success = "Profile updated successfully!";

        // Refresh profile
        $stmt = $pdo->prepare("SELECT * FROM profile WHERE id = ?");
        $stmt->execute([$profileId]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Profile</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: url('images/heart tree.webp') no-repeat center center fixed;
      background-size: cover;
      padding: 30px;
      position: relative;
    }
    body::before {
      content: "";
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(255, 255, 255, 0.5);
      z-index: -1;
    }

    .container {
      background: rgba(255, 255, 255, 0.95);
      padding: 25px 30px;
      max-width: 600px;
      margin: auto;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      border-radius: 8px;
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #333;
    }

    label {
      font-weight: bold;
      display: block;
      margin-top: 10px;
      margin-bottom: 5px;
    }

    input, textarea {
      width: 100%;
      padding: 10px;
      margin-bottom: 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
    }

    textarea {
      resize: vertical;
    }

    button {
      background-color: #d63384;
      color: white;
      padding: 12px;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      font-size: 16px;
      cursor: pointer;
      width: 100%;
      margin-top: 10px;
    }

    button:hover {
      background-color: #c2186b;
    }

    .msg {
      text-align: center;
      margin: 10px 0;
    }

    .msg.error { color: red; }
    .msg.success { color: green; }

    a {
      display: block;
      text-align: center;
      margin-top: 20px;
      text-decoration: none;
      color: #d63384;
      font-weight: bold;
    }

    .profile-header {
      display: flex;
      align-items: center;
      gap: 20px;
      margin-bottom: 30px;
      border-bottom: 1px solid #eee;
      padding-bottom: 20px;
    }

    .profile-pic {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 50%;
      border: 2px solid #ddd;
    }

    .profile-placeholder {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background-color: #f3a7c4;
      color: white;
      font-weight: bold;
      font-size: 26px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .profile-info h2 {
      margin: 0;
      font-size: 22px;
      color: #333;
    }

    .profile-info p {
      margin: 4px 0;
      color: #555;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="profile-header">
    <?php if (!empty($profile['profile_image']) && file_exists($profile['profile_image'])): ?>
      <img class="profile-pic" src="<?= htmlspecialchars($profile['profile_image']) ?>" alt="Profile Image">
    <?php else: ?>
      <div class="profile-placeholder">
        <?php
          $nameParts = explode(' ', trim($profile['name']));
          $firstInitial = strtoupper($nameParts[0][0] ?? '');
          $secondInitial = strtoupper($nameParts[1][0] ?? '');
          echo $firstInitial . $secondInitial;
        ?>
      </div>
    <?php endif; ?>

    <div class="profile-info">
      <h2><?= htmlspecialchars($profile['name']) ?></h2>
      <p><?= htmlspecialchars($profile['email']) ?></p>
      <p><?= htmlspecialchars($profile['bio']) ?></p>
    </div>
  </div>

  <?php if (!empty($error)) echo "<div class='msg error'>$error</div>"; ?>
  <?php if (!empty($success)) echo "<div class='msg success'>$success</div>"; ?>

  <form action="" method="POST" enctype="multipart/form-data">
    <label>Name:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($profile['name'] ?? '') ?>" required>

    <label>Email:</label>
    <input type="email" name="email" value="<?= htmlspecialchars($profile['email'] ?? '') ?>" required>

    <label>Phone:</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($profile['phone'] ?? '') ?>">

    <label>Location:</label>
    <input type="text" name="location" value="<?= htmlspecialchars($profile['location'] ?? '') ?>">

    <label>Role:</label>
    <input type="text" name="role" value="<?= htmlspecialchars($profile['role'] ?? '') ?>" placeholder="e.g. Volunteer & Social Contributor">

    <label>Providing Services:</label>
    <textarea name="services" rows="4"><?= htmlspecialchars($profile['services'] ?? '') ?></textarea>

    <label>Bio:</label>
    <textarea name="bio" rows="4"><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>

    <label>Hashtags:</label>
    <input type="text" name="hashtags" value="<?= htmlspecialchars($profile['hashtags'] ?? '') ?>" placeholder="#kindness #volunteer">

    <label>Profile Photo:</label>
    <input type="file" name="profile_image" accept="image/*">

    <button type="submit">Save Changes</button>
  </form>

  <a href="profile.php">← Back to Profile</a>
</div>

</body>
</html>
