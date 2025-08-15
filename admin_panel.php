<?php
session_start();
require_once 'database.php';

// Only allow if logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Add a new challenge
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['challenge_text'])) {
    $text = trim($_POST['challenge_text']);
    if ($text !== '') {
        $stmt = $pdo->prepare("INSERT INTO challenges (challenge_text) VALUES (?)");
        $stmt->execute([$text]);
    }
}

// Add task to existing challenge
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_text'], $_POST['challenge_id'])) {
    $task = trim($_POST['task_text']);
    $cid = (int)$_POST['challenge_id'];
    if ($task !== '' && $cid > 0) {
        $stmt = $pdo->prepare("INSERT INTO challenge_tasks (challenge_id, task_text) VALUES (?, ?)");
        $stmt->execute([$cid, $task]);
    }
}

// Fetch all challenges
$challenges = $pdo->query("SELECT * FROM challenges ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Get all tasks grouped by challenge_id
$tasksGrouped = [];
$taskQuery = $pdo->query("SELECT * FROM challenge_tasks ORDER BY challenge_id DESC");
while ($row = $taskQuery->fetch(PDO::FETCH_ASSOC)) {
    $tasksGrouped[$row['challenge_id']][] = $row;
}

// Fetch badge summary
$badges = $pdo->query("SELECT u.username, b.badge_level, b.awarded_at
                       FROM user_badges b
                       JOIN users u ON b.user_id = u.id
                       ORDER BY b.awarded_at DESC")->fetchAll(PDO::FETCH_ASSOC);
// Edit challenge
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_challenge'])) {
    $editId = (int)$_POST['edit_id'];
    $editText = trim($_POST['edit_text']);
    if ($editId > 0 && $editText !== '') {
        $stmt = $pdo->prepare("UPDATE challenges SET challenge_text = ? WHERE id = ?");
        $stmt->execute([$editText, $editId]);
        header("Location: admin_panel.php");
        exit;
    }
}
// Edit task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_task'])) {
    $taskId = (int)$_POST['task_id'];
    $newText = trim($_POST['new_text']);
    if ($taskId > 0 && $newText !== '') {
        $stmt = $pdo->prepare("UPDATE challenge_tasks SET task_text = ? WHERE id = ?");
        $stmt->execute([$newText, $taskId]);
        header("Location: admin_panel.php");
        exit;
    }
}

// Delete task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_task'])) {
    $taskId = (int)$_POST['task_id'];
    if ($taskId > 0) {
        $stmt = $pdo->prepare("DELETE FROM challenge_tasks WHERE id = ?");
        $stmt->execute([$taskId]);
        header("Location: admin_panel.php");
        exit;
    }
}

// Delete challenge
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_challenge'])) {
    $deleteId = (int)$_POST['delete_id'];
    if ($deleteId > 0) {
        // Optional: also delete associated tasks
        $pdo->prepare("DELETE FROM challenge_tasks WHERE challenge_id = ?")->execute([$deleteId]);

        // Delete challenge
        $pdo->prepare("DELETE FROM challenges WHERE id = ?")->execute([$deleteId]);
        header("Location: admin_panel.php");
        exit;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel - ActOfHearts</title>
    <style>
        body { 
font-family: Arial, sans-serif; 
background: #f3f3f3; 
padding: 80px;
width : auto;
 }
        h1 { 
color: #333; 
}
        .section { 
background: white; 
padding: 20px; 
margin-bottom: 30px;
 border-radius: 8px; 
box-shadow: 0 0 10px rgba(0,0,0,0.05);
 }
form input, form select, form textarea, form button {
            display: block; 
width: 100%;
 margin-top: 10px; 
padding: 10px; 
border: 1px solid #ccc;
 border-radius: 5px;
        }
 form button {
 background: #4CAF50; 
color: white; 
cursor: pointer; 
}
 .badge-table { 
width: 100%; 
border-collapse: collapse; 
margin-top: 10px; 
}
 .badge-table th, .badge-table td { 
border: 1px solid #ccc; 
padding: 10px; 
text-align: left; 
}
 .badge-table th { 
background-color: #f9f9f9; 
}
.topbar {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  padding: 10px 30px;
  background: #fff;
  border-bottom: 1px solid #ddd;
  position: sticky;
  top: 0;
  z-index: 999;
}

.topbar span {
  margin-right: 15px;
  font-size: 16px;
}
.header-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 30px;
  background-color: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  margin-bottom: 30px;
}

