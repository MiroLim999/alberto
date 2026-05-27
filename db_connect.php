<?php
$host = "localhost";
$user = "root";
$password = "DouglasMcGee115";
$database = "albertos_pizza_db";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
  die("Database connection failed: " . $conn->connect_error);
}
?>