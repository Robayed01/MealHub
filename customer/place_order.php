<?php
session_start();
include "../includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$address = trim($_POST['address']);
$phone = trim($_POST['phone']);
$payment_method = $_POST['payment_method'];

// get all cart items
$sql = "SELECT c.cart_id, c.food_id, c.quantity, f.price, r.restaurant_id
        FROM cart c
        JOIN food_items f ON c.food_id = f.food_id
        JOIN restaurants r ON c.restaurant_id = r.restaurant_id
        WHERE c.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$items = $stmt->get_result();

if ($items->num_rows === 0) {
    echo "<script>alert('Your cart is empty!'); window.location='restaurants.php';</script>";
    exit();
}

$total_amount = 0;
$cart_data = [];

while ($row = $items->fetch_assoc()) {
    $cart_data[] = $row;
    $total_amount += $row['price'] * $row['quantity'];
}

// insert order
$order = $conn->prepare("INSERT INTO orders (user_id, total_amount, address, phone, payment_method) VALUES (?,?,?,?,?)");
$order->bind_param("idsss", $user_id, $total_amount, $address, $phone, $payment_method);
$order->execute();
$order_id = $order->insert_id;

// insert order items
$item_stmt = $conn->prepare("INSERT INTO order_items (order_id, restaurant_id, food_id, quantity, price)
                             VALUES (?,?,?,?,?)");

foreach ($cart_data as $it) {
    $item_stmt->bind_param("iiiid", $order_id, $it['restaurant_id'], $it['food_id'], $it['quantity'], $it['price']);
    $item_stmt->execute();
}

// clear cart
$del = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
$del->bind_param("i", $user_id);
$del->execute();

// go to confirmation
header("Location: order_confirmation.php?order_id=$order_id");
exit();
