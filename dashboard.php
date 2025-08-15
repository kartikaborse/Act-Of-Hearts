<?php
session_start();
require_once 'database.php';

// Redirect if not logged in
$userId = $_SESSION['user']['id'] ?? null;
if (!$userId) {
    header("Location: login.php");
    exit;
}

$isNGO = false;
$stmt = $pdo->prepare("SELECT id FROM ngos WHERE user_id = ?");
$stmt->execute([$userId]);
if ($stmt->rowCount() > 0) {
    $isNGO = true;
}

// Get logged-in user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch all posts with user names, profile images and like counts
$postsStmt = $pdo->prepare("
  SELECT 
    posts.*, 
    users.name,
    profile.profile_image,
    (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count,
    (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id AND likes.user_id = ?) AS user_liked
  FROM posts 
  JOIN users ON posts.user_id = users.id 
  LEFT JOIN profile ON posts.user_id = profile.id
  ORDER BY posts.created_at DESC
");
$postsStmt->execute([$userId]);
$posts = $postsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Act of Hearts Dashboard</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to right, #ffd5e5, #ffebf0);
      background-attachment: fixed;
      min-height: 100vh;
      overflow-x: hidden;
    }
    .navbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 30px;
      background: linear-gradient(to right, #fbc2eb, #fcd5ce);
      border-bottom: 2px solid #e91e63;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }
    .navbar .logo img {
      height: 50px;
      border-radius: 50%;
    }
    .navbar .search-bar input {
      padding: 10px 20px; 
      border: none;
      border-radius: 15px;
      box-shadow: 0px 2px 5px rgba(233, 30, 99, 0.3);
      outline: none; 
      color: #555;
    }
    .navbar .nav-links a {
      color: #880e4f; 
      margin-left: 20px;
      text-decoration: none;
      font-weight: 600; 
      font-size: 16px;
    }
    .navbar .nav-links a:hover {
      color: #c2185b;
     }
    .content-container {
      background-color: #fff6f9; 
      margin: 30px; 
      padding: 40px 20px;
      border-radius: 25px;
      box-shadow: 0 0 20px rgba(255, 182, 193, 0.3);
    }
    h2 {
      font-size: 32px;
      color: #cc3366;
      text-align: center;
    }

    p {
      font-size: 18px;
      color: #444;
      max-width: 800px;
      margin: 10px auto;
      text-align: center;
    }

    .highlight {
      color: #d6336c;
      font-weight: bold;
    }

    .section-image {
      max-width: 90%;
      height: auto;
      display: block;
      margin: 20px auto;
      border-radius: 10px;
    }

    .image-row {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 30px;
    }

    .image-item {
      max-width: 300px;
      text-align: center;
    }

    .image-item img {
      width: 100%;
      border-radius: 10px;
    }

    @media (max-width: 960px) {
      .image-row {
        flex-direction: column;
        align-items: center;
      }

      .image-item {
        max-width: 90%;
      }
    }
    .post-card {
      background-color: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      padding: 20px;
      margin: 20px auto; 
      max-width: 700px;
    }
    .post-header {
      display: flex;
      align-items: center; 
      margin-bottom: 10px;
    }
    .post-header img {
      width: 40px; 
      height: 40px;
      border-radius: 50%; 
      margin-right: 10px;
    }
    .post-title {
      font-weight: bold; 
      font-size: 18px; 
      color: #d6336c; 
     }
    .post-description {
      font-size: 16px;
 	color: #444; 
	margin: 10px 0;
    }
    .post-image {
        width: 100%; 
	max-height: 400px; 
	object-fit: cover;
        border-radius: 10px; 
	margin: 10px 0;
    }
    .post-actions {
        display: flex; 
	justify-content: space-around;
        margin-top: 10px; 
	border-top: 1px solid #eee; 
	padding-top: 10px;
    }
    .post-actions button {
        background: none;
 	border: none; 
	cursor: pointer; 
	font-weight: bold;
    }
    .like-btn { 
	color: #e91e63; 
    }
    .comment-btn { 
	color: #3f51b5; 
    }
    .repost-btn { 
	color: #4caf50; 
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
@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.3); }
  100% { transform: scale(1); }
}

