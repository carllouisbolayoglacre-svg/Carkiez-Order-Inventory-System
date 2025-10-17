<?php 
include 'Includes/admin_auth.php';
include 'Includes/connection.php';

session_start();
check_login_redirect();
handle_logout();

// Get Total Orders with breakdown
$orders_stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN order_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_orders,
        SUM(CASE WHEN order_status = 'ready_for_pickup' THEN 1 ELSE 0 END) as ready_orders,
        SUM(CASE WHEN order_status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
        SUM(CASE WHEN order_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders
    FROM orders
");
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
$orders_data = $orders_result->fetch_assoc();
$orders_stmt->close();

// Get Total Sales / Revenue (only from completed orders)
$sales_stmt = $conn->prepare("
    SELECT COALESCE(SUM(total_amount), 0) as total_sales
    FROM orders
    WHERE order_status = 'completed' AND payment_status = 'paid'
");
$sales_stmt->execute();
$sales_result = $sales_stmt->get_result();
$sales_data = $sales_result->fetch_assoc();
$sales_stmt->close();

// Get Total Products in Inventory
$products_stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_products,
        COALESCE(SUM(quantity), 0) as total_stock
    FROM products
");
$products_stmt->execute();
$products_result = $products_stmt->get_result();
$products_data = $products_result->fetch_assoc();
$products_stmt->close();

// Get Total Registered Users
$users_stmt = $conn->prepare("SELECT COUNT(*) as total_users FROM user");
$users_stmt->execute();
$users_result = $users_stmt->get_result();
$users_data = $users_result->fetch_assoc();
$users_stmt->close();

// Get Recent Orders
$recent_orders_stmt = $conn->prepare("
    SELECT o.order_id, o.order_date, o.total_amount, o.order_status,
           u.first_name, u.last_name
    FROM orders o
    JOIN user u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC
    LIMIT 5
");
$recent_orders_stmt->execute();
$recent_orders_result = $recent_orders_stmt->get_result();
$recent_orders = [];
while ($row = $recent_orders_result->fetch_assoc()) {
    $recent_orders[] = $row;
}
$recent_orders_stmt->close();

// Get Low Stock Products
$low_stock_stmt = $conn->prepare("
    SELECT product_id, product_name, quantity, image_path
    FROM products
    WHERE quantity < 10
    ORDER BY quantity ASC
    LIMIT 5
");
$low_stock_stmt->execute();
$low_stock_result = $low_stock_stmt->get_result();
$low_stock = [];
while ($row = $low_stock_result->fetch_assoc()) {
    $low_stock[] = $row;
}
$low_stock_stmt->close();
?>

<?php
include 'Includes/adminHeader.php';
include 'Includes/adminNav.php';
?>

<main>
    <div class="dashboard-container">
        <h1>Admin Dashboard</h1>
        <p class="welcome-text">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</p>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-group">
                    <h3>Total Orders</h3>
                    <p class="stat-number"><?php echo number_format($orders_data['total_orders'] ?? 0); ?></p>
                </div>
                <div class="stat-breakdown">
                    <div class="breakdown-item">
                        <span class="breakdown-label">Pending:</span>
                        <span class="breakdown-value"><?php echo $orders_data['pending_orders'] ?? 0; ?></span>
                    </div>
                    <div class="breakdown-item">
                        <span class="breakdown-label">Confirmed:</span>
                        <span class="breakdown-value"><?php echo $orders_data['confirmed_orders'] ?? 0; ?></span>
                    </div>
                    <div class="breakdown-item">
                        <span class="breakdown-label">Ready:</span>
                        <span class="breakdown-value"><?php echo $orders_data['ready_orders'] ?? 0; ?></span>
                    </div>
                    <div class="breakdown-item">
                        <span class="breakdown-label">Completed:</span>
                        <span class="breakdown-value"><?php echo $orders_data['completed_orders'] ?? 0; ?></span>
                    </div>
                    <div class="breakdown-item">
                        <span class="breakdown-label">Cancelled:</span>
                        <span class="breakdown-value"><?php echo $orders_data['cancelled_orders'] ?? 0; ?></span>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-group">
                    <h3>Total Revenue</h3>
                    <p class="stat-number">₱<?php echo number_format($sales_data['total_sales'] ?? 0, 2); ?></p>
                </div>
                
                <p class="stat-subtitle">From completed orders</p>
            </div>

            <div class="stat-card">
                <div class="stat-group">
                    <h3>Products in Inventory</h3>
                    <p class="stat-number"><?php echo number_format($products_data['total_products'] ?? 0); ?></p>
                </div>
                <p class="stat-subtitle">Total Stock: <?php echo number_format($products_data['total_stock'] ?? 0); ?> units</p>
            </div>

            <div class="stat-card">
                <div class="stat-group">
                    <h3>Registered Users</h3>
                    <p class="stat-number"><?php echo number_format($users_data['total_users'] ?? 0); ?></p>
                </div>
                <p class="stat-subtitle">Active customers</p>
            </div>
        </div>

        <div class="activity-section">
            <div class="activity-card">
                <div class="activity-header">
                    <h3>Recent Orders</h3>
                    <a href="adminOrders.php" class="view-all-link">View All →</a>
                </div>
                <div class="activity-list">
                    <?php if (count($recent_orders) > 0): ?>
                        <?php foreach ($recent_orders as $order): ?>
                        <div class="activity-item">
                            <div class="activity-content">
                                <p class="activity-title">Order #<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></p>
                                <p class="activity-subtitle"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?> - ₱<?php echo number_format($order['total_amount'], 2); ?></p>
                                <p class="activity-time"><?php echo date('M d, Y h:i A', strtotime($order['order_date'])); ?></p>
                            </div>
                            <span class="activity-badge status-<?php echo $order['order_status']; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-data">No recent orders</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="activity-card">
                <div class="activity-header">
                    <h3>Low Stock Alert</h3>
                    <a href="adminProducts.php" class="view-all-link">View All →</a>
                </div>
                <div class="activity-list">
                    <?php if (count($low_stock) > 0): ?>
                        <?php foreach ($low_stock as $product): ?>
                        <div class="activity-item">
                            <div class="product-thumbnail">
                                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="Product">
                            </div>
                            <div class="activity-content">
                                <p class="activity-title"><?php echo htmlspecialchars($product['product_name']); ?></p>
                                <p class="activity-subtitle">Product ID: #<?php echo $product['product_id']; ?></p>
                            </div>
                            <span class="stock-badge <?php echo $product['quantity'] < 5 ? 'critical' : 'warning'; ?>">
                                <?php echo $product['quantity']; ?> left
                            </span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-data">All products are well-stocked</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

</body>
</html>