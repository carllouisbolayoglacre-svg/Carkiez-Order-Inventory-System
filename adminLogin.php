<?php 
include 'Includes/connection.php';
include 'Includes/adminHeader.php';
include 'Includes/admin_auth.php';
session_start();
Check_login();
$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // Get and sanitize inputs
    $username = trim($_POST['username']);
    $password = $_POST['password'] ?? '';

    $sql = "SELECT * FROM admin WHERE username = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if ($admin && password_verify($password, $admin['password'])) {
        // Valid login → set session
        $_SESSION['admin_logged_in'] = true;  // Set session status for admin
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];

        header("Location: adminDashboard.php");
        exit;
    } else {
        $error = "❌ Invalid username/email or password!";
    }
}
?>
<main>
    <div class="login-container-container">
        <div class="login-container">
            <h2>Admin Login</h2>
            <p>Welcome back!</p>
            <p>Please login to your account below.</p>
            <form action="adminLogin.php" method="post">
                <label for="username">Username or Email:</label>
                <input type="text" id="username" name="username" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">Login</button>
            </form>
        </div>
    </div>
</main>
<footer>
    <p>&copy; 2025 Carkiez.</p>
</footer>
</body>
</html>