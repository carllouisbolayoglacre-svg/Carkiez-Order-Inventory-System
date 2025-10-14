<?php
if (isset($_GET['query'])) {
    $search = $conn->real_escape_string($_GET['query']);

    $sql = "SELECT * FROM products 
            WHERE title LIKE '%$search%' 
            OR description LIKE '%$search%'";
    
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<h3>Search Results:</h3>";
        while ($row = $result->fetch_assoc()) {
            echo "<div>";
            echo "<h4>" . htmlspecialchars($row['title']) . "</h4>";
            echo "<p>" . htmlspecialchars($row['description']) . "</p>";
            echo "<p>₱" . number_format($row['price'], 2) . "</p>";
            echo "</div><hr>";
        }
    } else {
        echo "<p>No results found.</p>";
    }
}
?>
