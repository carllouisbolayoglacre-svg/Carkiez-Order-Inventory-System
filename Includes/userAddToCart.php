<?php
include 'connection.php';
include 'auth.php';
session_start();
check_login_redirect();

$user_id = $_SESSION['user_id'];
$product_id = intval($_POST['product_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);

// Validate product
$stmt = $conn->prepare("SELECT price, quantity FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product || $quantity < 1 || $quantity > $product['quantity']) {
    echo "<script>alert('Invalid product or quantity.'); window.history.back();</script>";
    exit();
}

// 1. Find or create cart for user
$stmt = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($cart_id);
if (!$stmt->fetch()) {
    // No cart, create one
    $stmt->close();
    $stmt = $conn->prepare("INSERT INTO cart (user_id) VALUES (?)");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cart_id = $stmt->insert_id;
}
$stmt->close();

// 2. Check if product already in cart
$stmt = $conn->prepare("SELECT cart_item_id, quantity FROM cart_item WHERE cart_id = ? AND product_id = ?");
$stmt->bind_param("ii", $cart_id, $product_id);
$stmt->execute();
$stmt->bind_result($cart_item_id, $existing_qty);
if ($stmt->fetch()) {
    // Update quantity
    $new_qty = $existing_qty + $quantity;
    if ($new_qty > $product['quantity']) $new_qty = $product['quantity'];
    $total_price = $product['price'] * $new_qty;
    $stmt->close();
    $stmt = $conn->prepare("UPDATE cart_item SET quantity = ?, total_price = ? WHERE cart_item_id = ?");
    $stmt->bind_param("idi", $new_qty, $total_price, $cart_item_id);
    $stmt->execute();
    $stmt->close();
} else {
    // Insert new cart item
    $stmt->close();
    $total_price = $product['price'] * $quantity;
    $stmt = $conn->prepare("INSERT INTO cart_item (cart_id, product_id, quantity, total_price) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $cart_id, $product_id, $quantity, $total_price);
    $stmt->execute();
    $stmt->close();
}

echo "<script>alert('Added to cart!'); window.location.href='../userProductPage.php?id=$product_id';</script>";
?>