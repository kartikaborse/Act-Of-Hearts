<?php
session_start();
require_once 'database.php';

// Check for post ID
$postId = $_POST['post_id'] ?? null;

header('Content-Type: application/json');

if (!$postId) {
    echo json_encode(['success' => false, 'message' => 'Missing post ID']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT comments.content, users.name, comments.created_at
    FROM comments
    JOIN users ON comments.user_id = users.id
    WHERE comments.post_id = ?
    ORDER BY comments.created_at DESC
");
$stmt->execute([$postId]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'comments' => $comments]);

<!DOCTYPE html>
<html lang="en">
<style>
.comments-box {
  margin-top: 10px;
  padding: 10px;
  background: #fff0f5;
  border-radius: 8px;
}
</style>
<body>

<button onclick="loadComments(<?= $post['id'] ?>)" class="comment-btn">💬 View Comments</button>
<div id="comments-<?= $post['id'] ?>" class="comments-box"></div>
<script>
function loadComments(postId) {
  fetch('fetch_comments.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'post_id=' + encodeURIComponent(postId)
  })
  .then(response => response.json())
  .then(data => {
    const container = document.getElementById('comments-' + postId);
    container.innerHTML = ''; // clear previous

    if (data.success && data.comments.length > 0) {
      data.comments.forEach(comment => {
        const div = document.createElement('div');
        div.style.cssText = 'padding:10px;border-bottom:1px solid #eee;';
        div.innerHTML = `
          <strong style="color:#880e4f;">${escapeHtml(comment.name)}</strong>
          <small style="float:right;color:gray;">${formatDate(comment.created_at)}</small>
          <p>${escapeHtml(comment.content)}</p>
        `;
        container.appendChild(div);
      });
    } else {
      container.innerHTML = '<p style="color:gray;">No comments yet.</p>';
    }
  })
  .catch(err => {
    console.error('Error loading comments', err);
    alert('Failed to load comments.');
  });
}

function escapeHtml(text) {
  const map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
  };
  return text.replace(/[&<>"']/g, m => map[m]);
}

function formatDate(dateStr) {
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-US', {
    month: 'short', day: 'numeric', year: 'numeric'
  });
}
</script>

</body>
</html>