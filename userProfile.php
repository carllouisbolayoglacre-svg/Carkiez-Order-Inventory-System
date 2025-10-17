<?php
include 'Includes/userHeader.php';
include 'Includes/connection.php';
check_login_redirect();

$user_id = $_SESSION['user_id'];

$user_stmt = $conn->prepare("
    SELECT user_id, username, email, first_name, middle_name, last_name, 
           contact_number, address, birthdate
    FROM user 
    WHERE user_id = ?
");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

$orders_stmt = $conn->prepare("
    SELECT o.order_id, o.order_date, o.total_amount, o.payment_method, 
           o.payment_status, o.order_status,
           COUNT(oi.order_item_id) as item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.order_id
    ORDER BY o.order_date DESC
");
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();

$orders = [];
while ($order = $orders_result->fetch_assoc()) {
    $orders[] = $order;
}
$orders_stmt->close();

$active_tab = $_GET['tab'] ?? 'account';
?>

<main>
    <div class="profile-page">
        <aside class="profile-sidebar">
            <div class="profile-user-card">
                <div class="user-avatar">
                    <img src="Assets/user-avatar-placeholder.png" alt="Profile">
                </div>
                <div class="user-info">
                    <p class="user-greeting">Hello</p>
                    <h3 class="user-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                </div>
            </div>
            
            <nav class="profile-nav">
                <a href="?tab=account" class="nav-item <?php echo $active_tab === 'account' ? 'active' : ''; ?>">
                    My Account
                </a>
                <a href="?tab=orders" class="nav-item <?php echo $active_tab === 'orders' ? 'active' : ''; ?>">
                    My Orders
                </a>
            </nav>
        </aside>

        <div class="profile-content">
            <?php if ($active_tab === 'account'): ?>
                <div class="content-header">
                    <h2>Personal Information</h2>
                </div>

                <div class="profile-card">
                    <div class="profile-avatar-section">
                        <div class="large-avatar">
                            <img src="Assets/user-avatar-placeholder.png" alt="Profile">
                        </div>
                    </div>

                    <div class="profile-form">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" readonly value="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['middle_name'] . ' ' . $user['last_name']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Date Of Birth</label>
                            <input type="text" readonly value="<?php echo date('d/m/Y', strtotime($user['birthdate'])); ?>">
                        </div>

                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" readonly value="<?php echo htmlspecialchars($user['contact_number']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Email</label>
                            <input type="text" readonly value="<?php echo htmlspecialchars($user['email']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" readonly value="<?php echo htmlspecialchars($user['address']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" readonly value="<?php echo htmlspecialchars($user['username']); ?>">
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="content-header">
                    <h2>Order History</h2>
                </div>

                <?php if (count($orders) > 0): ?>
                    <div class="orders-grid">
                        <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-card-header">
                                <div class="order-number-date">
                                    <h3>Order #<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></h3>
                                    <p class="order-date"><?php echo date('F d, Y', strtotime($order['order_date'])); ?></p>
                                </div>
                                <span class="order-badge status-<?php echo $order['order_status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                                </span>
                            </div>
                            
                            <div class="order-card-body">
                                <div class="order-detail-row">
                                    <span class="detail-label">Items:</span>
                                    <span class="detail-value"><?php echo intval($order['item_count']); ?> item(s)</span>
                                </div>
                                <div class="order-detail-row">
                                    <span class="detail-label">Total Amount:</span>
                                    <span class="detail-value">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                                </div>
                                <div class="order-detail-row">
                                    <span class="detail-label">Payment Method:</span>
                                    <span class="detail-value"><?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></span>
                                </div>
                                <div class="order-detail-row">
                                    <span class="detail-label">Payment Status:</span>
                                    <span class="detail-value payment-<?php echo $order['payment_status']; ?>">
                                        <?php echo ucfirst($order['payment_status']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="order-card-footer">
                                <a href="userReceipt.php?order_id=<?php echo $order['order_id']; ?>" class="btn-view-receipt">
                                    View Receipt
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <h3>No orders yet</h3>
                        <p>You haven't placed any orders yet.</p>
                        <a href="userProducts.php" class="btn-shop-now">Start Shopping</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'Includes/userFooter.php'; ?>