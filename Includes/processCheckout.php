<?php
include 'connection.php';
include 'auth.php';
session_start();
check_login_redirect();

$user_id = $_SESSION['user_id'];
$payment_method = $_POST['payment_method'] ?? 'pay_on_pickup';
$reference_number = null;
$proof_image_path = null;

// Handle GCash payment proof upload
if ($payment_method === 'online_payment') {
    $reference_number = trim($_POST['reference_number'] ?? '');

    if (empty($reference_number)) {
        echo "<script>alert('Please enter GCash reference number!'); window.location.href='../userCheckout.php';</script>";
        exit();
    }

    if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/payment_proofs/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_tmp = $_FILES['proof_image']['tmp_name'];
        $file_name = $_FILES['proof_image']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($file_ext, $allowed_extensions)) {
            echo "<script>alert('Invalid file type. Only JPG, PNG, and GIF are allowed!'); window.location.href='../userCheckout.php';</script>";
            exit();
        }

        $new_filename = 'gcash_' . $user_id . '_' . time() . '.' . $file_ext;
        $upload_path = $upload_dir . $new_filename;

        if (move_uploaded_file($file_tmp, $upload_path)) {
            $proof_image_path = 'uploads/payment_proofs/' . $new_filename;
        } else {
            echo "<script>alert('Failed to upload payment proof!'); window.location.href='../userCheckout.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('Please upload payment proof screenshot!'); window.location.href='../userCheckout.php';</script>";
        exit();
    }
}

// Get user's cart
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
    // Insert order record
    $order_stmt = $conn->prepare("
        INSERT INTO orders (user_id, total_amount, payment_method, order_status)
        VALUES (?, ?, ?, 'pending')
    ");
    $order_stmt->bind_param("ids", $user_id, $total_amount, $payment_method);
    $order_stmt->execute();
    $order_id = $order_stmt->insert_id;
    $order_stmt->close();

    // Insert payment record if applicable
    if ($payment_method === 'online_payment') {
        $payment_stmt = $conn->prepare("
            INSERT INTO payments (order_id, user_id, payment_method, amount_paid, reference_number, proof_image, status)
            VALUES (?, ?, 'gcash', ?, ?, ?, 'pending')
        ");
        $payment_stmt->bind_param("iidss", $order_id, $user_id, $total_amount, $reference_number, $proof_image_path);
        $payment_stmt->execute();
        $payment_stmt->close();
    } else {
        $payment_stmt = $conn->prepare("
            INSERT INTO payments (order_id, user_id, payment_method, amount_paid, status)
            VALUES (?, ?, 'pay_on_pickup', ?, 'pending')
        ");
        $payment_stmt->bind_param("iid", $order_id, $user_id, $total_amount);
        $payment_stmt->execute();
        $payment_stmt->close();
    }


    // Insert each order item and update stock
    foreach ($cart_items as $item) {
        $price = $item['total_price'] / $item['quantity'];
        $order_item_stmt = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price)
            VALUES (?, ?, ?, ?)
        ");
        $order_item_stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $price);
        $order_item_stmt->execute();
        $order_item_stmt->close();

        $update_stock_stmt = $conn->prepare("
            UPDATE products SET quantity = quantity - ? WHERE product_id = ?
        ");
        $update_stock_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
        $update_stock_stmt->execute();
        $update_stock_stmt->close();
    }

    // Clear cart
    $clear_cart_stmt = $conn->prepare("DELETE FROM cart_item WHERE cart_id = ?");
    $clear_cart_stmt->bind_param("i", $cart_id);
    $clear_cart_stmt->execute();
    $clear_cart_stmt->close();

    // Commit everything
    $conn->commit();

    header("Location: ../userReceipt.php?order_id=$order_id");
    exit();

} catch (Exception $e) {
    $conn->rollback();

    if ($proof_image_path && file_exists('../' . $proof_image_path)) {
        unlink('../' . $proof_image_path);
    }

    echo "<script>alert('Error processing order: " . addslashes($e->getMessage()) . "'); window.location.href='../userCheckout.php';</script>";
    exit();
}

$conn->close();
?>
