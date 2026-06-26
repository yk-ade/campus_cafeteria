<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "rectem_restaurant_db";

$conn = new mysqli('localhost', 'root', '', 'rectem_restaurant_db');

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>