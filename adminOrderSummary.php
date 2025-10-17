<?php
include 'Includes/admin_auth.php';
include 'Includes/connection.php';

session_start();
check_login_redirect();
handle_logout();

$order_id = intval($_GET['id'] ?? 0);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['order_status'];
    $new_payment_status = $_POST['payment_status'];
    
    $update_stmt = $conn->prepare("UPDATE orders SET order_status = ?, payment_status = ? WHERE order_id = ?");
    $update_stmt->bind_param("ssi", $new_status, $new_payment_status, $order_id);
    
    if ($update_stmt->execute()) {
        echo "<script>alert('Order updated successfully!'); window.location.href='adminOrderSummary.php?id=$order_id';</script>";
    } else {
        echo "<script>alert('Error updating order.');</script>";
    }
    $update_stmt->close();
}

// Get order details
$order_stmt = $conn->prepare("
    SELECT o.order_id, o.order_date, o.total_amount, o.payment_method, 
           o.payment_status, o.order_status,
           u.user_id, u.username, u.first_name, u.middle_name, u.last_name, 
           u.email, u.contact_number, u.address
    FROM orders o
    JOIN user u ON o.user_id = u.user_id
    WHERE o.order_id = ?
");
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows === 0) {
    echo "<script>alert('Order not found!'); window.location.href='adminOrders.php';</script>";
    exit();
}

$order = $order_result->fetch_assoc();
$order_stmt->close();

// Get order items
$items_stmt = $conn->prepare("
    SELECT oi.order_item_id, oi.quantity, oi.price,
           p.product_id, p.product_name, p.image_path
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

<?php
include 'Includes/adminHeader.php';
include 'Includes/adminNav.php';
?>

<main>
    <div class="order-summary-container">
        <div class="summary-header">
            <h2>Order Details - #<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></h2>
            <a href="adminOrders.php" class="back-btn">← Back to Orders</a>
        </div>

        <div class="summary-grid">
            <div class="summary-card">
                <h3>Customer Information</h3>
                <div class="info-row">
                    <span class="label">Name:</span>
                    <span class="value"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['middle_name'] . ' ' . $order['last_name']); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Username:</span>
                    <span class="value">@<?php echo htmlspecialchars($order['username']); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Email:</span>
                    <span class="value"><?php echo htmlspecialchars($order['email']); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Contact:</span>
                    <span class="value"><?php echo htmlspecialchars($order['contact_number']); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Address:</span>
                    <span class="value"><?php echo htmlspecialchars($order['address']); ?></span>
                </div>
            </div>

            <div class="summary-card">
                <h3>Order Information</h3>
                <div class="info-row">
                    <span class="label">Order Date:</span>
                    <span class="value"><?php echo date('F d, Y h:i A', strtotime($order['order_date'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Payment Method:</span>
                    <span class="value"><?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Total Amount:</span>
                    <span class="value amount">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>

            <div class="summary-card">
                <h3>Update Order Status</h3>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="order_status">Order Status:</label>
                        <select name="order_status" id="order_status" required>
                            <option value="pending" <?php echo $order['order_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $order['order_status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="ready_for_pickup" <?php echo $order['order_status'] === 'ready_for_pickup' ? 'selected' : ''; ?>>Ready for Pickup</option>
                            <option value="completed" <?php echo $order['order_status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $order['order_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="payment_status">Payment Status:</label>
                        <select name="payment_status" id="payment_status" required>
                            <option value="pending" <?php echo $order['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="paid" <?php echo $order['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="refunded" <?php echo $order['payment_status'] === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="update-btn">Update Status</button>
                </form>
            </div>
        </div>

        <div class="summary-card items-card">
            <h3>Order Items (<?php echo count($order_items); ?>)</h3>
            <div class="items-list">
                <?php foreach ($order_items as $item): ?>
                <div class="item-row">
                    <div class="item-image">
                        <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="Product">
                    </div>
                    <div class="item-details">
                        <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                        <p>Product ID: #<?php echo $item['product_id']; ?></p>
                    </div>
                    <div class="item-quantity">
                        <p>Qty: <?php echo $item['quantity']; ?></p>
                    </div>
                    <div class="item-price">
                        <p>₱<?php echo number_format($item['price'], 2); ?></p>
                    </div>
                    <div class="item-total">
                        <p><strong>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="order-total">
                <span>Total Amount:</span>
                <span class="total-value">₱<?php echo number_format($order['total_amount'], 2); ?></span>
            </div>
        </div>
    </div>
</main>
</body>
</html>