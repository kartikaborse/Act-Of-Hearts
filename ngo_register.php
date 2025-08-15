<?php
session_start();
require_once 'database.php';

$userId = $_SESSION['user']['id'] ?? null;

if (!$userId) {
    header("Location: login.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $city = $_POST['city'];
    $phone = $_POST['phone'];
    $description = $_POST['description'];
    $logo = ''; // handle file upload later

    // Insert into NGOs table
    $stmt = $pdo->prepare("INSERT INTO ngos (user_id, name, email, city, phone, description, logo) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $name, $email, $city, $phone, $description, $logo]);

    header("Location: ngo_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NGO Registration - ActOfHearts</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #ffe0ec, #e0f7fa);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .register-box {
            background-color: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 400px;
        }
        .register-box h2 {
            text-align: center;
            color: #d63384;
            margin-bottom: 20px;
        }
        .register-box input,
        .register-box textarea {
            width: 100%;
            padding: 10px 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        .register-box button {
            width: 100%;
            background-color: #d63384;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .register-box button:hover {
            background-color: #b0296d;
        }
        .note {
            text-align: center;
            font-size: 13px;
            margin-top: 10px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="register-box">
        <h2>NGO Registration</h2>
        <form method="POST">
            <input type="text" name="name" placeholder="NGO Name" required>
            <input type="email" name="email" placeholder="Contact Email" required>
            <input type="text" name="city" placeholder="City" required>
            <input type="text" name="phone" placeholder="Phone Number">
            <textarea name="description" placeholder="About your NGO..." rows="4"></textarea>
            <button type="submit">Register NGO</button>
        </form>
        <p class="note">Already registered? Go to your <a href="ngo_dashboard.php">Dashboard</a>.</p>
    </div>
</body>
</html>
