<?php
include 'Includes/admin_auth.php';
include 'Includes/connection.php';

session_start();
check_login_redirect();
handle_logout();

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');
$sort_by = $_GET['sort'] ?? 'date_desc'; // Default sort

// Build query with filters
$where = [];
$params = [];
$types = '';

if ($status_filter !== '' && $status_filter !== 'all') {
    $where[] = 'o.order_status = ?';
    $params[] = $status_filter;
    $types .= 's';
}

if ($search !== '') {
    $where[] = '(u.username LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR o.order_id LIKE ?)';
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ssss';
}

// Determine ORDER BY clause based on sort parameter
$order_by = "o.order_date DESC"; // Default
switch ($sort_by) {
    case 'date_asc':
        $order_by = "o.order_date ASC";
        break;
    case 'date_desc':
        $order_by = "o.order_date DESC";
        break;
    case 'amount_asc':
        $order_by = "o.total_amount ASC";
        break;
    case 'amount_desc':
        $order_by = "o.total_amount DESC";
        break;
    case 'customer_asc':
        $order_by = "u.first_name ASC, u.last_name ASC";
        break;
    case 'customer_desc':
        $order_by = "u.first_name DESC, u.last_name DESC";
        break;
}

$sql = "SELECT o.order_id, o.order_date, o.total_amount, o.payment_method, 
               o.payment_status, o.order_status,
               u.username, u.first_name, u.last_name, u.email, u.contact_number,
               COUNT(oi.order_item_id) as item_count
        FROM orders o
        JOIN user u ON o.user_id = u.user_id
        LEFT JOIN order_items oi ON o.order_id = oi.order_id";

if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}

$sql .= " GROUP BY o.order_id ORDER BY " . $order_by;