.like-btn.animate {
  animation: pulse 0.3s ease;
}

.comment-scroll {
  max-height: 150px;
  overflow-y: auto;
  margin-top: 10px;
  border: 1px solid #eee;
  border-radius: 6px;
  padding: 5px;
}
.deed-meter-btn {
  background-color: #e91e63;
  color: white;
  padding: 10px 20px;
  margin-left: 20px;
  border-radius: 15px;
  text-decoration: none;
  font-weight: bold;
  font-size: 15px;
  transition: background 0.3s ease;
  box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}
.deed-meter-btn:hover {
  background-color: #d81b60;
}
section[onclick] {
  transition: background 0.3s ease;
}
section[onclick]:hover {
  background-color: #ffeef5; /* subtle hover highlight */
}

.floating-buttons {
    position: fixed;
    top: 40%;
    right: 20px;
    display: flex;
    flex-direction: column;
    gap: 15px;
    z-index: 1000;
}

.circle-btn {
    width: 50px;
    height: 50px;
    background-color: #d63384;
    color: white;
    border-radius: 50%;
    text-align: center;
    line-height: 50px;
    font-size: 22px;
    font-weight: bold;
    text-decoration: none;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.circle-btn:hover {
    background-color: #b0296d;
    transform: scale(1.1);
}
.ngo-badge {
    background-color: #28a745; /* green for verified NGO */
}

.ngo-badge:hover {
    background-color: #218838;
}
.register-badge {
    background-color: #17a2b8; /* teal */
}

.register-badge:hover {
    background-color: #138496;
}
  </style>
</head>
<body>
<div class="navbar">
  <div class="logo"><a href="dashboard.php"><img src="images/actofheart.png" alt="Logo"></a></div>
  <div class="search-bar"><input type="text" placeholder="Search good deeds..." /></div>
  <div class="nav-links">
    <a href="index.php">Home</a>
    <a href="friends.php">Friends</a>
    <a href="profile.php">My Profile</a>
    <a href="logout.php">Logout</a>
<a href="deed_meter.php" class="deed-meter-btn">🎯 Deed Meter</a>


  </div>
</div>
<div class="content-container">
    <h2>Welcome to Act of Hearts</h2>
    <p>
       A community built on kindness and compassion. At <span class="highlight">Act of Hearts</span>, we celebrate humanity’s most powerful force — doing good. Share, support, and shine a light on the actions that truly matter. Your journey to inspire change starts here.
    </p>
  </section>

  <section>
    <h2>Inspiring Deeds, Real People</h2>
    <p>
      Every act of kindness tells a story. Discover heartfelt tales of strangers helping strangers, communities lifting each other, and everyday heroes who remind us of the good in the world. <span class="highlight">Let their stories move you to action.</span>
    </p>
    <img src="https://images.unsplash.com/photo-1492724441997-5dc865305da7?auto=format&fit=crop&w=900&q=80" alt="People helping each other" class="section-image" />
  </section>

  <section>
    <h2>See the Change Happening Now</h2>
    <p>
      Stay up-to-date with the most recent good deeds shared by users around the world. From food drives to blood donations, witness the impact of collective goodness. <span class="highlight">Post your own act of heart and become part of something bigger.</span>
    </p>
    <img src="https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=900&q=80" alt="Community volunteering" class="section-image" />
  </section>

  <section>
    <h2>Meet the Changemakers</h2>
    <p>
      These are the individuals and organizations going above and beyond. Our Top Contributors lead by example, driving causes forward and inspiring others every step of the way. <span class="highlight">Recognize, follow, and connect with them.</span>
    </p>
    <img src="https://images.unsplash.com/photo-1556740749-887f6717d7e4?auto=format&fit=crop&w=900&q=80" alt="Changemakers" class="section-image" />
  </section>

  <section>
    <h2>Support a Cause You Care About</h2>
    <p>
      Whether it's climate action, education, poverty, or mental health — there's a cause for everyone. <span class="highlight">Dive into real-world initiatives</span> and explore how you can make a tangible difference today.
    </p>
    <div class="image-row">
      <div class="image-item">
        <img src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80" alt="Climate Action" />
        <p><strong>Climate Action</strong><br />Protecting our planet for future generations.</p>
      </div>
      <div class="image-item">
        <img src="images/education.jpeg" alt="Education" />
        <p><strong>Education</strong><br />Empowering minds to build a better tomorrow.</p>
      </div>
      <div class="image-item">
        <img src="images/mental health.jpeg" alt="Mental Health" />
        <p><strong>Mental Health</strong><br />Supporting wellness and resilience.</p>
      </div>
    </div>
  </section>

<!-- Blog Section -->
<section onclick="window.location.href='blogs.php'" style="cursor: pointer;">
  <h2>Latest from Our Blog</h2>
  <div class="image-row">
    <div class="image-item">
      <img src="https://images.unsplash.com/photo-1515377905703-c4788e51af15?auto=format&fit=crop&w=400&q=80" alt="Blog Post 1" />
      <p><strong>Sarah M.</strong><br />How Small Acts Can Create Big Change</p>
    </div>
    <div class="image-item">
      <img src="https://images.unsplash.com/photo-1494172961521-33799ddd43a5?auto=format&fit=crop&w=400&q=80" alt="Blog Post 2" />
      <p><strong>John D.</strong><br />Top 10 Ways to Volunteer Safely</p>
    </div>
    <div class="image-item">
      <img src="images/community.jpeg" alt="Blog Post 3" />
      <p><strong>Alice R.</strong><br />The Power of Community Support</p>
    </div>
  </div>
</section>




<!-- Hero Section -->
<section>
  <h2>Be the Heart That Acts</h2>
  <p>
    You don’t need a title to be a hero. At Act of Hearts, your smallest action can spark the biggest change. 
    <span class="highlight">Join us in rewriting the world’s story — one good deed at a time.</span>
  </p>
  <img src="https://images.unsplash.com/photo-1508214751196-bcfd4ca60f91?auto=format&fit=crop&w=900&q=80" alt="Join the movement" class="section-image" />
</section>

<!-- Posts Section -->
<div class="content-container">
  <h2 style="text-align:center; color:#cc3366;">Recent Good Deeds</h2>
  <?php foreach ($posts as $post): ?>
    <div class="post-card">
      <div class="post-header">
        <?php
          $profileImage = (!empty($post['profile_image']) && file_exists($post['profile_image'])) 
                          ? $post['profile_image'] 
                          : 'https://i.imgur.com/HLzvH1Z.png';
        ?>
        <img src="<?= htmlspecialchars($profileImage) ?>" alt="user" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
        <div>
          <strong><?= htmlspecialchars($post['name'] ?? 'Anonymous') ?></strong><br>
          <small><?= date('F j, Y', strtotime($post['created_at'])) ?></small>
        </div>
      </div>

      <?php if (!empty($post['title'])): ?>
        <p class="post-title"><?= htmlspecialchars($post['title']) ?></p>
      <?php endif; ?>
      <?php if (!empty($post['caption'])): ?>
        <p class="post-description"><?= nl2br(htmlspecialchars($post['caption'])) ?></p>
      <?php endif; ?>
      <?php if (!empty($post['description'])): ?>
        <p class="post-description"><?= nl2br(htmlspecialchars($post['description'])) ?></p>
      <?php endif; ?>
      <?php if (!empty($post['image_url'])): ?>
        <img src="<?= htmlspecialchars($post['image_url']) ?>" class="post-image" alt="Post Image">
      <?php endif; ?>

      <!-- Fetch comments -->
      <?php
        $commentStmt = $pdo->prepare("
          SELECT comments.id, comments.user_id, comments.content, users.name, comments.created_at
          FROM comments
          JOIN users ON comments.user_id = users.id
          WHERE comments.post_id = ?
          ORDER BY comments.created_at DESC
        ");
        $commentStmt->execute([$post['id']]);
        $comments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
      ?>

      <div class="post-actions">
        <button id="like-btn-<?= $post['id'] ?>" class="like-btn" onclick="likePost(<?= $post['id'] ?>)">
  <?= $post['user_liked'] ? '❤️' : '🤍' ?> <?= $post['like_count'] ?>
</button>
<button onclick="showLikedBy(<?= $post['id'] ?>)" style="background:none;border:none;color:#777;font-size:14px;">
  👁️ Liked By
</button>

        <form action="share_post.php" method="GET" style="display:inline;">
          <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
          <button class="repost-btn" type="submit">🔁 Repost</button>
        </form>
      </div>

      <?php if ($post['user_id'] == $userId): ?>
        <button onclick="deletePost(<?= $post['id'] ?>)" style="color:red; background:none; border:none;">🗑️ Delete</button>
      <?php endif; ?>

      <!-- Comments Section -->
      <div style="margin-top:15px;">
        <strong style="color:#d6336c;">Comments</strong>
        <?php if ($comments): ?>
          <div id="comment-preview-<?= $post['id'] ?>">
            <?php $first = $comments[0]; ?>
            <div style="padding:10px; border-bottom:1px solid #eee;">
              <strong style="color:#880e4f;"><?= htmlspecialchars($first['name']) ?></strong>
              <small style="float:right;color:gray;"><?= date('M d, Y',strtotime($first['created_at'])) ?></small>
              <p><?= nl2br(htmlspecialchars($first['content'])) ?></p>
            </div>
          </div>
          <div id="all-comments-<?= $post['id'] ?>" style="display:none;margin-top:10px;">
            <?php foreach (array_slice($comments, 1) as $comment): ?>
              <div style="padding:10px; border-bottom:1px solid #eee;">
                <strong style="color:#880e4f;"><?= htmlspecialchars($comment['name']) ?></strong>
                <small style="float:right;color:gray;"><?= date('M d, Y', strtotime($comment['created_at'])) ?></small>
                <?php if ($comment['user_id'] == $userId): ?>
                  <button onclick="deleteComment(<?= $comment['id'] ?>)" style="color:red;background:none;border:none;float:right;">🗑️</button>
                <?php endif; ?>
                <p><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
              </div>
            <?php endforeach; ?>
          </div>
          <?php if (count($comments) > 1): ?>
            <button id="toggle-btn-<?= $post['id'] ?>" onclick="toggleComments(<?= $post['id'] ?>)" style="margin-top:5px;background:none;border:none;color:#3f51b5;cursor:pointer;">
              View All Comments
            </button>
          <?php endif; ?>
        <?php else: ?>
          <p style="color:gray;">No comments yet.</p>
        <?php endif; ?>

        <!-- Add Comment -->
        <form method="POST" action="add_comment.php" style="margin-top:10px;">
          <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
          <textarea name="content" rows="2" placeholder="Add a comment..." required style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;resize:none;"></textarea>
          <button type="submit" style="margin-top:5px;background-color:#3f51b5;color:white;border:none;padding:8px 16px;border-radius:6px;">Post Comment</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {
  // Like a post
function likePost(postId) {
  fetch('like_post.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'post_id=' + encodeURIComponent(postId)
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      const likeBtn = document.querySelector(`#like-btn-${postId}`);
      const heart = data.liked ? '❤️' : '🤍';
      likeBtn.innerHTML = `${heart} ${data.like_count}`;

      // Animate
      likeBtn.classList.add('animate');
      setTimeout(() => likeBtn.classList.remove('animate'), 300);
    } else {
      alert(data.message || "Couldn't update like.");
    }
  })
  .catch(err => {
    console.error(err);
    alert('Something went wrong.');
  });
}

  // Delete a post
  window.deletePost = function(postId) {
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

  // Delete a comment
  window.deleteComment = function(commentId) {
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
  }

  // Toggle comments
  window.toggleComments = function(postId) {
    const all = document.getElementById('all-comments-' + postId);
    const btn = document.getElementById('toggle-btn-' + postId);
    if (all.style.display === 'none' || all.style.display === '') {
      all.style.display = 'block';
      btn.textContent = 'View Less';
    } else {
      all.style.display = 'none';
      btn.textContent = 'View All Comments';
    }
  }

  // Like button access globally
  window.likePost = likePost;

  // Image popup
  document.querySelectorAll('.post-image').forEach(img => {
    img.style.cursor = 'pointer';
    img.addEventListener('click', () => {
      document.getElementById('modalImg').src = img.src;
      document.getElementById('imgModal').style.display = 'block';
    });
  });
});

