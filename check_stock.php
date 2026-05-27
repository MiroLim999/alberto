<?php
// =============================================
// check_stock.php — strict 3NF
// =============================================

include "db_connect.php";

$order_id = intval($_GET['order_id'] ?? 0);
if (!$order_id) {
    echo json_encode(["status" => "error", "message" => "Invalid order ID"]);
    exit;
}

// Pull from v_orders_full so we still get customer_name/total_amount
$orderResult = $conn->query("SELECT * FROM v_orders_full WHERE order_id = $order_id LIMIT 1");
if (!$orderResult || $orderResult->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Order not found"]);
    exit;
}
$order = $orderResult->fetch_assoc();

// Items via view (gives pizza_name, size, cheese, price, total)
$itemsResult = $conn->query("SELECT * FROM v_order_items_full WHERE order_id = $order_id");

$items      = [];
$outOfStock = [];

while ($item = $itemsResult->fetch_assoc()) {
    $pid = (int)$item['pizza_id'];
    $stockRow = $conn->query("SELECT stock FROM pizzas WHERE pizza_id = $pid LIMIT 1")->fetch_assoc();
    $stock    = $stockRow ? intval($stockRow['stock']) : 0;

    $item['current_stock'] = $stock;
    $items[] = $item;

    if ($stock < intval($item['quantity'])) {
        $outOfStock[] = [
            "pizza_name"    => $item['pizza_name'],
            "size"          => $item['size'],
            "cheese"        => $item['cheese'],
            "quantity"      => $item['quantity'],
            "current_stock" => $stock,
        ];
    }
}

$conn->close();

echo json_encode([
    "status"       => count($outOfStock) === 0 ? "ok" : "out_of_stock",
    "order"        => $order,
    "items"        => $items,
    "out_of_stock" => $outOfStock,
]);
?>
