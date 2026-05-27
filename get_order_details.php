<?php
// =============================================
// get_order_details.php
// 3NF: order_items now includes pizza_id + variant_id
// =============================================

include "db_connect.php";

$order_id = (int)$_GET['order_id'];

// ── Get order info ────────────────────────────
$orderQuery  = "SELECT * FROM orders WHERE order_id = $order_id LIMIT 1";
$orderResult = $conn->query($orderQuery);
$order       = $orderResult->fetch_assoc();

// ── Get items (with snapshot + FK columns) ────
$itemsQuery  = "SELECT * FROM order_items WHERE order_id = $order_id";
$itemsResult = $conn->query($itemsQuery);

$items = [];
while ($row = $itemsResult->fetch_assoc()) {
    $items[] = $row;
}

// ── Return JSON ───────────────────────────────
echo json_encode([
    "order" => $order,
    "items" => $items
]);
?>
