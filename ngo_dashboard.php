<?php
session_start();
require_once 'database.php';

// Check if NGO is logged in
$userId = $_SESSION['user']['id'] ?? null;
if (!$userId) {
    header("Location: login.php");
    exit;
}
// Handle photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_event_id']) && isset($_FILES['event_photo'])) {
    $eventId = $_POST['upload_event_id'];
    $file = $_FILES['event_photo'];

    if ($file['error'] === 0) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'uploads/event_' . $eventId . '_' . time() . '.' . $ext;
        $target = __DIR__ . '/' . $fileName;

        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        if (move_uploaded_file($file['tmp_name'], $target)) {
            $stmt = $pdo->prepare("INSERT INTO event_photos (event_id, photo_path) VALUES (?, ?)");
            $stmt->execute([$eventId, $fileName]);
        }
    }
}

// Fetch NGO record
$stmt = $pdo->prepare("SELECT * FROM ngos WHERE user_id = ?");
$stmt->execute([$userId]);
$ngo = $stmt->fetch();

if (!$ngo) {
    echo "Please complete NGO registration first.";
    exit;
}

// Handle event submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $city = $_POST['city'];
    $address = $_POST['address'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $volunteer_needed = $_POST['volunteer_needed'];

    $insert = $pdo->prepare("INSERT INTO events (ngo_id, title, description, city, address, event_date, event_time, volunteer_needed) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $insert->execute([$ngo['id'], $title, $description, $city, $address, $event_date, $event_time, $volunteer_needed]);
}

// Fetch events created by this NGO
$events = $pdo->prepare("SELECT * FROM events WHERE ngo_id = ? ORDER BY created_at DESC");
$events->execute([$ngo['id']]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NGO Dashboard - ActOfHearts</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #e0f7fa, #ffe0ec);
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
            margin-bottom: 30px;
        }

        form input,
        form textarea {
            width: 100%;
            padding: 10px;
            margin: 8px 0 15px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        form button {
            background-color: #d63384;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        form button:hover {
            background-color: #b0296d;
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

        ul.volunteer-list {
            padding-left: 20px;
        }

        .volunteer-list li {
            font-size: 14px;
            color: #555;
        }

        .section-title {
            margin-top: 40px;
            font-size: 18px;
            color: #555;
            border-bottom: 2px solid #eee;
            padding-bottom: 5px;
        }
        .event-card img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #ccc;
    transition: transform 0.3s ease;
}
.event-card img:hover {
    transform: scale(1.05);
}
.view-events-btn {
    background-color: #d63384;
    color: white;
    padding: 10px 16px;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
    transition: background 0.3s ease;
}
.view-events-btn:hover {
    background-color: #b0296d;
}

    </style>
</head>
<body>

<div class="container">

<div style="text-align: right; margin-bottom: 10px;">
    <a href="events.php" class="view-events-btn">→ View All Events</a>
</div>



    <h2>Welcome, <?= htmlspecialchars($ngo['name']) ?> 👋</h2>
   
  <div class="section-title">Post a New Event</div>
    <form method="POST">
        <input type="text" name="title" placeholder="Event Title" required>
        <textarea name="description" placeholder="Event Description" rows="3" required></textarea>
        <input type="text" name="city" placeholder="City" required>
        <input type="text" name="address" placeholder="Event Address" required>
        <input type="date" name="event_date" required>
        <input type="time" name="event_time" required>
        <input type="number" name="volunteer_needed" placeholder="Volunteers Needed" required>
        <button type="submit">Post Event</button>
    </form>

    <div class="section-title">Your Posted Events</div>

<div style="display: flex; gap: 10px; overflow-x: auto; max-width: 100%; padding-bottom: 5px;">

    <?php foreach ($events as $event): ?>
       <div class="event-card">
    <h4><?= htmlspecialchars($event['title']) ?></h4>
    <p><?= htmlspecialchars($event['description']) ?></p>
    <p>📍 <?= htmlspecialchars($event['city']) ?> | 🗓 <?= $event['event_date'] ?> at <?= $event['event_time'] ?></p>
    <p>👥 Volunteers Needed: <?= $event['volunteer_needed'] ?></p>

    <strong>Joined Volunteers:</strong>
    <ul class="volunteer-list">
        <?php
        $vols = $pdo->prepare("SELECT u.name FROM event_volunteers ev JOIN users u ON ev.user_id = u.id WHERE ev.event_id = ?");
        $vols->execute([$event['id']]);
        foreach ($vols as $v) {
            echo "<li>" . htmlspecialchars($v['name']) . "</li>";
        }
        ?>
    </ul>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="upload_event_id" value="<?= $event['id'] ?>">
        <input type="file" name="event_photo" accept="image/*" required>
        <button type="submit">Upload Photo</button>
    </form>

    <strong>📸 Gallery:</strong>
    <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;">
        <?php
        $photos = $pdo->prepare("SELECT photo_path FROM event_photos WHERE event_id = ?");
        $photos->execute([$event['id']]);
        foreach ($photos as $photo) {
            echo '<img src="' . htmlspecialchars($photo['photo_path']) . '" style="width:100px;height:100px;object-fit:cover;border-radius:8px;border:1px solid #ccc;">';
        }
        ?>
    </div>
</div>
  <?php endforeach; ?>
</div> <!-- closes .container -->
<script>
document.querySelectorAll("input[type='file']").forEach(input => {
    input.addEventListener('change', function() {
        const preview = document.createElement('img');
        preview.style.width = '80px';
        preview.style.marginTop = '8px';
        preview.src = URL.createObjectURL(this.files[0]);
        this.parentNode.appendChild(preview);
    });
});
</script>

</body>
</html>

