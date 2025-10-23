<?php
include 'Includes/admin_auth.php';
include 'Includes/connection.php';

session_start();
check_login_redirect();
handle_logout();

$search = trim($_GET['search'] ?? '');
$sort_by = $_GET['sort'] ?? 'id_asc';

$where = [];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = '(p.product_name LIKE ? OR c.category_name LIKE ? OR b.brand_name LIKE ?)';
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param];
    $types = 'sss';
}

$order_by = "p.product_id ASC";
switch ($sort_by) {
    case 'id_desc': $order_by = "p.product_id DESC"; break;
    case 'name_asc': $order_by = "p.product_name ASC"; break;
    case 'name_desc': $order_by = "p.product_name DESC"; break;
    case 'price_asc': $order_by = "p.price ASC"; break;
    case 'price_desc': $order_by = "p.price DESC"; break;
    case 'qty_asc': $order_by = "p.quantity ASC"; break;
    case 'qty_desc': $order_by = "p.quantity DESC"; break;
}

$sql = "SELECT p.product_id, p.product_name, p.price, p.quantity, p.description, p.image_path, p.created_at, c.category_name, b.brand_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id
        JOIN brands b ON p.brand_id = b.id";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY $order_by";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

include 'Includes/adminHeader.php';
include 'Includes/staffNav.php';
?>
<main>
    <div class="admin-products-container">
        <h2>All Products</h2>
        <form method="get" action="staffProducts.php" class="filter-controls" style="margin-bottom:1rem;">
            <div class="search-box">
                <input type="text" name="search" placeholder="Search products, category, brand..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="sort-box">
                <label for="sort">Sort by:</label>
                <select name="sort" id="sort">
                    <option value="id_asc" <?php if($sort_by=='id_asc')echo'selected';?>>ID (Asc)</option>
                    <option value="id_desc" <?php if($sort_by=='id_desc')echo'selected';?>>ID (Desc)</option>
                    <option value="name_asc" <?php if($sort_by=='name_asc')echo'selected';?>>Name (A-Z)</option>
                    <option value="name_desc" <?php if($sort_by=='name_desc')echo'selected';?>>Name (Z-A)</option>
                    <option value="price_asc" <?php if($sort_by=='price_asc')echo'selected';?>>Price (Low-High)</option>
                    <option value="price_desc" <?php if($sort_by=='price_desc')echo'selected';?>>Price (High-Low)</option>
                    <option value="qty_asc" <?php if($sort_by=='qty_asc')echo'selected';?>>Quantity (Low-High)</option>
                    <option value="qty_desc" <?php if($sort_by=='qty_desc')echo'selected';?>>Quantity (High-Low)</option>
                </select>
            </div>
            <button type="submit" class="filter-btn">Apply</button>
            <a href="staffProducts.php" class="clear-btn">Clear</a>
        </form>
        <table>
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Time Stamp</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Brand</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Description</th>
                    <th>Image</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . intval($row['product_id']) . '</td>';
                        echo '<td>' . date("Y-m-d H:i:s", strtotime($row['created_at'])) . '</td>';
                        echo '<td>' . htmlspecialchars($row['product_name']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['category_name']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['brand_name']) . '</td>';
                        echo '<td>₱' . number_format($row['price'], 2) . '</td>';
                        echo '<td>' . intval($row['quantity']) . '</td>';
                        $shortDesc = mb_strimwidth($row['description'], 0, 50, "...");
                        echo '<td title="' . htmlspecialchars($row['description']) . '">' 
                            . htmlspecialchars($shortDesc) . 
                            '</td>';
                        echo '<td><img src="' . htmlspecialchars($row['image_path']) . '" alt="' . htmlspecialchars($row['product_name']) . '" style="width: 50px; height: auto;"></td>';
                        echo '</tr>';
                    }
                } else {
                    echo "<tr><td colspan='10'>No products available.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>