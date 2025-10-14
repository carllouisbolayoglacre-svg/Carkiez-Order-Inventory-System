<?php 
include 'Includes/connection.php';
include 'Includes/admin_auth.php';

session_start();
check_login_redirect();
handle_logout();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $brand_name = trim($_POST['brand_name']);

    if (!empty($brand_name)) {
        // Prevent duplicate brand names
        $check = $conn->prepare("SELECT id FROM brands WHERE brand_name = ?");
        $check->bind_param("s", $brand_name);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            echo "<script>alert('brand already exists!'); window.history.back();</script>";
        } else {
            // Insert new brand
            $stmt = $conn->prepare("INSERT INTO brands (brand_name) VALUES (?)");
            $stmt->bind_param("s", $brand_name);

            if ($stmt->execute()) {
                echo "<script>alert('Brand added successfully!'); window.location.href='adminAddBrand.php';</script>";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
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
    <h2>Add Brand</h2>
    <form action="adminAddBrand.php" method="post" enctype="multipart/form-data">
        <label for="brand_name">Brand Name:</label>
        <input type="text" id="brand_name" name="brand_name" required>
        <button type="submit">Add Brand</button>
    </form>
    </div>
</main>
</body>
</html>