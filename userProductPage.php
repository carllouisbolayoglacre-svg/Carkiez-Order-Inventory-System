<?php 
include 'Includes/connection.php';
?>
<?php include 'Includes/userHeader.php'; ?>
<main>

    <div class='product-details-container'>
        <div class="product-image">
            <?php
            $product_id = $_GET['id'] ?? 0;
            $stmt = $conn->prepare("SELECT p.product_id, p.product_name, p.price, p.quantity, p.brand, p.description, p.image_path, c.category_name 
                                    FROM products p 
                                    JOIN categories c ON p.category_id = c.id
                                    WHERE p.product_id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                echo '<img src="' . htmlspecialchars($row['image_path']) . '" alt="' . htmlspecialchars($row['product_name']) . '">';
            } else {
                echo '<p>Product not found.</p>';
            }

            $stmt->close();
            ?>
        </div>
        <div class="product-details">
            <?php
            if (isset($row)) {
                echo '<h2>' . htmlspecialchars($row['product_name']) . '</h2>';
                echo '<p>' . htmlspecialchars($row['brand']) . '</p>';
                echo '<hr>';
                echo '<h3>₱' . number_format($row['price'], 2) . '</h3>';
                echo '<p><strong>Stock:</strong> ' . intval($row['quantity']) . '</p>';
            }
            ?>
            <form method="POST" action="userAddToCart.php">
                <input type="hidden" name="product_id" value="<?php echo intval($row['product_id'] ?? 0); ?>">
                <div>
                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo intval($row['quantity'] ?? 1); ?>" required>
                </div>
                <button type="submit" name="add_to_cart">Add to Cart</button>
            </form>
            <hr>
            <h2>Description</h2>
            <p><?php echo nl2br(htmlspecialchars($row['description'] ?? 'No description available.')); ?></p>
        </div>
    </div>

</main>
<?php include 'Includes/userFooter.php'; ?>