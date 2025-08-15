<?php
session_start();
require_once 'database.php';

$userId = $_SESSION['user']['id'] ?? null;
$cityFilter = $_GET['city'] ?? '';

// Join event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id']) && $userId) {
    $eventId = $_POST['event_id'];

    $check = $pdo->prepare("SELECT * FROM event_volunteers WHERE event_id = ? AND user_id = ?");
    $check->execute([$eventId, $userId]);
    if ($check->rowCount() == 0) {
        $join = $pdo->prepare("INSERT INTO event_volunteers (event_id, user_id) VALUES (?, ?)");
        $join->execute([$eventId, $userId]);
    }
}

if ($cityFilter) {
    $events = $pdo->prepare("SELECT e.*, n.name AS ngo_name FROM events e JOIN ngos n ON e.ngo_id = n.id WHERE e.city = ? ORDER BY e.event_date ASC");
    $events->execute([$cityFilter]);
} else {
    $events = $pdo->query("SELECT e.*, n.name AS ngo_name FROM events e JOIN ngos n ON e.ngo_id = n.id ORDER BY e.event_date ASC");
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>All Events - ActOfHearts</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #e0f7fa, #ffe0ec);
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 900px;
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

        form button {
            background-color: #d63384;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 10px;
        }

        form button:hover {
            background-color: #b0296d;
        }

        .search-bar {
            text-align: center;
            margin-bottom: 20px;
        }

        .search-bar input {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            width: 60%;
        }

        .search-bar button {
            padding: 8px 16px;
            background-color: #d63384;
            color: white;
            border: none;
            border-radius: 6px;
            margin-left: 8px;
            cursor: pointer;
        }

        .search-bar button:hover {
            background-color: #b0296d;
        }
     .event-card img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #ccc;
    transition: transform 0.2s ease;
}

.event-card img:hover {
    transform: scale(1.05);
}

    </style>
</head>
<body>

<div class="container">
    <h2>Volunteer Opportunities</h2>

    <div class="search-bar">
        <form method="GET">
            <input type="text" name="city" placeholder="Search by city..." value="<?= htmlspecialchars($cityFilter) ?>">
            <button type="submit">Filter</button>
        </form>
    </div>

    <?php foreach ($events as $event): ?>
        <div class="event-card">
            <h4><?= htmlspecialchars($event['title']) ?></h4>
            <p><?= htmlspecialchars($event['description']) ?></p>
            <p>By <strong><?= htmlspecialchars($event['ngo_name']) ?></strong></p>
            <p>📍 <?= htmlspecialchars($event['city']) ?> | 🗓 <?= $event['event_date'] ?> at <?= $event['event_time'] ?></p>
            <p>👥 Volunteers Needed: <?= $event['volunteer_needed'] ?></p>

            <?php if ($userId): ?>
                <form method="POST">
                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                    <button type="submit">Join as Volunteer</button>
                </form>
            <?php else: ?>
                <em><a href="login.php">Login to join</a></em>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
    <div style="margin-top: 10px;">
        <strong>📸 Event Gallery:</strong>
        <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 5px;">
            <?php
            $photos = $pdo->prepare("SELECT photo_path FROM event_photos WHERE event_id = ?");
            $photos->execute([$event['id']]);
            if ($photos->rowCount() > 0) {
                foreach ($photos as $photo) {
                    echo '<img src="' . htmlspecialchars($photo['photo_path']) . '" style="width:100px;height:100px;object-fit:cover;border-radius:8px;border:1px solid #ccc;">';
                }
            } else {
                echo '<p style="color:#999;font-size:14px;">No photos yet for this event.</p>';
            }
            ?>
        </div>
    </div>

</body>
</html>
