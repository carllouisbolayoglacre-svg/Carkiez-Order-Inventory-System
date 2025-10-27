<?php 
include 'Includes/connection.php';
include 'Includes/admin_auth.php';

session_start();
check_login_redirect();

$category_id = intval($_GET['id'] ?? 0);

$check_stmt = $conn->prepare("SELECT COUNT(*) as product_count FROM products WHERE category_id = ?");
$check_stmt->bind_param("i", $category_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$check_data = $check_result->fetch_assoc();
$check_stmt->close();

if ($check_data['product_count'] > 0) {
    echo "<script>alert('Cannot delete category! There are {$check_data['product_count']} products associated with this category.'); window.location.href='adminCategories.php';</script>";
    exit();
}

// Delete category
$stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
$stmt->bind_param("i", $category_id);

if ($stmt->execute()) {
    echo "<script>alert('category deleted successfully!'); window.location.href='adminCategories.php';</script>";
} else {
    echo "<script>alert('Error deleting category!'); window.location.href='adminCategories.php';</script>";
}

$stmt->close();
$conn->close();
?>