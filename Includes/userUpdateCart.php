<?php
include 'connection.php';
include 'auth.php';
session_start();
check_login_redirect();

header('Content-Type: application/json');

$cart_item_id = intval($_POST['cart_item_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$cart_item_id || !in_array($action, ['increase', 'decrease', 'remove', 'set_quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Verify the cart item belongs to this user
$verify = $conn->prepare("SELECT ci.cart_item_id, ci.quantity, ci.product_id, p.quantity as stock, p.price
                          FROM cart_item ci
                          JOIN cart c ON ci.cart_id = c.cart_id
                          JOIN products p ON ci.product_id = p.product_id
                          WHERE ci.cart_item_id = ? AND c.user_id = ?");
$verify->bind_param("ii", $cart_item_id, $user_id);
$verify->execute();
$result = $verify->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Cart item not found']);
    exit;
}

$item = $result->fetch_assoc();
$verify->close();

if ($action === 'remove') {
    $stmt = $conn->prepare("DELETE FROM cart_item WHERE cart_item_id = ?");
    $stmt->bind_param("i", $cart_item_id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true, 'action' => 'removed']);
    exit;
}

$new_qty = $item['quantity'];
if ($action === 'increase') {
    $new_qty = min($item['quantity'] + 1, $item['stock']);
} elseif ($action === 'decrease') {
    $new_qty = max($item['quantity'] - 1, 1);
} elseif ($action === 'set_quantity') {
    $requested_qty = intval($_POST['quantity'] ?? 0);
    if ($requested_qty < 1) {
        echo json_encode(['success' => false, 'message' => 'Quantity must be at least 1']);
        exit;
    }
    $new_qty = min($requested_qty, $item['stock']);
}

$total_price = $new_qty * $item['price'];

$stmt = $conn->prepare("UPDATE cart_item SET quantity = ?, total_price = ? WHERE cart_item_id = ?");
$stmt->bind_param("idi", $new_qty, $total_price, $cart_item_id);
$stmt->execute();
$stmt->close();

echo json_encode([
    'success' => true,
    'new_quantity' => $new_qty,
    'item_total' => number_format($new_qty * $item['price'], 2)
]);
?>