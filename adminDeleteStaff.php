<?php 
include 'Includes/connection.php';
include 'Includes/admin_auth.php';

session_start();
check_login_redirect();

$staff_id = intval($_GET['id'] ?? 0);

// Prevent deleting yourself (optional safety check)
if (isset($_SESSION['admin_id']) && $_SESSION['admin_id'] == $staff_id) {
    echo "<script>alert('You cannot delete your own staff account!'); window.location.href='adminStaff.php';</script>";
    exit();
}

// Delete staff member
$stmt = $conn->prepare("DELETE FROM staff WHERE staff_id = ?");
$stmt->bind_param("i", $staff_id);

if ($stmt->execute()) {
    echo "<script>alert('Staff member deleted successfully!'); window.location.href='adminStaff.php';</script>";
} else {
    echo "<script>alert('Error deleting staff member!'); window.location.href='adminStaff.php';</script>";
}

$stmt->close();
$conn->close();
?>