<?php
session_start();
require_once 'database.php';

$profileId = $_SESSION['user']['id'] ?? null;

if (!$profileId) {
    header('Location: login.php');
    exit();
}

// Fetch or create profile
$stmt = $pdo->prepare("SELECT * FROM profile WHERE id = ?");
$stmt->execute([$profileId]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    $stmt = $pdo->prepare("INSERT INTO profile (id) VALUES (?)");
    $stmt->execute([$profileId]);

    $stmt = $pdo->prepare("SELECT * FROM profile WHERE id = ?");
    $stmt->execute([$profileId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
}

$name = htmlspecialchars($profile['name'] ?? 'John Doe');
$email = htmlspecialchars($profile['email'] ?? 'john@example.com');
$bio = htmlspecialchars($profile['bio'] ?? '');
$location = htmlspecialchars($profile['location'] ?? '');
$role = htmlspecialchars($profile['role'] ?? '');
$services = htmlspecialchars($profile['services'] ?? '');
$profileImage = $profile['profile_image'] ?? '';
$profileImageUrl = htmlspecialchars($profileImage);
$profileImagePath = __DIR__ . '/' . $profileImage;
$hashtags = htmlspecialchars($profile['hashtags'] ?? '');

// Fetch user's posts
$postStmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$postStmt->execute([$profileId]);
$posts = $postStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch likes for all posts
$likeCounts = [];
$likedByUser = [];

foreach ($posts as $post) {
    $postId = $post['id'];

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
    $countStmt->execute([$postId]);
    $likeCounts[$postId] = $countStmt->fetchColumn();

    $likedStmt = $pdo->prepare("SELECT * FROM likes WHERE post_id = ? AND user_id = ?");
    $likedStmt->execute([$postId, $profileId]);
    $likedByUser[$postId] = $likedStmt->fetch() ? true : false;
}
// Count good acts posted
$actsStmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE user_id = ?");
$actsStmt->execute([$profileId]);
$goodActsCount = $actsStmt->fetchColumn();

$inspiredStmt = $pdo->prepare("
  SELECT COUNT(DISTINCT likes.user_id)
  FROM likes
  JOIN posts ON likes.post_id = posts.id
  WHERE posts.user_id = ?
");
$inspiredStmt->execute([$profileId]);
$peopleInspired = $inspiredStmt->fetchColumn();

$hoursStmt = $pdo->prepare("SELECT SUM(hours) FROM volunteering_logs WHERE user_id = ?");
$hoursStmt->execute([$profileId]);
$volunteeringHours = $hoursStmt->fetchColumn() ?? 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Act of Hearts - Profile</title>
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }
    body {
      background: linear-gradient(135deg, #ffd6e8, #ffb6d1);
      color: #333;
      overflow-x: hidden;
    }

    .navbar {
      background-color: #fff;
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 3px solid #ff6f91;
      box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }
    .navbar h2 {
      font-family: 'Pacifico', cursive;
      font-size: 28px;
      color: #ff3e75;
    }
    .nav-links {
      display: flex;
      gap: 20px;
    }
    .nav-links a {
      color: #ff3e75;
      text-decoration: none;
      font-weight: 500;
      background-color: #ffe0eb;
      padding: 8px 16px;
      border-radius: 25px;
      transition: all 0.3s ease;
    }
    .nav-links a:hover {
      background-color: #ff85a2;
      color: white;
    }

    .profile-container {
      max-width: 1200px;
      margin: 30px auto;
      background: linear-gradient(to top, #ffffff, #ffe6f0);
      border-radius: 25px;
      box-shadow: 0 12px 40px rgba(255, 105, 180, 0.4);
      padding: 50px;
    }

    .profile-header {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      border-bottom: 2px dashed #ff8fab;
      padding-bottom: 30px;
    }
    .profile-header img {
      width: 160px;
      height: 160px;
      border-radius: 50%;
      object-fit: cover;
      border: 6px solid #ff6f91;
      box-shadow: 0 0 20px rgba(255, 105, 180, 0.5);
      margin-bottom: 15px;
    }
    .profile-info h1 {
      font-size: 38px;
      color: #ff3e75;
      font-family: 'Pacifico', cursive;
    }
    .profile-info p {
      font-size: 16px;
      margin-top: 5px;
      font-style: italic;
    }
    
    .edit-button {
      background-color: #ff69b4;
      color: white;
      border: none;
      padding: 10px 20px;
      margin-top: 15px;
      margin-right: 10px;
      border-radius: 30px;
      cursor: pointer;
      transition: 0.3s;
    }
    .edit-button:hover {
      background-color: #ff4785;
      transform: scale(1.05);
    }

    .section-title {
      font-size: 26px;
      color: #d63384;
      margin-top: 40px;
      margin-bottom: 15px;
      position: relative;
    }
    .section-title::after {
      content: '';
      width: 60px;
      height: 4px;
      background-color: #ff85a2;
      display: block;
      margin-top: 5px;
      border-radius: 2px;
    }

    .stats {
      display: flex;
      gap: 30px;
      justify-content: space-between;
      flex-wrap: wrap;
    }
    .stat-box {
      background: #ffe0eb;
      border-radius: 15px;
      padding: 25px;
      text-align: center;
      flex: 1;
      min-width: 150px;
      box-shadow: 0 5px 15px rgba(255, 105, 180, 0.2);
      transition: 0.3s;
    }
    .stat-box:hover {
      transform: scale(1.05);
    }
    .stat-box h3 {
      color: #ff4d6d;
      font-size: 30px;
    }
    .stat-box p {
      font-size: 14px;
      margin-top: 5px;
    }

    .activities {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 25px;
    }
    .activity-card {
      background-color: #fff;
      border-radius: 15px;
      padding: 20px;
      box-shadow: 0 6px 20px rgba(255, 105, 180, 0.1);
      border-left: 6px solid #ff85a2;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .activity-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(255, 105, 180, 0.3);
    }

    .profile-posts {
      margin-top: 40px;
    }

.post-gallery {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 30px;
  margin-top: 20px;
}
.post {
  width: 100%;
  max-width: 600px;
  background-color: #fff;
  border-radius: 12px;
  box-shadow: 0 6px 18px rgba(255, 105, 180, 0.2);
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.post:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 24px rgba(255, 105, 180, 0.3);
}
.post img {
  width: 100%;
  display: block;
  border-bottom: 1px solid #eee;
}
.post .caption {
  padding: 15px;
  font-size: 15px;
  color: #555;
}

    a {
      text-decoration: none;
    }
#imgModal {
  transition: opacity 0.3s ease;
}
#imgModal img {
  animation: zoomIn 0.3s ease;
}
@keyframes zoomIn {
  from { transform: scale(0.8); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}
@media (max-width: 1000px) {
  .post-gallery {
    column-count: 2;
  }
}
@media (max-width: 600px) {
  .post-gallery {
    column-count: 1;
  }
}
@media (max-width: 600px) {
  .post {
    max-width: 90%;
  }
}

  </style>
</head>
<body>
  <div class="navbar">
    <h2>ActOfHearts</h2>
    <div class="nav-links">
      <a href="dashboard.php">Dashboard</a>
      <a href="friends.php">Friends</a>
      <a href="logout.php">Logout</a>
    </div>
  </div>

  <div class="profile-container">
    <div class="profile-header">
      <?php if (!empty($profileImage) && file_exists($profileImagePath)): ?>
        <img src="<?= $profileImageUrl ?>" alt="Profile Picture">
      <?php else: ?>
        <img src="https://i.imgur.com/HLzvH1Z.png" alt="Default Profile Picture">
      <?php endif; ?>
      <div class="profile-info">
        <h1><?= $name ?></h1>
        <p><?= $role ?: 'Kindness Ambassador' ?> · <?= $location ?: 'Somewhere on Earth' ?></p>
        <p><a href="mailto:<?= $email ?>"><?= $email ?></a></p>
        <button class="edit-button" onclick="window.location.href='newprofile.php'">Edit Profile</button>
      </div>
    </div>

<h2 class="section-title">About Me</h2>
    <p><?= $bio ?: 'This user hasn’t added a bio yet.' ?></p>

    <?php if (!empty($hashtags)): ?>
      <div class="section-title">#Hashtags</div>
      <p>
        <?php
        foreach (explode(',', $hashtags) as $tag) {
          $tag = trim($tag);
          echo "<a href='explore.php?tag=" . urlencode($tag) . "' style='margin-right:10px; color:#c2185b;'>#$tag</a>";
        }
        ?>
      </p>
    <?php endif; ?>

    <div class="stats-section">
      <h2 class="section-title">Impact Status</h2>
      <div class="stats">
       <div class="stat-box"><h3><?= $goodActsCount ?></h3><p>Good Acts Posted</p></div>
<div class="stat-box"><h3><?= $peopleInspired ?></h3><p>People Inspired</p></div>
<div class="stat-box"><h3><?= $volunteeringHours ?></h3><p>Volunteering Hours</p></div>

      </div>
    </div>

    

    <div class="profile-posts">
    <h2 class="section-title">Add a New Post</h2>
<form id="postForm" enctype="multipart/form-data">
  <input type="file" name="image" id="image" required><br><br>
  <textarea name="caption" id="caption" placeholder="Write a caption..." rows="3" style="width:100%;"></textarea><br><br>
  <button type="submit" class="edit-button">Post</button>
</form>
<div id="postStatus" style="margin-top:10px;"></div>


      <h2 class="section-title">My Posts</h2>
      <div class="post-gallery">
        <?php foreach ($posts as $post): ?>
          <div class="post">
            <img src="<?= htmlspecialchars($post['image_url']) ?>" alt="Post Image" onclick="enlargeImage(this.src)">


            <div class="caption"><?= htmlspecialchars($post['caption']) ?></div>
<div style="padding: 10px 15px;">
  <div style="display:flex; justify-content: space-between; align-items: center;">
    <form action="like_post.php" method="POST" style="display:inline;">
      <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
      <button type="submit" style="background:none; border:none; cursor:pointer; font-size:16px;">
        ❤️ <?= $likedByUser[$post['id']] ? 'Unlike' : 'Like' ?>
      </button>
      <span><?= $likeCounts[$post['id']] ?> likes</span>
    </form>
    <a href="share_post.php?post_id=<?= $post['id'] ?>" style="margin-left:10px;">🔗 Share</a>
  </div>
<?php if ($post['user_id'] == $_SESSION['user']['id']): ?>
  <button onclick="deletePost(<?= $post['id'] ?>)" style="color:red; background:none; border:none;">🗑️ Delete</button>
<?php endif; ?>

  <!-- Fetch and display comments -->
  <?php
    $commentStmt = $pdo->prepare("
      SELECT comments.content, users.name, comments.created_at
      FROM comments
      JOIN users ON comments.user_id = users.id
      WHERE comments.post_id = ?
      ORDER BY comments.created_at DESC
    ");
    $commentStmt->execute([$post['id']]);
    $comments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
  ?>

<div style="margin-top: 10px;">
  <strong style="color: #d6336c;">Comments</strong>

  <?php if ($comments): ?>
    <div id="comment-preview-<?= $post['id'] ?>">
      <?php $firstComment = $comments[0]; ?>
      <div style="padding:10px; border-bottom:1px solid #eee;">
        <strong style="color: #880e4f;"><?= htmlspecialchars($firstComment['name']) ?></strong>
        <small style="color:gray; float:right;"><?= date('M d, Y', strtotime($firstComment['created_at'])) ?></small>
        <p style="margin:5px 0 0;"><?= nl2br(htmlspecialchars($firstComment['content'])) ?></p>
      </div>
    </div>

    <div id="all-comments-<?= $post['id'] ?>" style="display:none; margin-top:10px;">
      <?php foreach (array_slice($comments, 1) as $comment): ?>
        <div style="padding:10px; border-bottom:1px solid #eee;">
          <strong style="color: #880e4f;"><?= htmlspecialchars($comment['name']) ?></strong>
          <small style="color:gray; float:right;"><?= date('M d, Y', strtotime($comment['created_at'])) ?></small>
          <p style="margin:5px 0 0;"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if (count($comments) > 1): ?>
      <button id="toggle-btn-<?= $post['id'] ?>" onclick="toggleComments(<?= $post['id'] ?>)"
        style="margin-top:5px; background:none; border:none; color:#3f51b5; cursor:pointer;">
        View All Comments
      </button>
    <?php endif; ?>
  <?php else: ?>
    <p style="color:gray;">No comments yet.</p>
  <?php endif; ?>

  <!-- Comment Form -->
  <form method="POST" action="add_comment.php" style="margin-top: 10px;">
    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
    <textarea name="content" rows="2" placeholder="Add a comment..." required
      style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px; resize: none;"></textarea>
    <button type="submit" style="margin-top: 5px; background-color: #3f51b5; color: white; border: none; padding: 8px 16px; border-radius: 6px;">Post Comment</button>
  </form>
</div>

          </div>
        <?php endforeach; ?>
      </div>
    </div>

<form method="GET" style="margin: 20px 0;">
  <input type="text" name="tag" placeholder="Search hashtag..." style="padding:5px 10px; border-radius:5px;">
  <input type="date" name="date" style="padding:5px 10px; border-radius:5px;">
  <button type="submit" class="edit-button">Filter</button>
  <a href="profile.php" style="margin-left: 10px; color: #ff3e75;">Clear</a>
</form>


  </div>
<script>
function deletePost(postId) {
    if (confirm("Are you sure you want to delete this post?")) {
        fetch('delete_post.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'post_id=' + encodeURIComponent(postId)
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.status === 'success') location.reload();
        });
    }
}

function deleteComment(commentId) {
    if (confirm("Are you sure you want to delete this comment?")) {
        fetch('delete_comment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'comment_id=' + encodeURIComponent(commentId)
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.status === 'success') location.reload();
        });
    }
    return false;
}

