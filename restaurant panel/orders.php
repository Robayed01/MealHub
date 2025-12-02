<?php
session_start();
if (!isset($_SESSION['owner_id'])) {
    header("Location: ../restaurant_owner_login.php");
    exit();
}
include "../includes/db.php";
$rid = intval($_SESSION['restaurant_id']);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Orders - <?php echo htmlspecialchars($_SESSION['restaurant_name']); ?></title>
<style>
body{font-family:Poppins,Arial,sans-serif;background:#f4f6f8;margin:0}
.container{max-width:1000px;margin:20px auto;padding:16px}
.card{background:#fff;padding:18px;border-radius:10px;box-shadow:0 6px 18px rgba(16,24,40,0.05);margin-bottom:14px}
.table{width:100%;border-collapse:collapse;margin-top:12px}
.table th,.table td{padding:10px;border-bottom:1px solid #eee;text-align:left;vertical-align:top}
.btn{padding:8px 12px;background:#007bff;color:#fff;border-radius:6px;text-decoration:none}
.small{font-size:13px;color:#666}
</style>
</head>
<body>
<div class="container">
  <a href="dashboard.php" class="btn" style="background:#6c757d">← Back to Dashboard</a>
  <br><br>

  <div class="card">
    <h3>Orders for <?php echo htmlspecialchars($_SESSION['restaurant_name']); ?></h3>

    <table class="table">
      <thead><tr><th>#</th><th>Order ID</th><th>Customer</th><th>Phone</th><th>Subtotal</th><th>Date</th><th>Action</th></tr></thead>
      <tbody>
        <?php
        $sql = "
          SELECT DISTINCT o.order_id, o.user_id, o.phone, o.address, o.created_at, u.name AS customer_name,
                 (SELECT COALESCE(SUM(oi.price * oi.quantity),0) FROM order_items oi WHERE oi.order_id = o.order_id AND oi.restaurant_id = ?) AS subtotal_for_rest
          FROM orders o
          JOIN order_items oi ON o.order_id = oi.order_id
          LEFT JOIN users u ON o.user_id = u.user_id
          WHERE oi.restaurant_id = ?
          GROUP BY o.order_id
          ORDER BY o.order_id DESC
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $rid, $rid);
        $stmt->execute();
        $res = $stmt->get_result();
        $i=1;
        while ($row = $res->fetch_assoc()):
        ?>
          <tr>
            <td><?php echo $i++; ?></td>
            <td><?php echo (int)$row['order_id']; ?></td>
            <td><?php echo htmlspecialchars($row['customer_name'] ?? 'Guest'); ?></td>
            <td><?php echo htmlspecialchars($row['phone']); ?></td>
            <td>৳<?php echo number_format((float)$row['subtotal_for_rest'],2); ?></td>
            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
            <td><a class="btn" href="order_view.php?id=<?php echo (int)$row['order_id']; ?>">View</a></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
