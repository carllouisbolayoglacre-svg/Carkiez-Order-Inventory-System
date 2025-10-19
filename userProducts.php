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
                <hr style="margin: 15px 0; border: none; border-top: 1px solid #ddd;">
                <h2>Brands</h2>
                <a href="userProducts.php" class="category-link">All</a>
                <?php
                $brand_stmt = $conn->prepare("SELECT id, brand_name FROM brands ORDER BY brand_name ASC");
                $brand_stmt->execute();
                $brand_result = $brand_stmt->get_result();

                while ($brand = $brand_result->fetch_assoc()) {
                    echo '<a href="userProducts.php?brand=' . intval($brand['id']) . '" class="category-link">' 
                        . htmlspecialchars($brand['brand_name']) . 
                        '</a>';
                }
                $brand_stmt->close();
                ?>
        </div>
        <div class="products-container">
            <?php
            $selected_category = $_GET['category'] ?? '';
            $selected_brand = $_GET['brand'] ?? '';
            $search = trim($_GET['search'] ?? '');
            $sort = $_GET['sort'] ?? 'newest';

            // Display header
            if ($selected_category) {
                $cat_name_stmt = $conn->prepare("SELECT category_name FROM categories WHERE id = ?");
                $cat_name_stmt->bind_param("i", $selected_category);
                $cat_name_stmt->execute();
                $cat_name_result = $cat_name_stmt->get_result();
                $cat_row = $cat_name_result->fetch_assoc();
                $category_name = $cat_row['category_name'] ?? 'Category';
                $cat_name_stmt->close();
                echo '<h2>' . htmlspecialchars($category_name) . '</h2>';
            } elseif ($selected_brand) {
                $brand_name_stmt = $conn->prepare("SELECT brand_name FROM brands WHERE id = ?");
                $brand_name_stmt->bind_param("i", $selected_brand);
                $brand_name_stmt->execute();
                $brand_name_result = $brand_name_stmt->get_result();
                $brand_row = $brand_name_result->fetch_assoc();
                $brand_name = $brand_row['brand_name'] ?? 'Brand';
                $brand_name_stmt->close();
                echo '<h2>' . htmlspecialchars($brand_name) . '</h2>';
            } elseif ($search) {
                echo '<h2>Search Results for "' . htmlspecialchars($search) . '"</h2>';
            } else {
                echo '<h2>All Products</h2>';
            }
            ?>

            <!-- Filter Section -->
            <div class="product-filters">
                <form method="get" action="userProducts.php" class="filter-form">
                    <!-- Preserve existing filters -->
                    <?php if ($selected_category): ?>
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($selected_category); ?>">
                    <?php endif; ?>
                    <?php if ($selected_brand): ?>
                        <input type="hidden" name="brand" value="<?php echo htmlspecialchars($selected_brand); ?>">
                    <?php endif; ?>
                    <?php if ($search): ?>
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                    <?php endif; ?>

                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="sort">Sort By:</label>
                            <select name="sort" id="sort">
                                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                                <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name: A-Z</option>
                                <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Name: Z-A</option>
                                <option value="stock_high" <?php echo $sort === 'stock_high' ? 'selected' : ''; ?>>Stock: High to Low</option>
                            </select>
                        </div>
                        <button type="submit" class="filter-btn">Apply Filters</button>
                        <a href="userProducts.php" class="clear-filter-btn">Clear All</a>
                    </div>
                </form>
            </div>

            <?php
            // Build the query with all filters
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
                $search_param = "%$search%";
                $params[] = $search_param;
                $params[] = $search_param;
                $types .= 'ss';
            }

            // Determine ORDER BY clause
            $order_by = "p.created_at DESC"; // Default: newest
            switch ($sort) {
                case 'oldest':
                    $order_by = "p.created_at ASC";
                    break;
                case 'price_low':
                    $order_by = "p.price ASC";
                    break;
                case 'price_high':
                    $order_by = "p.price DESC";
                    break;
                case 'name_asc':
                    $order_by = "p.product_name ASC";
                    break;
                case 'name_desc':
                    $order_by = "p.product_name DESC";
                    break;
                case 'stock_high':
                    $order_by = "p.quantity DESC";
                    break;
            }

            $sql = "SELECT p.product_id, p.product_name, p.price, p.quantity, p.description, p.image_path, 
                           c.category_name, b.brand_name
                    FROM products p 
                    JOIN categories c ON p.category_id = c.id
                    JOIN brands b ON p.brand_id = b.id";

            if ($where) {
                $sql .= " WHERE " . implode(' AND ', $where);
            }

            $sql .= " ORDER BY " . $order_by;

            $stmt = $conn->prepare($sql);

            if ($params) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $total_products = $result->num_rows;
            ?>

            <p class="product-count">Showing <?php echo $total_products; ?> product<?php echo $total_products !== 1 ? 's' : ''; ?></p>

            <div class="product-grid">
                <?php
                if ($total_products > 0) {
                    while($row = $result->fetch_assoc()) {
                        $out_of_stock = $row['quantity'] <= 0;
                        echo '<div class="product-card' . ($out_of_stock ? ' out-of-stock' : '') . '">';
                        echo '<a href="userProductPage.php?id=' . intval($row['product_id']) . '">';
                        echo '<div class="product-image-wrapper">';
                        echo '<img src="' . htmlspecialchars($row['image_path']) . '" alt="' . htmlspecialchars($row['product_name']) . '">';
                        if ($out_of_stock) {
                            echo '<div class="out-of-stock-badge">Out of Stock</div>';
                        }
                        echo '</div>';
                        echo '</a>';
                        echo '<a href="userProductPage.php?id=' . intval($row['product_id']) . '"><h3>' . htmlspecialchars($row['product_name']) . '</h3></a>';
                        echo '<p class="product-category">Category: ' . htmlspecialchars($row['category_name']) . '</p>';
                        echo '<p class="product-brand">Brand: ' . htmlspecialchars($row['brand_name']) . '</p>';
                        echo '<p class="product-price">₱' . number_format($row['price'], 2) . '</p>';
                        if (!$out_of_stock) {
                            echo '<p class="product-stock">In Stock: ' . intval($row['quantity']) . '</p>';
                        }
                        echo '</div>';
                    }
                } else {
                    echo '<div class="no-products">';
                    echo '<p>No products found matching your criteria.</p>';
                    echo '<a href="userProducts.php" class="back-to-products">View All Products</a>';
                    echo '</div>';
                }

                $stmt->close();
                $conn->close();
                ?>
            </div>
        </div>
    </div>
</main>
<?php include 'Includes/userFooter.php'; ?>