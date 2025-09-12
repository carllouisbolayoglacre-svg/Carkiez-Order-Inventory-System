<?php
//Check if logged in
function check_login_redirect() {
    // Check if admin is logged in; if not, redirect to admin login page.
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo "<script>alert('You are not logged in!'); window.location.href = 'adminLogin.php';</script>";
    exit();
    }
}
// Handle logout request
function handle_logout() {
    if (isset($_POST['logout'])) {
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session

    // Redirect to the login page
    header("Location: adminLogin.php");
    exit();
    }
}
// Check if the admin is already logged in
function Check_login(){
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    echo "<script>alert('Admin is already logged in!'); window.location.href = 'adminDashboard.php';</script>";
    exit();
    }
}
?>