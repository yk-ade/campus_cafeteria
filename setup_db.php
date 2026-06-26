<?php
$host = "localhost";
$username = "root";
$password = "";

// First, connect without specifying a database
$conn = new mysqli($host, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Read the SQL file
$sql = file_get_contents(__DIR__ . '/database/rectem_restaurant.sql');

// Execute each statement
if ($conn->multi_query($sql)) {
    do {
        // Store first result set
        if ($result = $conn->store_result()) {
            $result->free();
        }
        // Check for next result
        if (!$conn->more_results()) break;
    } while ($conn->next_result());
    
    echo "<h2 style='color: green;'>✓ Database setup completed successfully!</h2>";
    echo "<p>Database 'rectem_restaurant_db' has been created with all tables.</p>";
} else {
    echo "<h2 style='color: red;'>✗ Error setting up database</h2>";
    echo "<p>" . $conn->error . "</p>";
}

$conn->close();

// Verify connection works
echo "<h3>Verifying connection...</h3>";
$conn2 = new mysqli($host, $username, $password, "rectem_restaurant_db");
if (!$conn2->connect_error) {
    echo "<p style='color: green;'>✓ Connection to rectem_restaurant_db successful!</p>";
} else {
    echo "<p style='color: red;'>✗ " . $conn2->connect_error . "</p>";
}
$conn2->close();
?>
