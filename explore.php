<h2>Explore Deeds</h2>
<?php
$deeds = file_exists('data/deeds.json') ? json_decode(file_get_contents('data/deeds.json'), true) : [];
foreach ($deeds as $deed) {
    echo "<h3>" . htmlspecialchars($deed['title']) . "</h3>";
    echo "<p>" . htmlspecialchars($deed['description']) . "</p>";
    echo "<small>By " . $deed['username'] . " (" . $deed['role'] . ") on " . $deed['date'] . "</small><hr>";
}
?>
<a href="dashboard.php">Back to Dashboard</a>
