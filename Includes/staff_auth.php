<?php
//Check if logged in
function check_login_redirect() {
    // Check if staff is logged in; if not, redirect to staff login page.
    if (!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true) {
    echo "<script>alert('You are not logged in!'); window.location.href = 'staffLogin.php';</script>";
    exit();
    }
}
// Handle logout request
function handle_logout() {
    if (isset($_POST['logout'])) {
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session

    // Redirect to the login page
    header("Location: staffLogin.php");
    exit();
    }
}
// Check if the staff is already logged in
function Check_login(){
    if (isset($_SESSION['staff_logged_in']) && $_SESSION['staff_logged_in'] === true) {
    echo "<script>alert('Admin is already logged in!'); window.location.href = 'staffDashboard.php';</script>";
    exit();
    }
}
?>