<aside>
    <nav>
        <div class="nav-spacer">
            <a href="adminDashboard.php">Dashboard</a>
        
        <div class="dropdown">
            <button class="dropbtn">Products 
                <span class="caret"></span>
            </button>
            <div class="dropdown-content">
                <a href="adminAddProduct.php">Add Product</a>
                <a href="adminProducts.php">Manage Products</a>
                <a href="adminAddCategory.php">Add Category</a>
                <a href="adminCategories.php">Manage Categories</a>
                <a href="adminAddBrand.php">Add Brand</a>
                <a href="adminBrands.php">Manage Brands</a>
            </div>
        </div>
        <div class="dropdown">
            <button class="dropbtn">Orders 
                <span class="caret"></span>
            </button>
            <div class="dropdown-content">
                <a href="adminOrders.php">View Orders</a>
            </div>
        </div>
        <div class="dropdown">
            <button class="dropbtn">Users 
                <span class="caret"></span>
            </button>
            <div class="dropdown-content">
                <a href="adminUsers.php">View Users</a>
            </div>
        </div>
        <div class="dropdown">
            <button class="dropbtn">Staff 
                 <span class="caret"></span>
            </button>
            <div class="dropdown-content">
                <a href="adminAddStaff.php">Add Staff</a>
                <a href="adminStaff.php">Manage Staff</a>
            </div>
        </div>
        <a href="adminSettings.php">Settings</a>
        <form method="post" style="display:inline;">
            <button class="dropbtn" type="submit" name="logout">Logout</button>
        </form>
        </div>  
    </nav>
</aside>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(function(dropdown) {
        const btn = dropdown.querySelector('.dropbtn');
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            // Close other open dropdowns
            dropdowns.forEach(function(d) {
                if (d !== dropdown) d.classList.remove('open');
            });
            // Toggle current dropdown only
            dropdown.classList.toggle('open');
        });
    });
});
</script>