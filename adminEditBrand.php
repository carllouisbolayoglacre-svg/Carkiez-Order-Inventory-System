<?php 
include 'Includes/connection.php';
include 'Includes/admin_auth.php';

session_start();
check_login_redirect();
handle_logout();

$brand_id = intval($_GET['id'] ?? 0);

// Fetch brand data
$stmt = $conn->prepare("SELECT id, brand_name FROM brands WHERE id = ?");
$stmt->bind_param("i", $brand_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Brand not found!'); window.location.href='adminBrands.php';</script>";
    exit();
}

$brand = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $brand_name = trim($_POST['brand_name']);

    if (!empty($brand_name)) {
        // Check for duplicate brand names (excluding current brand)
        $check = $conn->prepare("SELECT id FROM brands WHERE brand_name = ? AND id != ?");
        $check->bind_param("si", $brand_name, $brand_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            echo "<script>alert('Brand name already exists!'); window.history.back();</script>";
        } else {
            // Update brand
            $update_stmt = $conn->prepare("UPDATE brands SET brand_name = ? WHERE id = ?");
            $update_stmt->bind_param("si", $brand_name, $brand_id);

            if ($update_stmt->execute()) {
                echo "<script>alert('Brand updated successfully!'); window.location.href='adminBrands.php';</script>";
            } else {
                echo "Error: " . $update_stmt->error;
            }
            $update_stmt->close();
        }
        $check->close();
    } else {
        echo "<script>alert('Please enter a brand name!'); window.history.back();</script>";
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
        <h2>Edit Brand</h2>
        <form action="adminEditBrand.php?id=<?php echo $brand_id; ?>" method="post">
            <label for="brand_name">Brand Name:</label>
            <input type="text" id="brand_name" name="brand_name" value="<?php echo htmlspecialchars($brand['brand_name']); ?>" required>
            <button type="submit">Update Brand</button>
            <a href="adminBrands.php" style="display: inline-block; margin-top: 1rem; text-align: center; padding: 0.75rem; border: 1px solid #ccc; border-radius: 4px; text-decoration: none; color: #333;">Cancel</a>
        </form>
    </div>
</main>
</body>
</html>