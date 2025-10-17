<?php 
include 'Includes/connection.php';
include 'Includes/admin_auth.php';

session_start();
check_login_redirect();
handle_logout();

$success_message = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $contact_number = trim($_POST['contact_number']);
    $address = trim($_POST['address']);

    // Validation
    if (empty($username) || empty($password) || empty($first_name) || empty($last_name)) {
        $error_message = "Username, password, first name, and last name are required.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } else {
        // Check if username already exists
        $check_stmt = $conn->prepare("SELECT staff_id FROM staff WHERE username = ?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $error_message = "Username already exists. Please choose a different username.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new staff member
            $stmt = $conn->prepare("INSERT INTO staff 
                (username, password, first_name, middle_name, last_name, contact_number, address) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $username, $hashed_password, $first_name, $middle_name, $last_name, $contact_number, $address);

            if ($stmt->execute()) {
                $success_message = "Staff member added successfully!";
                // Clear form fields
                $username = $first_name = $middle_name = $last_name = $contact_number = $address = '';
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}
?>
<?php
include 'Includes/adminHeader.php';
include 'Includes/adminNav.php';
?>
<main>
    <div class="input-product-container">
        <h2>Add Staff Member</h2>

        <form action="adminAddStaff.php" method="post">
            
            <label for="username">Username: <span style="color: red;">*</span></label>
            <input type="text" id="username" name="username" value="<?= isset($username) ? htmlspecialchars($username) : '' ?>" maxlength="50" required>

            <label for="password">Password: <span style="color: red;">*</span></label>
            <input type="password" id="password" name="password" minlength="6" maxlength="255" required>

            <div class="form-group">
                <div class="form-field">
                    <label for="first_name">First Name: <span style="color: red;">*</span></label>
                    <input type="text" id="first_name" name="first_name" value="<?= isset($first_name) ? htmlspecialchars($first_name) : '' ?>" maxlength="50" required>
                </div>
                <div class="form-field">
                    <label for="middle_name">Middle Name:</label>
                    <input type="text" id="middle_name" name="middle_name" value="<?= isset($middle_name) ? htmlspecialchars($middle_name) : '' ?>" maxlength="50">
                </div>
            </div>

            <label for="last_name">Last Name: <span style="color: red;">*</span></label>
            <input type="text" id="last_name" name="last_name" value="<?= isset($last_name) ? htmlspecialchars($last_name) : '' ?>" maxlength="50" required>

            <label for="contact_number">Contact Number:</label>
            <input type="text" id="contact_number" name="contact_number" value="<?= isset($contact_number) ? htmlspecialchars($contact_number) : '' ?>" maxlength="20">

            <label for="address">Address:</label>
            <textarea id="address" name="address" maxlength="255" rows="3" placeholder="Enter complete address"><?= isset($address) ? htmlspecialchars($address) : '' ?></textarea>

            <button type="submit">Add Staff Member</button>
        </form>
    </div>
</main>
</body>
</html>