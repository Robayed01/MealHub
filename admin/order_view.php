<?php
// admin/order_view.php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../admin_login.php");
    exit();
}
include "../includes/db.php";

if (!isset($_GET['id'])) {
    die("Order ID missing.");
}

$order_id = intval($_GET['id']);

// fetch order (uses total_amount and created_at)
$sql = "
    SELECT o.*, u.name AS customer_name
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.user_id
    WHERE o.order_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Order not found.");
}

// fetch items belonging to this order; include food name and restaurant name
$items_stmt = $conn->prepare("
    SELECT oi.item_id, oi.food_id, oi.quantity, oi.price AS unit_price,
           f.name AS food_name,
           r.name AS restaurant_name
    FROM order_items oi
    LEFT JOIN food_items f ON oi.food_id = f.food_id
    LEFT JOIN restaurants r ON oi.restaurant_id = r.restaurant_id
    WHERE oi.order_id = ?
");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$item_list = $items_stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<title>Order Details #<?php echo $order_id; ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body{font-family:Poppins,Arial,sans-serif;background:#f4f6f8;margin:0}
.header{background:#007bff;color:#fff;padding:14px}
.container{max-width:900px;margin:20px auto;padding:16px}
.card{background:#fff;padding:20px;border-radius:10px;box-shadow:0 6px 20px rgba(16,24,40,0.05)}
.table{width:100%;border-collapse:collapse;margin-top:10px}
.table th,.table td{padding:10px;border-bottom:1px solid #eee;text-align:left}
.btn{padding:8px 12px;background:#007bff;color:#fff;text-decoration:none;border-radius:6px}
.small{font-size:13px;color:#666}
</style>
</head>
<body>

<div class="header">MealHub Admin</div>

<div class="container">
  <div class="card">
    <h2>Order #<?php echo (int)$order_id; ?></h2>

    <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?></p>
    <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
    <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
    <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
    <p><strong>Date:</strong> <?php echo htmlspecialchars($order['created_at']); ?></p>

    <h3>Items</h3>
    <table class="table">
      <thead>
        <tr>
          <th>Restaurant</th>
          <th>Food Item</th>
          <th>Qty</th>
          <th>Unit Price</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $grand = 0.0;
        while ($row = $item_list->fetch_assoc()):
            $subtotal = ((float)$row['unit_price']) * ((int)$row['quantity']);
            $grand += $subtotal;
        ?>
        <tr>
          <td><?php echo htmlspecialchars($row['restaurant_name'] ?? '-'); ?></td>
          <td><?php echo htmlspecialchars($row['food_name'] ?? 'Unknown item'); ?></td>
          <td><?php echo (int)$row['quantity']; ?></td>
          <td><?php echo number_format((float)$row['unit_price'], 2); ?></td>
          <td><?php echo number_format($subtotal, 2); ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <h3 style="margin-top:14px">Total (recorded): <?php echo number_format((float)$order['total_amount'], 2); ?></h3>
    <h4>Calculated from items: <?php echo number_format($grand, 2); ?></h4>

    <p class="small">Note: The recorded order total (above) is the value saved in the <code>orders.total_amount</code> column.</p>

    <a href="orders.php" class="btn">Back to Orders</a>
  </div>
</div>

</body>
</html>
