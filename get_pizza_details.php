<?php
// =============================================
// get_pizza_details.php
// Strict 3NF: ingredients pulled from junction → comma list
// =============================================

include "db_connect.php";

$id = (int)$_POST['pizza_id'];

// Pizza + category + comma-joined ingredients
$pizzaQuery = $conn->query("
    SELECT pizza_id, pizza_name, category_id, category, image_path, stock, ingredients
    FROM v_pizzas_full
    WHERE pizza_id = $id
    LIMIT 1
");
$pizza = $pizzaQuery->fetch_assoc();

// Variants
$variantsQuery = $conn->query(
    "SELECT * FROM pizza_variants WHERE pizza_id = $id"
);
$prices = [];
while ($v = $variantsQuery->fetch_assoc()) {
    $key = $v['size'] . "_" . strtolower($v['cheese']);
    $prices[$key] = $v['price'];
}

echo json_encode([
    "pizza"  => $pizza,
    "prices" => $prices
]);
?>
