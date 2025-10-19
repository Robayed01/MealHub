<?php
include "includes/db.php";

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($password !== $confirm) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'customer')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $phone, $hashed);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful! Please login.'); window.location='index.php';</script>";
        } else {
            echo "<script>alert('Error: Email already exists!');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register - MealHub</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      background-color: #2a65ff;
      font-family: 'Poppins', sans-serif;
    }
    .container {
      width: 100%;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .card {
      background: #fff;
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.2);
      width: 400px;
      text-align: center;
    }
    .card h2 {
      margin-bottom: 20px;
    }
    input {
      width: 90%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    button {
      width: 95%;
      padding: 10px;
      background: #2a65ff;
      border: none;
      color: white;
      border-radius: 5px;
      cursor: pointer;
    }
    button:hover {
      background: #1e4fd3;
    }
    a {
      color: #2a65ff;
      text-decoration: none;
    }
    .footer {
      margin-top: 15px;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <h2>Registration</h2>
      <form method="POST">
        <input type="text" name="name" placeholder="Enter your name" required><br>
        <input type="email" name="email" placeholder="Enter your email" required><br>
        <input type="text" name="phone" placeholder="Enter your phone number" required><br>
        <input type="password" name="password" placeholder="Create a password" required><br>
        <input type="password" name="confirm" placeholder="Confirm a password" required><br>
        <label>
          <input type="checkbox" required> I accept all terms & conditions
        </label><br><br>
        <button type="submit" name="register">Register Now</button>
      </form>
      <div class="footer">
        <p>Already have an account? <a href="index.php">Login now</a></p>
      </div>
    </div>
  </div>
</body>
</html>
