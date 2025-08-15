<?php
session_start();
require_once 'database.php';

$userId = $_SESSION['user']['id'] ?? null;
if (!$userId) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT e.*, n.name AS ngo_name FROM event_volunteers ev 
JOIN events e ON ev.event_id = e.id 
JOIN ngos n ON e.ngo_id = n.id 
WHERE ev.user_id = ? ORDER BY e.event_date ASC");
$stmt->execute([$userId]);
$joinedEvents = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>My Events - ActOfHearts</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #ffe0ec, #e0f7fa);
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #d63384;
        }

        .event-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin-top: 20px;
            border-radius: 10px;
            background-color: #fdfcfc;
        }

        .event-card h4 {
            color: #d63384;
            margin: 0 0 10px;
        }

        .no-events {
            text-align: center;
            margin-top: 40px;
            font-size: 16px;
            color: #555;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>My Joined Events</h2>

    <?php if ($joinedEvents): ?>
        <?php foreach ($joinedEvents as $event): ?>
            <div class="event-card">
                <h4><?= htmlspecialchars($event['title']) ?></h4>
                <p><?= htmlspecialchars($event['description']) ?></p>
                <p>Organized by <strong><?= htmlspecialchars($event['ngo_name']) ?></strong></p>
                <p>📍 <?= $event['city'] ?> | 🗓 <?= $event['event_date'] ?> 🕒 <?= $event['event_time'] ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="no-events">You haven't joined any events yet. <a href="events.php">Explore opportunities</a>.</p>
    <?php endif; ?>
</div>

</body>
</html>
