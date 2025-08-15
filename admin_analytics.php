<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Analytics - ActOfHearts</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      padding: 40px;
      background: #f9f9f9;
    }
    h1 {
      text-align: center;
      color: #333;
    }
    .chart-container, .leaderboard, .filters {
      background: #fff;
      border-radius: 10px;
      padding: 20px;
      margin: 30px auto;
      width: 90%;
      max-width: 1000px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    table th, table td {
      padding: 10px;
      border: 1px solid #ccc;
    }
    table th {
      background-color: #f0f0f0;
    }
    .filters form {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
      justify-content: space-between;
    }
    .filters input, .filters select {
      padding: 10px;
      border: 1px solid #aaa;
      border-radius: 5px;
    }
  </style>
</head>
<body>

<h1>📊 Admin Analytics & Leaderboard</h1>
<?php
$taskData = $pdo->query("SELECT u.username, COUNT(p.id) as completed_tasks 
                         FROM users u
                         JOIN user_challenge_progress p ON u.id = p.user_id
                         WHERE p.completed = 1
                         GROUP BY u.username
                         ORDER BY completed_tasks DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
$usernames = array_column($taskData, 'username');
$taskCounts = array_column($taskData, 'completed_tasks');
?>
<div class="chart-container">
  <h2>Top 10 Users - Tasks Completed</h2>
  <canvas id="tasksChart"></canvas>
</div>
<script>
  const ctx = document.getElementById('tasksChart').getContext('2d');
  const tasksChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?= json_encode($usernames) ?>,
      datasets: [{
        label: 'Tasks Completed',
        data: <?= json_encode($taskCounts) ?>,
        backgroundColor: '#4CAF50'
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true }
      }
    }
  });
</script>

<?php
$taskData = $pdo->query("SELECT u.username, COUNT(p.id) as completed_tasks 
                         FROM users u
                         JOIN user_challenge_progress p ON u.id = p.user_id
                         WHERE p.completed = 1
                         GROUP BY u.username
                         ORDER BY completed_tasks DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
$usernames = array_column($taskData, 'username');
$taskCounts = array_column($taskData, 'completed_tasks');
?>
<div class="chart-container">
  <h2>Top 10 Users - Tasks Completed</h2>
  <canvas id="tasksChart"></canvas>
</div>
<script>
  const ctx = document.getElementById('tasksChart').getContext('2d');
  const tasksChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?= json_encode($usernames) ?>,
      datasets: [{
        label: 'Tasks Completed',
        data: <?= json_encode($taskCounts) ?>,
        backgroundColor: '#4CAF50'
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true }
      }
    }
  });
</script>

<?php
$leaderboard = $pdo->query("SELECT u.username, COUNT(p.id) as tasks_done, b.badge_level
                             FROM users u
                             LEFT JOIN user_challenge_progress p ON u.id = p.user_id AND p.completed = 1
                             LEFT JOIN user_badges b ON u.id = b.user_id
                             GROUP BY u.id
                             ORDER BY tasks_done DESC
                             LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="leaderboard">
  <h2>🏆 Top Users Leaderboard</h2>
  <table>
    <tr><th>Username</th><th>Tasks Completed</th><th>Badge</th></tr>
    <?php foreach ($leaderboard as $row): ?>
      <tr>
        <td><?= htmlspecialchars($row['username']) ?></td>
        <td><?= $row['tasks_done'] ?></td>
        <td><?= htmlspecialchars($row['badge_level'] ?? '-') ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>

<div class="filters">
  <h2>🔍 Filter Challenges or Users</h2>
  <form method="GET">
    <input type="text" name="username" placeholder="Search by Username">
    <select name="badge">
      <option value="">All Badges</option>
      <option value="Beginner Helper">Beginner Helper</option>
      <option value="Kindness Champion">Kindness Champion</option>
    </select>
    <input type="date" name="from_date">
    <input type="date" name="to_date">
    <button type="submit">Apply</button>
  </form>
</div>
</body>
</html>
