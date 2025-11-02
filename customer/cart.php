<?php
session_start();
include "../includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle optional update request (from update form)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'update' && isset($_POST['cart_id'], $_POST['quantity'])) {
        $cart_id = intval($_POST['cart_id']);
        $qty = intval($_POST['quantity']);
        if ($qty < 1) $qty = 1;
        $upd = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
        $upd->bind_param("iii", $qty, $cart_id, $user_id);
        $upd->execute();
        $upd->close();
    } elseif ($action === 'remove' && isset($_POST['cart_id'])) {
        $cart_id = intval($_POST['cart_id']);
        $del = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
        $del->bind_param("ii", $cart_id, $user_id);
        $del->execute();
        $del->close();
    }
    // after action reload so GET view shows current state
    header("Location: cart.php");
    exit();
}

// fetch all cart items for this user (join food_items and restaurants)
$sql = "SELECT c.cart_id, c.food_id, c.quantity, f.name AS food_name, f.price, f.image,
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

// compute totals grouped by restaurant (and overall)
$grouped = [];
$grandTotal = 0.0;
while ($row = $items->fetch_assoc()) {
    $rid = $row['restaurant_id'];
    if (!isset($grouped[$rid])) {
        $grouped[$rid] = [
            'restaurant_name' => $row['restaurant_name'],
            'items' => []
        ];
    }
    $sub = $row['price'] * $row['quantity'];
    $grandTotal += $sub;
    $grouped[$rid]['items'][] = $row;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Cart - MealHub</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body{font-family:Inter,Arial,sans-serif;margin:0;background:#f6f7fb}
.header{background:#007bff;color:#fff;padding:12px 20px;display:flex;justify-content:space-between;align-items:center}
.container{max-width:1100px;margin:18px auto;padding:0 16px}
.card{background:#fff;border-radius:10px;box-shadow:0 8px 30px rgba(16,24,40,0.06);padding:16px;margin-bottom:14px}
.table{width:100%;border-collapse:collapse}
.table th,.table td{padding:10px;border-bottom:1px solid #eee;text-align:left}
.table img{width:70px;height:70px;object-fit:cover;border-radius:8px}
.qty{width:64px;padding:6px;border-radius:8px;border:1px solid #dfe6ef}
.btn{padding:8px 12px;border-radius:8px;border:none;background:#007bff;color:#fff;cursor:pointer}
.btn.secondary{background:#6c757d}
.smalllink{font-size:13px;color:#007bff;text-decoration:none}
.totalbox{text-align:right;font-weight:700;font-size:18px;padding:10px}
.empty{padding:24px;text-align:center;color:#666}
.form-inline{display:flex;gap:8px;align-items:center}
</style>
</head>
<body>
<div class="header">
  <div><strong>MealHub</strong> - My Cart</div>
  <div><a class="smalllink" href="restaurants.php" style="color:#fff">Continue shopping</a> | <a class="smalllink" href="../logout.php" style="color:#fff">Logout</a></div>
</div>

<div class="container">
  <?php if (empty($grouped)): ?>
    <div class="card empty">Your cart is empty. <a href="restaurants.php">Browse restaurants</a></div>
  <?php else: ?>
    <?php foreach ($grouped as $rid => $section): ?>
      <div class="card">
        <h3 style="margin:0 0 10px 0;"><?php echo htmlspecialchars($section['restaurant_name']); ?></h3>
        <table class="table">
          <thead>
            <tr><th>Item</th><th>Price</th><th>Qty</th><th>Subtotal</th><th>Action</th></tr>
          </thead>
          <tbody>
            <?php foreach ($section['items'] as $it): 
              $subtotal = $it['price'] * $it['quantity'];
            ?>
              <tr>
                <td style="display:flex;gap:12px;align-items:center">
                  <img src="../assets/images/<?php echo htmlspecialchars($it['image']); ?>" alt="">
                  <div>
                    <div style="font-weight:600"><?php echo htmlspecialchars($it['food_name']); ?></div>
                    <div style="font-size:13px;color:#666">Food ID: <?php echo (int)$it['food_id']; ?></div>
                  </div>
                </td>
                <td>$<?php echo number_format($it['price'],2); ?></td>
                <td>
                  <form class="form-inline" method="post" style="margin:0">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="cart_id" value="<?php echo (int)$it['cart_id']; ?>">
                    <input class="qty" type="number" name="quantity" value="<?php echo (int)$it['quantity']; ?>" min="1">
                    <button class="btn" type="submit">Update</button>
                  </form>
                </td>
                <td>$<?php echo number_format($subtotal,2); ?></td>
                <td>
                  <form method="post" style="margin:0">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="cart_id" value="<?php echo (int)$it['cart_id']; ?>">
                    <button class="btn secondary" type="submit">Remove</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endforeach; ?>

    <div class="card" style="text-align:right">
      <div class="totalbox">Grand Total: $<?php echo number_format($grandTotal,2); ?></div>
      <!-- Checkout button placeholder -->
      <div style="margin-top:8px">
        <a href="checkout.php" style="display:inline-block;padding:10px 16px;background:#28a745;color:#fff;border-radius:8px;text-decoration:none">Proceed to Checkout</a>
      </div>
    </div>
  <?php endif; ?>
</div>

</body>
</html>
