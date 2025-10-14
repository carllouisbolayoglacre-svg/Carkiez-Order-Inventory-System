<?php
include_once 'Includes/connection.php';
include 'Includes/auth.php';
session_start();
handle_logout();
?>
<!DOCTYPE html>
<html>
<head>
<title>Carkiez Store</title>
<link rel="stylesheet" type="text/css" href="Style/UserSide.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<body>
<header>
    <div class="login-bar">
        <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            <form method="post" style="display:inline;">
                <button type="submit" name="logout">Logout</button>
            </form>
        <?php else: ?>
            <a href="userLogin.php">Login</a>
            <p>|</p>
            <a href="userRegister.php">Sign Up</a>
        <?php endif; ?>
    </div>
    <div class="header-container">
        <div class="header-inside">
            <button class="nav-button"><img src="Assets/align-justify-svgrepo-com.png" alt="nav"></button>
            <div class="header-logo"><img src="Assets/TemporaryLogo.png" alt="Logo" class="logo"></div>
            <div class="header-search-bar-container">
                <form method="get" action="userProducts.php" class="product-search-form">
                    <div class="search-bar">
                        <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                        <button type="submit"><img src="Assets/search-svgrepo-com.png" alt="Search"></button>
                    </div>
                </form>
            </div>
            <a href="userCart.php">
                <button class="cart-button">
                    <img src="Assets/cart-shopping-svgrepo-com.png" alt="nav">
                </button>
            </a>
        </div>
    </div>
    <div class="responsive-search-bar-container">
        <form method="get" action="userProducts.php" class="product-search-form">
            <div class="search-bar">
                <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <button type="submit"><img src="Assets/search-svgrepo-com.png" alt="Search"></button>
            </div>
        </form>
    </div>
    <nav>
        <div class="nav-spacer">
            <a href="index.php">Home</a>
            <div class="dropdown">
                <button class="dropbtn">Categories 
                <span class="caret"></span></i>
                </button>
                    <div class="dropdown-content">
                    <?php
                        $sql = "SELECT id, category_name FROM categories";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo '<a href="userProducts.php?category=' . intval($row['id']) . '">' . htmlspecialchars($row['category_name']) . '</a>';
                            }
                        } else {
                            echo '<a href="#">No Categories</a>';
                        }
                    ?>
                </div>
            </div>
             <div class="dropdown">
                <button class="dropbtn">Brands 
                <span class="caret"></span></i>
                </button>
                    <div class="dropdown-content">
                    <?php
                        $sql = "SELECT id, brand_name FROM brands";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo '<a href="userProducts.php?brand=' . intval($row['id']) . '">' . htmlspecialchars($row['brand_name']) . '</a>';
                            }
                        } else {
                            echo '<a href="#">No Brands</a>';
                        }
                    ?>
                </div>
            </div>
            <a href="userProducts.php">All Products</a>
        </div>
    </nav>
    <!-- Collapsible mobile nav -->
    <div id="mobile-nav" class="mobile-nav">
        <a href="index.php">Home</a>
        <div class="dropdown-mobile">
        <button onclick="toggleDropdown('mobileCategoryDropdown')" class="dropbtn">Categories<span class="caret"></span></button>
            <div id="mobileCategoryDropdown" class="dropdown-content">
                <?php
                    $sql = "SELECT id, category_name FROM categories";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                        echo '<a href="userProducts.php?category=' . intval($row['id']) . '">' . htmlspecialchars($row['category_name']) . '</a>';
                        }
                    } else {
                        echo '<a href="#">No Categories</a>';
                    }
                ?>
            </div>
        </div>
        <div class="dropdown-mobile">
        <button onclick="toggleDropdown('mobileBrandDropdown')" class="dropbtn">Brands<span class="caret"></span></button>
            <div id="mobileBrandDropdown" class="dropdown-content">
                <?php
                    $sql = "SELECT id, brand_name FROM brands";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo '<a href="userProducts.php?brand=' . intval($row['id']) . '">' . htmlspecialchars($row['brand_name']) . '</a>';
                        }
                    } else {
                        echo '<a href="#">No Brands</a>';
                    }
                ?>
            </div>
        </div>
        <a href="userProducts.php">All Products</a>
    </div>
    <script>
        // Toggle mobile nav
    document.addEventListener('DOMContentLoaded', function() {
        const navBtn = document.querySelector('.nav-button');
        const mobileNav = document.getElementById('mobile-nav');
        navBtn.addEventListener('click', function() {
            mobileNav.classList.toggle('show');
        });
    });

    // Toggle specific mobile dropdown
    function toggleDropdown(id) {
        var dropdown = document.getElementById(id);
        dropdown.classList.toggle('show');
        // Close other dropdowns
        document.querySelectorAll('.dropdown-mobile .dropdown-content').forEach(function(el) {
            if (el.id !== id) el.classList.remove('show');
        });
    }

    // Close dropdowns when clicking outside
    window.onclick = function(event) {
        if (!event.target.matches('.dropbtn')) {
            document.querySelectorAll('.dropdown-mobile .dropdown-content').forEach(function(el) {
                el.classList.remove('show');
            });
        }
    }
    </script>
</header>