<?php 
include 'Includes/connection.php';
?>
<?php include 'Includes/userHeader.php'; ?>
<main>
    <div class="product-page-container">
        <div class="category-list">
                <a href="userProducts.php" class="category-link">All</a>
                <?php
                $cat_stmt = $conn->prepare("SELECT id, category_name FROM categories ORDER BY category_name ASC");
                $cat_stmt->execute();
                $cat_result = $cat_stmt->get_result();

                while ($cat = $cat_result->fetch_assoc()) {
                    echo '<a href="userProducts.php?category=' . urlencode($cat['category_name']) . '" class="category-link">' 
                        . htmlspecialchars($cat['category_name']) . 
                        '</a>';
                }

                $cat_stmt->close();
                ?>
        </div>
        <div class="products-container">
            <h2>All Products</h2>
            <div class="product-grid">
                <?php
                include 'Includes/connection.php';

                $sql = "SELECT p.product_id, p.product_name, p.price, p.quantity, p.brand, p.description, p.image_path, c.category_name 
                        FROM products p 
                        JOIN categories c ON p.category_id = c.id";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<div class="product-card">';
                        echo '<img src="' . htmlspecialchars($row['image_path']) . '" alt="' . htmlspecialchars($row['product_name']) . '">';
                        echo '<h3>' . htmlspecialchars($row['product_name']) . '</h3>';
                        echo '<p>Category: ' . htmlspecialchars($row['category_name']) . '</p>';
                        echo '<p>Brand: ' . htmlspecialchars($row['brand']) . '</p>';
                        echo '<p>Price: $' . number_format($row['price'], 2) . '</p>';
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