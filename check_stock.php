<?php
// =============================================
// check_stock.php — Check stock for a pending order
// =============================================

include "db_connect.php";

$order_id = intval($_GET['order_id'] ?? 0);

if (!$order_id) {
    echo json_encode(["status" => "error", "message" => "Invalid order ID"]);
    exit;
}

// ── 1. GET ORDER HEADER ──────────────────────
$orderResult = $conn->query(
    "SELECT * FROM orders WHERE order_id = $order_id LIMIT 1"
);

if (!$orderResult || $orderResult->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Order not found"]);
    exit;
}

$order = $orderResult->fetch_assoc();

// ── 2. GET ORDER ITEMS ───────────────────────
$itemsResult = $conn->query(
    "SELECT * FROM order_items WHERE order_id = $order_id"
);

$items        = [];
$outOfStock   = [];   // items that can't be fulfilled

while ($item = $itemsResult->fetch_assoc()) {

    // Check current stock for this pizza
    $pizzaName  = $conn->real_escape_string($item['pizza_name']);
    $stockQuery = $conn->query(
        "SELECT stock FROM pizzas WHERE pizza_name = '$pizzaName' LIMIT 1"
    );
    $pizzaRow   = $stockQuery ? $stockQuery->fetch_assoc() : null;
    $stock      = $pizzaRow ? intval($pizzaRow['stock']) : 0;

    $item['current_stock'] = $stock;
    $items[] = $item;

    if ($stock < intval($item['quantity'])) {
        $outOfStock[] = [
            "pizza_name"    => $item['pizza_name'],
            "size"          => $item['size'],
            "cheese"        => $item['cheese'],
            "quantity"      => $item['quantity'],
            "current_stock" => $stock
        ];
    }
}

$conn->close();

// ── 3. RESPOND ───────────────────────────────
echo json_encode([
    "status"       => count($outOfStock) === 0 ? "ok" : "out_of_stock",
    "order"        => $order,
    "items"        => $items,
    "out_of_stock" => $outOfStock
]);
?>