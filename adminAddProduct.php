<?php 
include 'Includes/connection.php';
include 'Includes/admin_auth.php';

session_start();
check_login_redirect();
handle_logout();

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
                $stmt = $conn->prepare("INSERT INTO products 
                    (product_name, price, quantity, category_id, brand_id, description, image_path) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sdiisss", $product_name, $price, $quantity, $category_id, $brand_id, $description, $dest_path);

                if ($stmt->execute()) {
                    echo "<script>alert('Product added successfully!'); window.location.href='adminAddProduct.php';</script>";
                } else {
                    echo "Error: " . $stmt->error;
                }
                $stmt->close();
            } else {
                echo "<script>alert('There was an error moving the uploaded file.'); window.history.back();</script>";
            }
        } else {
            echo "<script>alert('Upload failed. Allowed file types: " . implode(', ', $allowedfileExtensions) . "'); window.history.back();</script>";
        }
    }
}
?>
<?php
include 'Includes/adminHeader.php';
include 'Includes/adminNav.php';
?>
<main>
    <div class="input-product-container">
    <h2>Add Product</h2>
    <form action="adminAddProduct.php" method="post" enctype="multipart/form-data">
        
        <label for="product_name">Product Name:</label>
        <input type="text" id="product_name" name="product_name" required>

        <div class="form-group">
            <div class="form-field">
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" step="0.01" required>
            </div>
            <div class="form-field">
                <label for="quantity">Stock Quantity:</label>
                <input type="number" id="quantity" name="quantity" required>
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
                        echo '<option value="' . htmlspecialchars($cat['id']) . '">' 
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
                    $cat_stmt = $conn->prepare("SELECT id, brand_name FROM brands ORDER BY brand_name ASC");
                    $cat_stmt->execute();
                    $cat_result = $cat_stmt->get_result();

                    while ($cat = $cat_result->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($cat['id']) . '">' 
                            . htmlspecialchars($cat['brand_name']) . 
                            '</option>';
                    }
                    $cat_stmt->close();
                    ?>
                </select>
            </div>
        </div>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea>

        <label for="image">Product Image:</label>
        <input type="file" id="image" name="image" accept="image/*" required>

        <button type="submit">Add Product</button>
    </form>
</div>
</main>
</body>
</html>