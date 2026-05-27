<?php
// =============================================
// get_pizza_details.php
// 3NF: JOIN categories to return category_name
// =============================================

include "db_connect.php";

$id = (int)$_POST['pizza_id'];

// ── GET MAIN PIZZA (with category name via JOIN) ──
$pizzaQuery = $conn->query("
    SELECT p.pizza_id, p.pizza_name, c.category_name AS category,
           p.ingredients, p.image_path, p.stock
    FROM pizzas p
    JOIN categories c ON p.category_id = c.category_id
    WHERE p.pizza_id = $id
    LIMIT 1
");
$pizza = $pizzaQuery->fetch_assoc();

// ── GET VARIANTS ──────────────────────────────
$variantsQuery = $conn->query(
    "SELECT * FROM pizza_variants WHERE pizza_id = $id"
);

$prices = [];
while ($v = $variantsQuery->fetch_assoc()) {
    $key = $v['size'] . "_" . strtolower($v['cheese']);
    $prices[$key] = $v['price'];
}

// ── RETURN JSON ───────────────────────────────
echo json_encode([
    "pizza"  => $pizza,
    "prices" => $prices
]);
?>
