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
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
<header>
    <div class="header-container">
        <div class="header-inside">
            <button class="nav-button"><img src="Assets/align-justify-svgrepo-com.png" alt="nav"></button>
            <div class="header-logo"><a href="index.php"><img src="Assets/TemporaryLogo.png" alt="Logo" class="logo"></a></div>
            <div class="header-search-bar-container">
                <form method="get" action="userProducts.php" class="product-search-form">
                    <div class="search-bar">
                        <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                        <button type="submit"><img src="Assets/search-svgrepo-com.png" alt="Search"></button>
                    </div>
                </form>
            </div>
            <div class="header-actions">
                <!-- User Dropdown -->
                <div class="user-dropdown">
                    <button class="user-button">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </button>
                    <div class="user-dropdown-content">
                        <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
                            <div class="dropdown-header">
                                <span class="user-name-header">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                            </div>
                            <a href="userProfile.php" class="dropdown-item">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                My Profile
                            </a>
                            <div class="dropdown-divider"></div>
                            <form method="post" style="margin: 0;">
                                <button type="submit" name="logout" class="dropdown-item logout-btn">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                        <polyline points="16 17 21 12 16 7"></polyline>
                                        <line x1="21" y1="12" x2="9" y2="12"></line>
                                    </svg>
                                    Logout
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="userLogin.php" class="dropdown-item">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                    <polyline points="10 17 15 12 10 7"></polyline>
                                    <line x1="15" y1="12" x2="3" y2="12"></line>
                                </svg>
                                Login
                            </a>
                            <a href="userRegister.php" class="dropdown-item">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="8.5" cy="7" r="4"></circle>
                                    <line x1="20" y1="8" x2="20" y2="14"></line>
                                    <line x1="23" y1="11" x2="17" y2="11"></line>
                                </svg>
                                Register
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Cart Button -->
                <a href="userCart.php">
                    <button class="cart-button">
                        <img src="Assets/cart-shopping-svgrepo-com.png" alt="cart">
                    </button>
                </a>
            </div>
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