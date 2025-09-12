<?php
include 'Includes/connection.php';
include 'Includes/auth.php';
session_start();
Check_login();

// Process form on submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName   = mysqli_real_escape_string($conn, $_POST['firstname']);
    $middleName  = mysqli_real_escape_string($conn, $_POST['middlename']);
    $lastName    = mysqli_real_escape_string($conn, $_POST['lastname']);
    $birthdate   = mysqli_real_escape_string($conn, $_POST['birthdate']);
    $email       = mysqli_real_escape_string($conn, $_POST['email']);
    $username    = mysqli_real_escape_string($conn, $_POST['username']);
    $password    = mysqli_real_escape_string($conn, $_POST['password']);
    $confirmPass = mysqli_real_escape_string($conn, $_POST['confirm-password']);
    $contact_number = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    // Validate password match
    if ($password !== $confirmPass) {
        echo "<script>alert('Passwords do not match!'); window.location.href='userRegister.php';</script>";
        exit();
    }

    // Validate age is at least 16
    $birthDateObj = DateTime::createFromFormat('Y-m-d', $birthdate);
    $today = new DateTime();
    $age = $birthDateObj->diff($today)->y;

    if ($age < 16) {
        echo "<script>alert('You must be at least 16 years old to register.'); window.location.href='userRegister.php';</script>";
        exit();
    }

    // Check if username or email already exists
    $check_query = "SELECT * FROM user WHERE username='$username' OR email='$email'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>alert('Username or Email already exists!'); window.location.href='userRegister.php';</script>";
    } else {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert user
        $insert_query = "INSERT INTO user (first_name, middle_name, last_name, birthdate, email, username, password, contact_number, address) 
                         VALUES ('$firstName', '$middleName', '$lastName', '$birthdate', '$email', '$username', '$hashedPassword', '$contact_number', '$address')";

        if (mysqli_query($conn, $insert_query)) {
            echo "<script>alert('Registered successfully!'); window.location.href='userLogin.php';</script>";
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
<title>Register</title>
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
    <div class="register-container-container">
        <div class="register-container">
            <h2>User Registration</h2>
            <p>Create your account!</p>
            <p>Please fill in the details below to register.</p>
            <form action="userRegister.php" method="post">
                <div class="fullname-field">
                    <div class="name-field">
                        <label for="firstname">First Name:</label>
                        <input type="text" id="firstname" name="firstname" required>
                    </div>
                    <div class="name-field">
                        <label for="middlename">Middle Name:</label>
                        <input type="text" id="middlename" name="middlename" required>
                    </div>
                    <div class="name-field">
                        <label for="lastname">Last Name:</label>
                        <input type="text" id="lastname" name="lastname" required>
                    </div>
                </div>
                

                <label for="birthdate">Date of Birth</label>
                <input type="date" id="birthdate" name="birthdate" required>

                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" required>

                <label for="address">Address:</label>
                <input type="text" id="address" name="address" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <label for="confirm-password">Confirm Password:</label>
                <input type="password" id="confirm-password" name="confirm-password" required>

                <button type="submit">Register</button>
            </form>
            <p>Already have an account? <a href="userLogin.php">Login</a></p>
    </div>
</main>
<footer>
    <p>&copy; 2025 Carkiez.</p>
</footer>
</body>
</html>