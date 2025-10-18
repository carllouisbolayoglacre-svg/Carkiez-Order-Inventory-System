<?php 
include 'Includes/connection.php';
include 'Includes/admin_auth.php';

session_start();
check_login_redirect();

$product_id = intval($_GET['id'] ?? 0);

// Get product info to delete image file
$stmt = $conn->prepare("SELECT image_path FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Product not found!'); window.location.href='adminProducts.php';</script>";
    exit();
}

$product = $result->fetch_assoc();
$stmt->close();

// Check if product is in any cart or order
$check_cart = $conn->prepare("SELECT COUNT(*) as count FROM cart_item WHERE product_id = ?");
$check_cart->bind_param("i", $product_id);
$check_cart->execute();
$cart_result = $check_cart->get_result();
$cart_data = $cart_result->fetch_assoc();
$check_cart->close();

$check_order = $conn->prepare("SELECT COUNT(*) as count FROM order_items WHERE product_id = ?");
$check_order->bind_param("i", $product_id);
$check_order->execute();
$order_result = $check_order->get_result();
$order_data = $order_result->fetch_assoc();
$check_order->close();

if ($cart_data['count'] > 0 || $order_data['count'] > 0) {
    echo "<script>alert('Cannot delete product! It is referenced in cart items or orders.'); window.location.href='adminProducts.php';</script>";
    exit();
}

// Delete product
$delete_stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
$delete_stmt->bind_param("i", $product_id);

if ($delete_stmt->execute()) {
    // Delete image file
    if (file_exists($product['image_path'])) {
        unlink($product['image_path']);
    }
    echo "<script>alert('Product deleted successfully!'); window.location.href='adminProducts.php';</script>";
} else {
    echo "<script>alert('Error deleting product!'); window.location.href='adminProducts.php';</script>";
}

$delete_stmt->close();
$conn->close();
?>