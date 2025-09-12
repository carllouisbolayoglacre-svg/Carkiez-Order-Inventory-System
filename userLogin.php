<?php
include 'Includes/connection.php';
include 'Includes/auth.php';

session_start();
Check_login();

$login_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize inputs
    $username = trim($_POST['username']);
    $password = $_POST['password'] ?? '';

    $sql = "SELECT * FROM user WHERE username = ? OR email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Valid login → set session
        $_SESSION['user_logged_in'] = true;  // Set session status for user
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        header("Location: userLanding.php");
        exit;
    } else {
        $login_error = "❌ Invalid username/email or password!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<link rel="stylesheet" type="text/css" href="Style/UserSide.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<body>
<header>
    <div class="header-container">
        <div class="header-inside">
            <div class="header-logo"><img src="Assets/TemporaryLogo.png" alt="Logo" class="logo"></div>
        </div>
    </div>
</header>
<main>
    <div class="login-container-container">
        <div class="login-container">
            <h2>User Login</h2>
            <p>Welcome back!</p>
            <p>Please login to your account below.</p>
            <form action="userLogin.php" method="post">
                <label for="username">Username or Email:</label>
                <input type="text" id="username" name="username" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">Login</button>
            </form>
            <p>Don't have an account? <a href="userRegister.php">Sign Up</a></p>
        </div>
    </div>
</main>
<footer>
    <p>&copy; 2025 Carkiez.</p>
</footer>
</body>
</html>