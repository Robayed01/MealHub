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
    body {
      font-family: Inter, Arial;
      background: #C7CFB7;
      margin: 0
    }

    .container {
      max-width: 800px;
      margin: 24px auto;
      padding: 0 20px
    }

    .card {
      background: #F7F7E8;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1)
    }

    .row {
      display: flex;
      justify-content: space-between;
      border-bottom: 1px solid #eee;
      padding: 10px 0
    }

    .price {
      font-weight: 900;
      color: #557174
    }

    .header {
      background: #557174;
      color: #1F2937;
      font-weight: bold;
      padding: 15px;
      font-size: 20px
    }

    .back-btn {
      background: #557174;
      display: inline-block;
      padding: 10px 16px;
      color: white;
      border-radius: 6px;
      text-decoration: none;
      margin-bottom: 20px
    }
  </style>
</head>

<body>
  <div class="header">Order Details</div>
  <br>
  <div class="container">
    <a href=" order_history.php" class="back-btn">← Back to Order History</a>

    <div class="card">
      <h2>Order #
        <?php echo $order_id; ?>
      </h2>

      <?php while ($it = $items->fetch_assoc()): ?>
        <div class="row">
          <div>
            <strong><?php echo $it['food_name']; ?></strong><br>
            <small><?php echo $it['restaurant_name']; ?></small><br>
            Qty:
            <?php echo $it['quantity']; ?>
          </div>
          <div class="price">
            Tk
            <?php echo number_format($it['price'] * $it['quantity'], 2); ?>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>


</body>

</html>