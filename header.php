<?php
if (session_status() == PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Act of Hearts Dashboard</title>
  <link rel="stylesheet" href="styles.css"> <!-- Optional: external CSS -->
</head>
<body>
<div class="navbar">
  <div class="logo"><a href="dashboard.php"><img src="images/actofheart.png" alt="Logo"></a></div>
  <div class="search-bar"><input type="text" placeholder="Search good deeds..." /></div>
  <div class="nav-links">
    <a href="index.php">Home</a>
    <a href="friends.php">Friends</a>
    <a href="profile.php">My Profile</a>
    <a href="logout.php">Logout</a>
  </div>
</div>
