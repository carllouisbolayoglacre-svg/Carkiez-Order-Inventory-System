<?php 
include 'Includes/connection.php';
include 'Includes/staff_auth.php';

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
    WHERE DATE(order_date) = CURDATE()
");
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
$orders_data = $orders_result->fetch_assoc();
$orders_stmt->close();

// Get Today's Sales
$sales_stmt = $conn->prepare("
    SELECT COALESCE(SUM(total_amount), 0) as today_sales
    FROM orders
    WHERE DATE(order_date) = CURDATE() AND order_status = 'completed' AND payment_status = 'paid'
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

// Get Pending Orders Count
$pending_stmt = $conn->prepare("SELECT COUNT(*) as pending_count FROM orders WHERE order_status = 'pending'");
$pending_stmt->execute();
$pending_result = $pending_stmt->get_result();
$pending_data = $pending_result->fetch_assoc();
$pending_stmt->close();

// Get Recent Orders (Today's orders)
$recent_orders_stmt = $conn->prepare("
    SELECT o.order_id, o.order_date, o.total_amount, o.order_status,
           u.first_name, u.last_name
    FROM orders o
    JOIN user u ON o.user_id = u.user_id
    WHERE DATE(o.order_date) = CURDATE()
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

// Get Orders Needing Attention
$attention_stmt = $conn->prepare("
    SELECT o.order_id, o.order_date, o.total_amount, o.order_status,
           u.first_name, u.last_name, u.contact_number
    FROM orders o
    JOIN user u ON o.user_id = u.user_id
    WHERE o.order_status IN ('pending', 'confirmed')
    ORDER BY o.order_date ASC
    LIMIT 5
");
$attention_stmt->execute();
$attention_result = $attention_stmt->get_result();
$attention_orders = [];
while ($row = $attention_result->fetch_assoc()) {
    $attention_orders[] = $row;
}
$attention_stmt->close();
?>

<?php
include 'Includes/adminHeader.php';
include 'Includes/staffNav.php';
?>

<main>
    <div class="dashboard-container">
        <h1>Staff Dashboard</h1>
        <p class="welcome-text">Welcome back, <?php echo htmlspecialchars($_SESSION['staff_username']); ?>!</p>
        
        <div class="stats-grid">
            <!-- Today's Orders Card -->
            <div class="stat-card orders-card">
                <div class="stat-info">
                    <h3>Today's Orders</h3>
                    <p class="stat-number"><?php echo number_format($orders_data['total_orders'] ?? 0); ?></p>
                    <p class="stat-subtitle">Orders received today</p>
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
                </div>
            </div>

            <!-- Today's Sales Card -->
            <div class="stat-card sales-card">
                <div class="stat-info">
                    <h3>Today's Sales</h3>
                    <p class="stat-number">₱<?php echo number_format($sales_data['today_sales'] ?? 0, 2); ?></p>
                    <p class="stat-subtitle">Completed orders today</p>
                </div>
            </div>

            <!-- Products in Stock Card -->
            <div class="stat-card products-card">
                <div class="stat-info">
                    <h3>Products Available</h3>
                    <p class="stat-number"><?php echo number_format($products_data['total_products'] ?? 0); ?></p>
                    <p class="stat-subtitle">Total Stock: <?php echo number_format($products_data['total_stock'] ?? 0); ?> units</p>
                </div>
            </div>

            <!-- Pending Actions Card -->
            <div class="stat-card users-card">
                <div class="stat-info">
                    <h3>Pending Actions</h3>
                    <p class="stat-number"><?php echo number_format($pending_data['pending_count'] ?? 0); ?></p>
                    <p class="stat-subtitle">Orders need attention</p>
                </div>
            </div>
        </div>
            <!-- Low Stock Alert -->
             <div class="activity-section">
                <!-- Orders Needing Attention -->
                <div class="activity-card">
                    <div class="activity-header">
                        <h3>Orders</h3>
                        <a href="staffOrders.php?status=pending" class="view-all-link">View All →</a>
                    </div>
                    <div class="activity-list">
                        <?php if (count($attention_orders) > 0): ?>
                            <?php foreach ($attention_orders as $order): ?>
                            <div class="activity-item">
                                <div class="activity-content">
                                    <p class="activity-title">Order #<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></p>
                                    <p class="activity-subtitle"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?> - <?php echo htmlspecialchars($order['contact_number']); ?></p>
                                    <p class="activity-time"><?php echo date('M d, Y h:i A', strtotime($order['order_date'])); ?></p>
                                </div>
                                <span class="activity-badge status-<?php echo $order['order_status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-data">No orders needing attention</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="activity-card">
                    <div class="activity-header">
                        <h3>Low Stock Alert</h3>
                        <a href="staffProducts.php" class="view-all-link">View All →</a>
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
    </div>
</main>