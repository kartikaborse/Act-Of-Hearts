<?php
session_start();
require_once 'database.php';

// Ensure admin access
if (!isset($_SESSION['user']['is_admin']) || $_SESSION['user']['is_admin'] !== 1) {
    die("Access denied.");
}

// Handle Add Challenge
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['challenge_text'])) {
    $text = trim($_POST['challenge_text']);
    if (!empty($text)) {
        $stmt = $pdo->prepare("INSERT INTO challenges (challenge_text) VALUES (?)");
        $stmt->execute([$text]);
    }
}

// Handle Delete Challenge
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM challenges WHERE id = ?")->execute([$deleteId]);
    header("Location: manage_challenges.php");
    exit;
}

// Fetch all challenges
$challenges = $pdo->query("SELECT * FROM challenges ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Challenges</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9f9f9;
            padding: 30px;
        }
        .container {
            max-width: 700px;
            margin: auto;
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #ff3366;
        }
        form {
            margin-bottom: 25px;
        }
        input[type="text"] {
            width: 80%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        button {
            padding: 10px 20px;
            background: #ff3366;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #eee;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #ffe6ec;
        }
        .delete-btn {
            color: red;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>📋 Manage Daily Challenges</h1>

    <form method="POST">
        <input type="text" name="challenge_text" placeholder="Enter new challenge..." required />
        <button type="submit">➕ Add Challenge</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Challenge</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($challenges as $challenge): ?>
            <tr>
                <td><?= $challenge['id'] ?></td>
                <td><?= htmlspecialchars($challenge['challenge_text']) ?></td>
                <td><a class="delete-btn" href="?delete=<?= $challenge['id'] ?>" onclick="return confirm('Delete this challenge?');">🗑 Delete</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
