<?php
if (!isset($_GET['post_id'])) {
    echo "Invalid share link.";
    exit();
}

$postId = intval($_GET['post_id']);
$link = "http://localhost/actofhearts/view_post.php?post_id=" . $postId;
?>

<!DOCTYPE html>
<html>
<head><title>Share Post</title></head>
<body>
    <h2>Share this post</h2>
    <input type="text" value="<?= $link ?>" readonly style="width:100%;">
    <p>Copy the above link to share with others!</p>
</body>
</html>
