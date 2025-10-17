<?php 
include 'Includes/connection.php';
include 'Includes/admin_auth.php';

session_start();
check_login_redirect();
handle_logout();

$success_message = '';
$error_message = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $staff_id = (int)$_GET['delete'];
    
    $delete_stmt = $conn->prepare("DELETE FROM staff WHERE staff_id = ?");
    $delete_stmt->bind_param("i", $staff_id);
    
    if ($delete_stmt->execute()) {
        $success_message = "Staff member deleted successfully!";
    } else {
        $error_message = "Error deleting staff member: " . $delete_stmt->error;
    }
    $delete_stmt->close();
}

// Fetch all staff members
$staff_query = "SELECT staff_id, username, first_name, middle_name, last_name, contact_number, address FROM staff ORDER BY staff_id DESC";
$staff_result = $conn->query($staff_query);
?>
<?php
include 'Includes/adminHeader.php';
include 'Includes/adminNav.php';
?>
<main>
    <div class="admin-products-container">
        <h2>Manage Staff Members</h2>
        
        <?php if (!empty($success_message)): ?>
            <div style="padding: 1rem; margin-bottom: 1rem; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 6px;">
                ✓ <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div style="padding: 1rem; margin-bottom: 1rem; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 6px;">
                ✗ <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <?php if ($staff_result && $staff_result->num_rows > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Staff ID</th>
                            <th>Username</th>
                            <th>First Name</th>
                            <th>Middle Name</th>
                            <th>Last Name</th>
                            <th>Contact Number</th>
                            <th>Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($staff = $staff_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($staff['staff_id']) ?></td>
                                <td><strong><?= htmlspecialchars($staff['username']) ?></strong></td>
                                <td><?= htmlspecialchars($staff['first_name']) ?></td>
                                <td><?= htmlspecialchars($staff['middle_name'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($staff['last_name']) ?></td>
                                <td><?= htmlspecialchars($staff['contact_number'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($staff['address'] ?: '-') ?></td>
                                <td>
                                    <a href="adminEditProduct.php?id=' . intval($row['product_id']) . '">Edit</a> | 
                                    <a href="adminDeleteProduct.php?id=' . intval($row['product_id']) . '" onclick="return confirm(\'Are you sure you want to delete this product?\');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: #666; padding: 2rem;">No staff members found. <a href="adminAddStaff.php" style="color: #FF1E00; font-weight: bold;">Add your first staff member</a>.</p>
        <?php endif; ?>
    </div>
</main>
</body>
</html>