$stmt = $conn->prepare($sql);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Get count for each status
$count_stmt = $conn->query("
    SELECT 
        order_status,
        COUNT(*) as count
    FROM orders
    GROUP BY order_status
");

$status_counts = [
    'all' => 0,
    'pending' => 0,
    'confirmed' => 0,
    'ready_for_pickup' => 0,
    'completed' => 0,
    'cancelled' => 0
];

// Get total count
$total_count = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total'];
$status_counts['all'] = $total_count;

while ($count_row = $count_stmt->fetch_assoc()) {
    $status_counts[$count_row['order_status']] = $count_row['count'];
}

$stmt->close();
?>

<?php
include 'Includes/adminHeader.php';
include 'Includes/adminNav.php';
?>

<main>
    <div class="admin-products-container">
        <h2>All Orders</h2>
        
        <div class="order-tabs">
            <a href="?status=all&sort=<?php echo htmlspecialchars($sort_by); ?>&search=<?php echo htmlspecialchars($search); ?>" 
               class="order-tab <?php echo ($status_filter === '' || $status_filter === 'all') ? 'active' : ''; ?>">
                All Orders (<?php echo $status_counts['all']; ?>)
            </a>
            <a href="?status=pending&sort=<?php echo htmlspecialchars($sort_by); ?>&search=<?php echo htmlspecialchars($search); ?>" 
               class="order-tab <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">
                Pending (<?php echo $status_counts['pending']; ?>)
            </a>
            <a href="?status=confirmed&sort=<?php echo htmlspecialchars($sort_by); ?>&search=<?php echo htmlspecialchars($search); ?>" 
               class="order-tab <?php echo $status_filter === 'confirmed' ? 'active' : ''; ?>">
                Confirmed (<?php echo $status_counts['confirmed']; ?>)
            </a>
            <a href="?status=ready_for_pickup&sort=<?php echo htmlspecialchars($sort_by); ?>&search=<?php echo htmlspecialchars($search); ?>" 
               class="order-tab <?php echo $status_filter === 'ready_for_pickup' ? 'active' : ''; ?>">
                Ready for Pickup (<?php echo $status_counts['ready_for_pickup']; ?>)
            </a>
            <a href="?status=completed&sort=<?php echo htmlspecialchars($sort_by); ?>&search=<?php echo htmlspecialchars($search); ?>" 
               class="order-tab <?php echo $status_filter === 'completed' ? 'active' : ''; ?>">
                Completed (<?php echo $status_counts['completed']; ?>)
            </a>
            <a href="?status=cancelled&sort=<?php echo htmlspecialchars($sort_by); ?>&search=<?php echo htmlspecialchars($search); ?>" 
               class="order-tab <?php echo $status_filter === 'cancelled' ? 'active' : ''; ?>">
                Cancelled (<?php echo $status_counts['cancelled']; ?>)
            </a>
        </div>

        <form method="get" action="adminOrders.php" class="filter-controls">
            <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
            
            <div class="search-box">
                <input type="text" name="search" placeholder="Search by customer name, username, or order ID..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div class="sort-box">
                <label for="sort">Sort by:</label>
                <select name="sort" id="sort">
                    <option value="date_desc" <?php echo $sort_by === 'date_desc' ? 'selected' : ''; ?>>Date (Newest First)</option>
                    <option value="date_asc" <?php echo $sort_by === 'date_asc' ? 'selected' : ''; ?>>Date (Oldest First)</option>
                    <option value="amount_desc" <?php echo $sort_by === 'amount_desc' ? 'selected' : ''; ?>>Amount (High to Low)</option>
                    <option value="amount_asc" <?php echo $sort_by === 'amount_asc' ? 'selected' : ''; ?>>Amount (Low to High)</option>
                    <option value="customer_asc" <?php echo $sort_by === 'customer_asc' ? 'selected' : ''; ?>>Customer (A-Z)</option>
                    <option value="customer_desc" <?php echo $sort_by === 'customer_desc' ? 'selected' : ''; ?>>Customer (Z-A)</option>
                </select>
            </div>
            
            <button type="submit" class="filter-btn">Apply</button>
            <a href="adminOrders.php" class="clear-btn">Clear</a>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Order Date</th>
                    <th>Customer</th>
                    <th>Contact</th>
                    <th>Items</th>
                    <th>Total Amount</th>
                    <th>Payment Method</th>
                    <th>Payment Status</th>
                    <th>Order Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>#' . str_pad($row['order_id'], 6, '0', STR_PAD_LEFT) . '</td>';
                        echo '<td>' . date("M d, Y h:i A", strtotime($row['order_date'])) . '</td>';
                        echo '<td>' . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . '<br><small>@' . htmlspecialchars($row['username']) . '</small></td>';
                        echo '<td>' . htmlspecialchars($row['contact_number']) . '</td>';
                        echo '<td>' . intval($row['item_count']) . '</td>';
                        echo '<td>₱' . number_format($row['total_amount'], 2) . '</td>';
                        echo '<td>' . ucwords(str_replace('_', ' ', $row['payment_method'])) . '</td>';
                        
                        $payment_class = '';
                        if ($row['payment_status'] === 'paid') $payment_class = 'status-paid';
                        elseif ($row['payment_status'] === 'pending') $payment_class = 'status-pending-payment';
                        elseif ($row['payment_status'] === 'refunded') $payment_class = 'status-refunded';
                        echo '<td><span class="status-badge ' . $payment_class . '">' . ucfirst($row['payment_status']) . '</span></td>';
                        
                        $status_class = '';
                        if ($row['order_status'] === 'pending') $status_class = 'status-pending';
                        elseif ($row['order_status'] === 'confirmed') $status_class = 'status-confirmed';
                        elseif ($row['order_status'] === 'ready_for_pickup') $status_class = 'status-ready';
                        elseif ($row['order_status'] === 'completed') $status_class = 'status-completed';
                        elseif ($row['order_status'] === 'cancelled') $status_class = 'status-cancelled';
                        echo '<td><span class="status-badge ' . $status_class . '">' . ucwords(str_replace('_', ' ', $row['order_status'])) . '</span></td>';
                        
                        echo '<td>
                                <a href="adminOrderSummary.php?id=' . intval($row['order_id']) . '" class="action-link">View</a>
                              </td>';
                        echo '</tr>';
                    }
                } else {
                    echo "<tr><td colspan='10' style='text-align: center; padding: 30px; color: #999;'>No orders found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>