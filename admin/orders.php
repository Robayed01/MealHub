<?php
// admin/orders.php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../admin_login.php");
    exit();
}
include "../includes/db.php";
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Manage Orders - MealHub Admin</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body{font-family:Poppins,Arial,sans-serif;margin:0;background:#f4f6f8}
.header{background:#007bff;color:#fff;padding:14px 18px;display:flex;justify-content:space-between;align-items:center}
.container{max-width:1100px;margin:20px auto;padding:16px}
.card{background:#fff;border-radius:10px;padding:16px;margin-bottom:14px;
      box-shadow:0 6px 20px rgba(16,24,40,0.05)}
.table{width:100%;border-collapse:collapse;margin-top:10px}
.table th,.table td{padding:10px;border-bottom:1px solid #eee;text-align:left;vertical-align:top}
.btn{padding:8px 12px;background:#007bff;color:#fff;border-radius:6px;text-decoration:none}
.small{font-size:13px;color:#666}
.back-btn { padding: 10px 18px; background:#6c757d; color:white; border-radius:8px; text-decoration:none}
</style>
</head>
<body>

<div class="header">
  <div><strong>MealHub Admin</strong></div>
  <div>
    <a href="dashboard.php" style="color:#fff;text-decoration:none">Dashboard</a> |
    <a href="../logout.php" style="color:#fff;text-decoration:none">Logout</a>
  </div>
</div>

<div class="container">
<a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
<br><br>

  <div class="card">
    <h2>All Orders</h2>
    <p class="small">Listing of orders. Click <strong>View</strong> to see items inside an order.</p>

    <table class="table">
      <thead>
        <tr>
          <th>#</th>
          <th>Customer</th>
          <th>Restaurants</th>
          <th>Address</th>
          <th>Phone</th>
          <th>Payment</th>
          <th>Total</th>
          <th>Date</th>
          <th>Details</th>
        </tr>
      </thead>
      <tbody>
      <?php
      // Use orders.total_amount and orders.created_at per your schema.
      // Compose restaurants involved using order_items -> restaurants.
      $sql = "
        SELECT 
          o.order_id,
          o.user_id,
          o.total_amount,
          o.address,
          o.phone,
          o.payment_method,
          o.created_at,
          u.name AS customer_name,
          (
            SELECT GROUP_CONCAT(DISTINCT r.name SEPARATOR ', ')
            FROM order_items oi
            JOIN restaurants r ON oi.restaurant_id = r.restaurant_id
            WHERE oi.order_id = o.order_id
          ) AS restaurants
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        ORDER BY o.order_id DESC
      ";

      $orders = $conn->query($sql);
      if ($orders === false) {
          echo "<tr><td colspan='9' style='color:red;'>Database error: " . htmlspecialchars($conn->error) . "</td></tr>";
      } else {
          $i = 1;
          while ($o = $orders->fetch_assoc()):
      ?>
        <tr>
          <td><?php echo $i++; ?></td>
          <td><?php echo htmlspecialchars($o['customer_name'] ?? 'Guest'); ?></td>
          <td style="max-width:220px;white-space:normal;"><?php echo htmlspecialchars($o['restaurants'] ?? '—'); ?></td>
          <td style="max-width:220px;white-space:normal;"><?php echo htmlspecialchars($o['address']); ?></td>
          <td><?php echo htmlspecialchars($o['phone']); ?></td>
          <td><?php echo htmlspecialchars($o['payment_method']); ?></td>
          <td><strong><?php echo number_format((float)$o['total_amount'], 2); ?></strong></td>
          <td><?php echo htmlspecialchars($o['created_at']); ?></td>
          <td>
            <a class="btn" href="order_view.php?id=<?php echo (int)$o['order_id']; ?>">View</a>
          </td>
        </tr>
      <?php
          endwhile;
      }
      ?>
      </tbody>
    </table>

  </div>

</div>

</body>
</html>
