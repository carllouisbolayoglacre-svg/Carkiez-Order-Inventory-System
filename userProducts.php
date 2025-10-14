<?php 
include 'Includes/connection.php';
?>
<?php include 'Includes/userHeader.php'; ?>
<main>
    <div class="product-page-container">
        <div class="category-list">
                <h2>Categories</h2>
                <a href="userProducts.php" class="category-link">All</a>
                <?php
                $cat_stmt = $conn->prepare("SELECT id, category_name FROM categories ORDER BY category_name ASC");
                $cat_stmt->execute();
                $cat_result = $cat_stmt->get_result();

                while ($cat = $cat_result->fetch_assoc()) {
                    echo '<a href="userProducts.php?category=' . intval($cat['id']) . '" class="category-link">' 
                        . htmlspecialchars($cat['category_name']) . 
                        '</a>';
                }
                $cat_stmt->close();
                ?>
        </div>
        <div class="products-container">
            <?php
            $selected_category = $_GET['category'] ?? '';
            $selected_brand = $_GET['brand'] ?? '';

            if ($selected_category) {
                // Get category name for display
                $cat_name_stmt = $conn->prepare("SELECT category_name FROM categories WHERE id = ?");
                $cat_name_stmt->bind_param("i", $selected_category);
                $cat_name_stmt->execute();
                $cat_name_result = $cat_name_stmt->get_result();
                $cat_row = $cat_name_result->fetch_assoc();
                $category_name = $cat_row['category_name'] ?? 'Category';

                // Count products
                $stmt = $conn->prepare("SELECT COUNT(*) AS total_items FROM products WHERE category_id = ?");
                $stmt->bind_param("i", $selected_category);
                $stmt->execute();
                $count_result = $stmt->get_result();
                $row = $count_result->fetch_assoc();
                $total_items = $row['total_items'] ?? 0;

                echo '<h2>' . htmlspecialchars($category_name) . '</h2>';
                echo '<p>Showing ' . $total_items . ' products</p>';

                $stmt->close();
                $cat_name_stmt->close();
            } elseif ($selected_brand) {
                // Get brand name for display
                $brand_name_stmt = $conn->prepare("SELECT brand_name FROM brands WHERE id = ?");
                $brand_name_stmt->bind_param("i", $selected_brand);
                $brand_name_stmt->execute();
                $brand_name_result = $brand_name_stmt->get_result();
                $brand_row = $brand_name_result->fetch_assoc();
                $brand_name = $brand_row['brand_name'] ?? 'Brand';

                // Count products
                $stmt = $conn->prepare("SELECT COUNT(*) AS total_items FROM products WHERE brand_id = ?");
                $stmt->bind_param("i", $selected_brand);
                $stmt->execute();
                $count_result = $stmt->get_result();
                $row = $count_result->fetch_assoc();
                $total_items = $row['total_items'] ?? 0;

                echo '<h2>' . htmlspecialchars($brand_name) . '</h2>';
                echo '<p>Showing ' . $total_items . ' products</p>';

                $stmt->close();
                $brand_name_stmt->close();
            } else {
                echo '<h2>All Products</h2>';
            }
            ?>
            <div class="product-grid">
                <?php
                include 'Includes/connection.php';

                $selected_category = $_GET['category'] ?? '';
                $selected_brand = $_GET['brand'] ?? '';
                $search = trim($_GET['search'] ?? '');

                $where = [];
                $params = [];
                $types = '';

                if ($selected_category) {
                    $where[] = 'p.category_id = ?';
                    $params[] = $selected_category;
                    $types .= 'i';
                }
                if ($selected_brand) {
                    $where[] = 'p.brand_id = ?';
                    $params[] = $selected_brand;
                    $types .= 'i';
                }
                if ($search !== '') {
                    $where[] = '(p.product_name LIKE ? OR p.description LIKE ?)';
                    $params[] = "%$search%";
                    $params[] = "%$search%";
                    $types .= 'ss';
                }

                $sql = "SELECT p.product_id, p.product_name, p.price, p.quantity, p.description, p.image_path, c.category_name, b.brand_name
                        FROM products p 
                        JOIN categories c ON p.category_id = c.id
                        JOIN brands b ON p.brand_id = b.id";

                if ($where) {
                    $sql .= " WHERE " . implode(' AND ', $where);
                }

                $stmt = $conn->prepare($sql);

                if ($params) {
                    $stmt->bind_param($types, ...$params);
                }

                $stmt->execute();
                $result = $stmt->get_result();
                $stmt->close();


                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<div class="product-card">';
                        echo '<a href="userProductPage.php?id=' . intval($row['product_id']) . '"><img src="' . htmlspecialchars($row['image_path']) . '" alt="' . htmlspecialchars($row['product_name']) . '"></a>';
                        echo '<a href="userProductPage.php?id=' . intval($row['product_id']) . '"><h3>' . htmlspecialchars($row['product_name']) . '</h3></a>';
                        echo '<p>Category: ' . htmlspecialchars($row['category_name']) . '</p>';
                        echo '<p>Brand: ' . htmlspecialchars($row['brand_name']) . '</p>';
                        echo '<p>Price: ₱' . number_format($row['price'], 2) . '</p>';
                        echo '<p>In Stock: ' . intval($row['quantity']) . '</p>';
                        echo '</div>';
                    }
                } else {
                    echo "<p>No products available.</p>";
                }

                $conn->close();
                ?>
            </div>
        </div>
    </div>

</main>
<?php include 'Includes/userFooter.php'; ?>