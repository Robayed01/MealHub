<?php
session_start();
include "../includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// fetch cart items
$sql = "SELECT c.cart_id, c.quantity, f.food_id, f.name AS food_name, f.price, f.image,
        r.restaurant_id, r.name AS restaurant_name
        FROM cart c
        JOIN food_items f ON c.food_id = f.food_id
        JOIN restaurants r ON c.restaurant_id = r.restaurant_id
        WHERE c.user_id = ?
        ORDER BY r.name, f.name";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$items = $stmt->get_result();

$cart_items = [];
$total_amount = 0;

while ($row = $items->fetch_assoc()) {
    $cart_items[] = $row;
    $total_amount += $row['price'] * $row['quantity'];
}

if (empty($cart_items)) {
    echo "<script>alert('Your cart is empty!'); window.location='restaurants.php';</script>";
    exit();
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Checkout - MealHub</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body{font-family:Inter,Arial;margin:0;background:#f4f6f8}
.header{background:#007bff;color:#fff;padding:14px 20px;font-size:18px;display:flex;justify-content:space-between}
.container{max-width:900px;margin:24px auto;padding:0 16px}
.card{background:#fff;padding:20px;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.08);margin-bottom:20px}
input,textarea{width:100%;padding:12px;border-radius:8px;border:1px solid #ccc;margin-top:8px;font-size:15px}
label{font-weight:600}
.btn{padding:12px 16px;border:none;border-radius:8px;background:#007bff;color:#fff;font-size:16px;cursor:pointer}
.btn:hover{background:#005ec2}
.total{font-size:19px;font-weight:700;text-align:right;margin-top:12px}
.item{display:flex;gap:12px;margin-bottom:12px}
.item img{width:70px;height:70px;object-fit:cover;border-radius:8px}
.item-info{flex:1}
.price{font-weight:700;color:#007bff}
</style>
</head>
<body>

<div class="header">
  <div><strong>MealHub</strong> – Checkout</div>
  <div><a href="cart.php" style="color:white;text-decoration:none">Back to Cart</a></div>
</div>

<div class="container">

  <div class="card">
    <h2>Delivery Details</h2>
    <form method="POST" action="place_order.php">

      <label>Full Address</label>
      <textarea name="address" required placeholder="House No, Street, City"></textarea>

      <label>Phone Number</label>
      <input type="text" name="phone" required placeholder="Enter phone number">

      <label>Payment Method</label>
      <input type="text" value="Cash on Delivery" disabled>
      <input type="hidden" name="payment_method" value="COD">

      <br><br>
      <div class="card">
        <h3>Order Summary</h3>
        <?php foreach($cart_items as $it): ?>
          <div class="item">
            <img src="../assets/images/<?php echo $it['image']; ?>">
            <div class="item-info">
              <?php echo $it['food_name']; ?>
              <div class="price">$<?php echo number_format($it['price'],2); ?> × <?php echo $it['quantity']; ?></div>
            </div>
            <div class="price">$<?php echo number_format($it['price']*$it['quantity'],2); ?></div>
          </div>
        <?php endforeach; ?>

        <div class="total">Total: $<?php echo number_format($total_amount,2); ?></div>
      </div>

      <button class="btn" type="submit">Place Order</button>
    </form>
  </div>
</div>

</body>
</html>
