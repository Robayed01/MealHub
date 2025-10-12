<?php
session_start();
include "includes/db.php";

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['name'] = $row['name'];

            if ($row['role'] == 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: customer/menu.php");
            }
            exit();
        } else {
            echo "<script>alert('Invalid password');</script>";
        }
    } else {
        echo "<script>alert('Email not found');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - MealHub</title>
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
      width: 350px;
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
      <h2>Login</h2>
      <form method="POST">
        <input type="email" name="email" placeholder="Enter your email" required><br>
        <input type="password" name="password" placeholder="Confirm a password" required><br>
        <button type="submit" name="login">Login Now</button>
      </form>
      <div class="footer">
        <p>Don't have an account? <a href="register.php">Signup now</a></p>
      </div>
    </div>
  </div>
</body>
</html>
