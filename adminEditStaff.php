<?php 
include 'Includes/connection.php';
include 'Includes/admin_auth.php';

session_start();
check_login_redirect();
handle_logout();

$staff_id = intval($_GET['id'] ?? 0);

// Fetch staff data
$stmt = $conn->prepare("SELECT * FROM staff WHERE staff_id = ?");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Staff member not found!'); window.location.href='adminStaff.php';</script>";
    exit();
}

$staff = $result->fetch_assoc();
$stmt->close();

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $contact_number = trim($_POST['contact_number']);
    $address = trim($_POST['address']);
    $new_password = trim($_POST['password'] ?? '');

    // Validation
    if (empty($username) || empty($first_name) || empty($last_name)) {
        $error_message = "Username, first name, and last name are required.";
    } else {
        // Check if username already exists (excluding current staff)
        $check_stmt = $conn->prepare("SELECT staff_id FROM staff WHERE username = ? AND staff_id != ?");
        $check_stmt->bind_param("si", $username, $staff_id);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $error_message = "Username already exists. Please choose a different username.";
        } else {
            if (!empty($new_password)) {
                if (strlen($new_password) < 6) {
                    $error_message = "Password must be at least 6 characters long.";
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_stmt = $conn->prepare("UPDATE staff SET username = ?, password = ?, first_name = ?, middle_name = ?, last_name = ?, contact_number = ?, address = ? WHERE staff_id = ?");
                    $update_stmt->bind_param("sssssssi", $username, $hashed_password, $first_name, $middle_name, $last_name, $contact_number, $address, $staff_id);

                    if ($update_stmt->execute()) {
                        echo "<script>alert('Staff member updated successfully!'); window.location.href='adminStaff.php';</script>";
                        exit();
                    } else {
                        $error_message = "Error: " . $update_stmt->error;
                    }
                    $update_stmt->close();
                }
            } else {
                // Update without changing password
                $update_stmt = $conn->prepare("UPDATE staff SET username = ?, first_name = ?, middle_name = ?, last_name = ?, contact_number = ?, address = ? WHERE staff_id = ?");
                $update_stmt->bind_param("ssssssi", $username, $first_name, $middle_name, $last_name, $contact_number, $address, $staff_id);

                if ($update_stmt->execute()) {
                    echo "<script>alert('Staff member updated successfully!'); window.location.href='adminStaff.php';</script>";
                    exit();
                } else {
                    $error_message = "Error: " . $update_stmt->error;
                }
                $update_stmt->close();
            }
        }
        $check_stmt->close();
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
        <h2>Edit Staff Member</h2>
        
        <?php if (!empty($error_message)): ?>
            <div style="padding: 1rem; margin-bottom: 1rem; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 6px;">
                ✗ <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <form action="adminEditStaff.php?id=<?php echo $staff_id; ?>" method="post">
            
            <label for="username">Username: <span style="color: red;">*</span></label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($staff['username']); ?>" maxlength="50" required>

            <label for="password">New Password: (Leave empty to keep current password)</label>
            <input type="password" id="password" name="password" minlength="6" maxlength="255" placeholder="Leave blank to keep current password">

            <div class="form-group">
                <div class="form-field">
                    <label for="first_name">First Name: <span style="color: red;">*</span></label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($staff['first_name']); ?>" maxlength="50" required>
                </div>
                <div class="form-field">
                    <label for="middle_name">Middle Name:</label>
                    <input type="text" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($staff['middle_name']); ?>" maxlength="50">
                </div>
            </div>

            <label for="last_name">Last Name: <span style="color: red;">*</span></label>
            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($staff['last_name']); ?>" maxlength="50" required>

            <label for="contact_number">Contact Number:</label>
            <input type="text" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($staff['contact_number']); ?>" maxlength="20">

            <label for="address">Address:</label>
            <textarea id="address" name="address" maxlength="255" rows="3" placeholder="Enter complete address"><?php echo htmlspecialchars($staff['address']); ?></textarea>

            <button type="submit">Update Staff Member</button>
            <a href="adminStaff.php" style="display: inline-block; margin-top: 1rem; text-align: center; padding: 0.75rem; border: 1px solid #ccc; border-radius: 4px; text-decoration: none; color: #333;">Cancel</a>
        </form>
    </div>
</main>
</body>
</html>