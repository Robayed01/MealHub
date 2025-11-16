<?php
session_start();
include "../includes/db.php";

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();
?>
<!doctype html>
<html>
<head>
<title>My Orders</title>
<style>
body{font-family:Inter,Arial;background:#f4f6f8;margin:0}
.container{max-width:800px;margin:24px auto;padding:0 16px}
.card{background:white;padding:16px;border-radius:10px;margin-bottom:12px;
box-shadow:0 3px 14px rgba(0,0,0,0.07)}
a.btn{padding:8px 14px;background:#007bff;color:white;border-radius:6px;text-decoration:none}
</style>
</head>
<body>


<div class="container">
  <h2>Order History</h2>
  <?php while($o = $orders->fetch_assoc()): ?>
    <div class="card">
      <h3>Order #<?php echo $o['order_id']; ?></h3>
      <p>Total: $<?php echo number_format($o['total_amount'],2); ?></p>
      <p>Date: <?php echo $o['created_at']; ?></p>
      <a class="btn" href="order_view.php?order_id=<?php echo $o['order_id']; ?>">View Details</a>
    </div>

    
  <?php endwhile; ?>
</div>
<div style="text-align:center; margin-top: 20px;">
    <a href="restaurants.php" 
       style="background:#007bff; padding:12px 20px; color:white; 
              border-radius:8px; text-decoration:none; font-size:16px;">
        ← Back to Restaurants
    </a>
</div>

<br>

</body>
</html>
