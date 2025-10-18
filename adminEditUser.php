<?php 
include 'Includes/connection.php';
include 'Includes/admin_auth.php';

session_start();
check_login_redirect();
handle_logout();

$user_id = intval($_GET['id'] ?? 0);

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM user WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('User not found!'); window.location.href='adminUsers.php';</script>";
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $birthdate = trim($_POST['birthdate']);
    $contact_number = trim($_POST['contact_number']);
    $address = trim($_POST['address']);
    $new_password = trim($_POST['password'] ?? '');

    // Validation
    if (empty($username) || empty($email) || empty($first_name) || empty($last_name) || empty($birthdate)) {
        $error_message = "Username, email, first name, last name, and birthdate are required.";
    } else {
        // Validate age is at least 16
        $birthDateObj = DateTime::createFromFormat('Y-m-d', $birthdate);
        $today = new DateTime();
        $age = $birthDateObj->diff($today)->y;

        if ($age < 16) {
            $error_message = "User must be at least 16 years old.";
        } else {
            // Check if username or email already exists (excluding current user)
            $check_stmt = $conn->prepare("SELECT user_id FROM user WHERE (username = ? OR email = ?) AND user_id != ?");
            $check_stmt->bind_param("ssi", $username, $email, $user_id);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows > 0) {
                $error_message = "Username or email already exists. Please choose different ones.";
            } else {
                // Update user - with or without password change
                if (!empty($new_password)) {
                    if (strlen($new_password) < 6) {
                        $error_message = "Password must be at least 6 characters long.";
                    } else {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $update_stmt = $conn->prepare("UPDATE user SET username = ?, email = ?, password = ?, first_name = ?, middle_name = ?, last_name = ?, birthdate = ?, contact_number = ?, address = ? WHERE user_id = ?");
                        $update_stmt->bind_param("sssssssssi", $username, $email, $hashed_password, $first_name, $middle_name, $last_name, $birthdate, $contact_number, $address, $user_id);
                    }
                } else {
                    // Update without changing password
                    $update_stmt = $conn->prepare("UPDATE user SET username = ?, email = ?, first_name = ?, middle_name = ?, last_name = ?, birthdate = ?, contact_number = ?, address = ? WHERE user_id = ?");
                    $update_stmt->bind_param("ssssssssi", $username, $email, $first_name, $middle_name, $last_name, $birthdate, $contact_number, $address, $user_id);
                }

                if (!isset($error_message) && $update_stmt->execute()) {
                    echo "<script>alert('User updated successfully!'); window.location.href='adminUsers.php';</script>";
                    exit();
                } else {
                    $error_message = "Error: " . ($update_stmt->error ?? 'Unknown error');
                }
                if (isset($update_stmt)) $update_stmt->close();
            }
            $check_stmt->close();
        }
    }
}

$conn->close();
?>

<?php
include 'Includes/adminHeader.php';
include 'Includes/adminNav.php';
?>
<main>
    <div class="input-product-container">
        <h2>Edit User</h2>
        
        <?php if (!empty($error_message)): ?>
            <div style="padding: 1rem; margin-bottom: 1rem; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 6px;">
                ✗ <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <form action="adminEditUser.php?id=<?php echo $user_id; ?>" method="post">
            
            <label for="username">Username: <span style="color: red;">*</span></label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" maxlength="50" required>

            <label for="email">Email: <span style="color: red;">*</span></label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" maxlength="100" required>

            <label for="password">New Password: (Leave empty to keep current password)</label>
            <input type="password" id="password" name="password" minlength="6" maxlength="255" placeholder="Leave blank to keep current password">

            <div class="form-group">
                <div class="form-field">
                    <label for="first_name">First Name: <span style="color: red;">*</span></label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" maxlength="50" required>
                </div>
                <div class="form-field">
                    <label for="middle_name">Middle Name:</label>
                    <input type="text" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($user['middle_name']); ?>" maxlength="50">
                </div>
            </div>

            <label for="last_name">Last Name: <span style="color: red;">*</span></label>
            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" maxlength="50" required>

            <label for="birthdate">Date of Birth: <span style="color: red;">*</span></label>
            <input type="date" id="birthdate" name="birthdate" value="<?php echo htmlspecialchars($user['birthdate']); ?>" required>

            <label for="contact_number">Contact Number:</label>
            <input type="text" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number']); ?>" maxlength="20">

            <label for="address">Address:</label>
            <textarea id="address" name="address" maxlength="255" rows="3" placeholder="Enter complete address"><?php echo htmlspecialchars($user['address']); ?></textarea>

            <button type="submit">Update User</button>
            <a href="adminUsers.php" style="display: inline-block; margin-top: 1rem; text-align: center; padding: 0.75rem; border: 1px solid #ccc; border-radius: 4px; text-decoration: none; color: #333;">Cancel</a>
        </form>
    </div>
</main>
</body>
</html>