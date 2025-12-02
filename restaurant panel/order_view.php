<?php
session_start();
if (!isset($_SESSION['owner_id'])) {
    header("Location: ../restaurant_owner_login.php");
    exit();
}
include "../includes/db.php";

if (!isset($_GET['id'])) die("Order ID required.");
$order_id = intval($_GET['id']);
$rid = intval($_SESSION['restaurant_id']);

// fetch order (basic)
$s = $conn->prepare("SELECT o.*, u.name AS customer_name FROM orders o LEFT JOIN users u ON o.user_id = u.user_id WHERE o.order_id = ?");
$s->bind_param("i", $order_id);
$s->execute();
$order = $s->get_result()->fetch_assoc();
$s->close();
if (!$order) die("Order not found.");

// fetch items for this order that belong to THIS restaurant
$it = $conn->prepare("SELECT oi.quantity, oi.price, fi.name as food_name FROM order_items oi LEFT JOIN food_items fi ON oi.food_id = fi.food_id WHERE oi.order_id = ? AND oi.restaurant_id = ?");
$it->bind_param("ii", $order_id, $rid);
$it->execute();
$items = $it->get_result();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Order #<?php echo (int)$order_id; ?> - <?php echo htmlspecialchars($_SESSION['restaurant_name']); ?></title>
<style>
body{font-family:Poppins,Arial,sans-serif;background:#f4f6f8;margin:0}
.container{max-width:900px;margin:20px auto;padding:16px}
.card{background:#fff;padding:18px;border-radius:10px;box-shadow:0 6px 18px rgba(16,24,40,0.05)}
.table{width:100%;border-collapse:collapse;margin-top:12px}
.table th,.table td{padding:10px;border-bottom:1px solid #eee;text-align:left}
.btn{padding:8px 12px;background:#007bff;color:#fff;border-radius:6px;text-decoration:none}
</style>
</head>
<body>
<div class="container">
  <a href="orders.php" class="btn" style="background:#6c757d">← Back to Orders</a>
  <br><br>

  <div class="card">
    <h3>Order #<?php echo (int)$order_id; ?></h3>
    <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?></p>
    <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
    <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
    <p><strong>Date:</strong> <?php echo htmlspecialchars($order['created_at']); ?></p>

    <h4>Your items in this order</h4>
    <table class="table">
      <thead><tr><th>Food</th><th>Qty</th><th>Unit</th><th>Subtotal</th></tr></thead>
      <tbody>
        <?php $grand=0; while($r = $items->fetch_assoc()):
          $sub = (float)$r['price'] * (int)$r['quantity'];
          $grand += $sub;
        ?>
          <tr>
            <td><?php echo htmlspecialchars($r['food_name'] ?? 'Unknown'); ?></td>
            <td><?php echo (int)$r['quantity']; ?></td>
            <td>৳<?php echo number_format((float)$r['price'],2); ?></td>
            <td>৳<?php echo number_format($sub,2); ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <h4>Your subtotal: ৳<?php echo number_format($grand,2); ?></h4>

</div>
</body>
</html>
