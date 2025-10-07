<?php
include 'Includes/admin_auth.php';
include 'Includes/connection.php';

session_start();
check_login_redirect();
handle_logout();
?>
<?php
include 'Includes/adminHeader.php';
include 'Includes/adminNav.php';
?>
<main>
    <div class="admin-products-container">
        <h2>All Products</h2>
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT p.product_id, p.product_name, p.price, p.quantity, p.brand, p.description, p.image_path, p.created_at, c.category_name 
                            FROM products p 
                            JOIN categories c ON p.category_id = c.id";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . intval($row['product_id']) . '</td>';
                            echo '<td>' . date("Y-m-d H:i:s", strtotime($row['created_at'])) . '</td>';
                            echo '<td>' . htmlspecialchars($row['product_name']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['category_name']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['brand']) . '</td>';
                            echo '<td>₱' . number_format($row['price'], 2) . '</td>';
                            echo '<td>' . intval($row['quantity']) . '</td>';
                            $shortDesc = mb_strimwidth($row['description'], 0, 50, "...");
                            echo '<td title="' . htmlspecialchars($row['description']) . '">' 
                                . htmlspecialchars($shortDesc) . 
                                '</td>';
                            echo '<td><img src="' . htmlspecialchars($row['image_path']) . '" alt="' . htmlspecialchars($row['product_name']) . '" style="width: 50px; height: auto;"></td>';
                            echo '<td>
                                    <a href="adminEditProduct.php?id=' . intval($row['product_id']) . '">Edit</a> | 
                                    <a href="adminDeleteProduct.php?id=' . intval($row['product_id']) . '" onclick="return confirm(\'Are you sure you want to delete this product?\');">Delete</a>
                                  </td>';
                        }
                    } else {
                        echo "<tr><td colspan='10'>No products available.</td></tr>";
                    }
                    ?>
                    </tr>
                </tbody>
            </table>
    </div>
</main>
</body>
</html>