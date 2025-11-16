<?php
session_start();
include "../includes/db.php";

$order_id = intval($_GET['order_id']);

$sql = "SELECT oi.*, f.name AS food_name, r.name AS restaurant_name 
        FROM order_items oi
        JOIN food_items f ON oi.food_id = f.food_id
        JOIN restaurants r ON oi.restaurant_id = r.restaurant_id
        WHERE oi.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();
?>
<!doctype html>
<html>
<head>
<title>Order Details</title>
<style>
body{font-family:Inter,Arial;background:#f4f6f8;margin:0}
.container{max-width:800px;margin:24px auto;background:white;padding:20px;border-radius:12px;
box-shadow:0 4px 14px rgba(0,0,0,0.1)}
.row{display:flex;justify-content:space-between;border-bottom:1px solid #eee;padding:10px 0}
.price{font-weight:700;color:#007bff}
</style>
</head>
<body>
<div class="container">
  <h2>Order #<?php echo $order_id; ?></h2>

  <?php while($it = $items->fetch_assoc()): ?>
    <div class="row">
      <div>
        <strong><?php echo $it['food_name']; ?></strong><br>
        <small><?php echo $it['restaurant_name']; ?></small><br>
        Qty: <?php echo $it['quantity']; ?>
      </div>
      <div class="price">
        $<?php echo number_format($it['price']*$it['quantity'],2); ?>
      </div>
    </div>
  <?php endwhile; ?>

</div>
<div style="text-align:center; margin-top: 25px;">
    <a href="order_history.php" 
       style="background:#007bff; padding:12px 20px; color:white; 
              border-radius:8px; text-decoration:none; font-size:16px;">
        ← Back to Order History
    </a>
</div>

</body>
</html>
