<?php
$host = 'localhost';
$username = 'root';  // Default in XAMPP is 'root'
$password = '';      // Default no password
$database = 'auto_supply_db'; // Your database name

$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>