<?php
session_start();
include "includes/db.php";

$msg = "";
$msg_type = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_id'] = $row['user_id'];
            $_SESSION['admin_name'] = $row['name'];
            $_SESSION['role'] = 'admin';
            header("Location: admin/dashboard.php");
            exit();
        } else {
            $msg = "Invalid password!";
            $msg_type = "error";
        }
    } else {
        $msg = "Admin account not found!";
        $msg_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login - MealHub</title>
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
    .msg {
      margin-bottom: 10px;
      color: red;
      font-weight: 500;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <h2>Admin Login</h2>
      <?php if ($msg): ?>
        <div class="msg"><?php echo $msg; ?></div>
      <?php endif; ?>
      <form method="POST">
        <input type="email" name="email" placeholder="Enter admin email" required><br>
        <input type="password" name="password" placeholder="Enter password" required><br>
        <button type="submit" name="login">Login Now</button>
      </form>
    </div>
  </div>
</body>
</html>
