<aside>
    <nav>
        <div class="nav-spacer">
            <a href="staffDashboard.php">Dashboard</a>
        
        <div class="dropdown">
            <button class="dropbtn">Products 
                <span class="caret"></span>
            </button>
            <div class="dropdown-content">
                <a href="staffProducts.php">Manage Products</a>
            </div>
        </div>
        <div class="dropdown">
            <button class="dropbtn">Orders 
                <span class="caret"></span>
            </button>
            <div class="dropdown-content">
                <a href="staffOrders.php">View Orders</a>
            </div>
        </div>
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