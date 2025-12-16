<?php
// customer/dashboard.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
include "../includes/db.php";
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Dashboard - MealHub</title>
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
            font-weight: 900;
            font-size: 25px;
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

        .nav a svg {
            width: 20px;
            height: 20px
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
        <h2 style="font-weight: 900;font-size: 25px; color: #1F2937">MealHub</h2>

        <div class="profile">
            <?php
            $parts = explode(" ", $_SESSION['name']);
            $initials = strtoupper($parts[0][0]);
            if (isset($parts[1])) {
                $initials .= strtoupper($parts[1][0]);
            }
            ?>
            <div class="avatar-circle"><?php echo $initials; ?></div>
            <div class="name"><?php echo htmlspecialchars($_SESSION['name']); ?></div>
        </div>

        <nav class="nav">
            <a href="dashboard.php" class="active">
                <span>Dashboard</span>
            </a>
            <a href="restaurants.php">
                <span>Restaurants</span>
            </a>
            <a href="order_history.php">
                <span>View Orders</span>
            </a>
            <a href="#" onclick="alert('Future Work')">
                <span>Profile Management</span>
            </a>
            <a href="#" onclick="alert('Future Work')">
                <span>Settings</span>
            </a>
            <a href="../logout.php" style="color: red">
                <span>Logout</span>
            </a>
        </nav>
    </div>

    <div class="main">
        <div>
            <h1>Welcome to MealHub</h1>
        </div>
    </div>

</body>

</html>