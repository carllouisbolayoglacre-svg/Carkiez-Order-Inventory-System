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
    $where[] = 'brand_name LIKE ?';
    $params[] = "%$search%";
    $types .= 's';
}

$order_by = "id ASC";
if ($sort_by == 'id_desc') $order_by = "id DESC";
if ($sort_by == 'name_asc') $order_by = "brand_name ASC";
if ($sort_by == 'name_desc') $order_by = "brand_name DESC";

$sql = "SELECT id, brand_name FROM brands";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY $order_by";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<?php
include 'Includes/adminHeader.php';
include 'Includes/adminNav.php';
?>
<main>
    
    <div class="admin-products-container">
        <h2>Brands</h2>

        <form method="get" action="adminBrands.php" class="filter-controls" style="margin-bottom:1rem;">
            <div class="search-box">
                <input type="text" name="search" placeholder="Search brands..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="sort-box">
                <label for="sort">Sort by:</label>
                <select name="sort" id="sort">
                    <option value="id_asc" <?php if($sort_by=='id_asc')echo'selected';?>>ID (Asc)</option>
                    <option value="id_desc" <?php if($sort_by=='id_desc')echo'selected';?>>ID (Desc)</option>
                    <option value="name_asc" <?php if($sort_by=='name_asc')echo'selected';?>>Name (A-Z)</option>
                    <option value="name_desc" <?php if($sort_by=='name_desc')echo'selected';?>>Name (Z-A)</option>
                </select>
            </div>
            <button type="submit" class="filter-btn">Apply</button>
            <a href="adminBrands.php" class="clear-btn">Clear</a>
        </form>
        
            <table>
                <thead>
                    <tr>
                        <th>Brand ID</th>
                        <th>Brand Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . intval($row['id']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['brand_name']) . '</td>';
                            echo '<td>
                                    <a href="adminEditBrand.php?id=' . intval($row['id']) . '">Edit</a> | 
                                    <a href="adminDeleteBrand.php?id=' . intval($row['id']) . '" onclick="return confirm(\'Are you sure you want to delete this Brand?\');">Delete</a>
                                  </td>';
                            echo '</tr>';
                        }
                    } else {
                        echo "<tr><td colspan='3'>No Brands available.</td></tr>";
                    }
                    ?>
                    </tr>
                </tbody>
            </table>
    </div>
</main>
</body>
</html>