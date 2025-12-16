<?php
// restaurant panel/dashboard.php
session_start();
if (!isset($_SESSION['owner_id'])) {
  header("Location: ../restaurant_owner_login.php");
  exit();
}
include "../includes/db.php";

$rid = intval($_SESSION['restaurant_id']);
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Owner Dashboard - <?php echo htmlspecialchars($_SESSION['restaurant_name']); ?></title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body {
      font-family: "Poppins", sans-serif;
      margin: 0;
      display: flex;
      height: 100vh;
      overflow: hidden;
      background: #C7CFB7
    }

    .sidebar {
      width: 280px;
      background: #557174;
      color: white;
      display: flex;
      flex-direction: column;
      padding: 20px;
      flex-shrink: 0
    }

    .sidebar h2 {
      margin: 0 0 30px 0;
      text-align: center
    }

    .profile {
      text-align: center;
      margin-bottom: 40px
    }

    .profile .avatar-circle {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      margin: 0 auto 10px;
      background: #1F2937;
      color: #C7CFB7;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 30px;
      font-weight: bold;
    }

    .profile .name {
      font-weight: 600;
      font-size: 25px;
      color: #1F2937
    }

    .profile .sub {
      font-size: 13px;
      opacity: 0.9;
      color: #1F2937
    }

    .nav {
      display: flex;
      flex-direction: column;
      gap: 10px
    }

    .nav a {
      color: #1F2937;
      text-decoration: none;
      font-weight: bold;
      font-size: 17px;
      padding: 12px 18px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      gap: 12px;
      transition: 0.2s
    }

    .nav a:hover,
    .nav a.active {
      background: rgba(255, 255, 255, 0.2)
    }

    .main {
      flex: 1;
      padding: 40px;
      overflow-y: auto;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center
    }
  </style>
</head>

<body>

  <div class="sidebar">
    <h2 style="font-weight: 900;font-size: 25px; color: #1F2937">Owner Panel</h2>

    <div class="profile">
      <?php
      $parts = explode(" ", $_SESSION['owner_username']);
      $initials = strtoupper($parts[0][0]);
      if (isset($parts[1])) {
        $initials .= strtoupper($parts[1][0]);
      }
      ?>
      <div class="avatar-circle"><?php echo $initials; ?></div>
      <div class="name"><?php echo htmlspecialchars($_SESSION['owner_username']); ?></div>
      <div class="sub"><?php echo htmlspecialchars($_SESSION['restaurant_name']); ?></div>
    </div>

    <nav class="nav">
      <a href="dashboard.php" class="active">
        <span>Dashboard</span>
      </a>
      <a href="categories.php">
        <span>Manage Categories</span>
      </a>
      <a href="food_items.php">
        <span>Manage Food Items</span>
      </a>
      <a href="orders.php">
        <span>View Orders</span>
      </a>
      <a href="reports.php">
        <span>Revenue</span>
      </a>
      <a href="logout.php" style="color: red">
        <span>Logout</span>
      </a>
    </nav>
  </div>

  <div class="main">
    <h1>Welcome to Restaurant owner panel</h1>
  </div>

</body>

</html>