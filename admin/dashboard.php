<?php
// admin/dashboard.php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../admin_login.php");
    exit();
}
include "../includes/db.php";

// quick counts
$countRes = $conn->query("SELECT COUNT(*) AS cnt FROM restaurants");
$restaurants_count = ($countRes->fetch_assoc()['cnt'] ?? 0);

$countOwners = $conn->query("SELECT COUNT(*) AS cnt FROM restaurant_owners");
$owners_count = ($countOwners->fetch_assoc()['cnt'] ?? 0);

$countOrders = $conn->query("SELECT COUNT(*) AS cnt FROM orders");
$orders_count = ($countOrders->fetch_assoc()['cnt'] ?? 0);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Dashboard - MealHub</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body{font-family:Inter,Arial,sans-serif;margin:0;background:#f4f6f8}
.header{background:#007bff;color:#fff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center}
.container{max-width:1100px;margin:20px auto;padding:16px}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:18px}
.card{background:#fff;padding:20px;border-radius:10px;box-shadow:0 6px 20px rgba(16,24,40,0.05);text-align:center}
.card h3{margin:0;font-size:20px}
.card p{margin:8px 0 0 0;color:#666}
.links{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}
.links a{display:inline-block;padding:10px 14px;background:#007bff;color:#fff;border-radius:8px;text-decoration:none}
.small{font-size:17px;color:#444}
.footer{margin-top:18px}
</style>
</head>
<body>
  <div class="header">
    <div><strong>MealHub Admin</strong></div>
    <div>
      <span class="small">Hi, <?php echo htmlspecialchars($_SESSION['admin']); ?></span>
      &nbsp;|&nbsp;
      <a href="../logout.php" style="color:#fff;text-decoration:none">Logout</a>
    </div>
  </div>

  <div class="container">
    <div class="grid">
      <div class="card"><h3><?php echo (int)$restaurants_count; ?></h3><p>Restaurants</p></div>
      <div class="card"><h3><?php echo (int)$owners_count; ?></h3><p>Restaurant Owners</p></div>
    </div>

    <div class="card">
      <h3>Quick Links</h3>
      <div class="links" style="margin-top:12px">
        <a href="restaurants.php">Manage Restaurants</a>
        <a href="owners.php">Manage Restaurant Owners</a>
        <a href="reports.php">Reports</a>
      </div>
    </div>

  </div>
</body>
</html>