.header-bar h1 {
  margin: 0;
  font-size: 28px;
  color: #333;
}

.admin-info {
  font-size: 16px;
}

.logout-btn {
  background-color: #dc3545;
  color: white;
  text-decoration: none;
  padding: 8px 14px;
  margin-left: 10px;
  border-radius: 5px;
  font-weight: bold;
  transition: background-color 0.3s;
}

.logout-btn:hover {
  background-color: #c82333;
}


    </style>
</head>
<body>
   <div class="header-bar">
  <h1>🌟 Admin Panel</h1>
  <div class="admin-info">
    Logged in as <strong><?= $_SESSION['admin_username'] ?></strong>
    <a href="admin_analytics.php" class="logout-btn" style="background-color:#17a2b8;">📈 Analytics</a>
    <a href="admin_logout.php" class="logout-btn">Logout</a>
  </div>
</div>


    <!-- Add Challenge -->
    <div class="section">
        <h2>Add New Challenge</h2>
     <form method="POST">
            <textarea name="challenge_text" placeholder="Enter challenge description" required></textarea>
            <button type="submit">Add Challenge</button>
        </form>
    </div>

    <!-- Add Task to Challenge -->
    <div class="section">
        <h2>Add Task to Challenge</h2>
        <form method="POST">
            <select name="challenge_id" required>
                <option value="">Select Challenge</option>
                <?php foreach ($challenges as $c): ?>
                    <option value="<?= $c['id'] ?>">#<?= $c['id'] ?> - <?= htmlspecialchars($c['challenge_text']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="task_text" placeholder="Enter task" required />
            <button type="submit">Add Task</button>
        </form>
    </div>


<!-- Manage Challenges -->
<div class="section">
  <h2>Manage Challenges</h2>
  <table class="badge-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Challenge Text</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($challenges as $c): ?>
        <tr>
          <td>#<?= $c['id'] ?></td>
          <td><?= htmlspecialchars($c['challenge_text']) ?></td>
          <td>
            <form method="POST" style="display:inline-block;">
              <input type="hidden" name="edit_id" value="<?= $c['id'] ?>">
              <input type="text" name="edit_text" placeholder="New text" required>
              <button type="submit" name="edit_challenge">Edit</button>
            </form>

            <form method="POST" onsubmit="return confirm('Delete this challenge?')" style="display:inline-block;">
              <input type="hidden" name="delete_id" value="<?= $c['id'] ?>">
              <button type="submit" name="delete_challenge" style="background:#dc3545; color:white;">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Manage Tasks -->
<div class="section">
  <h2>Manage Tasks</h2>
  <?php foreach ($challenges as $challenge): ?>
    <h4>#<?= $challenge['id'] ?> - <?= htmlspecialchars($challenge['challenge_text']) ?></h4>
    <ul>
      <?php if (!empty($tasksGrouped[$challenge['id']])): ?>
        <?php foreach ($tasksGrouped[$challenge['id']] as $task): ?>
          <li style="margin-bottom: 10px;">
            <?= htmlspecialchars($task['task_text']) ?>
            <form method="POST" style="display:inline-block;">
              <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
              <input type="text" name="new_text" placeholder="Edit task..." required>
              <button type="submit" name="edit_task">Edit</button>
            </form>
            <form method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this task?');">
              <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
              <button type="submit" name="delete_task" style="background:#dc3545;color:white;">Delete</button>
            </form>
          </li>
        <?php endforeach; ?>
      <?php else: ?>
        <li>No tasks found for this challenge.</li>
      <?php endif; ?>
    </ul>
    <hr>
  <?php endforeach; ?>
</div>

    <!-- Badge Summary -->
    <div class="section">
        <h2>User Badge Summary 🏅</h2>
        <table class="badge-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Badge Level</th>
                    <th>Date Awarded</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($badges as $b): ?>
                    <tr>
                        <td><?= htmlspecialchars($b['username']) ?></td>
                        <td><?= htmlspecialchars($b['badge_level']) ?></td>
                        <td><?= htmlspecialchars($b['awarded_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
