<?php
include 'connection.php';
include 'auth.php';
session_start();
check_login_redirect();

$user_id = $_SESSION['user_id'];
$payment_method = $_POST['payment_method'] ?? 'pay_on_pickup';

// Get cart
$cart_stmt = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();
$cart = $cart_result->fetch_assoc();
$cart_stmt->close();

if (!$cart) {
    echo "<script>alert('Cart not found!'); window.location.href='../userCart.php';</script>";
    exit();
}

$cart_id = $cart['cart_id'];

// Get cart items
$items_stmt = $conn->prepare("
    SELECT ci.product_id, ci.quantity, ci.total_price, p.quantity as stock
    FROM cart_item ci
    JOIN products p ON ci.product_id = p.product_id
    WHERE ci.cart_id = ?
");
$items_stmt->bind_param("i", $cart_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

if ($items_result->num_rows === 0) {
    echo "<script>alert('Your cart is empty!'); window.location.href='../userCart.php';</script>";
    exit();
}

$cart_items = [];
$total_amount = 0;

while ($item = $items_result->fetch_assoc()) {
    // Check stock availability
    if ($item['quantity'] > $item['stock']) {
        echo "<script>alert('Insufficient stock for some items!'); window.location.href='../userCart.php';</script>";
        exit();
    }
    $cart_items[] = $item;
    $total_amount += $item['total_price'];
}
$items_stmt->close();

// Start transaction
$conn->begin_transaction();

try {
    // Create order
    $order_stmt = $conn->prepare("
        INSERT INTO orders (user_id, total_amount, payment_method, order_status) 
        VALUES (?, ?, ?, 'pending')
    ");
    $order_stmt->bind_param("ids", $user_id, $total_amount, $payment_method);
    $order_stmt->execute();
    $order_id = $order_stmt->insert_id;
    $order_stmt->close();

    // Insert order items and update product stock
    foreach ($cart_items as $item) {
        // Insert order item
        $order_item_stmt = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price) 
            VALUES (?, ?, ?, ?)
        ");
        $price = $item['total_price'] / $item['quantity'];
        $order_item_stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $price);
        $order_item_stmt->execute();
        $order_item_stmt->close();

        // Update product stock
        $update_stock_stmt = $conn->prepare("
            UPDATE products 
            SET quantity = quantity - ? 
            WHERE product_id = ?
        ");
        $update_stock_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
        $update_stock_stmt->execute();
        $update_stock_stmt->close();
    }

    // Clear cart items
    $clear_items_stmt = $conn->prepare("DELETE FROM cart_item WHERE cart_id = ?");
    $clear_items_stmt->bind_param("i", $cart_id);
    $clear_items_stmt->execute();
    $clear_items_stmt->close();

    // Commit transaction
    $conn->commit();

    // Redirect to receipt page
    header("Location: ../userReceipt.php?order_id=$order_id");
    exit();

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo "<script>alert('Error processing order: " . $e->getMessage() . "'); window.location.href='../userCheckout.php';</script>";
    exit();
}

$conn->close();
?>