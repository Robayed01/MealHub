<?php
session_start();
if (!isset($_SESSION['owner_id'])) {
    header("Location: ../restaurant_owner_login.php");
    exit();
}
include "../includes/db.php";

$rid = intval($_SESSION['restaurant_id']);

// total distinct orders that include this restaurant
$stmt = $conn->prepare("SELECT COUNT(DISTINCT order_id) AS cnt FROM order_items WHERE restaurant_id = ?");
$stmt->bind_param("i", $rid);
$stmt->execute();
$orders_count = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
$stmt->close();

// revenue for this restaurant (sum price * quantity)
$stmt = $conn->prepare("SELECT COALESCE(SUM(price * quantity),0) AS rev FROM order_items WHERE restaurant_id = ?");
$stmt->bind_param("i", $rid);
$stmt->execute();
$revenue = $stmt->get_result()->fetch_assoc()['rev'] ?? 0.0;
$stmt->close();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Owner Dashboard - <?php echo htmlspecialchars($_SESSION['restaurant_name']); ?></title>
<style>
body{font-family:Poppins,Arial,sans-serif;background:#f4f6f8;margin:0}
.header{background:#007bff;color:#fff;padding:14px;display:flex;justify-content:space-between;align-items:center}
.container{max-width:1000px;margin:20px auto;padding:16px}
.card{background:#fff;padding:20px;border-radius:10px;box-shadow:0 6px 20px rgba(16,24,40,0.05);margin-bottom:14px;text-align:center}
.btn{padding:8px 12px;background:#007bff;color:#fff;border-radius:6px;text-decoration:none}
.grid{display:flex;gap:14px}
.stat{font-size:28px;font-weight:700}
</style>
</head>
<body>
<div class="header">
  <div><strong><?php echo htmlspecialchars($_SESSION['restaurant_name']); ?> — Owner Panel</strong></div>
  <div>
    <span><?php echo htmlspecialchars($_SESSION['owner_username']); ?></span> |
    <a href="logout.php" style="color:#fff;text-decoration:none">Logout</a>
  </div>
</div>

<div class="container">
  <div class="grid">
    <div class="card" style="flex:1">
      <div class="small">Total Orders</div>
      <div class="stat"><?php echo (int)$orders_count; ?></div>
    </div>
    <div class="card" style="flex:1">
      <div class="small">Total Revenue</div>
      <div class="stat" style="color:green">৳<?php echo number_format((float)$revenue,2); ?></div>
    </div>
  </div>

  <div style="margin-top:16px" class="card">
    <h3>Quick Links</h3>
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:12px">
      <a class="btn" href="food_items.php">Manage Menu</a>
      <a class="btn" href="categories.php">Manage Categories</a>
      <a class="btn" href="orders.php">View Orders</a>
      <a class="btn" href="reports.php">Revenue</a>
    </div>
  </div>
</div>
</body>
</html>