document.getElementById('postForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const formData = new FormData();
  const imageFile = document.getElementById('image').files[0];
  const caption = document.getElementById('caption').value;

  if (!imageFile) {
    alert("Please select an image.");
    return;
  }

  formData.append('image', imageFile);
  formData.append('caption', caption);

  fetch('post_deed.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    const statusDiv = document.getElementById('postStatus');
    if (data.status === 'success') {
      statusDiv.innerHTML = '<p style="color:green;">Post uploaded successfully!</p>';
      setTimeout(() => location.reload(), 1500);
    } else {
      statusDiv.innerHTML = '<p style="color:red;">' + data.message + '</p>';
    }
  })
  .catch(() => {
    document.getElementById('postStatus').innerHTML = '<p style="color:red;">Something went wrong.</p>';
  });
});
function enlargeImage(src) {
  const modal = document.getElementById('imgModal');
  const modalImage = document.getElementById('modalImage');
  modalImage.src = src;
  modal.style.display = 'flex';
}

function toggleComments(postId) {
  const allComments = document.getElementById('all-comments-' + postId);
  const toggleBtn = document.getElementById('toggle-btn-' + postId);

  if (allComments.style.display === 'none') {
    allComments.style.display = 'block';
    toggleBtn.textContent = 'View Less';
  } else {
    allComments.style.display = 'none';
    toggleBtn.textContent = 'View All Comments';
  }
}
</script>
<!-- Modal for enlarged image -->
<div id="imgModal" onclick="this.style.display='none'"
     style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.8); z-index:10000; justify-content:center; align-items:center;">
  <img id="modalImage" src=""
       style="max-width:90%; max-height:90%; border-radius:10px; box-shadow:0 0 20px rgba(0,0,0,0.5);">
</div>
<div class="comment-scroll">
  <?php foreach ($comments as $comment): ?>
    <!-- comment display -->
  <?php endforeach; ?>
</div>

</body>
</html>
