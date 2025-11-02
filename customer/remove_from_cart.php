<?php
session_start();
include "../includes/db.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$cart_id = intval($_POST['cart_id'] ?? 0);
if ($cart_id > 0) {
    $del = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $del->bind_param("ii", $cart_id, $user_id);
    $del->execute();
}
header("Location: cart.php");
exit();
