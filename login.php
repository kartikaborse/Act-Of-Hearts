<?php
session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    // DB connection
    $conn = new mysqli("localhost", "root", "", "actofhearts");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Use prepared statement securely with mysqli
    $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($pass, $user['password'])) {
            // ✅ Now safe to set session
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email']
            ];

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Invalid email or password.";
    }

    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Login | ActOfHearts</title>
<style>
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: url('images/peace.jpg') no-repeat center center fixed;
    background-size: cover;
    margin: 0;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  form {
    background: rgba(255, 255, 255, 0.6);
    padding: 30px 40px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    width: 350px;
    backdrop-filter: blur(8px);
  }
  form h2 {
    font-family: 'Dancing Script', cursive;
    font-size: 38px;
    font-weight: 700;
    color: #ff6f61;
    text-align: center;
    letter-spacing: 2px;
    text-shadow: 1px 1px 3px rgba(255,111,97,0.5);
    margin-bottom: 25px;
  }
  label {
    color: #000000;
    font-weight: 600;
    display: block;
    margin-top: 15px;
  }
  input {
    width: 100%;
    padding: 10px 12px;
    margin-top: 6px;
    border: 1.8px solid #ddd;
    border-radius: 6px;
    font-size: 15px;
    color: #000;
    font-weight: normal;
    transition: border-color 0.3s ease;
  }
  input:focus {
    border-color: #ff6f61;
    outline: none;
  }
  button {
    margin-top: 25px;
    width: 100%;
    background-color: #ff6f61;
    color: white;
    font-size: 17px;
    padding: 12px 0;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 700;
    transition: background-color 0.3s ease;
  }
  button:hover {
    background-color: #e65a4f;
  }
  .error {
    margin-top: 15px;
    background-color: #f8d7da;
    padding: 10px;
    border-radius: 6px;
    color: #721c24;
    font-weight: 600;
    text-align: center;
  }
  @import url('https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&display=swap');
</style>
</head>
<body>

<form method="POST" novalidate>
  <h2>Login</h2>

  <?php if (isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <label for="email">Email</label>
  <input id="email" type="email" name="email" required value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">

  <label for="password">Password</label>
  <input id="password" type="password" name="password" required>

  <button type="submit">Login</button>
</form>

</body>
</html>
