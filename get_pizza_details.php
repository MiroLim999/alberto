<?php
session_start();
include "db_connect.php";

// ── Auth: admin only (used in admin product modal) ──
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(["error" => "forbidden"]);
    exit;
}

$id = intval($_POST['pizza_id'] ?? 0);
if (!$id) {
    echo json_encode(["error" => "invalid id"]);
    exit;
}

// Pizza + category name + comma-joined ingredients (prepared)
$stmt = $conn->prepare("
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
    WHERE p.pizza_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $id);
$stmt->execute();
$pizza = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Variants
$stmt = $conn->prepare("SELECT * FROM pizza_variants WHERE pizza_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$vr = $stmt->get_result();
$prices = [];
while ($v = $vr->fetch_assoc()) {
    $key = $v['size'] . "_" . strtolower($v['cheese']);
    $prices[$key] = $v['price'];
}
$stmt->close();

echo json_encode(["pizza" => $pizza, "prices" => $prices]);
?>
