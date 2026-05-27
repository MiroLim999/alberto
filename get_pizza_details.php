<?php
include "db_connect.php";

$id = $_POST['pizza_id'];

// ✅ GET MAIN PIZZA
$pizzaQuery = $conn->query("SELECT * FROM pizzas WHERE pizza_id='$id'");
$pizza = $pizzaQuery->fetch_assoc();

// ✅ GET VARIANTS
$variantsQuery = $conn->query("SELECT * FROM pizza_variants WHERE pizza_id='$id'");

$prices = [];

while ($v = $variantsQuery->fetch_assoc()) {
    $key = $v['size'] . "_" . strtolower($v['cheese']);
    $prices[$key] = $v['price'];
}

// ✅ RETURN JSON
echo json_encode([
    "pizza" => $pizza,
    "prices" => $prices
]);
?>