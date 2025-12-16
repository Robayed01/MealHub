<?php
session_start();
include "../includes/db.php";

if (!isset($_GET['order_id'])) {
  header("Location: order_history.php");
  exit();
}

$order_id = intval($_GET['order_id']);

$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
?>
<!doctype html>
<html>

<head>
  <title>Order Successful</title>
  <style>
    body {
      font-family: Inter, Arial;
      background: #C7CFB7;
      margin: 0
    }

    .center {
      max-width: 600px;
      margin: 40px auto;
      background: #F7F7E8;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      text-align: center
    }

    .btn {
      padding: 10px 18px;
      background: #557174;
      color: white;
      border-radius: 8px;
      text-decoration: none
    }
  </style>
</head>

<body>
  <div class="center">
    <h2>🎉 Order Placed Successfully!</h2>
    <p>Your order ID is <strong>#<?php echo $order_id; ?></strong></p>
    <p>Total Paid: <strong>Tk<?php echo number_format($order['total_amount'], 2); ?></strong></p>
    <p>Payment Method: <strong><?php echo $order['payment_method']; ?></strong></p>


    <div style="text-align:center; margin-top: 35px;">
      <a href="restaurants.php" style="background:#557174; padding:12px 20px; color:white; 
              border-radius:8px; text-decoration:none; font-size:16px;">
        ← Back to Restaurants
      </a>
    </div>

    <br>

    <br><a class="btn" href="order_history.php">View Order History</a>
  </div>
</body>

</html>