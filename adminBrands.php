<?php
include 'Includes/admin_auth.php';
include 'Includes/connection.php';

session_start();
check_login_redirect();
handle_logout();
?>
<?php
include 'Includes/adminHeader.php';
include 'Includes/adminNav.php';
?>
<main>
    <div class="admin-products-container">
        <h2>Brands</h2>
            <table>
                <thead>
                    <tr>
                        <th>Brand ID</th>
                        <th>Brand Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT id, Brand_name FROM Brands";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . intval($row['id']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['Brand_name']) . '</td>';
                            echo '<td>
                                    <a href="adminEditBrand.php?id=' . intval($row['id']) . '">Edit</a> | 
                                    <a href="adminDeleteBrand.php?id=' . intval($row['id']) . '" onclick="return confirm(\'Are you sure you want to delete this Brand?\');">Delete</a>
                                  </td>';
                            echo '</tr>';
                        }
                    } else {
                        echo "<tr><td colspan='3'>No Brands available.</td></tr>";
                    }
                    ?>
                    </tr>
                </tbody>
            </table>
    </div>
</main>
</body>
</html>