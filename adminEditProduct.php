<?php 
include 'Includes/connection.php';
include 'Includes/admin_auth.php';

session_start();
check_login_redirect();
handle_logout();

$product_id = intval($_GET['id'] ?? 0);

// Fetch product data
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Product not found!'); window.location.href='adminProducts.php';</script>";
    exit();
}

$product = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $product_name = trim($_POST['product_name']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $category_id = intval($_POST['category']);
    $brand_id = intval($_POST['brand']);
    $description = trim($_POST['description']);

    // Validate category
    $check_stmt = $conn->prepare("SELECT id FROM categories WHERE id = ?");
    $check_stmt->bind_param("i", $category_id);
    $check_stmt->execute();
    $check_stmt->store_result();    
    if ($check_stmt->num_rows === 0) {
        echo "<script>alert('Invalid category selected.'); window.history.back();</script>";
        exit;
    }
    $check_stmt->close();

    // Validate brand
    $check_stmt = $conn->prepare("SELECT id FROM brands WHERE id = ?");
    $check_stmt->bind_param("i", $brand_id);
    $check_stmt->execute();
    $check_stmt->store_result();    
    if ($check_stmt->num_rows === 0) {
        echo "<script>alert('Invalid brand selected.'); window.history.back();</script>";
        exit;
    }
    $check_stmt->close();

    // Check if new image is uploaded
    $image_path = $product['image_path']; // Keep existing image by default
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');
        
        if (in_array($fileExtension, $allowedfileExtensions)) {
            $uploadFileDir = './Uploads/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            $dest_path = $uploadFileDir . $newFileName;

            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                // Delete old image file if it exists
                if (file_exists($product['image_path'])) {
                    unlink($product['image_path']);
                }
                $image_path = $dest_path;
            } else {
                echo "<script>alert('There was an error moving the uploaded file.'); window.history.back();</script>";
                exit;
            }
        } else {
            echo "<script>alert('Upload failed. Allowed file types: " . implode(', ', $allowedfileExtensions) . "'); window.history.back();</script>";
            exit;
        }
    }

    // Update product
    $update_stmt = $conn->prepare("UPDATE products SET product_name = ?, price = ?, quantity = ?, category_id = ?, brand_id = ?, description = ?, image_path = ? WHERE product_id = ?");
    $update_stmt->bind_param("sdiisssi", $product_name, $price, $quantity, $category_id, $brand_id, $description, $image_path, $product_id);

    if ($update_stmt->execute()) {
        echo "<script>alert('Product updated successfully!'); window.location.href='adminProducts.php';</script>";
    } else {
        echo "Error: " . $update_stmt->error;
    }
    $update_stmt->close();
}
?>

<?php
include 'Includes/adminHeader.php';
include 'Includes/adminNav.php';
?>
<main>
    <div class="input-product-container">
        <h2>Edit Product</h2>
        <form action="adminEditProduct.php?id=<?php echo $product_id; ?>" method="post" enctype="multipart/form-data">
            
            <label for="product_name">Product Name:</label>
            <input type="text" id="product_name" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>

            <div class="form-group">
                <div class="form-field">
                    <label for="price">Price:</label>
                    <input type="number" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                </div>
                <div class="form-field">
                    <label for="quantity">Stock Quantity:</label>
                    <input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($product['quantity']); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <div class="form-field">
                    <label for="category">Category:</label>
                    <select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <?php
                        $cat_stmt = $conn->prepare("SELECT id, category_name FROM categories ORDER BY category_name ASC");
                        $cat_stmt->execute();
                        $cat_result = $cat_stmt->get_result();

                        while ($cat = $cat_result->fetch_assoc()) {
                            $selected = ($cat['id'] == $product['category_id']) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($cat['id']) . '" ' . $selected . '>' 
                                . htmlspecialchars($cat['category_name']) . 
                                '</option>';
                        }
                        $cat_stmt->close();
                        ?>
                    </select>
                </div>
                <div class="form-field">
                    <label for="brand">Brand:</label>
                    <select id="brand" name="brand" required>
                        <option value="">Select Brand</option>
                        <?php
                        $brand_stmt = $conn->prepare("SELECT id, brand_name FROM brands ORDER BY brand_name ASC");
                        $brand_stmt->execute();
                        $brand_result = $brand_stmt->get_result();

                        while ($brand = $brand_result->fetch_assoc()) {
                            $selected = ($brand['id'] == $product['brand_id']) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($brand['id']) . '" ' . $selected . '>' 
                                . htmlspecialchars($brand['brand_name']) . 
                                '</option>';
                        }
                        $brand_stmt->close();
                        ?>
                    </select>
                </div>
            </div>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>

            <label for="image">Product Image: (Leave empty to keep current image)</label>
            <div style="margin-bottom: 1rem;">
                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="Current Product Image" style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px;">
            </div>
            <input type="file" id="image" name="image" accept="image/*">

            <button type="submit">Update Product</button>
            <a href="adminProducts.php" style="display: inline-block; margin-top: 1rem; text-align: center; padding: 0.75rem; border: 1px solid #ccc; border-radius: 4px; text-decoration: none; color: #333;">Cancel</a>
        </form>
    </div>
</main>
</body>
</html>
<?php $conn->close(); ?>