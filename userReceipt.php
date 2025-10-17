<?php
include 'Includes/userHeader.php';
include 'Includes/connection.php';
check_login_redirect();

$user_id = $_SESSION['user_id'];
$order_id = intval($_GET['order_id'] ?? 0);

// Get order details
$order_stmt = $conn->prepare("
    SELECT o.order_id, o.total_amount, o.payment_method, o.order_status, o.order_date,
           u.first_name, u.last_name, u.email, u.contact_number
    FROM orders o
    JOIN user u ON o.user_id = u.user_id
    WHERE o.order_id = ? AND o.user_id = ?
");
$order_stmt->bind_param("ii", $order_id, $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows === 0) {
    echo "<script>alert('Order not found!'); window.location.href='index.php';</script>";
    exit();
}

$order = $order_result->fetch_assoc();
$order_stmt->close();

// Get order items
$items_stmt = $conn->prepare("
    SELECT oi.quantity, oi.price, p.product_name, p.image_path
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

$order_items = [];
while ($item = $items_result->fetch_assoc()) {
    $order_items[] = $item;
}
$items_stmt->close();
?>

<main>
    <div class="receipt-container">
        <div class="receipt-header">
            <h1>Order Confirmed!</h1>
            <p>Thank you for your order. Your order has been received and is being processed.</p>
        </div>

        <div class="receipt-details">
            <div class="receipt-section">
                <h2>Order Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Order Number:</span>
                        <span class="info-value">#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Order Date:</span>
                        <span class="info-value"><?php echo date('F d, Y h:i A', strtotime($order['order_date'])); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Payment Method:</span>
                        <span class="info-value"><?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Order Status:</span>
                        <span class="info-value status-<?php echo $order['order_status']; ?>">
                            <?php echo ucfirst($order['order_status']); ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="receipt-section">
                <h2>Customer Details</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Name:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['email']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Contact:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['contact_number']); ?></span>
                    </div>
                </div>
            </div>

            <div class="receipt-section">
                <h2>Pickup Information</h2>
                <div class="pickup-info">
                    <p><strong>Pickup Location:</strong></p>
                    <p>Carkiez Store<br>
                    Purok 3, Poblacion, Kapatagan Highway<br>
                    Kapatagan, Lanao Del Norte</p>
                    <p class="pickup-reminder">⚠️ Please pick up your order within 3 days after confirmation.</p>
                </div>
            </div>

            <div class="receipt-section">
                <h2>Order Items</h2>
                <div class="receipt-items">
                    <?php foreach ($order_items as $item): ?>
                    <div class="receipt-item">
                        <div class="receipt-item-image">
                            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                        </div>
                        <div class="receipt-item-details">
                            <p class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></p>
                            <p class="item-quantity">Quantity: <?php echo intval($item['quantity']); ?></p>
                            <p class="item-price">₱<?php echo number_format($item['price'], 2); ?> each</p>
                        </div>
                        <div class="receipt-item-total">
                            <p>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="receipt-total">
                    <div class="total-row">
                        <span>Total Amount:</span>
                        <span class="total-amount">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="receipt-actions">
            <a href="index.php" class="btn-home">Continue Shopping</a>
        </div>
    </div>
</main>