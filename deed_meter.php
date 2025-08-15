<?php
session_start();
require_once 'database.php';

$userId = $_SESSION['user']['id'] ?? null;
if (!$userId) {
    header("Location: login.php");
    exit;
}

$badge = "";
$acts = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE user_id = ?");
$acts->execute([$userId]);
$actsCount = $acts->fetchColumn();

$likes = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id IN (SELECT id FROM posts WHERE user_id = ?)");
$likes->execute([$userId]);
$likeCount = $likes->fetchColumn();

$vol = $pdo->prepare("SELECT SUM(hours) FROM volunteering WHERE user_id = ?");
$vol->execute([$userId]);
$volHours = $vol->fetchColumn() ?? 0;

$totalScore = $actsCount * 10 + $likeCount * 2 + $volHours;
$targetScore = 200;
$progress = min(100, ($totalScore / $targetScore) * 100);

$today = date('Y-m-d');
$challengeToday = null;
$tasks = [];

try {
    $stmt = $pdo->prepare("SELECT dc.*, c.challenge_text FROM daily_challenges dc
                           JOIN challenges c ON dc.challenge_id = c.id
                           WHERE dc.user_id = ? AND dc.challenge_date = ?");
    $stmt->execute([$userId, $today]);
    $challengeToday = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$challengeToday) {
        $randomChallenge = $pdo->query("SELECT id FROM challenges ORDER BY RAND() LIMIT 1")->fetchColumn();

        if ($randomChallenge) {
            $insert = $pdo->prepare("INSERT INTO daily_challenges (user_id, challenge_id, challenge_date) VALUES (?, ?, ?)");
            $insert->execute([$userId, $randomChallenge, $today]);

            $stmt = $pdo->prepare("SELECT dc.*, c.challenge_text FROM daily_challenges dc
                                   JOIN challenges c ON dc.challenge_id = c.id
                                   WHERE dc.user_id = ? AND dc.challenge_date = ?");
            $stmt->execute([$userId, $today]);
            $challengeToday = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    if (!empty($challengeToday['challenge_id'])) {
        $taskStmt = $pdo->prepare("SELECT t.id, t.task_text, IFNULL(p.completed, 0) AS completed
                                   FROM challenge_tasks t
                                   LEFT JOIN user_challenge_progress p ON t.id = p.task_id AND p.user_id = ?
                                   WHERE t.challenge_id = ?");
        $taskStmt->execute([$userId, $challengeToday['challenge_id']]);
        $tasks = $taskStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_task'])) {
        $taskId = $_POST['complete_task'];
        $mark = $pdo->prepare("INSERT INTO user_challenge_progress (user_id, task_id, completed, completed_at)
                               VALUES (?, ?, 1, NOW()) ON DUPLICATE KEY UPDATE completed = 1, completed_at = NOW()");
        $mark->execute([$userId, $taskId]);
        header("Location: deed_meter.php");
        exit;
    }

    $badgeStmt = $pdo->prepare("SELECT COUNT(*) FROM user_challenge_progress WHERE user_id = ? AND completed = 1");
    $badgeStmt->execute([$userId]);
    $completedTasks = $badgeStmt->fetchColumn();

    if ($completedTasks >= 25) {
        $badge = "🏅 Kindness Champion";
    } elseif ($completedTasks >= 10) {
        $badge = "🎖️ Beginner Helper";
    }

    if ($badge) {
        $check = $pdo->prepare("SELECT badge_level FROM user_badges WHERE user_id = ?");
        $check->execute([$userId]);
        $current = $check->fetchColumn();

        if ($current !== $badge) {
            $update = $pdo->prepare("REPLACE INTO user_badges (user_id, badge_level, awarded_at) VALUES (?, ?, NOW())");
            $update->execute([$userId, $badge]);
        }
    }
} catch (PDOException $e) {
    $challengeToday = null;
    $tasks = [];
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Deed Meter</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #a1ffce, #faffd1);
      padding: 0;
      margin: 0;
    }

    .topbar {
      background: linear-gradient(135deg, #a1ffce, #faffd1);
      padding: 15px 30px;
      text-align: right;
      border-bottom: 1px solid #ddd;
      font-weight: bold;
    }

    .topbar a {
      color: #fff;
      text-decoration: none;
      font-size: 16px;
      background: #ff7eb9;
      padding: 10px 18px;
      border-radius: 6px;
     transition: background 0.3s;
    }
    .topbar a:hover {
	  background: #ff5691;
     }
    .container {
      text-align: center;
      padding: 40px 20px;
    }

    .meter-container {
      background: #eee;
      border-radius: 30px;
      width: 80%;
      max-width: 600px;
      margin: auto;
      height: 40px;
      box-shadow: inset 0 0 5px rgba(0,0,0,0.2);
    }

    .meter-fill {
      height: 100%;
      border-radius: 30px;
      background: linear-gradient(to right, #ff416c, #ffb347);
      width: <?= $progress ?>%;
      transition: width 0.5s ease-in-out;
    }

    .labels {
      display: flex;
      justify-content: space-between;
      margin: 10px auto;
      max-width: 600px;
      color: #333;
    }

    .challenge-box {
      background: #fff3f3;
      border: 2px dashed #ff4081;
      padding: 20px;
      margin-top: 30px;
      border-radius: 15px;
      width: 70%;
      margin-left: auto;
      margin-right: auto;
    }

    .challenge-box form button {
      margin-top: 10px;
      background-color: #ff4081;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 10px;
      cursor: pointer;
    }

    .task-item {
      background: #fff;
      margin: 5px auto;
      padding: 10px;
      border-radius: 8px;
      max-width: 500px;
      border: 1px solid #ddd;
    }

    .badge {
      margin-top: 20px;
      font-size: 20px;
      color: #4caf50;
    }
  </style>
</head>
<body>
  <div class="topbar">
    <a href="dashboard.php">🏠 Back to Dashboard</a>
  </div>

  <div class="container">
    <h1>🎯 Your Deed Meter</h1>
    <div class="labels">
      <span>0%</span><span>25%</span><span>50%</span><span>75%</span><span>100%</span>
    </div>
    <div class="meter-container">
      <div class="meter-fill"></div>
    </div>
    <p>
      <strong>Total Points:</strong> <?= $totalScore ?> / <?= $targetScore ?><br>
      <strong>Good Acts:</strong> <?= $actsCount ?> |
      <strong>Inspired:</strong> <?= $likeCount ?> |
      <strong>Hours:</strong> <?= $volHours ?>
    </p>

    <div class="challenge-box">
      <h3>🧹 Daily Challenge</h3>
      <?php if ($challengeToday): ?>
        <p><strong><?= htmlspecialchars($challengeToday['challenge_text']) ?></strong></p>
        <?php if (!empty($tasks)): ?>
          <?php foreach ($tasks as $task): ?>
            <div class="task-item">
              <?= htmlspecialchars($task['task_text']) ?>
              <?php if (!$task['completed']): ?>
                <form method="POST" style="display:inline;">
                  <button type="submit" name="complete_task" value="<?= $task['id'] ?>">Mark Done</button>
                </form>
              <?php else: ?>
                <span style="color:green; font-weight:bold;">✔ Completed</span>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="color:gray;">No tasks assigned for this challenge.</p>
        <?php endif; ?>
      <?php else: ?>
        <p style="color:gray;">No challenge assigned for today.</p>
      <?php endif; ?>
    </div>

    <?php if ($badge): ?>
      <div class="badge">
        🏅 Badge Earned: <strong><?= $badge ?></strong>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>

