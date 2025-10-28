<?php
include 'Includes/admin_auth.php';
include 'Includes/connection.php';
session_start();
check_login_redirect();

$search = trim($_GET['search'] ?? '');
$sort_by = $_GET['sort'] ?? 'id_asc';

$where = [];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = "(p.reference_number LIKE ? OR u.username LIKE ? OR p.payment_method LIKE ? OR p.status LIKE ?)";
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param, $search_param];
    $types = 'ssss';
}

$order_by = "p.payment_id ASC";
switch ($sort_by) {
    case 'id_desc': $order_by = "p.payment_id DESC"; break;
    case 'date_asc': $order_by = "p.payment_date ASC"; break;
    case 'date_desc': $order_by = "p.payment_date DESC"; break;
    case 'amount_asc': $order_by = "p.amount_paid ASC"; break;
    case 'amount_desc': $order_by = "p.amount_paid DESC"; break;
    case 'status_asc': $order_by = "p.status ASC"; break;
    case 'status_desc': $order_by = "p.status DESC"; break;
}

$sql = "SELECT p.payment_id, p.order_id, p.user_id, u.username, p.payment_method, p.amount_paid, p.payment_date, p.reference_number, p.proof_image, p.status
        FROM payments p
        JOIN user u ON p.user_id = u.user_id";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY $order_by";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

include 'Includes/adminHeader.php';
include 'Includes/adminNav.php';
?>
<main>
    <div class="admin-products-container">
        <h2>Payments</h2>
        <form method="get" action="adminPayments.php" class="filter-controls" style="margin-bottom:1rem;">
            <div class="search-box">
                <input type="text" name="search" placeholder="Search reference, user, method, status..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="sort-box">
                <label for="sort">Sort by:</label>
                <select name="sort" id="sort">
                    <option value="id_asc" <?php if($sort_by=='id_asc')echo'selected';?>>ID (Asc)</option>
                    <option value="id_desc" <?php if($sort_by=='id_desc')echo'selected';?>>ID (Desc)</option>
                    <option value="date_asc" <?php if($sort_by=='date_asc')echo'selected';?>>Date (Asc)</option>
                    <option value="date_desc" <?php if($sort_by=='date_desc')echo'selected';?>>Date (Desc)</option>
                    <option value="amount_asc" <?php if($sort_by=='amount_asc')echo'selected';?>>Amount (Low-High)</option>
                    <option value="amount_desc" <?php if($sort_by=='amount_desc')echo'selected';?>>Amount (High-Low)</option>
                    <option value="status_asc" <?php if($sort_by=='status_asc')echo'selected';?>>Status (A-Z)</option>
                    <option value="status_desc" <?php if($sort_by=='status_desc')echo'selected';?>>Status (Z-A)</option>
                </select>
            </div>
            <button type="submit" class="filter-btn">Apply</button>
            <a href="adminPayments.php" class="clear-btn">Clear</a>
        </form>
        <table>
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Order</th>
                    <th>User</th>
                    <th>Method</th>
                    <th>Amount Paid</th>
                    <th>Date</th>
                    <th>Reference #</th>
                    <th>Status</th>
                    <th>Proof</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . intval($row['payment_id']) . '</td>';
                        echo '<td><a href="adminOrderSummary.php?id=' . intval($row['order_id']) . '">View</a></td>';
                        echo '<td>' . htmlspecialchars($row['username']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['payment_method']) . '</td>';
                        echo '<td>₱' . number_format($row['amount_paid'], 2) . '</td>';
                        echo '<td>' . htmlspecialchars($row['payment_date']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['reference_number']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['status']) . '</td>';
                        if ($row['proof_image']) {
                            echo '<td><a href="' . htmlspecialchars($row['proof_image']) . '" target="_blank">View</a></td>';
                        } else {
                            echo '<td>No Proof</td>';
                        }
                        echo '</tr>';
                    }
                } else {
                    echo "<tr><td colspan='10'>No payments found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>