<?php
session_start();
require_once 'database.php';

$userId = $_SESSION['user']['id'] ?? null;
if (!$userId) {
    header('Location: login.php');
    exit();
}

// Send request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_friend'])) {
        $receiverId = $_POST['add_friend'];
        $stmt = $pdo->prepare("INSERT INTO friend_requests (sender_id, receiver_id) VALUES (?, ?)");
        $stmt->execute([$userId, $receiverId]);
    } elseif (isset($_POST['accept'])) {
        $requestId = $_POST['accept'];
        $stmt = $pdo->prepare("UPDATE friend_requests SET status = 'accepted' WHERE id = ? AND receiver_id = ?");
        $stmt->execute([$requestId, $userId]);
    } elseif (isset($_POST['reject'])) {
        $requestId = $_POST['reject'];
        $stmt = $pdo->prepare("UPDATE friend_requests SET status = 'rejected' WHERE id = ? AND receiver_id = ?");
        $stmt->execute([$requestId, $userId]);
    }
}

// Get other users
$usersStmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id != ?");
$usersStmt->execute([$userId]);
$allUsers = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch requests
$incomingStmt = $pdo->prepare("SELECT fr.id, u.name FROM friend_requests fr JOIN users u ON fr.sender_id = u.id WHERE fr.receiver_id = ? AND fr.status = 'pending'");
$incomingStmt->execute([$userId]);
$incomingRequests = $incomingStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch accepted friends
$friendsStmt = $pdo->prepare("
    SELECT u.name FROM friend_requests fr
    JOIN users u ON (u.id = fr.sender_id OR u.id = fr.receiver_id)
    WHERE ((fr.sender_id = ? OR fr.receiver_id = ?) AND fr.status = 'accepted') AND u.id != ?
");
$friendsStmt->execute([$userId, $userId, $userId]);
$friends = $friendsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Friends - ActOfHearts</title>
    <style>
        body { font-family: Arial; background: #ffeef5; margin: 0; padding: 20px; }
        h2 { color: #e91e63; }
        .card { background: #fff; padding: 15px; margin: 15px 0; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        button { padding: 8px 12px; background: #e91e63; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #d81b60; }
    </style>
</head>
<body>

<h2>Friend Requests</h2>
<?php if ($incomingRequests): ?>
    <?php foreach ($incomingRequests as $req): ?>
        <div class="card">
            <p><strong><?= htmlspecialchars($req['name']) ?></strong> sent you a request</p>
            <form method="post">
                <button name="accept" value="<?= $req['id'] ?>">Accept</button>
                <button name="reject" value="<?= $req['id'] ?>" style="background:#ccc; color:black;">Reject</button>
            </form>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No new friend requests</p>
<?php endif; ?>

<h2>All Users</h2>
<?php foreach ($allUsers as $user): ?>
    <div class="card">
        <p><strong><?= htmlspecialchars($user['name']) ?></strong> (<?= htmlspecialchars($user['email']) ?>)</p>
        <form method="post">
            <button name="add_friend" value="<?= $user['id'] ?>">Add Friend</button>
        </form>
    </div>
<?php endforeach; ?>

<h2>Your Friends</h2>
<?php if ($friends): ?>
    <ul>
        <?php foreach ($friends as $friend): ?>
            <li><?= htmlspecialchars($friend['name']) ?></li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>You don’t have any friends yet.</p>
<?php endif; ?>

</body>
</html>
