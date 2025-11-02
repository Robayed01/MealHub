<?php
session_start();
include "../includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$food_id = intval($_POST['food_id'] ?? 0);
if ($food_id <= 0) {
    header("Location: restaurants.php");
    exit();
}

// Fetch restaurant_id for the food (ensure consistency)
$fr = $conn->prepare("SELECT restaurant_id FROM food_items WHERE food_id = ?");
$fr->bind_param("i", $food_id);
$fr->execute();
$res = $fr->get_result();
if (!$res || $res->num_rows === 0) {
    // invalid food
    header("Location: restaurants.php");
    exit();
}
$foodRow = $res->fetch_assoc();
$restaurant_id = (int)$foodRow['restaurant_id'];
$fr->close();

// Check if this user already has this food in cart
$chk = $conn->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND food_id = ?");
$chk->bind_param("ii", $user_id, $food_id);
$chk->execute();
$cr = $chk->get_result();

if ($cr && $cr->num_rows > 0) {
    // increment quantity
    $upd = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND food_id = ?");
    $upd->bind_param("ii", $user_id, $food_id);
    $upd->execute();
    $upd->close();
} else {
    // insert new row with restaurant_id
    $ins = $conn->prepare("INSERT INTO cart (user_id, restaurant_id, food_id, quantity) VALUES (?, ?, ?, 1)");
    $ins->bind_param("iii", $user_id, $restaurant_id, $food_id);
    $ins->execute();
    $ins->close();
}
$chk->close();

// Redirect back to the referring page (menu.php?rid=...), if available
$ref = $_SERVER['HTTP_REFERER'] ?? 'restaurants.php';
header("Location: $ref");
exit();
