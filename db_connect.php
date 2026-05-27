<?php
$host     = "localhost";
$user     = "root";
$password = "miro";
$database = "albertos_pizza_db_3nf";   // 3NF normalized database

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
