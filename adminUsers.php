<?php
include 'Includes/admin_auth.php';
include 'Includes/connection.php';

session_start();
check_login_redirect();
handle_logout();

$search = trim($_GET['search'] ?? '');
$sort_by = $_GET['sort'] ?? 'id_asc';

$where = [];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = '(username LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR contact_number LIKE ?)';
    $search_param = "%$search%";
    $params = array_fill(0, 5, $search_param);
    $types = str_repeat('s', 5);
}

$order_by = "user_id ASC";
switch ($sort_by) {
    case 'id_desc': $order_by = "user_id DESC"; break;
    case 'username_asc': $order_by = "username ASC"; break;
    case 'username_desc': $order_by = "username DESC"; break;
    case 'name_asc': $order_by = "first_name ASC, last_name ASC"; break;
    case 'name_desc': $order_by = "first_name DESC, last_name DESC"; break;
}

$sql = "SELECT user_id, username, first_name, last_name, email, contact_number FROM user";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY $order_by";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

include "Includes/adminHeader.php";
include "Includes/adminNav.php";
?>

<main>
    <div class="admin-products-container">
        <h2>All Users</h2>
        <form method="get" action="adminUsers.php" class="filter-controls" style="margin-bottom:1rem;">
            <div class="search-box">
                <input type="text" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="sort-box">
                <label for="sort">Sort by:</label>
                <select name="sort" id="sort">
                    <option value="id_asc" <?php if($sort_by=='id_asc')echo'selected';?>>ID (Asc)</option>
                    <option value="id_desc" <?php if($sort_by=='id_desc')echo'selected';?>>ID (Desc)</option>
                    <option value="username_asc" <?php if($sort_by=='username_asc')echo'selected';?>>Username (A-Z)</option>
                    <option value="username_desc" <?php if($sort_by=='username_desc')echo'selected';?>>Username (Z-A)</option>
                    <option value="name_asc" <?php if($sort_by=='name_asc')echo'selected';?>>Name (A-Z)</option>
                    <option value="name_desc" <?php if($sort_by=='name_desc')echo'selected';?>>Name (Z-A)</option>
                </select>
            </div>
            <button type="submit" class="filter-btn">Apply</button>
            <a href="adminUsers.php" class="clear-btn">Clear</a>
        </form>
        <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse;">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Contact Number</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['first_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['last_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['contact_number']) . "</td>";
                        echo '<td>
                                <a href="adminEditUser.php?id=' . intval($row['user_id']) . '">Edit</a> | 
                                <a href="adminDeleteUser.php?id=' . intval($row['user_id']) . '" onclick="return confirm(\'Are you sure you want to delete this category?\');">Delete</a>
                            </td>';
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No users found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</main>