// Close modal
function closeModal() {
  document.getElementById('imgModal').style.display = 'none';
}
function showLikedBy(postId) {
  fetch('get_likes.php?post_id=' + postId)
    .then(res => res.json())
    .then(data => {
      const list = document.getElementById('likedByList');
      list.innerHTML = '';
      if (data.length === 0) {
        list.innerHTML = '<li>No likes yet.</li>';
      } else {
        data.forEach(user => {
          const li = document.createElement('li');
          li.textContent = user.name;
          list.appendChild(li);
        });
      }
      document.getElementById('likedByModal').style.display = 'block';
    });
}

function closeLikedBy() {
  document.getElementById('likedByModal').style.display = 'none';
}

function toggleDeedMeter() {
  const meter = document.getElementById('deedMeter');
  if (meter.style.display === 'none') {
    fetchDeedStats();
    meter.style.display = 'block';
  } else {
    meter.style.display = 'none';
  }
}

function fetchDeedStats() {
  fetch('fetch_deed_stats.php')
    .then(res => res.json())
    .then(data => {
      document.getElementById('actsPosted').textContent = data.posts;
      document.getElementById('peopleInspired').textContent = data.likes;
      document.getElementById('volHours').textContent = data.hours;
      document.getElementById('commentsMade').textContent = data.comments;
    })
    .catch(err => {
      console.error('Failed to load deed stats:', err);
    });
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

<div id="imgModal" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.8); text-align:center;">
  <span onclick="closeModal()" style="color:white; font-size:30px; position:absolute; top:20px; right:30px; cursor:pointer;">&times;</span>
  <img id="modalImg" src="" style="max-width:90%; max-height:90%; margin-top:5%;">
