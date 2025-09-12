<?php 
include 'Includes/connection.php';
include 'Includes/admin_auth.php';

session_start();
check_login_redirect();
handle_logout();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $category_name = trim($_POST['category_name']);

    if (!empty($category_name)) {
        // Prevent duplicate category names
        $check = $conn->prepare("SELECT id FROM categories WHERE category_name = ?");
        $check->bind_param("s", $category_name);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            echo "<script>alert('Category already exists!'); window.history.back();</script>";
        } else {
            // Insert new category
            $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
            $stmt->bind_param("s", $category_name);

            if ($stmt->execute()) {
                echo "<script>alert('Category added successfully!'); window.location.href='adminAddCategory.php';</script>";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
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
    <h2>Add Category</h2>
    <form action="adminAddCategory.php" method="post" enctype="multipart/form-data">
        <label for="category_name">Product Name:</label>
        <input type="text" id="category_name" name="category_name" required>
        <button type="submit">Add Category</button>
    </form>
    </div>
</main>
</body>
</html>