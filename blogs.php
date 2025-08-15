<?php
session_start();
require_once 'database.php';

$userId = $_SESSION['user']['id'] ?? null;
if (!$userId) {
    header("Location: login.php");
    exit;
}

// Fetch all blogs from DB
$blogs = $pdo->query("SELECT * FROM blogs ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Blogs and Articles - Act of Hearts</title>
  <style>
    body { 
margin: 0; 
font-family: 'Segoe UI', sans-serif; 
background: #fff5f9; }
    nav {
      background-color: #d6336c;
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
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
    }
    nav a:hover {
      background-color: white;
      color: #d6336c;
    }
    .blog-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 30px;
      max-width: 1200px;
      margin: 40px auto;
      padding: 0 20px;
    }
    .card {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.05);
      padding: 20px;
      transition: transform 0.3s ease;
      display: flex;
      flex-direction: column;
    }
 .nav-left {
      display: flex;
      align-items: center;
      gap: 20px;
    }
    .card:hover { transform: translateY(-5px); }
    .card img { width: 100%; border-radius: 12px; margin-bottom: 15px; }
    .card h3 { color: #e91e63; margin: 10px 0 5px; }
    .card small { color: #888; }
    .card p { font-size: 14px; color: #444; margin-top: 10px; }
    .full { display: none; }
    .read-more { color: #e91e63; cursor: pointer; font-weight: bold; }
    .actions {
      margin-top: 10px;
      font-size: 13px;
      color: #999;
      display: flex;
      gap: 20px;
    }
    .like-btn { cursor: pointer; color: #e91e63; }
    .hidden { display: none; }
    .comments { margin-top: 10px; }
    .comment-form input {
      width: 80%; padding: 5px; margin-right: 5px;
    }
    .comment-form button {
      padding: 5px 10px;
      background: #e91e63;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
  </style>
</head>
<body>

<nav>
  <div class="nav-left">
    <a href="dashboard.php">← Dashboard</a>
    <h2 style="margin: 0 0 0 15px;">🧠 Blogs and Articles</h2>
  </div>
  <div class="nav-right">
    <a href="blog_form.php">+ Add Blog</a>
  </div>

</nav>

<div class="blog-grid">
  <?php foreach ($blogs as $blog): ?>
    <div class="card" data-id="<?= $blog['id'] ?>">
      <?php if (!empty($blog['image_url'])): ?>
        <img src="<?= htmlspecialchars($blog['image_url']) ?>" alt="Blog Image">
      <?php endif; ?>
      
      <h3><?= htmlspecialchars($blog['title']) ?></h3>
      <small>By <?= htmlspecialchars($blog['author']) ?> | <?= date('M j, Y', strtotime($blog['created_at'])) ?></small>

      <?php
        $content = htmlspecialchars($blog['content']);
        $preview = substr($content, 0, 200);
        $isLong = strlen($content) > 200;
      ?>
      <p>
        <span class="preview"><?= nl2br($preview) ?></span>
        <?php if ($isLong): ?>
          <span class="full"><?= nl2br($content) ?></span>
          <span class="read-more" onclick="toggleContent(this)">Read More</span>
        <?php endif; ?>
      </p>

      <div class="actions">
        <span>👁️ <?= $blog['views'] ?? 0 ?> views</span>
        <span class="like-btn">❤️ <span class="like-count"><?= $blog['likes'] ?? 0 ?></span></span>
      </div>

      <?php if ($blog['user_id'] == $userId): ?>
        <div style="margin-top: 10px;">
          <a href="edit_blog.php?id=<?= $blog['id'] ?>" style="color: green;">✏️ Edit</a> |
          <a href="delete_blog.php?id=<?= $blog['id'] ?>" style="color: red;" onclick="return confirm('Are you sure to delete this blog?');">🗑️ Delete</a>
        </div>
      <?php endif; ?>

      <div class="comments hidden">
        <div class="existing-comments">
          <?php
            $stmt = $pdo->prepare("SELECT c.id, c.comment, u.name, c.user_id FROM blog_comments c JOIN users u ON c.user_id = u.id WHERE c.blog_id = ?");
            $stmt->execute([$blog['id']]);
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($comments as $comment):
          ?>
            <p>
              <strong><?= htmlspecialchars($comment['name']) ?>:</strong>
              <?= htmlspecialchars($comment['comment']) ?>
              <?php if ($comment['user_id'] == $userId): ?>
                <a href="#" class="delete-comment" data-id="<?= $comment['id'] ?>" style="color: red; margin-left:10px;">🗑️</a>
              <?php endif; ?>
            </p>
          <?php endforeach; ?>
        </div>
        <form class="comment-form">
          <input type="text" name="comment" placeholder="Write a comment..." required>
          <button type="submit">Post</button>
        </form>
      </div>

      <span class="read-more" onclick="toggleComments(this)">💬 Comment</span>
    </div>
  <?php endforeach; ?>
</div>

<script>
function toggleContent(button) {
  const card = button.closest('.card');
  const preview = card.querySelector('.preview');
  const full = card.querySelector('.full');

  if (full.style.display === "inline") {
    full.style.display = "none";
    preview.style.display = "inline";
    button.innerText = "Read More";
  } else {
    full.style.display = "inline";
    preview.style.display = "none";
    button.innerText = "Read Less";
  }
}

function toggleComments(button) {
  const card = button.closest('.card');
  const comments = card.querySelector('.comments');
  comments.classList.toggle('hidden');
}

document.querySelectorAll('.like-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const blogId = btn.closest('.card').dataset.id;
    fetch('like_blog.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'blog_id=' + blogId
    })
    .then(res => res.json())
    .then(data => {
      const countEl = btn.querySelector('.like-count');
      if (data.status === 'liked') {
        countEl.textContent = parseInt(countEl.textContent) + 1;
      } else if (data.status === 'unliked') {
        countEl.textContent = parseInt(countEl.textContent) - 1;
      }
    });
  });
});

document.querySelectorAll('.comment-form').forEach(form => {
  form.addEventListener('submit', e => {
    e.preventDefault();
    const blogId = form.closest('.card').dataset.id;
    const comment = form.comment.value;
    fetch('add_blogcomment.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'blog_id=' + blogId + '&comment=' + encodeURIComponent(comment)
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        const div = form.previousElementSibling;
        const p = document.createElement('p');
        p.innerHTML = '<strong>You:</strong> ' + data.comment;
        div.appendChild(p);
        form.comment.value = '';
      }
    });
  });
});

document.querySelectorAll('.delete-comment').forEach(btn => {
  btn.addEventListener('click', e => {
    e.preventDefault();
    const commentId = btn.dataset.id;
    if (confirm('Delete this comment?')) {
      fetch('delete_blogcomment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'comment_id=' + commentId
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'deleted') {
          btn.parentElement.remove();
        }
      });
    }
  });
});
</script>

</body>
</html>
