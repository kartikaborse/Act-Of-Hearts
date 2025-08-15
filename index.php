<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ActOfHearts | Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * {
      padding: 0;
      margin: 0;
      box-sizing: border-box;
    }

    body {
      overflow-x: hidden;
      font-family: 'Segoe UI', sans-serif;
      background: url('images/pink tree.jpeg') no-repeat center center fixed;
      background-size: cover;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .wrapper {
      position: relative;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      background-color: rgba(255, 255, 255, 0.3);
      overflow: hidden;
      backdrop-filter: blur(3px);
      -webkit-backdrop-filter: blur(3px);
    }

    .content {
      flex-grow: 1;
    }

    .navbar {
      height: 80px;
      width: 100%;
      background: rgba(0, 0, 0, 0.3);
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 50px;
    }

    .logo {
      height: 100px;
      width: 100px;
      border-radius: 50%;
      object-fit: cover;
    }

    .navbar ul {
      display: flex;
      list-style: none;
    }

    .navbar ul li {
      margin: 0 10px;
      line-height: 80px;
    }

    .navbar ul li a {
      text-decoration: none;
      color: black;
      font-weight: bold;
      font-size: 25px;
      padding: 6px 13px;
      font-family: 'Roboto', sans-serif;
      transition: 0.4s;
    }

    .navbar ul li a.active,
    .navbar ul li a:hover {
      background: red;
      border-radius: 2px;
    }

    .center {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      margin-top: 140px;
      margin-bottom: 100px;
      font-family: sans-serif;
      user-select: none;
      padding: 0 20px;
    }

    .center h1 {
      font-size: 60px;
      font-weight: bold;
      color: maroon;
      letter-spacing: 2px;
      text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.6);
      margin-bottom: 10px;
      animation: fadeIn 1.5s ease-in-out;
    }

    .center h2 {
      font-size: 28px;
      margin-bottom: 30px;
      color: black;
      font-weight: bold;
    }

    .buttons a button {
      height: 50px;
      width: 150px;
      font-size: 18px;
      font-weight: bold;
      color: #ffb3b3;
      background: red;
      border: 1px solid #cc0000;
      outline: none;
      cursor: pointer;
      border-radius: 25px;
      transition: 0.5s;
    }

    .buttons a button:hover {
      background: #cc0000;
    }
 .quote-line {
  margin-top: 25px;
  font-size: 22px;
  color: maroon;
  font-style: italic;
  font-weight: bold;
  text-align: center;
  max-width: 700px;
  padding: 10px 20px;
  line-height: 1.6;
  background-color: rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  animation: fadeIn 2s ease-in-out;
}

    .about-us-section {
      margin-top: 280px;
      background-color: rgba(255, 255, 255, 0.8);
      padding: 40px 20px;
      border-radius: 0px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .about-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      align-items: flex-start;
      max-width: 1250px;
      margin: 0 auto;
      gap: 30px;
      flex-direction: column;
      align-items: center;
    }

    .about-text {
      flex: 1;
      text-align: center;
    }

    .about-text h3 {
      font-size: 20px;
      color: #777;
    }

    .about-text h1 {
      font-size: 66px;
      margin: 10px 0;
      color: #333;
    }

    .about-text h1 span {
      font-style: italic;
      font-weight: 300;
    }

    .about-text p {
      font-size: 28px;
      line-height: 1.6;
      color: #333;
    }

    .about-images {
      flex: 1;
      display: flex;
      flex-direction: row;
      justify-content: center;
      align-items: center;
      gap: 20px;
      min-width: 280px;
      max-width: 700px;
      margin: 0 auto;
      flex-wrap: wrap;
    }

    .about-images img {
      width: 30%;
      min-width: 180px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    @media (max-width: 768px) {
      .about-container {
        flex-direction: column;
        align-items: center;
        text-align: center;
      }

      .about-images {
        flex-direction: column;
      }

      .about-images img {
        width: 90%;
      }
    }

    @keyframes fadeIn {
      0% { opacity: 0; transform: translateY(-10px); }
      100% { opacity: 1; transform: translateY(0); }
    }

    footer {
  background-color: rgba(255, 255, 255, 0.2); /* match About Us background */
  color: black;
  text-align: center;
  padding: 15px;
  font-size: 14px;
}

    /* Custom Footer Section */
    .custom-footer {
      background-color:rgba(255, 255, 255, 0.8);
      color: #2c2c2c;
      padding: 60px 20px;
      font-family: 'Georgia', serif;
      border-top: 1px solid #ccc;
    }

    .footer-container {
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
      max-width: 1200px;
      margin: auto;
      gap: 40px;
    }

    .footer-left h2 {
      font-size: 22px;
      font-weight: 500;
    }

    .footer-left h1 {
      font-size: 48px;
      font-weight: 500;
      line-height: 1.3;
    }

    .footer-right {
      font-size: 16px;
      max-width: 400px;
    }

    .footer-right p {
      margin-bottom: 15px;
    }

    .footer-right ul {
      list-style: none;
      padding: 0;
    }

    .footer-right ul li {
      margin-bottom: 8px;
    }

    .footer-right ul li a {
      text-decoration: underline;
      color: #2c2c2c;
    }

    .social-icons {
      font-size: 22px;
      margin: 10px 0 20px;
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="content">
      <nav class="navbar">
        <img class="logo" src="images/actofheart.png" alt="Logo">
        <ul>
          <li><a class="active" href="#about">About Us</a></li>
          <li><a href="sign_up.php">Signup</a></li>
          <li><a href="login.php">Login</a></li>
        </ul>
      </nav>

      <div class="center">
        <h1>ActOfHearts Welcome's You!</h1>
        <h2>Promote Kind Acts Online.</h2>
        <div class="buttons">
          <?php if (isset($_SESSION['user'])): ?>
            <a href="dashboard.php"><button>Explore More</button></a>
          <?php else: ?>
            <a href="#about"><button>Explore More</button></a>
          <?php endif; ?>
          
        </div>
         <p class="quote-line">
  "Don't use social media to impress people; use it to impact people"
</p>

      </div>

      <section class="about-us-section" id="about">
        <div class="about-container">
          <div class="about-text">
            <h3>Our Story</h3>
            <h1><span>About</span> Us</h1>
            <p>
              ActOfHearts is a social platform dedicated to spreading kindness and facilitating community service opportunities.
              Users can showcase their good deeds, while NGOs can engage volunteers and showcase their initiatives.
              With a focus on positivity and community building, ActOfHearts offers a user-friendly interface to inspire and promote charitable actions.
            </p>
          </div>
          <div class="about-images">
            <img src="images/inspire others.jpg" alt="Inspire">
            <img src="images/live the values.jpg" alt="Values">
            <img src="images/one good act.png" alt="One Good Act">
          </div>
        </div>
      </section>

      <!-- Custom Footer Section -->
      <section class="custom-footer">
        <div class="footer-container">
          <div class="footer-left">
            <h2>ActOfHearts</h2>
            <h1>Spread Kindness<br>with <span style="color: maroon;">Us</span></h1>
          </div>
          <div class="footer-right">
            <p>123-456-7890<br>info@actofhearts.com</p>
            <p>Hyderabad</p>
            <div class="social-icons">
              <span>🌐</span> <span>📷</span> <span>✉️</span> <span>🎵</span>
            </div>
            <ul>
              <li><a href="#">Privacy Policy</a></li>
              <li><a href="#">Terms & Conditions</a></li>
              <li><a href="#">Refund Policy</a></li>
            </ul>
          </div>
        </div>
      </section>
    </div>
  </div>

  <footer>
    &copy; 2025 ActOfHearts. All rights reserved.
  </footer>
</body>
</html>
