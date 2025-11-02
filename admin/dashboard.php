<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../admin_login.php");
    exit();
}
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Admin Dashboard</title></head>
<body style="font-family:Arial,Helvetica,sans-serif;padding:20px">
  <h1>Admin Dashboard</h1>
  <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin']); ?>.</p>
  <p><a href="../logout.php">Logout</a></p>
  <p>(Create admin features here: manage categories, food items, orders.)</p>
</body>
</html>
