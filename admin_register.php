<?php
include "includes/db.php";

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm']);

    if ($password !== $confirm) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        // Check if admin already exists
        $check = $conn->prepare("SELECT * FROM users WHERE role='admin'");
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('An admin already exists. You cannot create another.');</script>";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'admin')");
            $stmt->bind_param("ssss", $name, $email, $phone, $hashed);

            if ($stmt->execute()) {
                echo "<script>alert('Admin account created successfully!'); window.location='admin_login.php';</script>";
            } else {
                echo "<script>alert('Error creating admin account.');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Registration - MealHub</title>
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
    .card h2 { margin-bottom: 20px; }
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
    button:hover { background: #1e4fd3; }
    a { color: #2a65ff; text-decoration: none; }
    .footer { margin-top: 15px; }
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <h2>Admin Registration</h2>
      <form method="POST">
        <input type="text" name="name" placeholder="Enter name" required><br>
        <input type="email" name="email" placeholder="Enter email" required><br>
        <input type="text" name="phone" placeholder="Enter phone number" required><br>
        <input type="password" name="password" placeholder="Create password" required><br>
        <input type="password" name="confirm" placeholder="Confirm password" required><br>
        <button type="submit" name="register">Create Admin Account</button>
      </form>
      <div class="footer">
        <p>Already have an admin account? <a href="admin_login.php">Login here</a></p>
      </div>
    </div>
  </div>
</body>
</html>
