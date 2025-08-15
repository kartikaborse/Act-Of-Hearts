<script>
function likePost(postId) {
  fetch('like_post.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'post_id=' + encodeURIComponent(postId)
  }).then(res => res.json()).then(data => {
    alert(data.message);
    if (data.status === 'success') location.reload();
  });
}

function deletePost(postId) {
  if (confirm("Delete this post?")) {
    fetch('delete_post.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'post_id=' + encodeURIComponent(postId)
    }).then(res => res.json()).then(data => {
      alert(data.message);
      if (data.status === 'success') location.reload();
    });
  }
}

function deleteComment(commentId) {
  if (confirm("Delete this comment?")) {
    fetch('delete_comment.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'comment_id=' + encodeURIComponent(commentId)
    }).then(res => res.json()).then(data => {
      alert(data.message);
      if (data.status === 'success') location.reload();
    });
  }
}
</script>
</body>
</html>
