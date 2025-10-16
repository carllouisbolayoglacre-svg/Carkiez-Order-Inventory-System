<?php
include 'Includes/admin_auth.php';
include 'Includes/connection.php';

session_start();
check_login_redirect();
handle_logout();

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

// Build query with filters
$where = [];
$params = [];
$types = '';

if ($status_filter !== '') {
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

$sql .= " GROUP BY o.order_id ORDER BY o.order_date DESC";

$stmt = $conn->prepare($sql);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<?php
include 'Includes/adminHeader.php';
include 'Includes/adminNav.php';
?>

<main>
    <div class="admin-products-container">
        <h2>All Orders</h2>
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
                        
                        // Payment Status with color
                        $payment_class = '';
                        if ($row['payment_status'] === 'paid') $payment_class = 'status-paid';
                        elseif ($row['payment_status'] === 'pending') $payment_class = 'status-pending-payment';
                        elseif ($row['payment_status'] === 'refunded') $payment_class = 'status-refunded';
                        echo '<td><span class="status-badge ' . $payment_class . '">' . ucfirst($row['payment_status']) . '</span></td>';
                        
                        // Order Status with color
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
                    echo "<tr><td colspan='10' style='text-align: center;'>No orders found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>