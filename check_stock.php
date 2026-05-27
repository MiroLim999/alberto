<?php
include "db_connect.php";

$order_id = intval($_GET['order_id'] ?? 0);
if (!$order_id) {
    echo json_encode(["status" => "error", "message" => "Invalid order ID"]);
    exit;
}

// ── Order header ──────────────────────────────────────────────
$r = $conn->query("
    SELECT
        o.order_id, o.user_id, o.branch_id, o.address,
        o.order_type, o.payment_method, o.status, o.created_at,
        COALESCE(u.username,      oc.customer_name) AS customer_name,
        COALESCE(u.mobile_number, oc.mobile_number) AS mobile_number,
        COALESCE(u.email,         oc.email)         AS email,
        (SELECT SUM(oi2.quantity * pv2.price)
         FROM order_items oi2
         JOIN pizza_variants pv2 ON oi2.variant_id = pv2.variant_id
         WHERE oi2.order_id = o.order_id)           AS total_amount
    FROM orders o
    LEFT JOIN users          u  ON o.user_id  = u.user_id
    LEFT JOIN order_contacts oc ON o.order_id = oc.order_id
    WHERE o.order_id = $order_id
    LIMIT 1
");
if (!$r || $r->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Order not found"]);
    exit;
}
$order = $r->fetch_assoc();

// ── Items with stock check ────────────────────────────────────
$r = $conn->query("
    SELECT
        oi.item_id, oi.order_id, oi.variant_id, oi.quantity,
        pv.pizza_id, p.pizza_name,
        pv.size, pv.cheese, pv.price,
        (oi.quantity * pv.price) AS total,
        p.stock AS current_stock
    FROM order_items oi
    JOIN pizza_variants pv ON oi.variant_id = pv.variant_id
    JOIN pizzas         p  ON pv.pizza_id   = p.pizza_id
    WHERE oi.order_id = $order_id
");

$items = [];
$outOfStock = [];
while ($item = $r->fetch_assoc()) {
    $items[] = $item;
    if ((int)$item['current_stock'] < (int)$item['quantity']) {
        $outOfStock[] = [
            "pizza_name"    => $item['pizza_name'],
            "size"          => $item['size'],
            "cheese"        => $item['cheese'],
            "quantity"      => $item['quantity'],
            "current_stock" => $item['current_stock'],
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
