<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Connect to DB
        $conn = new mysqli("localhost", "root", "", "actofhearts");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check if email exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            // Insert new user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashedPassword);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit;
            } else {
                $error = "Error saving user data.";
            }
        }
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Sign Up | ActOfHearts</title>
<style>
  body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  /* Background image added below */
  background: url('images/peace.jpg') no-repeat center center fixed;
  background-size: cover;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  margin: 0;
}

  form {
  background: rgba(255, 255, 255, 0.6); /* semi-transparent */
  padding: 30px 40px;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  width: 350px;
  backdrop-filter: blur(8px); /* optional: adds blur behind form for better readability */
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
  color: #000000;      /* black text */
  font-weight: 600;    /* slightly bold */
  display: block;
  margin-top: 15px;
}

  input, select {
    width: 100%;
    padding: 10px 12px;
    margin-top: 6px;
    border: 1.8px solid #ddd;
    border-radius: 6px;
    font-size: 15px;
    transition: border-color 0.3s ease;
  }
  input:focus, select:focus {
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
</style>
</head>
<body>

<form method="POST" novalidate>
  <h2>Create Account</h2>

  <?php if (isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <label for="username">Username</label>
  <input id="username" name="username" required value="<?= isset($username) ? htmlspecialchars($username) : '' ?>">

  <label for="email">Email</label>
  <input id="email" type="email" name="email" required value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">

    <label for="password">Password</label>
  <input id="password" type="password" name="password" required>

  </select>

  <button type="submit">Sign Up</button>
</form>

</body>
</html>

