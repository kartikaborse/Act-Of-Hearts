<?php
session_start();
require_once 'database.php';

// Ensure admin access
if (!isset($_SESSION['user']['is_admin']) || $_SESSION['user']['is_admin'] !== 1) {
    die("Access denied.");
}

// Fetch all challenges for dropdown
$challenges = $pdo->query("SELECT * FROM challenges ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Add Task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_text'], $_POST['challenge_id'])) {
    $text = trim($_POST['task_text']);
    $challengeId = intval($_POST['challenge_id']);
    if ($text && $challengeId) {
        $stmt = $pdo->prepare("INSERT INTO challenge_tasks (challenge_id, task_text) VALUES (?, ?)");
        $stmt->execute([$challengeId, $text]);
    }
}

// Delete Task
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM challenge_tasks WHERE id = ?")->execute([$deleteId]);
    header("Location: manage_tasks.php");
    exit;
}

// Filter tasks by challenge (if selected)
$selectedChallengeId = $_GET['challenge_id'] ?? null;
$tasks = [];
if ($selectedChallengeId) {
    $stmt = $pdo->prepare("SELECT * FROM challenge_tasks WHERE challenge_id = ?");
    $stmt->execute([$selectedChallengeId]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Challenge Tasks</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f7f7f7;
            padding: 30px;
        }
        .container {
            max-width: 800px;
            background: white;
            margin: auto;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #ff4081;
        }
        select, input[type="text"] {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin-bottom: 10px;
            width: 60%;
        }
        button {
            padding: 10px 20px;
            background-color: #ff4081;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .task-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #eee;
        }
        th {
            background: #ffe6f0;
        }
        .delete-btn {
            color: red;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>📝 Manage Challenge Tasks</h1>

    <!-- Select Challenge to Filter -->
    <form method="GET">
        <label for="challenge_id">📌 View Tasks for Challenge:</label><br>
        <select name="challenge_id" onchange="this.form.submit()">
            <option value="">-- Select Challenge --</option>
            <?php foreach ($challenges as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($selectedChallengeId == $c['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['challenge_text']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <!-- Add Task -->
    <form method="POST">
        <label>Add Task to Challenge:</label><br>
        <select name="challenge_id" required>
            <option value="">-- Select Challenge --</option>
            <?php foreach ($challenges as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['challenge_text']) ?></option>
            <?php endforeach; ?>
        </select><br>
        <input type="text" name="task_text" placeholder="Enter task..." required />
        <button type="submit">➕ Add Task</button>
    </form>


    <!-- Task List -->
    <?php if ($selectedChallengeId): ?>
        <h3>📋 Tasks for Selected Challenge</h3>
        <table class="task-table">
            <tr>
                <th>ID</th>
                <th>Task</th>
                <th>Action</th>
            </tr>
            <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?= $task['id'] ?></td>
                    <td><?= htmlspecialchars($task['task_text']) ?></td>
                    <td><a class="delete-btn" href="?delete=<?= $task['id'] ?>" onclick="return confirm('Delete this task?');">🗑 Delete</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
