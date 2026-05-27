<?php
// =============================================
// get_order_details.php (strict 3NF — uses views)
// =============================================

include "db_connect.php";

$order_id = (int)$_GET['order_id'];

$orderResult = $conn->query("SELECT * FROM v_orders_full WHERE order_id = $order_id LIMIT 1");
$order       = $orderResult->fetch_assoc();

$itemsResult = $conn->query("SELECT * FROM v_order_items_full WHERE order_id = $order_id");
$items = [];
while ($row = $itemsResult->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode(["order" => $order, "items" => $items]);
?>
