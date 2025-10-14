<?php 
include 'Includes/connection.php';
?>
<?php include 'Includes/userHeader.php'; ?>
<main>

    <div class='product-details-container'>
        <div class="product-image">
            <?php
            $product_id = $_GET['id'] ?? 0;
            $stmt = $conn->prepare("SELECT p.product_id, p.product_name, p.price, p.quantity, p.description, p.image_path, c.category_name, b.brand_name
                                    FROM products p 
                                    JOIN categories c ON p.category_id = c.id
                                    JOIN brands b ON p.brand_id = b.id
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
        <?php if (isset($row)): ?>
        <div class="product-details">
            <h2><?php echo htmlspecialchars($row['product_name']); ?></h2>
            <p><?php echo htmlspecialchars($row['brand_name']); ?></p>
            <hr>
            <h3>₱<?php echo number_format($row['price'], 2); ?></h3>
            <p><strong>Stock:</strong> <?php echo intval($row['quantity']); ?></p>
            <form method="POST" action="Includes/userAddToCart.php">
                <input type="hidden" name="product_id" value="<?php echo intval($row['product_id']); ?>">
                <div>
                    <label for="quantity">Quantity:</label>
                    <div class="quantity">
                        <button type="button" class="minus">−</button>
                        <input type="number" value="1" min="1" max="<?php echo intval($row['quantity']); ?>" name="quantity" id="quantity" required>
                        <button type="button" class="plus">+</button>
                    </div>
                </div>
                <button type="submit" name="add_to_cart">Add to Cart</button>
            </form>
            <hr>
            <h2>Description</h2>
            <p><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
        </div>
        <?php endif; ?>
    </div>

</main>
<?php include 'Includes/userFooter.php'; ?>
<?php include 'Includes/quantitybuttons.php'; ?>