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
    body {
      font-family: Inter, Arial;
      background: #C7CFB7;
      margin: 0
    }

    .container {
      max-width: 800px;
      margin: 24px auto;
      padding: 0 16px
    }

    .card {
      background: #F7F7E8;
      padding: 16px;
      border-radius: 10px;
      margin-bottom: 12px;
      box-shadow: 0 3px 14px rgba(0, 0, 0, 0.07)
    }

    a.btn {
      padding: 8px 14px;
      background: #557174;
      color: white;
      border-radius: 6px;
      text-decoration: none
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
  <style>
    .header {
      background: #557174;
      color: #1F2937;
      font-weight: bold;
      padding: 15px;
      font-size: 20px;
      font-family: Poppins
    }
  </style>
</head>

<body>
  <div class="header">Order History</div>


  <div class="container">
    <a href="restaurants.php" class="back-btn">←Back to Restaurants</a>
    <h2>Order History</h2>
    <?php while ($o = $orders->fetch_assoc()): ?>
      <div class="card">
        <h3>Order #<?php echo $o['order_id']; ?></h3>
        <p>Total: Tk<?php echo number_format($o['total_amount'], 2); ?></p>
        <p>Date: <?php echo $o['created_at']; ?></p>
        <a class="btn" href="order_view.php?order_id=<?php echo $o['order_id']; ?>">View Details</a>
      </div>


    <?php endwhile; ?>
  </div>
  </div>

  <br>

</body>

</html>