</div>


<!-- Liked By Modal -->
<div id="likedByModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999;">
  <div style="background:#fff; padding:20px; width:400px; max-width:90%; margin:100px auto; border-radius:10px; position:relative;">
    <h3 style="margin-top:0; color:#d6336c;">Liked By</h3>
    <ul id="likedByList" style="list-style:none; padding:0;"></ul>
    <button onclick="closeLikedBy()" style="position:absolute; top:10px; right:10px; background:none; border:none; font-size:20px;">✖</button>
  </div>
</div>
<div id="deedMeter" style="display:none; position:fixed; top:80px; right:30px; background:#fff0f5; border:2px solid #e91e63; border-radius:12px; padding:20px; z-index:10001; box-shadow: 0 5px 15px rgba(0,0,0,0.1); max-width:300px;">
  <h3 style="margin-top:0; color:#d6336c;">🌟 Your Deed Meter</h3>
  <ul style="list-style:none; padding-left:0; font-size:16px;">
    <li><strong>Good Acts Posted:</strong> <span id="actsPosted">...</span></li>
    <li><strong>People Inspired (Likes):</strong> <span id="peopleInspired">...</span></li>
    <li><strong>Volunteering Hours:</strong> <span id="volHours">...</span></li>
    <li><strong>Comments Made:</strong> <span id="commentsMade">...</span></li>
  </ul>
  <button onclick="toggleDeedMeter()" style="margin-top:10px; background:#e91e63; color:white; border:none; padding:5px 10px; border-radius:6px;">Close</button>
</div>
<!-- Floating Circle Buttons -->
<div class="floating-buttons">
    <a href="events.php" class="circle-btn" title="Browse Events">🌍</a>

    <?php if ($isNGO): ?>
        <!-- NGO already registered -->
        <a href="ngo_dashboard.php" class="circle-btn ngo-badge" title="NGO Dashboard">✅</a>
    <?php else: ?>
        <!-- NGO not yet registered -->
        <a href="ngo_entry.php" class="circle-btn" title="NGO Access">🏢</a>
        <a href="ngo_register.php" class="circle-btn register-badge" title="Register NGO">📝</a>
    <?php endif; ?>
</div>

</body>
</html>
