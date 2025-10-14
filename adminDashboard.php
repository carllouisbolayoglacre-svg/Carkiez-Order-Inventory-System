<?php 

include 'Includes/admin_auth.php';

session_start();
check_login_redirect();
handle_logout();
?>
<?php
include 'Includes/adminHeader.php';
include 'Includes/adminNav.php';
?>
<main>
    <div class="dashboard-container">
        <h1>Admin Dashboard</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</p>
    </div>
</body>
</html>