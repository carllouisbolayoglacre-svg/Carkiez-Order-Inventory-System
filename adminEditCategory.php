<?php 
include 'Includes/connection.php';
include 'Includes/admin_auth.php';

session_start();
check_login_redirect();
handle_logout();

$category_id = intval($_GET['id'] ?? 0);

// Fetch category data
$stmt = $conn->prepare("SELECT id, category_name FROM categories WHERE id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Category not found!'); window.location.href='adminCategories.php';</script>";
    exit();
}

$category = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $category_name = trim($_POST['category_name']);

    if (!empty($category_name)) {
        // Check for duplicate category names (excluding current category)
        $check = $conn->prepare("SELECT id FROM categories WHERE category_name = ? AND id != ?");
        $check->bind_param("si", $category_name, $category_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            echo "<script>alert('Category name already exists!'); window.history.back();</script>";
        } else {
            // Update category
            $update_stmt = $conn->prepare("UPDATE categories SET category_name = ? WHERE id = ?");
            $update_stmt->bind_param("si", $category_name, $category_id);

            if ($update_stmt->execute()) {
                echo "<script>alert('Category updated successfully!'); window.location.href='adminCategories.php';</script>";
            } else {
                echo "Error: " . $update_stmt->error;
            }
            $update_stmt->close();
        }
        $check->close();
    } else {
        echo "<script>alert('Please enter a category name!'); window.history.back();</script>";
    }
}

$conn->close();
?>

<?php
include 'Includes/adminHeader.php';
include 'Includes/adminNav.php';
?>
<main>
    <div class="input-product-container">
        <h2>Edit Category</h2>
        <form action="adminEditCategory.php?id=<?php echo $category_id; ?>" method="post">
            <label for="category_name">Category Name:</label>
            <input type="text" id="category_name" name="category_name" value="<?php echo htmlspecialchars($category['category_name']); ?>" required>
            <button type="submit">Update Category</button>
            <a href="adminCategories.php" style="display: inline-block; margin-top: 1rem; text-align: center; padding: 0.75rem; border: 1px solid #ccc; border-radius: 4px; text-decoration: none; color: #333;">Cancel</a>
        </form>
    </div>
</main>
</body>
</html>