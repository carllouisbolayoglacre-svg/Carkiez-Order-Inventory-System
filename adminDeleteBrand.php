<?php 
include 'Includes/connection.php';
include 'Includes/admin_auth.php';

session_start();
check_login_redirect();

$brand_id = intval($_GET['id'] ?? 0);

// Check if brand has associated products
$check_stmt = $conn->prepare("SELECT COUNT(*) as product_count FROM products WHERE brand_id = ?");
$check_stmt->bind_param("i", $brand_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$check_data = $check_result->fetch_assoc();
$check_stmt->close();

if ($check_data['product_count'] > 0) {
    echo "<script>alert('Cannot delete brand! There are {$check_data['product_count']} products associated with this brand.'); window.location.href='adminBrands.php';</script>";
    exit();
}

// Delete brand
$stmt = $conn->prepare("DELETE FROM brands WHERE id = ?");
$stmt->bind_param("i", $brand_id);

if ($stmt->execute()) {
    echo "<script>alert('Brand deleted successfully!'); window.location.href='adminBrands.php';</script>";
} else {
    echo "<script>alert('Error deleting brand!'); window.location.href='adminBrands.php';</script>";
}

$stmt->close();
$conn->close();
?>