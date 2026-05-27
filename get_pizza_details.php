<?php
include "db_connect.php";

$id = (int)$_POST['pizza_id'];

// Pizza + category name + comma-joined ingredients (no view)
$r = $conn->query("
    SELECT
        p.pizza_id, p.pizza_name, p.category_id,
        c.category_name AS category,
        p.image_path, p.stock,
        (SELECT GROUP_CONCAT(i.ingredient_name ORDER BY i.ingredient_name SEPARATOR ', ')
         FROM pizza_ingredients pi
         JOIN ingredients i ON pi.ingredient_id = i.ingredient_id
         WHERE pi.pizza_id = p.pizza_id) AS ingredients
    FROM pizzas p
    JOIN categories c ON p.category_id = c.category_id
    WHERE p.pizza_id = $id
    LIMIT 1
");
$pizza = $r->fetch_assoc();

// Variants
$vr = $conn->query("SELECT * FROM pizza_variants WHERE pizza_id = $id");
$prices = [];
while ($v = $vr->fetch_assoc()) {
    $key = $v['size'] . "_" . strtolower($v['cheese']);
    $prices[$key] = $v['price'];
}

echo json_encode(["pizza" => $pizza, "prices" => $prices]);
?>
