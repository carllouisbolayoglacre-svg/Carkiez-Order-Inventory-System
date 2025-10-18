<?php 
include 'Includes/connection.php';
include 'Includes/admin_auth.php';

session_start();
check_login_redirect();

$user_id = intval($_GET['id'] ?? 0);

// Check if user has any orders
$check_orders = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
$check_orders->bind_param("i", $user_id);
$check_orders->execute();
$orders_result = $check_orders->get_result();
$orders_data = $orders_result->fetch_assoc();
$check_orders->close();

if ($orders_data['count'] > 0) {
    echo "<script>alert('Cannot delete user! User has {$orders_data['count']} order(s) in the system.'); window.location.href='adminUsers.php';</script>";
    exit();
}

// Check if user has items in cart
$check_cart = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
$check_cart->bind_param("i", $user_id);
$check_cart->execute();
$cart_result = $check_cart->get_result();

if ($cart_result->num_rows > 0) {
    $cart = $cart_result->fetch_assoc();
    $cart_id = $cart['cart_id'];
    
    // Delete cart items first
    $delete_cart_items = $conn->prepare("DELETE FROM cart_item WHERE cart_id = ?");
    $delete_cart_items->bind_param("i", $cart_id);
    $delete_cart_items->execute();
    $delete_cart_items->close();
    
    // Delete cart
    $delete_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $delete_cart->bind_param("i", $user_id);
    $delete_cart->execute();
    $delete_cart->close();
}
$check_cart->close();

// Delete user
$stmt = $conn->prepare("DELETE FROM user WHERE user_id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    echo "<script>alert('User deleted successfully!'); window.location.href='adminUsers.php';</script>";
} else {
    echo "<script>alert('Error deleting user!'); window.location.href='adminUsers.php';</script>";
}

$stmt->close();
$conn->close();
?>